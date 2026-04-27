<#
.SYNOPSIS
    Sets up a local PHP development environment on Windows with IIS + FastCGI.

.DESCRIPTION
    Creates a self‑signed certificate, configures IIS site + app pool,
    sets up FastCGI for PHP, and adds a hosts file entry.

.NOTES
    Requires:
      - Windows
      - Admin privileges
      - PHP installed (php.exe + php-cgi.exe)
      - IIS + CGI + URL Rewrite + IISAdministration module

.EXAMPLE
    .\setup-local-windows.ps1
    Uses default PHP path (C:\PHP) and default site path (..\src).

.EXAMPLE
    .\setup-local-windows.ps1 -sitePhysicalPath "C:\dev\PHPApp\src" -phpInstallationPath "C:\dev\php"
#>

using namespace System.Security.Cryptography.X509Certificates

[CmdletBinding()]
param(
  [Parameter(HelpMessage = "Site physical path. Default: ..\src relative to script.")]
  [string] $sitePhysicalPath,

  [Parameter(HelpMessage = "PHP installation path. Default: C:\PHP")]
  [string] $phpInstallationPath = "C:\PHP"
)

begin {
  $GREEN = "`e[32m"
  $NC = "`e[0m"

  Write-Host "${GREEN}Preparing environment...${NC}"

  $ErrorActionPreference = "Stop"

  # Resolve site path
  $scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
  if (-not $sitePhysicalPath) {
    $sitePhysicalPath = Resolve-Path (Join-Path $scriptDir "..\src")
  }

  if (-not (Test-Path $sitePhysicalPath)) {
    throw "Site physical path not found: $sitePhysicalPath"
  }

  # Auto-detect PHP if null
  if (-not $phpInstallationPath) {
    foreach ($path in $env:Path.Split([IO.Path]::PathSeparator)) {
      $candidate = Join-Path $path "php.exe"
      if (Test-Path $candidate) {
        $phpInstallationPath = $path
        break
      }
    }
  }

  $phpExe = Join-Path $phpInstallationPath "php.exe"
  if (-not (Test-Path $phpExe)) {
    throw "PHP not found at: $phpInstallationPath"
  }

  Import-Module IISAdministration

  $server = Get-IISServerManager
  $siteName = "usi-php-app"
  $siteUrl = "windows.usiphp.net"

  $iniSourcePath = Join-Path $scriptDir "php-settings-windows.ini"
}

process {

  # -------------------------
  # SSL CERTIFICATE
  # -------------------------
  Write-Host "${GREEN}Setting up SSL certificate...${NC}"

  $stores = "My", "Root", "WebHosting"

  foreach ($storeName in $stores) {
    $store = [X509Store]::new($storeName, "LocalMachine")
    $store.Open("ReadWrite")

    $store.Certificates |
    Where-Object { $_.Subject -eq "CN=$siteUrl" } |
    ForEach-Object { $store.Remove($_) }

    $store.Close()
  }

  $certificate = New-SelfSignedCertificate `
    -Subject $siteUrl `
    -DnsName $siteUrl `
    -CertStoreLocation "Cert:\LocalMachine\My" `
    -NotAfter (Get-Date).AddYears(10)

  foreach ($storeName in $stores) {
    $store = [X509Store]::new($storeName, "LocalMachine")
    $store.Open("ReadWrite")

    if ($storeName -eq "My") {
      $store.Remove($certificate)
    }
    else {
      $store.Add($certificate)
    }

    $store.Close()
  }

  # -------------------------
  # IIS SITE + APP POOL
  # -------------------------
  Write-Host "${GREEN}Configuring IIS site and app pool...${NC}"

  if ($server.Sites[$siteName]) {
    $server.Sites.Remove($server.Sites[$siteName])
  }

  if ($server.ApplicationPools[$siteName]) {
    $server.ApplicationPools.Remove($server.ApplicationPools[$siteName])
  }

  $appPool = $server.ApplicationPools.Add($siteName)
  $appPool.ManagedRuntimeVersion = ""   # No .NET runtime for PHP

  $site = $server.Sites.Add(
    $siteName,
    "*:443:$siteUrl",
    $sitePhysicalPath,
    $certificate.GetCertHash(),
    "WebHosting"
  )

  $site.ApplicationDefaults.ApplicationPoolName = $appPool.Name

  # -------------------------
  # FASTCGI CONFIG
  # -------------------------
  Write-Host "${GREEN}Configuring FastCGI...${NC}"

  $confDir = Join-Path $phpInstallationPath "conf.d"
  if (-not (Test-Path $confDir)) {
    New-Item -ItemType Directory -Path $confDir | Out-Null
  }

  $targetIni = Join-Path $confDir "99-$siteUrl.ini"
  Copy-Item $iniSourcePath $targetIni -Force

  $phpCgi = Join-Path $phpInstallationPath "php-cgi.exe"

  $config = $server.GetApplicationHostConfiguration()
  $fastCgiSection = $config.GetSection("system.webServer/fastCgi")
  $fastCgiCollection = $fastCgiSection.GetCollection()

  # Remove existing matching FastCGI entry
  $existing = $fastCgiCollection |
  Where-Object { $_["fullPath"] -eq $phpCgi -and $_["arguments"] -eq "" }

  foreach ($entry in $existing) {
    $fastCgiCollection.Remove($entry)
  }

  # Add new FastCGI entry
  $app = $fastCgiCollection.CreateElement("application")
  $app["fullPath"] = $phpCgi
  $app["instanceMaxRequests"] = 10000

  $env = $app.GetCollection("environmentVariables")

  $vars = @{
    "PHP_FCGI_MAX_REQUESTS" = "10000"
    "PHPRC"                 = Join-Path $phpInstallationPath "php.ini"
    "PHP_INI_SCAN_DIR"      = $confDir
  }

  foreach ($k in $vars.Keys) {
    $ev = $env.CreateElement("environmentVariable")
    $ev["name"] = $k
    $ev["value"] = $vars[$k]
    $env.Add($ev) | Out-Null
  }

  $fastCgiCollection.Add($app) | Out-Null
  $server.CommitChanges()

  # -------------------------
  # HOSTS FILE
  # -------------------------
  Write-Host "${GREEN}Updating hosts file...${NC}"

  $hostsFile = Join-Path $env:WinDir "System32\drivers\etc\hosts"
  $hosts = Get-Content $hostsFile

  if (-not ($hosts -match "127\.0\.0\.1\s+$siteUrl")) {
    Add-Content -Path $hostsFile -Encoding UTF8 -Value "`r`n127.0.0.1`t$siteUrl"
  }

  ipconfig /flushdns | Out-Null
}

end {
  Write-Host "${GREEN}Setup complete. Access your site at: https://$siteUrl${NC}"
}
