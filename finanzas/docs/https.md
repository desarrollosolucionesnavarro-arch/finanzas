HTTPS local con mkcert

Objetivo
- Habilitar HTTPS local para finanzas.local usando mkcert.

Requisitos
- mkcert instalado (https://github.com/FiloSottile/mkcert)
- XAMPP con Apache instalado y en ejecución
- Privilegios de Administrador

Pasos (Windows)
1. Instalar mkcert
- Instrucciones: https://github.com/FiloSottile/mkcert#installation
- Ejemplo con Chocolatey:
  ```powershell
  choco install mkcert
  mkcert -install
  ```

2. Generar certificados para finanzas.local
- Ubica el directorio del proyecto: `C:\xampp\htdocs\finanzas`
- Ejecuta (desde PowerShell como administrador):
  ```powershell
  cd C:\xampp\htdocs\finanzas
  mkcert -cert-file apache/ssl/finanzas.local.crt -key-file apache/ssl/finanzas.local.key finanzas.local
  ```
- Esto crea dos archivos en `apache/ssl/`: `finanzas.local.crt` y `finanzas.local.key`.

3. Configurar Apache
- Añadir un VirtualHost SSL en `C:\xampp\apache\conf\extra\finanzas.local-ssl.conf` (o usar `apache/finanzas.local-ssl.conf` del repo y copiar a la ubicación de Apache):

  - Asegúrate de que `DocumentRoot` apunte a `C:\xampp\htdocs\finanzas\public`.
  - Revisa que las rutas a `SSLCertificateFile` y `SSLCertificateKeyFile` sean las de los archivos generados.

Ejemplo de VirtualHost SSL:

<VirtualHost *:443>
    ServerName finanzas.local
    DocumentRoot "C:/xampp/htdocs/finanzas/public"
    SSLEngine on
    SSLCertificateFile "C:/xampp/htdocs/finanzas/apache/ssl/finanzas.local.crt"
    SSLCertificateKeyFile "C:/xampp/htdocs/finanzas/apache/ssl/finanzas.local.key"
    <Directory "C:/xampp/htdocs/finanzas/public">
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog "logs/finanzas.local-ssl-error.log"
    CustomLog "logs/finanzas.local-ssl-access.log" common
</VirtualHost>

4. Habilitar mod_ssl en Apache
- En `C:\xampp\apache\conf\httpd.conf`, asegúrate de que `LoadModule ssl_module modules/mod_ssl.so` está descomentado.
- En `C:\xampp\apache\conf\extra\httpd-ssl.conf`, habilita la configuración necesaria o incluye el archivo SSL que creaste.

5. Reiniciar Apache
- Usa el XAMPP Control Panel para reiniciar Apache o el script PowerShell si lo usaste.

Pruebas
- Abre: https://finanzas.local/
- Si el certificado es válido, el navegador no mostrará advertencias.

Notas
- mkcert solo para desarrollo local. No se use en producción.
- Si deseas automatizar el flujo, revisa `scripts/setup-https-mkcert.ps1`.

Limpieza (opcional)
 - Para eliminar los certificados generados y la configuración SSL copiada, ejecuta:

```powershell
cd C:\xampp\htdocs\finanzas
.\scripts\setup-https-mkcert.ps1 -RemoveCerts
```
