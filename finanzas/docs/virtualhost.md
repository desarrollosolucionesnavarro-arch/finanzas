Configuración de VirtualHost local para finanzas.local

Objetivo
- Acceder al proyecto desde http://finanzas.local/ sin el subdirectorio /public.

Requisitos
- XAMPP instalado y Apache en ejecución.
- Permisos de administrador para editar C:\Windows\System32\drivers\etc\hosts y reiniciar Apache.

Pasos (Windows + XAMPP)
1. Crear VirtualHost en Apache
- Abre el archivo: C:\xampp\apache\conf\extra\httpd-vhosts.conf
- Añade la siguiente entrada (ajusta la ruta si tu proyecto está en otro lugar):

<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/finanzas/public"
    ServerName finanzas.local
    <Directory "C:/xampp/htdocs/finanzas/public">
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog "logs/finanzas.local-error.log"
    CustomLog "logs/finanzas.local-access.log" common
</VirtualHost>

2. Habilitar Virtual Hosts (si no lo está ya)
- En el archivo: C:\xampp\apache\conf\httpd.conf
- Verifica que la siguiente línea no está comentada (quita el "#" si es necesario):

#Include conf/extra/httpd-vhosts.conf

3. Añadir entrada al archivo hosts (requiere administrador)
- Abre el archivo: C:\Windows\System32\drivers\etc\hosts
- Añade esta línea:

127.0.0.1 finanzas.local

4. Reiniciar Apache
- Abre el Panel de Control de XAMPP y haz clic en "Stop" en Apache y luego en "Start".

5. Acceder al proyecto
- Abre el navegador y visita:

http://finanzas.local/

Automatización (opcional)
 - Si quieres, puedes usar el script `scripts/setup-virtualhost.ps1` para automatizar este proceso. Ejecuta el script con PowerShell como administrador desde la raíz del proyecto:

```powershell
cd C:\xampp\htdocs\finanzas
.\scripts\setup-virtualhost.ps1
```

 - El script copia `apache/finanzas.local.conf`, añade la línea en `httpd-vhosts.conf` y la entrada de hosts, y trata de reiniciar Apache.

Deshacer cambios (opcional)
 - Para eliminar la entrada en `hosts` y el vhost copiado por el script, ejecuta (PowerShell como Administrador):

```powershell
cd C:\xampp\htdocs\finanzas
.\scripts\setup-virtualhost.ps1 -Remove
```

Pruebas y Troubleshooting
- Probar con: ping finanzas.local en CMD para verificar que apunta a 127.0.0.1.
- Si ves la página del directorio o error, confirma que `DocumentRoot` apunta a `public`.
- Si aparece un error de permisos, confirma que `Require all granted` está presente y Apache puede leer la carpeta.
- Asegúrate de que no haya otros VirtualHosts con `ServerName` duplicados.

Notas de seguridad
- Este VirtualHost es para desarrollo local. Para entornos de producción, usa HTTPS y configuración adecuada.

Opcional: Habilitar HTTPS (mencionado)
- Para HTTPS local, considera crear un certificado autofirmado o usar mkcert.
- Se requieren pasos adicionales en Apache para habilitar `ssl.conf` y `VirtualHost *:443`.

Si quieres, puedo crear una entrada de ejemplo en el repositorio para facilitar el uso.
