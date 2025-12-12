Setup VirtualHost Script

This script automates adding a local VirtualHost for finanzas.local on Windows XAMPP.

Usage (Windows PowerShell, Run as Administrator):

1. Open PowerShell as Administrator.
2. Change directory to the project repository root (C:\xampp\htdocs\finanzas):

```powershell
cd C:\xampp\htdocs\finanzas
```

3. Run the script:

```powershell
.\scripts\setup-virtualhost.ps1
```

4. If your Apache installation path or project path is different, pass parameters:

```powershell
.\scripts\setup-virtualhost.ps1 -ProjectPath "D:\repos\finanzas" -ApachePath "C:\xampp\apache" -HostName "finanzas.local"
```

To undo the changes made by the virtualhost script (remove hosts entry and copied vhost):

```powershell
.\scripts\setup-virtualhost.ps1 -Remove
```

Notes:
- The script requires Administrator privileges to modify `hosts` and Apache config.
- It attempts to restart the Apache service named `Apache2.4`; if Apache runs via XAMPP control panel, restart the service using the panel after running the script.
- This script performs changes to your system and assumes a standard XAMPP layout. Review the script before running.

HTTPS mkcert Script
--------------------

This script automates mkcert-based certificate generation and copies the SSL VirtualHost into Apache.

Usage (Run as Administrator):
```powershell
.\scripts\setup-https-mkcert.ps1
```

Pass parameters if needed:
```powershell
.\scripts\setup-https-mkcert.ps1 -ProjectPath "D:\repos\finanzas" -ApachePath "C:\xampp\apache" -HostName "finanzas.local"
```

To remove generated certificates and copied SSL vhost configuration:
```powershell
.\scripts\setup-https-mkcert.ps1 -RemoveCerts
```
