# Downloads Bootstrap CSS and JS into public/assets
$base = Join-Path -Path $PSScriptRoot -ChildPath "..\public\assets"
if (-not (Test-Path $base)) { New-Item -ItemType Directory -Path $base -Force | Out-Null }

$cssUrl = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
$jsUrl = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'

$cssDest = Join-Path $base 'bootstrap.min.css'
$jsDest = Join-Path $base 'bootstrap.bundle.min.js'

Write-Output "Downloading Bootstrap CSS to $cssDest"
Invoke-WebRequest -Uri $cssUrl -OutFile $cssDest -UseBasicParsing
Write-Output "Downloading Bootstrap JS to $jsDest"
Invoke-WebRequest -Uri $jsUrl -OutFile $jsDest -UseBasicParsing
Write-Output "Done."
