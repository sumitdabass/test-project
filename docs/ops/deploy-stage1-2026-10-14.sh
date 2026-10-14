#!/usr/bin/env bash
# Stage 1 deploy for the ipu.co.in template unification (prepared 2026-10-14).
# Ships: shared foundation (base-head/page-hero/cta-strip/faq-details), BPIT pilot, college template v2 + dme canary,
#        AI-file claim removals (agent-data.php, ai.json, llms.txt patched from the LIVE copy).
# NOT shipped: sitemap.xml, .htaccess, news files, other college pages (their data files still lack the v2 flag), legacy pages.
# Usage:  bash docs/ops/deploy-stage1-2026-10-14.sh            # deploy + verify
#         bash docs/ops/deploy-stage1-2026-10-14.sh --verify    # verify only (read-only)
set -euo pipefail
REPO="$(cd "$(dirname "$0")/../.." && pwd)"
WEB="$REPO/website_download"
KEY="$HOME/.ssh/davyas-active"
HOST="ipuc@ipu.co.in"
SSH="ssh -i $KEY -o BatchMode=yes"
BACKUPS="$HOME/ipu-prod-backups"
SITE="https://ipu.co.in"
MODE="${1:-deploy}"
UA="Mozilla/5.0 (compatible; ipu-deploy-verify)"

FILES_NEW=(include/components/faq-details.php include/templates/college-page-v2.php)
FILES_SHARED=(include/base-head.php include/components/page-hero.php include/components/cta-strip.php)
FILES_TEMPLATE=(include/templates/college-page-template.php)
FILES_PAGES=(BPIT.php dme-admission.php api/agent-data.php .well-known/ai.json)
LLMS_PATCHED="$BACKUPS/llms_live_patched.txt"   # live llms.txt with only the 3 unsourced claims removed (keeps CI's news block)

q() { grep -v 'post-quantum\|store now\|server may need' || true; }

# name of the CollegeOrUniversity JSON-LD block of a page (python parses the JSON-LD)
ld_name() {
  curl -s -A "$UA" "$SITE/$1" | python3 -c '
import sys, re, json, html
h = sys.stdin.read()
for x in re.findall(r"<script type=\"application/ld\+json\">(.*?)</script>", h, re.S):
    try: b = json.loads(x)
    except Exception: continue
    if b.get("@type") == "CollegeOrUniversity": print(html.unescape(b.get("name", ""))); break
'
}

