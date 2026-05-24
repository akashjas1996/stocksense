# StockSense

A mobile-first home inventory app for tracking household groceries and pantry items. Built for families — scan a QR code on any room or container to instantly see what's inside, log usage, and get notified before things expire.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-ready-D97706)
![License](https://img.shields.io/badge/license-MIT-green)

---

## Features

- **Room → Container → Item hierarchy** — organise stock by where it physically lives; items can also sit loose in a room without a container
- **QR codes** — print and stick a QR on any room or container; scanning opens a public page showing everything inside, no login needed
- **Barcode scanning** — scan a product barcode to auto-fill the item name and fetch its image from Open Food Facts
- **Item catalog** — master list of items with multilingual names (English + Hindi/Hinglish) and product images; auto-fetched from Open Food Facts or set manually
- **Expiry tracking** — items turn amber when expiring soon and red when expired; alerts on the dashboard and location pages
- **Consumption log** — log how much you used; history survives even if the inventory entry is deleted (denormalised log)
- **Charts** — 14-day bar chart of daily consumption, top-8 doughnut chart, per-item 30-day trend
- **Live search** — search by item name, English name, or Hindi name from the dashboard; shows exact room → container location
- **Explore view** — visual "mall walk" through all rooms and containers; items shown as product cards with a quantity fill bar
- **PWA** — installable on iPhone via Safari "Add to Home Screen"; runs full-screen with no browser chrome, 30-day session
- **Mobile-first design** — warm/earthy colour palette, floating pill bottom nav, amber progress bar on navigation, no iOS input zoom

---

## Tech stack

| Layer | Choice |
|---|---|
| Language | PHP 8.2 (no framework) |
| Database | MySQL 8 via PDO |
| Routing | Custom front-controller (`public/index.php`) |
| CSS | Custom design system (CSS variables, no Bootstrap CSS) |
| Icons | Bootstrap Icons CDN |
| Font | Nunito (Google Fonts) |
| Barcode / QR scanning | html5-qrcode |
| Charts | Chart.js 4 |
| QR generation | qrcode.js |
| Product data | Open Food Facts API (free, no key) |
| PWA | Web App Manifest + Apple meta tags |

---

## Project structure

```
stocksense/
├── config/
│   ├── config.example.php   # copy to config.php and fill in your values
│   └── config.php           # gitignored — never committed
├── database/
│   ├── schema.sql           # create all tables
│   ├── migrate_consumption_log.sql
│   ├── seed.php             # optional sample inventory data
│   ├── seed_item_names.php  # optional multilingual item names
│   └── seed_expiry.php      # optional expiry dates
├── public/                  # document root
│   ├── index.php            # front controller / router
│   ├── manifest.php         # PWA manifest
│   ├── css/app.css
│   ├── js/app.js
│   └── .htaccess
└── src/
    ├── Controllers/
    ├── Helpers/
    │   ├── db.php           # singleton PDO connection
    │   └── functions.php    # helpers: formatWeight, expiryStatus, itemEmoji …
    └── Views/
        ├── layouts/
        │   ├── app.php      # authenticated layout with bottom nav
        │   └── public.php   # public layout (QR landing pages)
        └── …
```

---

## Setup

### Requirements

- PHP 8.1+ with PDO, PDO_MySQL, and GD extensions
- MySQL 8.0+
- Apache with `mod_rewrite` enabled (or Nginx equivalent)

### 1. Clone and configure

```bash
git clone https://github.com/your-username/stocksense.git
cd stocksense
cp config/config.example.php config/config.php
```

Edit `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'stocksense');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'StockSense');
define('APP_URL',  'https://yourdomain.com');   // no trailing slash

define('SESSION_LIFETIME', 60 * 60 * 24 * 30); // 30 days
define('EXPIRY_WARN_DAYS', 7);
```

### 2. Create the database

```bash
mysql -u your_db_user -p < database/schema.sql
```

If you're upgrading from an earlier version that doesn't have the extended consumption log columns:

```bash
mysql -u your_db_user -p your_db_name < database/migrate_consumption_log.sql
```

### 3. Set the document root

Point your web server's document root to the `public/` subdirectory, not the repo root.

**Apache** (virtual host):
```apache
DocumentRoot /path/to/stocksense/public
<Directory /path/to/stocksense/public>
    AllowOverride All
</Directory>
```

**Nginx**:
```nginx
root /path/to/stocksense/public;
index index.php;
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 4. Generate PWA icons (one-time)

```bash
php database/generate_icons.php
```

Creates `public/icons/icon-192.png`, `icon-512.png`, and `apple-touch-icon.png`.

### 5. Register and start

Open the app in a browser, create your first account, then add rooms and containers.

---

## Installing on iPhone (PWA)

1. Open the app in **Safari**
2. Tap the Share button → **Add to Home Screen**
3. Tap **Add**

The app opens full-screen without browser chrome, exactly like a native app.

---

## How the DB connection works

```
Request
  └── public/index.php
        └── require config/config.php      (defines DB_HOST, DB_USER, DB_PASS …)
              └── src/Helpers/db.php
                    └── db()               (singleton — one PDO connection per request)
                          └── MySQL server (DB_HOST:3306)
```

`config/config.php` lives only on the server — it is gitignored and never committed. Each environment has its own copy.

---

## QR code flow

```
Print QR  →  Stick on room/container
Scan QR   →  /scan/location?qr={uuid}
          →  /location/{uuid}             (public, no login required)
          →  Shows all items inside
```

Each room and container has a UUID stored in the database. The QR encodes the full URL. Scanning resolves the UUID to the right location page.

---

## Open Food Facts integration

- **Barcode scan** — when you scan a product barcode, the app queries `world.openfoodfacts.org` by barcode, gets the product name and front image, and pre-fills the add-item form
- **Image auto-fetch** — from the Item Catalog, you can fetch a product image by name (searches OFF by the item's English name, picks the most-scanned result)
- No API key required — Open Food Facts is free and open

---

## License

MIT — do whatever you want with it.
