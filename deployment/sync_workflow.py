#!/usr/bin/env python3
"""
Sync IPU n8n workflows between local JSON files and the shared n8n instance.

The n8n server at N8N_BASE_URL is multi-tenant — it hosts KYNE, Casa Mazamiro,
and now IPU. This script ONLY acts on IPU workflows (identified by the workflow
files in ./workflows/*.json, each of which stores its own n8n `id`). It will
never modify workflows it didn't write.

Usage:
    ./sync_workflow.py list                    # list all workflows on the server (safe — read-only)
    ./sync_workflow.py pull <n8n_id>           # download a workflow by its n8n id → workflows/<name>.json
    ./sync_workflow.py push <file>             # upload local file → live (creates if no id, updates if has id)
    ./sync_workflow.py diff <file>             # compare local file to live
    ./sync_workflow.py activate <file>         # activate the workflow on the server
    ./sync_workflow.py deactivate <file>       # deactivate
    ./sync_workflow.py reload <file>           # deactivate + activate (webhook-cache refresh)

Reads N8N_BASE_URL and N8N_API_KEY from the project-root .env (one level up
from this script), falling back to ./.env if that's where you prefer to put them.

Workflow files live in ./workflows/ and each has the shape:
    {"name": "...", "nodes": [...], "connections": {...}, "settings": {...}, "id": "..."}
On a fresh create (push without an id), the returned id is written back into
the file so the next push is an update.
"""
import argparse
import json
import sys
import time
import urllib.request
import urllib.error
from datetime import datetime
from pathlib import Path

HERE = Path(__file__).resolve().parent
PROJECT_ROOT = HERE.parent
WORKFLOWS_DIR = HERE / "workflows"
BACKUP_DIR = HERE / "backups"

# n8n Public API PUT accepts only these fields; everything else is server-managed.
PUT_ALLOWED_FIELDS = {"name", "nodes", "connections", "settings", "staticData"}

# Never touch workflows whose names don't start with this prefix — they belong
# to other tenants (KYNE, Casa Mazamiro, etc.) on the shared instance.
IPU_NAME_PREFIX = "IPU — "


def load_env() -> dict:
    for candidate in (PROJECT_ROOT / ".env", HERE / ".env"):
        if candidate.exists():
            env = {}
            for line in candidate.read_text().splitlines():
                line = line.strip()
                if not line or line.startswith("#") or "=" not in line:
                    continue
                k, v = line.split("=", 1)
                env[k.strip()] = v.strip()
            if env.get("N8N_API_KEY") and env.get("N8N_BASE_URL"):
                return env
    sys.exit("Need N8N_API_KEY and N8N_BASE_URL in project-root .env or deployment/.env")


def api(env, method, path, body=None):
    url = f"{env['N8N_BASE_URL']}{path}"
    data = None
    headers = {"X-N8N-API-KEY": env["N8N_API_KEY"], "Accept": "application/json"}
    if body is not None:
        data = json.dumps(body).encode("utf-8")
        headers["Content-Type"] = "application/json"
    req = urllib.request.Request(url, data=data, method=method, headers=headers)
    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            raw = resp.read().decode("utf-8")
            return resp.status, (json.loads(raw) if raw else None)
    except urllib.error.HTTPError as e:
        raw = e.read().decode("utf-8", errors="replace")
        sys.exit(f"HTTP {e.code} on {method} {path}: {raw[:500]}")


def assert_ipu(name: str) -> None:
    if not name.startswith(IPU_NAME_PREFIX):
        sys.exit(
            f"Refusing to act on workflow '{name}' — it doesn't start with "
            f"'{IPU_NAME_PREFIX}'. This script only manages IPU workflows on "
            f"the shared instance."
        )


def cmd_list(env):
    status, data = api(env, "GET", "/api/v1/workflows?limit=100")
    print(f"{len(data['data'])} workflows on {env['N8N_BASE_URL']}:\n")
    for wf in data["data"]:
        marker = "IPU " if wf["name"].startswith(IPU_NAME_PREFIX) else "    "
        active = "●" if wf.get("active") else "○"
        print(f"  [{marker}] {active} {wf['id']}  {wf['name']}")


def cmd_pull(env, n8n_id):
    status, wf = api(env, "GET", f"/api/v1/workflows/{n8n_id}")
    assert_ipu(wf["name"])
    WORKFLOWS_DIR.mkdir(exist_ok=True)
    # Derive a stable filename from the name, stripping the "IPU — " prefix.
    stub = wf["name"][len(IPU_NAME_PREFIX):].lower().replace(" ", "-").replace("—", "-")
    out = WORKFLOWS_DIR / f"{stub}.json"
    out.write_text(json.dumps(wf, indent=2, ensure_ascii=False))
    print(f"pulled -> {out}")
    print(f"  name   : {wf['name']}")
    print(f"  active : {wf.get('active')}")
    print(f"  nodes  : {len(wf['nodes'])}")


