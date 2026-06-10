import subprocess, sys, os
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def run(args, env=None):
    e = dict(os.environ); e.update(env or {})
    return subprocess.run([sys.executable, str(ROOT / "deploy.py"), *args],
                          capture_output=True, text=True, env=e)

def test_dry_run_lists_files_without_credentials():
    r = run(["--files", "website_download/index.php", "--dry-run"],
            env={"FTP_HOST": "", "FTP_USER": "", "FTP_PASS": ""})
    assert r.returncode == 0, r.stderr
    assert "index.php" in r.stdout
    assert "dry-run" in r.stdout.lower()

def test_missing_local_file_fails_preflight():
    r = run(["--files", "website_download/does-not-exist.php", "--dry-run"])
    assert r.returncode == 1
    assert "missing" in (r.stdout + r.stderr).lower()

def test_real_deploy_requires_credentials():
    r = run(["--files", "website_download/index.php"],
            env={"FTP_HOST": "", "FTP_USER": "", "FTP_PASS": ""})
    assert r.returncode == 2
    assert "FTP_" in (r.stdout + r.stderr)

def test_delete_requires_explicit_flag_and_confirm(tmp_path):
    r = run(["--manifest", "/dev/null", "--delete", "website_download/x.php"],
            env={"DEPLOY_NONINTERACTIVE": "1"})
    assert r.returncode == 2
    assert "confirm" in (r.stdout + r.stderr).lower()
