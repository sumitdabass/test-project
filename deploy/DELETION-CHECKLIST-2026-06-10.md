# Prod deletion checklist — Phase 0 legacy cleanup (2026-06-10)

Remote root: /public_html. Delete via:
  python3 deploy.py --delete <remote paths…> --yes   (after FTP_* env set)

Pages:
- /public_html/index-old.php
- /public_html/index-new.php

Includes:
- /public_html/include/common-head.php
- /public_html/include/header.php
- /public_html/include/header2.php
- /public_html/include/footer.php
- /public_html/include/call-widgets.php
- /public_html/include/form-code.php
- /public_html/include/form-codecopy.php

Legacy JS:
- /public_html/assets/js/ajax-contact.js
- /public_html/assets/js/main.js
- /public_html/assets/js/bootstrap.min.js
- /public_html/assets/js/popper.min.js
- /public_html/assets/js/slick.min.js
- /public_html/assets/js/isotope.pkgd.min.js
- /public_html/assets/js/imagesloaded.pkgd.min.js
- /public_html/assets/js/jquery.appear.min.js
- /public_html/assets/js/jquery.counterup.min.js
- /public_html/assets/js/jquery.magnific-popup.min.js
- /public_html/assets/js/jquery.nice-select.min.js
- /public_html/assets/js/waypoints.min.js
- /public_html/assets/js/vendor/jquery-1.12.4.min.js
- /public_html/assets/js/vendor/modernizr-3.6.0.min.js

Legacy CSS:
- /public_html/assets/css/bootstrap.min.css
- /public_html/assets/css/bundle.css
- /public_html/assets/css/default.css
- /public_html/assets/css/flaticon.css
- /public_html/assets/css/font-awesome.min.css
- /public_html/assets/css/magnific-popup.css
- /public_html/assets/css/nice-select.css
- /public_html/assets/css/slick.css
- /public_html/assets/css/style.css
- /public_html/assets/css/style2.css

Images:
- /public_html/assets/images/call.gif

KEEP (do NOT delete): assets/css/critical.min.css, assets/css/bootstrap5.min.css,
assets/css/bundle.min.css, assets/js/app.js, assets/js/bootstrap.bundle.min.js
