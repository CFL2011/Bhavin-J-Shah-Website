# Bhavin J. Shah Website — Setup & Admin Guide

## File su che (folder ma)
- `index.html`        → website (aa j live thashe)
- `content.json`      → photos & contact ni saved details (auto banshe/badlashe)
- `admin/index.php`   → ADMIN PANEL (login + photo change)
- `uploads/`          → admin mathi upload karel badhi photos ahi save thashe

---

## 1) Website LIVE kevi rite karvi (Hostinger / cPanel)

1. Hostinger ma login → **hPanel** → **File Manager** kholo.
2. `public_html` folder ma jao (juni demo file hoy to delete kari do).
3. Aa 4 vastu UPLOAD karo (badhu sathe):
   - `index.html`
   - `content.json`
   - `admin` (aakho folder)
   - `uploads` (aakho folder)
4. `uploads` folder par right-click → **Permissions** → **755** set karo
   (jethi admin photo save kari shake).
5. Have website live: `https://tamaru-domain.com`

> Domain na hoy to Hostinger ma "Add Domain" mathi jodi shako, athva temporary
> Hostinger preview URL thi pan chale.

---

## 2) Admin Panel kevi rite vaprvu

- Address: `https://tamaru-domain.com/admin/`
- Password: **`bjs@admin123`**  ← (aa badalvu jaruri, niche juo)

### Photo badalva:
1. Admin ma login karo.
2. Je jagya ni photo badalvi hoy tya **"Choose File"** → photo pasand karo.
3. **"Update"** dabavo → photo turant live site par badhane dekhashe.
4. Photo kaadi nakhvi hoy to **"Remove"**.

### Contact details (phone / WhatsApp / email):
- "Contact Details" ma lakhi ne **"Save"** dabavo.

### Video & Social links:
- "Video & Social Links" ma YouTube / Instagram / Facebook / LinkedIn ane
  video ni full link paste karo → **"Save Links"**.
- Site par e box clickable thai jashe (click karta video/page khulshe).

### Website texts:
- "Website Texts" ma hero intro ane footer text badli shako → **"Save Texts"**.

### Google Map:
- Google Maps → Share → "Embed a map" → HTML code copy → "Google Map" box ma
  paste → **"Save Map"**. Contact page par map dekhashe.

---

### Enquiries (form submissions) & Data anywhere:
- Website na contact/corporate/newsletter form mathi aavel badhi enquiry
  admin panel ni "Enquiries" section ma dekhashe.
- **Export CSV** button thi download kari Google Sheet / Excel ma kholo.
- **Send Data Anywhere (Integrations)**: ahi webhook URL paste karo etle
  dareke nava enquiry e jagya e auto pahochi jashe:
  - Google Sheet → Sheety / SheetDB / Zapier / Make ni webhook URL
  - Notion → Zapier ke Make ni "Catch Hook" URL
  - Biji koi platform → temni webhook URL
- Email notification pan set kari shako (hosting par mail support hovu joie).

> NOTE: `data/` folder ma enquiries save thay che ane e public mate blocked che
> (`.htaccess`). Upload karta `data/` folder ni permission **755** rakho.

---

## 3) Password badalvu (IMPORTANT - security)

`admin/index.php` file kholo, line ~12 par:

```php
$ADMIN_PASSWORD = "bjs@admin123";
```

ahi `bjs@admin123` ni jagya e tamaru navu strong password lakho ane save karo.

---

## Photo slots list (admin ma j name dekhashe)
Home Hero, Home Meet, About Portrait, Life Coaching, Business Coaching,
Unleash Potential, Business Growth Poster, Corporate Session, Blog Cover 1/2/3.

---

## Local testing (computer par)
- Website joeva: `python -m http.server 8000` → `http://127.0.0.1:8000`
- **Admin panel local par nahi chale** (PHP joie). Admin panel fakt Hostinger
  jeva PHP hosting par j chale. Local par fakt website (index.html) dekhi shako.
