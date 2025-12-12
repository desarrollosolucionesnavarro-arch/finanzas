<#
PowerShell script to configure Apache VirtualHost and hosts entry for finanzas.local
Run as Administrator.
#>

param(
    [string]$ProjectPath = "C:\xampp\htdocs\finanzas",
    [string]$ApachePath = "C:\xampp\apache",
    [string]$HostName = "finanzas.local",
    [switch]$Remove
)

function Assert-Admin {
    if (-not ([bool] (New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())).IsInRole([Security.Principal.WindowsBuiltinRole]::Administrator))) {
        Write-Error "Este script requiere privilegios de Administrador. Ejecuta PowerShell como Administrador."
        exit 1
    }
}

function Add-HostsEntry {
    param($HostName)
    $hostsPath = "C:\Windows\System32\drivers\etc\hosts"
    $entry = "127.0.0.1 `t$HostName"
    $content = Get-Content -Path $hostsPath -ErrorAction Stop -Raw
    if ($content -match "\b$HostName\b") {
        Write-Host "El host $HostName ya está configurado en hosts."
    } else {
        Write-Host "Agregando entrada en hosts para $HostName..."
        Add-Content -Path $hostsPath -Value "`n$entry"
        Write-Host "Entrada agregada."
    }
}

function Copy-Vhost {
    param($ProjectPath, $ApachePath, $HostName)
    $source = Join-Path $ProjectPath "apache\$HostName.conf"
    $destDir = Join-Path $ApachePath "conf\extra"
    $dest = Join-Path $destDir "$HostName.conf"

    if (-Not (Test-Path $source)) {
        Write-Warning "El archivo de vhost ejemplo no existe en $source. Asegúrate de que apache/$HostName.conf exista en el repositorio."
        return
    }

    Write-Host "Copiando archivo de vhost a $dest..."
    Copy-Item -Path $source -Destination $dest -Force
    Write-Host "Vhost copiado."

    # Ensure httpd-vhosts.conf includes this vhost
    $vhostsFile = Join-Path $ApachePath "conf\extra\httpd-vhosts.conf"
    $includeLine = "Include conf/extra/$HostName.conf"
    $vhostsContent = Get-Content -Path $vhostsFile -Raw -ErrorAction SilentlyContinue
    if ($vhostsContent -and ($vhostsContent -notmatch [regex]::Escape($includeLine))) {
        Add-Content -Path $vhostsFile -Value "`n$includeLine"
        Write-Host "Incluido $HostName.conf dentro de httpd-vhosts.conf"
    } else {
        Write-Host "httpd-vhosts.conf ya contiene la referencia o no existe."
    }
}

function Remove-VhostAndHostsEntry {
    param($ProjectPath, $ApachePath, $HostName)

    $hostsPath = "C:\Windows\System32\drivers\etc\hosts"
    $entryLine = "127.0.0.1 `t$HostName"
    $vhostCopy = Join-Path $ApachePath "conf\extra\$HostName.conf"
    $vhostsFile = Join-Path $ApachePath "conf\extra\httpd-vhosts.conf"

    Write-Host "Eliminando entrada hosts y vhost copiado para $HostName..."
    # Remove hosts entry (simple removal by exact match)
    $hostsContent = Get-Content -Path $hostsPath -ErrorAction SilentlyContinue
    if ($hostsContent -and ($hostsContent -match "\b$HostName\b")) {
        $newHosts = $hostsContent -replace "(?m)^.*\b$HostName\b.*$", ''
        Set-Content -Path $hostsPath -Value $newHosts
        Write-Host "Entrada en hosts eliminada (si existía)."
    } else {
        Write-Host "No se encontró entrada en hosts para $HostName."
    }

    # Remove vhost copy
    if (Test-Path $vhostCopy) {
        Remove-Item -Path $vhostCopy -Force
        Write-Host "Archivo $vhostCopy eliminado."
    } else {
        Write-Host "No se encontró $vhostCopy."
    }

    # Remove include line from httpd-vhosts.conf
    if (Test-Path $vhostsFile) {
        $includeLine = "Include conf/extra/$HostName.conf"
        $vhostsContent = Get-Content -Path $vhostsFile -Raw -ErrorAction SilentlyContinue
        if ($vhostsContent -and ($vhostsContent -match [regex]::Escape($includeLine))) {
            $newContent = $vhostsContent -replace "(?m)^.*\Q$includeLine\E.*$", ''
            Set-Content -Path $vhostsFile -Value $newContent
            Write-Host "Se removió la referencia en httpd-vhosts.conf."
        }
    }
}

function Ensure-VhostsIncluded {
    param($ApachePath)
    $httpdConf = Join-Path $ApachePath "conf\httpd.conf"
    $line = "Include conf/extra/httpd-vhosts.conf"
    $httpdContent = Get-Content -Path $httpdConf -Raw -ErrorAction Stop
    if ($httpdContent -match "^#\s*$line") {
        Write-Host "Descomentando la línea $line en httpd.conf"
        $newContent = $httpdContent -replace "^#\s*($line)", '$1'
        Set-Content -Path $httpdConf -Value $newContent
    } elseif ($httpdContent -notmatch [regex]::Escape($line)) {
        Write-Host "Añadiendo la línea $line a httpd.conf"
        Add-Content -Path $httpdConf -Value "`n$line"
    } else {
        Write-Host "httpd.conf ya contiene la línea Include para httpd-vhosts.conf"
    }
}

function Restart-Apache {
    # Try service control for Apache2.4 (common in XAMPP)
    try {
        Write-Host "Reiniciando servicio Apache (si está registrado como servicio)..."
        Stop-Service -Name Apache2.4 -ErrorAction SilentlyContinue
        Start-Service -Name Apache2.4 -ErrorAction SilentlyContinue
        Write-Host "Servicio Apache reiniciado (o no estaba instalado como servicio)."
    } catch {
        Write-Warning "No se pudo reiniciar el servicio Apache. Intenta reiniciarlo desde el Panel XAMPP manualmente."
    }
}

# Ejecución
Assert-Admin
if ($Remove) {
    $ok = Read-Host "¿Confirmas que quieres eliminar el VirtualHost y la entrada en hosts para $HostName? (yes/no)"
    if ($ok -eq 'yes') {
        Remove-VhostAndHostsEntry -ProjectPath $ProjectPath -ApachePath $ApachePath -HostName $HostName
        Restart-Apache
    } else {
        Write-Host "Operación cancelada."
    }
} else {
    Add-HostsEntry -HostName $HostName
    Copy-Vhost -ProjectPath $ProjectPath -ApachePath $ApachePath -HostName $HostName
    Ensure-VhostsIncluded -ApachePath $ApachePath
    Restart-Apache
}

Write-Host "Listo. Abre http://$HostName/ en tu navegador." -ForegroundColor Green
