## Troubleshooting local environment

- PHP startup warning about `php_imagick.dll`:
  - Edit your `php.ini` (XAMPP: `C:\xampp\php\php.ini`) and comment out the line that enables `php_imagick.dll`, e.g. `;extension=php_imagick.dll`, then restart Apache.

- MySQL connection refused / long page load on first request:
  - Ensure MySQL/MariaDB service is running in XAMPP.
  - For local development use `127.0.0.1` as `db_host` in `app/config.php` (already set by default) and set `db_persistent` to `false` to avoid persistent connection issues on Windows.

- Asset 404s when app is served under a subpath:
  - Use relative paths for internal links and assets (changes were applied to `views/header.php` and `views/footer.php`).

To download Bootstrap locally (run from project root):

PowerShell:
```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\download-bootstrap.ps1
```

Or manually with curl:
```powershell
mkdir public\assets
curl.exe -L -o public\assets\bootstrap.min.css https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css
curl.exe -L -o public\assets\bootstrap.bundle.min.js https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js
```

After downloading, the app will automatically prefer the local files.
