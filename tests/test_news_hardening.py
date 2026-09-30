import importlib.util, sys, tempfile
from pathlib import Path
import yaml

ROOT = Path(__file__).resolve().parents[1]


def load(name, rel):
    spec = importlib.util.spec_from_file_location(name, ROOT / rel)
    mod = importlib.util.module_from_spec(spec)
    sys.modules[name] = mod
    spec.loader.exec_module(mod)
    return mod


# ---- scraper ----------------------------------------------------------------
def test_unknown_category_is_coerced_to_general():
    sc = load("news_scraper", "automation/news-scraper.py")
    assert sc.coerce_category("Results") == "Results"
    assert sc.coerce_category("counselling") == "Counselling"      # case-insensitive match, canonical spelling out
    assert sc.coerce_category("Breaking Gossip") == "General"
    assert sc.coerce_category("") == "General" and sc.coerce_category(None) == "General"


def test_exit_code_fails_only_when_everything_errored():
    sc = load("news_scraper", "automation/news-scraper.py")
    assert sc.exit_code(errors=[("x", "e")], written=[]) == 1
    assert sc.exit_code(errors=[("x", "e")], written=["a.md"]) == 0
    assert sc.exit_code(errors=[], written=[]) == 0


# ---- upload_news.py: sync deletion guards -------------------------------------
class FakeFTP:
    def __init__(self, names):
        self.names, self.deleted = names, []
    def cwd(self, _): pass
    def retrlines(self, _cmd, cb):
        for n in self.names: cb(n)
    def delete(self, path): self.deleted.append(path)


def _local(n):
    d = Path(tempfile.mkdtemp())
    for i in range(n): (d / f"p{i}.php").write_text("x")
    return d


def test_sync_refuses_when_almost_no_local_posts():
    up = load("upload_news", "upload_news.py")
    ftp = FakeFTP([f"remote{i}.php" for i in range(50)])
    assert up.sync_delete_remote_orphans(ftp, "/public_html/news", _local(2)) == []
    assert ftp.deleted == []


def test_sync_refuses_mass_deletion_but_allows_normal_cleanup():
    up = load("upload_news", "upload_news.py")
    local = _local(5)
    # 5 kept + 40 orphans on a 45-file remote = 89% deletion -> refused
    mass = FakeFTP([f"p{i}.php" for i in range(5)] + [f"old{i}.php" for i in range(40)])
    assert up.sync_delete_remote_orphans(mass, "/public_html/news", local) == []
    assert mass.deleted == []
    # 5 kept + 2 orphans -> normal cleanup still works
    ok = FakeFTP([f"p{i}.php" for i in range(5)] + ["old1.php", "old2.php"])
    assert sorted(up.sync_delete_remote_orphans(ok, "/public_html/news", local)) == ["/public_html/news/old1.php", "/public_html/news/old2.php"]


# ---- workflows ------------------------------------------------------------------
def wf(name):
    return yaml.safe_load((ROOT / ".github/workflows" / name).read_text())


def test_workflows_are_serialised_and_alert_on_failure():
    for name in ("news-build-deploy.yml", "news-scrape.yml"):
        w = wf(name)
        assert w["concurrency"]["group"] == "ipu-deploy" and w["concurrency"]["cancel-in-progress"] is False, name
        job = next(iter(w["jobs"].values()))
        assert any(s.get("if") == "failure()" for s in job["steps"]), f"{name}: no failure alert step"


def test_deploy_job_uses_the_production_deploy_environment():
    job = wf("news-build-deploy.yml")["jobs"]["build-and-deploy"]
    assert job["environment"] == "production-deploy"