def cmd_push(env, file_path):
    path = Path(file_path).resolve()
    if not path.exists():
        sys.exit(f"no file at {path}")
    try:
        local = json.loads(path.read_text())
    except json.JSONDecodeError as e:
        sys.exit(f"invalid JSON in {path}: {e}")
    name = local.get("name") or ""
    assert_ipu(name)
    for f in ("nodes", "connections"):
        if f not in local:
            sys.exit(f"local workflow missing required field '{f}'")

    BACKUP_DIR.mkdir(exist_ok=True)
    payload = {k: v for k, v in local.items() if k in PUT_ALLOWED_FIELDS}

    if local.get("id"):
        wf_id = local["id"]
        # backup live before PUT
        _, live_before = api(env, "GET", f"/api/v1/workflows/{wf_id}")
        assert_ipu(live_before["name"])
        ts = datetime.now().strftime("%Y%m%d_%H%M%S")
        bkp = BACKUP_DIR / f"{wf_id}_{ts}.json"
        bkp.write_text(json.dumps(live_before, indent=2, ensure_ascii=False))
        print(f"backup -> {bkp}")
        status, data = api(env, "PUT", f"/api/v1/workflows/{wf_id}", payload)
        print(f"updated live  HTTP {status}  updatedAt={data.get('updatedAt')}")
    else:
        # create new
        status, data = api(env, "POST", "/api/v1/workflows", payload)
        local["id"] = data["id"]
        path.write_text(json.dumps(local, indent=2, ensure_ascii=False))
        print(f"created new workflow id={data['id']} (written back to {path})")


def cmd_diff(env, file_path):
    local = json.loads(Path(file_path).read_text())
    if not local.get("id"):
        sys.exit("local workflow has no id — nothing to diff against on the server")
    _, live = api(env, "GET", f"/api/v1/workflows/{local['id']}")
    ln = {n["name"] for n in local["nodes"]}
    vn = {n["name"] for n in live["nodes"]}
    print(f"local nodes: {len(ln)}   live nodes: {len(vn)}")
    only_live = sorted(vn - ln)
    only_local = sorted(ln - vn)
    if only_live:
        print("only on LIVE :", only_live)
    if only_local:
        print("only in LOCAL:", only_local)
    lm = {n["name"]: n for n in local["nodes"]}
    vm = {n["name"]: n for n in live["nodes"]}
    diffs = []
    for name in sorted(ln & vn):
        strip = lambda n: {k: v for k, v in n.items() if k not in ("id", "position", "webhookId")}
        if json.dumps(strip(lm[name]), sort_keys=True) != json.dumps(strip(vm[name]), sort_keys=True):
            diffs.append(name)
    if diffs:
        print(f"nodes with diffs ({len(diffs)}):")
        for n in diffs:
            print(f"  - {n}")
    if json.dumps(local.get("connections", {}), sort_keys=True) != json.dumps(live.get("connections", {}), sort_keys=True):
        print("connections: DIFFER")
    if not only_live and not only_local and not diffs:
        print("local and live are in sync")


def cmd_activate(env, file_path, active=True):
    local = json.loads(Path(file_path).read_text())
    assert_ipu(local.get("name") or "")
    if not local.get("id"):
        sys.exit("workflow has no id — push it first")
    verb = "activate" if active else "deactivate"
    api(env, "POST", f"/api/v1/workflows/{local['id']}/{verb}")
    print(f"{verb}d {local['name']}")


def cmd_reload(env, file_path):
    cmd_activate(env, file_path, active=False)
    time.sleep(0.5)
    cmd_activate(env, file_path, active=True)
    print("reload complete")


def main():
    p = argparse.ArgumentParser(description="Sync IPU n8n workflows")
    sp = p.add_subparsers(dest="cmd", required=True)
    sp.add_parser("list", help="list all workflows on the server (IPU marker shown)")
    pull = sp.add_parser("pull", help="download a workflow by n8n id")
    pull.add_argument("id", help="n8n workflow id")
    push = sp.add_parser("push", help="upload local -> live (create or update)")
    push.add_argument("file", help="path to workflow JSON file")
    diff = sp.add_parser("diff", help="compare local file vs live")
    diff.add_argument("file")
    act = sp.add_parser("activate", help="activate a workflow")
    act.add_argument("file")
    deact = sp.add_parser("deactivate", help="deactivate a workflow")
    deact.add_argument("file")
    rel = sp.add_parser("reload", help="deactivate + activate (webhook cache refresh)")
    rel.add_argument("file")
    args = p.parse_args()

    env = load_env()
    if args.cmd == "list":
        cmd_list(env)
    elif args.cmd == "pull":
        cmd_pull(env, args.id)
    elif args.cmd == "push":
        cmd_push(env, args.file)
    elif args.cmd == "diff":
        cmd_diff(env, args.file)
    elif args.cmd == "activate":
        cmd_activate(env, args.file, active=True)
    elif args.cmd == "deactivate":
        cmd_activate(env, args.file, active=False)
    elif args.cmd == "reload":
        cmd_reload(env, args.file)


if __name__ == "__main__":
    main()
