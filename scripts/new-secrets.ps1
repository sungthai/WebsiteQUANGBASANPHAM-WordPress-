$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

if (Test-Path ".env") {
    throw ".env already exists. It was not overwritten. Keep your existing credentials or back it up and remove it yourself before generating a new file."
}

$source = ".env.example"
if (-not (Test-Path $source)) {
    throw "Missing .env.example."
}

$names = @(
    "MYSQL_PASSWORD",
    "MYSQL_ROOT_PASSWORD",
    "MYSQL_EXPORTER_PASSWORD",
    "WORDPRESS_ADMIN_PASSWORD",
    "GRAFANA_ADMIN_PASSWORD"
)
$random = [System.Security.Cryptography.RandomNumberGenerator]::Create()
$values = @{}
foreach ($name in $names) {
    $bytes = New-Object byte[] 32
    $random.GetBytes($bytes)
    $values[$name] = ([BitConverter]::ToString($bytes) -replace "-", "").ToLowerInvariant()
}
$random.Dispose()

$updated = foreach ($line in Get-Content $source) {
    if ($line -match "^([A-Z_]+)=CHANGE_ME_") {
        $name = $matches[1]
        if (-not $values.ContainsKey($name)) {
            throw "Unexpected placeholder in .env.example: $name"
        }
        "$name=$($values[$name])"
    } else {
        $line
    }
}
Set-Content -Path ".env" -Value $updated -Encoding ascii
Write-Output "Created .env with unique random passwords. Store the file securely and never commit it."
