# Twitter-Style Stealth Shortlink 🚀

A lightweight, single-file PHP shortlink system with a "Twitter/X" style loading animation. Designed for stealth, simplicity, and ease of use. No database setup required.

![PHP](https://img.shields.io/badge/PHP-%3E%3D%207.4-777bb4?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)

## ✨ Features

* **Single File Architecture:** The entire system (Admin Panel, Database Logic, Frontend) lives in one `index.php` file.
* **Auto-Installer:** Automatically generates the necessary `.htaccess` file for clean URLs upon first run.
* **Twitter-Style Loader:** Features a realistic "Checking browser..." animation similar to Twitter/X t.co links.
* **Stealth Mode:**
    * Direct access to the folder redirects to a fallback URL (e.g., a movie site).
    * Invalid links redirect to a fallback URL.
    * Admin panel is hidden behind a secret query parameter.
* **Clean URLs:** Generates links like `domain.com/v/AbCd1` instead of `?id=AbCd1`.
* **JSON Database:** No MySQL required. Uses a flat-file JSON database that is automatically created.
* **Modern Admin UI:** Dark mode, glassmorphism design, and auto-copy functionality.

## 🛠️ Installation

1.  Download the `index.php` file from this repository.
2.  Upload it to any folder on your PHP server (e.g., `public_html/v/` or `public_html/go/`).
3.  **Done!** The script creates the database and `.htaccess` file automatically.

## ⚙️ Configuration

Open `index.php` and edit the configuration block at the top:

```php
// --- CONFIGURATION ---
$admin_password   = "12345";                 // CHANGE THIS IMMEDIATELY
$secret_path      = "panel";                 // Admin URL: [domain.com/folder/?panel](https://domain.com/folder/?panel)
$fallback_url     = "[https://google.com](https://google.com)";    // Redirect for invalid/direct access
$loading_duration = 3;                       // Loading time in seconds
