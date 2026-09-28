[CmdletBinding()]
param(
    [ValidateSet("base", "ct40", "ct32")]
    [string] $Profile = "base"
)

$mysql = "C:\xampp\mysql\bin\mysql.exe"

if (-not (Test-Path -LiteralPath $mysql)) {
    throw "Cliente MariaDB/MySQL não encontrado em $mysql."
}

$scripts = @(
    (Join-Path $PSScriptRoot "01_schema.sql"),
    (Join-Path $PSScriptRoot "02_seed_base.sql")
)

if ($Profile -eq "ct40") {
    $scripts += Join-Path $PSScriptRoot "03_seed_ct40.sql"
}

if ($Profile -eq "ct32") {
    $scripts += Join-Path $PSScriptRoot "04_seed_ct32_performance.sql"
}

foreach ($script in $scripts) {
    $resolved = (Resolve-Path -LiteralPath $script).Path.Replace("\", "/")
    & $mysql --host=127.0.0.1 --user=root --execute="SOURCE $resolved"

    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao executar $script."
    }
}

Write-Host "Banco campustrack_test recriado com o perfil '$Profile'."

