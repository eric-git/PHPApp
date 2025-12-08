<#
.SYNOPSIS
    This command sets up a local development environment in Windows OS.
.DESCRIPTION
    This command sets up a local development environment for the PHP project. PHP, URL Rewrite module, CGI module must be installed before running this script.
.NOTES
    The command requires administration privilege.
.LINK
    www.usi.gov.au
.EXAMPLE
    .\setup-local-windows.ps1
    This is using the default installation path "C:\PHP". Or you are not sure where PHP is installed, the function will find it out for you.
    This is using the default site physical path ..\src relative to this script.
.EXAMPLE
    .\setup-local-windows.ps1 -sitePhysicalPath "C:\developer\Repos\PHPApp\src" -phpInstallationPath "C:\developer\php"
#>
using namespace System.Security.Cryptography.X509Certificates
[CmdletBinding()]
param(
  [Parameter(HelpMessage = "Site physical path, default is ..\src relative to this script")]
  [string]
  $sitePhysicalPath,
  [Parameter(HelpMessage = "PHP installation path, default is C:\PHP")]
  [string]
  $phpInstallationPath = "C:\PHP"
)
begin {
  $GREEN = "`e[32m"
  $NC = "`e[0m"
  Write-Host "${GREEN}Preparing...${NC}"
  #Requires -RunAsAdministrator
  #Requires -Version 7.0
  #requires -Module IISAdministration
  $ErrorActionPreference = "Stop"
  $scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
  if (-not $sitePhysicalPath) {
    $sitePhysicalPath = Resolve-Path (Join-Path -Path $scriptDir -ChildPath "..\src")
  }
  if (-not (Test-Path -Path $sitePhysicalPath)) {
    Write-Error "Site physical path not found. Exiting..."
  }
  if ($null -eq $phpInstallationPath) {
    $envPath = $env:Path
    if ($null -ne $envPath) {
      foreach ($pathFromEnv in @($envPath.Split([System.IO.Path]::PathSeparator))) {
        $executablePath = Join-Path $pathFromEnv "php.exe"
        if (Test-Path `
            -Path $executablePath `
            -PathType Leaf) {
          $phpInstallationPath = $pathFromEnv
          break
        }
      }
    }
  }
  $executablePath = Join-Path `
    -Path $phpInstallationPath `
    -ChildPath "php.exe"
  if (-not (Test-Path `
        -Path $executablePath `
        -PathType Leaf)) {
    Write-Error "PHP not installed. Exiting..."
  }
  Import-Module IISAdministration
  $server = Get-IISServerManager
  $siteName = "usi-php-app"
  $siteUrl = "windows.usiphp.net"
  $iniSourcePath = Join-Path $scriptDir "php-settings-windows.ini"
}
process {
  Write-Host "${GREEN}Setting up SSL certificate...${NC}"
  foreach ($storeName in "My", "Root", "WebHosting") {
    $store = New-Object X509Store($storeName, "LocalMachine")
    $store.Open("ReadWrite")
    $store.Certificates | Where-Object {
      $_.Subject -eq "CN=$siteUrl"
    } | ForEach-Object {
      $store.Remove($_)
    }
    $store.Close()
  }
  $certificate = New-SelfSignedCertificate `
    -Subject $siteUrl `
    -DnsName $siteUrl `
    -CertStoreLocation Cert:\LocalMachine\My `
    -NotAfter (Get-Date).AddYears(10)
  foreach ($storeName in "My", "Root", "WebHosting") {
    $store = New-Object X509Store($storeName, "LocalMachine")
    $store.Open("ReadWrite")
    if ($storeName -eq "My") {
      $store.Remove($certificate)
    }
    else {
      $store.Add($certificate)
    }
    $store.Close()
  }

  Write-Host "${GREEN}Setting up app pool and site...${NC}"
  $site = $server.Sites[$siteName]
  if ($null -ne $site) {
    $server.Sites.Remove($site)
  }
  $appPool = $server.ApplicationPools[$siteName]
  if ($null -ne $appPool) {
    $server.ApplicationPools.Remove($appPool)
  }
  $appPool = $server.ApplicationPools.Add($siteName)
  $site = $server.Sites.Add($siteName, "*:443:$siteUrl", $sitePhysicalPath, $certificate.GetCertHash(), "WebHosting")
  $site.ApplicationDefaults.ApplicationPoolName = $appPool.Name

  Write-Host "${GREEN}Setting up FastCGI...${NC}"
  $confDir = Join-Path `
    -Path $phpInstallationPath `
    -ChildPath "conf.d"
  if (-not (Test-Path `
        -Path $confDir `
        -PathType Container)) {
    New-Item `
      -Path $confDir `
      -ItemType Directory | Out-Null
  }
  $targetIni = Join-Path $confDir "99-$siteUrl.ini"
  Copy-Item -Path $iniSourcePath -Destination $targetIni -Force
  $fullPath = Join-Path `
    -Path $phpInstallationPath `
    -ChildPath "php-cgi.exe"
  $config = $server.GetApplicationHostConfiguration()
  $fastCgiSection = $config.GetSection("system.webServer/fastCgi")
  $fastCgiCollection = $fastCgiSection.GetCollection()
  if ($null -ne $fastCgiCollection) {
    foreach ($applicationElement in $fastCgiCollection) {
      if ($applicationElement["fullPath"] -eq $fullPath -and $applicationElement["arguments"] -eq "") {
        $fastCgiCollection.Remove($applicationElement)
        break
      }
    }
  }
  $applicationElement = $fastCgiCollection.CreateElement("application")
  $applicationElement["fullPath"] = $fullPath
  $applicationElement["instanceMaxRequests"] = 10000
  $environmentVariablesCollection = $applicationElement.GetCollection("environmentVariables")
  $environmentVariableElement = $environmentVariablesCollection.CreateElement("environmentVariable")
  $environmentVariableElement["name"] = "PHP_FCGI_MAX_REQUESTS"
  $environmentVariableElement["value"] = "10000"
  $environmentVariablesCollection.Add($environmentVariableElement) | Out-Null
  $environmentVariableElement = $environmentVariablesCollection.CreateElement("environmentVariable")
  $environmentVariableElement["name"] = "PHPRC"
  $environmentVariableElement["value"] = Join-Path $phpInstallationPath "php.ini"
  $envVar = $environmentVariablesCollection.CreateElement("environmentVariable")
  $envVar["name"] = "PHP_INI_SCAN_DIR"
  $envVar["value"] = $confDir
  $environmentVariablesCollection.Add($envVar) | Out-Null
  $environmentVariablesCollection.Add($environmentVariableElement) | Out-Null
  $fastCgiCollection.Add($applicationElement) | Out-Null
  $server.CommitChanges()

  Write-Host "${GREEN}Setting up local DNS...${NC}"
  $hostsFilePath = Join-Path `
    -Path $env:WinDir `
    -ChildPath "system32\Drivers\etc\hosts"
  $hosts = Get-Content -Path $hostsFilePath
  $entryExists = $hosts -match "$([Regex]::Escape("127.0.0.1"))\s+$([Regex]::Escape($siteUrl))"
  if (-not $entryExists) {
    Add-Content `
      -Path $hostsFilePath `
      -Encoding UTF8 `
      -Value "`r`n`t127.0.0.1`t$siteUrl"
  }
  & ipconfig /flushdns | Out-Null
}
end {
  Write-Host "${GREEN}Setup completed. You can access the site at https://$siteUrl${NC}"
}
