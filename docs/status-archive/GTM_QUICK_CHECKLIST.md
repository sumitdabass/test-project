# ⚡ QUICK REFERENCE — GTM Setup Checklist

**Print this out or keep it open while implementing GTM changes**

---

## Pre-Implementation
- [ ] Read GTM_SETUP_GUIDE.md (10 min)
- [ ] Login to Google Tag Manager: GTM-5GXCN7Z
- [ ] Open Google Ads in another tab (for verification)

---

## TASK 1: Create Phone Click Tag (5 minutes)

**Location:** GTM → Tags → New

**Configuration:**
- [ ] Tag Name: `ADS - Phone Click Conversion (cPhqCM...)`
- [ ] Type: Google Ads Conversion
- [ ] Conversion ID: `10900888879`
- [ ] Label: `cPhqCMizhZIYEK-6-c0o`
- [ ] Value: (leave blank)
- [ ] Currency: (leave blank)

**Trigger:**
- [ ] Create New Trigger
- [ ] Name: `Link Click - Tel URL`
- [ ] Type: Click - Just Links
- [ ] Wait for Tags: ✓ Checked
- [ ] Validation: ✓ Checked
- [ ] Condition: Click URL | contains | `tel:9899991342`
- [ ] Save Trigger

- [ ] Save Tag
- [ ] ✓ Complete

---

## TASK 2: Remove Redundant Labels (3 minutes)

**Location:** GTM → Tags

**Find and DELETE these tags:**
- [ ] ❌ `hZSHCIK7x_wbEK-6-c0o` (page_view on thank-you)
- [ ] ❌ `1jUiCIWo1rkaEK-6-c0o` (page_view on thank-you)

**KEEP:**
- [ ] ✓ `YU9JCMP9m74DEK-6-c0o` (enquiry conversion)

- [ ] ✓ Complete

---

## TASK 3: Update WCM Config (2 minutes)

**Option A: Edit Existing Variable**
- [ ] Go to GTM → Variables → User-Defined Variables
- [ ] Find: WCM Config OR phone_conversion_number
- [ ] Change: `cc=ZZ` → `cc=IN`
- [ ] Save

**Option B: Create New Tag**
- [ ] GTM → Tags → New
- [ ] Name: `WCM - Phone Config (cc=IN)`
- [ ] Type: Custom HTML
- [ ] Paste code:
```html
<script>
gtag('config', 'AW-10900888879/LvVmCJeut7IaEK-6-c0o', {
  'phone_conversion_number': '9899991342',
  'cc': 'IN'
});
</script>
```
- [ ] Trigger: All Pages (Page View)
- [ ] Save

- [ ] ✓ Complete

---

## TASK 4: Link GA4 to Google Ads (3 minutes)

**Location:** Google Ads (NOT GTM)

- [ ] Go to Google Ads
- [ ] Left sidebar → Conversions
- [ ] Click blue **Import** button
- [ ] Source: Google Analytics 4
- [ ] Select events:
  - [ ] ✓ `sign_up` (form submission)
  - [ ] ✓ `thankyou_page_view` or `page_view` (thank you page)
- [ ] Category: Lead
- [ ] Attribution: First Click
- [ ] Save

- [ ] ✓ Complete

---

## POST-IMPLEMENTATION

### Publish GTM
- [ ] GTM → Click **Submit** (top right)
- [ ] Version name: "Audit Fixes - Double-count Removal"
- [ ] Description: "Removed duplicate scripts, unified phone tracking"
- [ ] Publish

### Wait
- ⏱️ Wait 10 minutes for GTM to propagate globally

### Test
- [ ] Hard refresh homepage: Cmd+Shift+R
- [ ] Open DevTools → Network tab
- [ ] Click mobile call button
- [ ] Look for request to `googleadservices.com` OR `google-analytics.com`
- [ ] ✓ GTM should fire the phone conversion tag

### Monitor
- [ ] Open Google Ads → Conversions dashboard
- [ ] Watch for new data in next 30 minutes
- [ ] Expected conversions:
  - Phone clicks: `cPhqCMizhZIYEK-6-c0o`
  - Form submissions: `YU9JCMP9m74DEK-6-c0o`

---

## Troubleshooting

**Problem: GTM changes not visible**
- [ ] Clear browser cache: Cmd+Shift+Del
- [ ] Hard refresh: Cmd+Shift+R
- [ ] Disable cache (DevTools → Network → Disable cache)
- [ ] Try in Incognito window

**Problem: Conversion tags not firing**
- [ ] Enable GTM Preview Mode (test → preview)
- [ ] Install Tag Assistant Chrome extension
- [ ] Verify GTM container ID: GTM-5GXCN7Z
- [ ] Check trigger conditions

**Problem: Can't find certain tags in GTM**
- [ ] Use GTM search (Ctrl+F in GTM)
- [ ] Check all tags (not just conversion tags)
- [ ] Verify you're in the correct container (GTM-5GXCN7Z)

---

## Success Indicators ✓

After all tasks complete:
- [ ] Phone click tag firing on tel: links
- [ ] Enquiry conversion firing once (not twice)
- [ ] GA4 events imported in Google Ads
- [ ] WCM config set to cc=IN
- [ ] No redundant page_view conversions

---

## Key Phone Numbers

**Important Labels (Save these)**

| Purpose | Label | Where |
|---------|-------|-------|
| Phone Clicks | `cPhqCMizhZIYEK-6-c0o` | GTM tag |
| Form Enquiry | `YU9JCMP9m74DEK-6-c0o` | GTM tag |
| WCM Calls | `LvVmCJeut7IaEK-6-c0o` | GTM tag |

---

## Time Estimate

| Task | Time |
|------|------|
| Task 1: Phone tag | 5 min |
| Task 2: Remove tags | 3 min |
| Task 3: WCM config | 2 min |
| Task 4: GA4 import | 3 min |
| Publish | 2 min |
| **Total** | **15 min** |

---

## Questions?

- **Setup Help:** GTM_SETUP_GUIDE.md (detailed instructions + screenshots)
- **Status:** CONVERSION_AUDIT_STATUS.md (what changed)
- **Code:** CODE_CHANGES_SUMMARY.md (technical details)

---

**Checklist Version:** 1.0  
**Date:** 24 March 2026  
**Account:** AW-10900888879 | GTM-5GXCN7Z  
**Contact:** Sumit | 9899991342
