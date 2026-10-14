#!/usr/bin/env bash
# Restore the live files saved by deploy-stage1 (pass the backup directory it printed).
set -euo pipefail
BK="${1:?usage: rollback-stage1.sh <backup dir>}"
SSH="ssh -i $HOME/.ssh/davyas-active -o BatchMode=yes"
cd "$BK"
# files that existed before are restored; files that did not exist (faq-details.php, college-page-v2.php) are harmless and left in place
find . -type f ! -name '.DS_Store' | sed 's#^\./##' | while read -r f; do
  rsync -aR -e "$SSH" "$f" ipuc@ipu.co.in:public_html/ 2> >(grep -v 'post-quantum\|store now\|server may need' >&2)
  echo "restored $f"
done
echo "Rolled back from $BK. Re-run:  bash docs/ops/deploy-stage1-2026-10-14.sh --verify   (BPIT/dme checks should now fail, others pass)"
