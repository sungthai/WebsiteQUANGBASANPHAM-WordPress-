$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

if (-not (Test-Path ".env")) {
    throw "Missing .env. Copy .env.example to .env and set unique strong passwords first."
}

$requiredPasswords = @(
    "MYSQL_PASSWORD",
    "MYSQL_ROOT_PASSWORD",
    "MYSQL_EXPORTER_PASSWORD",
    "WORDPRESS_ADMIN_PASSWORD",
    "GRAFANA_ADMIN_PASSWORD"
)
$environment = @{}
Get-Content ".env" | ForEach-Object {
    if ($_ -match "^([A-Z_]+)=(.*)$") {
        $environment[$matches[1]] = $matches[2]
    }
}
foreach ($name in $requiredPasswords) {
    if (-not $environment.ContainsKey($name) -or
        $environment[$name].Length -lt 24 -or
        $environment[$name] -match "CHANGE_ME") {
        throw "$name must be set to a unique password of at least 24 characters in .env."
    }
}

$dockerInfo = Join-Path $env:TEMP "wordpress-stack-docker-info.txt"
$dockerError = Join-Path $env:TEMP "wordpress-stack-docker-error.txt"
$docker = Start-Process -FilePath "docker.exe" `
    -ArgumentList "info --format {{.ServerVersion}}" `
    -NoNewWindow -PassThru `
    -RedirectStandardOutput $dockerInfo `
    -RedirectStandardError $dockerError
if (-not $docker.WaitForExit(15000)) {
    Stop-Process -Id $docker.Id -Force
    throw "Docker Engine did not answer within 15 seconds. Start/restart Docker Desktop and check its disk image location/free space, then run this script again."
}
if ($docker.ExitCode -ne 0) {
    $details = Get-Content $dockerError -Raw
    throw "Docker Engine is unavailable: $details"
}

docker compose --env-file .env config --quiet
if ($LASTEXITCODE -ne 0) {
    throw "Docker Compose configuration validation failed."
}

docker compose --env-file .env up -d
if ($LASTEXITCODE -ne 0) {
    throw "Docker Compose could not start the stack. Check Docker Desktop storage and run: docker compose --env-file .env logs --tail=100"
}

docker compose --env-file .env ps