verify() {
  set +o pipefail          # 'curl | grep -q' would otherwise fail on SIGPIPE when grep exits early
  local fail=0
  chk() { if eval "$2"; then echo "  ok   $1"; else echo "  FAIL $1"; fail=1; fi; }
  echo "== verification against $SITE =="
  for p in BPIT.php dme-admission.php mait-admission.php cuet-admission-ipu.php IPU-B-Tech-admission-2026.php mba-admission-ip-university.php index.php ipu-counselling.php; do
    body=$(curl -s -A "$UA" "$SITE/$p"); code=$(curl -s -o /dev/null -w '%{http_code}' -A "$UA" "$SITE/$p")
    chk "$p returns 200 and no PHP errors" "[ '$code' = 200 ] && ! grep -qE '(Fatal error|Parse error|Warning:|Notice:)' <<<\"\$body\""
  done
  bpit=$(curl -s -A "$UA" "$SITE/BPIT.php"); dme=$(curl -s -A "$UA" "$SITE/dme-admission.php")
  chk "BPIT is on the unified grid (ipu-body)"            "grep -q 'ipu-body' <<<\"\$bpit\""
  chk "BPIT has exactly one H1"                           "[ \$(grep -o '<h1' <<<\"\$bpit\" | wc -l) -eq 1 ]"
  chk "BPIT has exactly one enquiry form"                 "[ \$(grep -o 'id=\"enquiry-form\"' <<<\"\$bpit\" | wc -l) -eq 1 ]"
  chk "dme is on v2 with exactly one H1"                  "grep -q 'ipu-body' <<<\"\$dme\" && [ \$(grep -o '<h1' <<<\"\$dme\" | wc -l) -eq 1 ]"
  chk "dme H1 has no double-escaped ampersand"            "! grep -q '&amp;amp;' <<<\"\$dme\""
  chk "dme schema name is the real college name"          "[ \"\$(ld_name dme-admission.php)\" = 'Delhi Metropolitan Education' ]"
  chk "another college page (mait) still renders (v1)"    "curl -s -A \"$UA\" $SITE/mait-admission.php | grep -q '<h1'"
  chk "a college-template v1 page still renders"          "curl -s -A \"$UA\" $SITE/gtb4cec-admission.php | grep -q '<h1'"
  chk "llms.txt keeps its news block, claims removed"     "t=\$(curl -s -A \"$UA\" $SITE/llms.txt); grep -q '/news/' <<<\"\$t\" && ! grep -qE '99% admission|5000\\+ students' <<<\"\$t\""
  chk "agent-data MSIT answer corrected"                  "curl -s -A \"$UA\" '$SITE/api/agent-data.php?action=faq' | grep -q '6,832'"
  chk "agent-data stats claims removed"                   "! curl -s -A \"$UA\" '$SITE/api/agent-data.php?action=stats' | grep -q 'admission_success_rate'"
  chk "ai.json is valid JSON without the claims"          "curl -s -A \"$UA\" $SITE/.well-known/ai.json | python3 -c 'import sys,json; d=json.load(sys.stdin); assert \"admission_success_rate\" not in d[\"expertise_signals\"]'"
  chk "cta-strip uses the new orange pill (gtb4cec)"      "curl -s -A \"$UA\" $SITE/gtb4cec-admission.php | grep -q 'ipu-cta-call'"
  set -o pipefail
  return $fail
}

if [ "$MODE" = "--verify" ]; then verify; exit $?; fi

echo "== 1. drift check: live files must equal the last-deployed git state (6b8955b) =="
for f in "${FILES_SHARED[@]}" "${FILES_TEMPLATE[@]}" "${FILES_PAGES[@]}"; do
  if ! git -C "$REPO" show "6b8955b:website_download/$f" | cmp -s - <($SSH $HOST "cat public_html/$f" 2> >(q >&2)); then
    echo "DRIFT in $f - live differs from git. STOP and investigate."; exit 2
  fi
done
echo "  no drift"

echo "== 2. fresh backup of live files =="
BK="$BACKUPS/pre-deploy-$(date +%Y%m%d-%H%M%S)"; mkdir -p "$BK"
for f in "${FILES_NEW[@]}" "${FILES_SHARED[@]}" "${FILES_TEMPLATE[@]}" "${FILES_PAGES[@]}" llms.txt; do
  mkdir -p "$BK/$(dirname "$f")"; $SSH $HOST "cat public_html/$f" > "$BK/$f" 2> >(q >&2) || rm -f "$BK/$f"
done
echo "  backup in $BK"

echo "== 3. upload (new files, shared includes, template, pages, llms.txt) =="
cd "$WEB"
rsync -aR -e "$SSH" "${FILES_NEW[@]}"      "$HOST:public_html/" 2> >(q >&2)
rsync -aR -e "$SSH" "${FILES_SHARED[@]}"   "$HOST:public_html/" 2> >(q >&2)
rsync -aR -e "$SSH" "${FILES_TEMPLATE[@]}" "$HOST:public_html/" 2> >(q >&2)
rsync -aR -e "$SSH" "${FILES_PAGES[@]}"    "$HOST:public_html/" 2> >(q >&2)
rsync -a  -e "$SSH" "$LLMS_PATCHED"        "$HOST:public_html/llms.txt" 2> >(q >&2)
echo "  uploaded"

echo "== 4. verify =="
if verify; then echo; echo "ALL CHECKS PASSED. Rollback if ever needed: bash docs/ops/rollback-stage1.sh $BK"; else
  echo; echo "SOME CHECKS FAILED. Roll back now:  bash docs/ops/rollback-stage1.sh $BK"; exit 1; fi
