# IPU n8n Deployment

This folder houses the sync tooling and workflow definitions for IPU's n8n automation (daily news scraper, rebuild-single, unpublish-by-slug, suggest-edit, weekly summary).

## Shared instance — multi-tenant

**Host:** `https://n8n.srv1117424.hstgr.cloud`

This n8n instance is shared with other projects (KYNE, Casa Mazamiro, and more — ~58 workflows as of April 2026). **Every IPU workflow name MUST start with `IPU — `** (em-dash, no hyphens) so it's distinguishable in the n8n UI from other tenants' workflows.

`sync_workflow.py` enforces this: it refuses to `push`, `activate`, or `deactivate` any workflow whose name doesn't start with `IPU — `. The `pull` command also asserts the fetched workflow is IPU-prefixed before saving.

**Do not edit KYNE or other tenants' workflows from this repo. Ever.**

## Credentials

Read from `../.env` (project root) or `./.env`. Required:

```
N8N_BASE_URL=https://n8n.srv1117424.hstgr.cloud
N8N_API_KEY=<JWT from n8n Settings → API>
```

The JWT key expires 2026-07-13. Regenerate in the n8n UI when it lapses.

`.env` is gitignored.

## Usage

```bash
./sync_workflow.py list                    # all workflows on server; IPU ones marked
./sync_workflow.py pull <n8n_id>           # live → workflows/<stub>.json
./sync_workflow.py push workflows/foo.json # local → live (creates if new, updates if has id)
./sync_workflow.py diff workflows/foo.json # compare
./sync_workflow.py activate workflows/foo.json
./sync_workflow.py deactivate workflows/foo.json
./sync_workflow.py reload workflows/foo.json   # deactivate + activate; needed after PUT on live workflows with webhook nodes
```

## Files

```
deployment/
├── README.md              — this file
├── sync_workflow.py       — multi-workflow sync CLI
├── workflows/             — committed JSON workflow definitions (populated in Tasks 18–22)
└── backups/               — auto-saved on every push (gitignored)
```

## Webhook-reload gotcha

When a live workflow contains webhook trigger nodes, an n8n API PUT does NOT refresh the in-memory webhook handler. The editor shows the new nodes, but inbound webhook traffic keeps using the old cached compiled version. Always follow a push with `./sync_workflow.py reload <file>` whenever webhook-node params or structure changed.
