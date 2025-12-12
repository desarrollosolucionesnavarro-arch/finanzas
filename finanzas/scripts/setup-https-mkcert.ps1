<#
PowerShell script to generate mkcert certificates for finanzas.local and copy SSL configs for Apache in XAMPP.
Run as Administrator.
#>

param(
    [string]$ProjectPath = "C:\xampp\htdocs\finanzas",
    [string]$ApachePath = "C:\xampp\apache",
    [string]$HostName = "finanzas.local",
    [string]$MkcertBinary = "mkcert",
    [switch]$RemoveCerts
)

function Assert-Admin {
    if (-not ([bool] (New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())).IsInRole([Security.Principal.WindowsBuiltinRole]::Administrator))) {
        Write-Error "Este script requiere privilegios de Administrador. Ejecuta PowerShell como Administrador."
        exit 1
    }
}

function Check-Mkcert {
    param($MkcertBinary)
    try {
        $version = & $MkcertBinary -version 2>$null
        if ($LASTEXITCODE -ne 0) { throw }
        Write-Host "mkcert detectado: $version"
    } catch {
        Write-Error "mkcert no se encuentra en PATH. Instala mkcert (https://github.com/FiloSottile/mkcert)."
        exit 1
    }
}

function Generate-Certs {
    param($ProjectPath, $HostName, $MkcertBinary)
    $sslDir = Join-Path $ProjectPath "apache\ssl"
    if (-not (Test-Path $sslDir)) { New-Item -Path $sslDir -ItemType Directory | Out-Null }

    $certFile = Join-Path $sslDir "$HostName.crt"
    $keyFile = Join-Path $sslDir "$HostName.key"

    Write-Host "Generando certificados mkcert para $HostName..."
    & $MkcertBinary -cert-file $certFile -key-file $keyFile $HostName
    if ($LASTEXITCODE -ne 0) {
        Write-Error "mkcert falló al generar los certificados. Revisa la instalación."
        exit 1
    }
    Write-Host "Certificados generados en $sslDir"
}

function Copy-SSL-Conf {
    param($ProjectPath, $ApachePath, $HostName)
    $source = Join-Path $ProjectPath "apache\$HostName-ssl.conf"
    $destDir = Join-Path $ApachePath "conf\extra"
    $dest = Join-Path $destDir "$HostName-ssl.conf"

    if (-Not (Test-Path $source)) {
        Write-Warning "El archivo de vhost SSL ejemplo no existe en $source. Asegúrate de que apache/$HostName-ssl.conf exista en el repositorio."
        return
    }

    Write-Host "Copiando archivo de vhost SSL a $dest..."
    Copy-Item -Path $source -Destination $dest -Force
    Write-Host "Vhost SSL copiado."
}

function Ensure-SSLIncluded {
    param($ApachePath, $HostName)
    $vhostsFile = Join-Path $ApachePath "conf\extra\httpd-ssl.conf"
    $includeLine = "Include conf/extra/$HostName-ssl.conf"
    $vhostsContent = Get-Content -Path $vhostsFile -Raw -ErrorAction SilentlyContinue
    if ($vhostsContent -and ($vhostsContent -notmatch [regex]::Escape($includeLine))) {
        Add-Content -Path $vhostsFile -Value "`n$includeLine"
        Write-Host "Incluido $HostName-ssl.conf dentro de httpd-ssl.conf"
    } else {
        Write-Host "httpd-ssl.conf ya contiene la referencia o no existe."
    }
}

function Remove-SSLCertsAndConfig {
    param($ProjectPath, $ApachePath, $HostName)

    $sslDir = Join-Path $ProjectPath "apache\ssl"
    $certFile = Join-Path $sslDir "$HostName.crt"
    $keyFile = Join-Path $sslDir "$HostName.key"
    $destConf = Join-Path $ApachePath "conf\extra\$HostName-ssl.conf"
    $httpdSslConf = Join-Path $ApachePath "conf\extra\httpd-ssl.conf"

    Write-Host "Eliminando certificados y configuración SSL para $HostName..."
    if (Test-Path $certFile) { Remove-Item -Path $certFile -Force; Write-Host "Eliminado $certFile" } else { Write-Host "No se encontró $certFile" }
    if (Test-Path $keyFile) { Remove-Item -Path $keyFile -Force; Write-Host "Eliminado $keyFile" } else { Write-Host "No se encontró $keyFile" }

    if (Test-Path $destConf) { Remove-Item -Path $destConf -Force; Write-Host "Eliminado $destConf" } else { Write-Host "No se encontró $destConf" }

    if (Test-Path $httpdSslConf) {
        $includeLine = "Include conf/extra/$HostName-ssl.conf"
        $content = Get-Content -Path $httpdSslConf -Raw -ErrorAction SilentlyContinue
        if ($content -match [regex]::Escape($includeLine)) {
            $newContent = $content -replace "(?m)^.*\Q$includeLine\E.*$", ''
            Set-Content -Path $httpdSslConf -Value $newContent
            Write-Host "Se removió la referencia en httpd-ssl.conf."
        }
    }
}

# Ejecución
Assert-Admin
if ($RemoveCerts) {
    $ok = Read-Host "¿Confirmas que quieres eliminar certificados y configuración SSL para $HostName? (yes/no)"
    if ($ok -eq 'yes') {
        Remove-SSLCertsAndConfig -ProjectPath $ProjectPath -ApachePath $ApachePath -HostName $HostName
        Write-Host "Operación completada. Reinicia Apache si es necesario." -ForegroundColor Green
    } else {
        Write-Host "Operación cancelada."
    }
} else {
    Check-Mkcert -MkcertBinary $MkcertBinary
    Generate-Certs -ProjectPath $ProjectPath -HostName $HostName -MkcertBinary $MkcertBinary
    Copy-SSL-Conf -ProjectPath $ProjectPath -ApachePath $ApachePath -HostName $HostName
    Ensure-SSLIncluded -ApachePath $ApachePath -HostName $HostName
    Write-Host "Certificados generados y archivos copiados. Reinicia Apache desde XAMPP Control Panel si es necesario." -ForegroundColor Green
}
