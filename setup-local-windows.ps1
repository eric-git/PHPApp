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
    .\setup-local-windows.ps1 -sitePhysicalPath "D:\MyFiles\Development\PHPApp\src"
    This is using the default installation path "C:\PHP". Or you are not sure where PHP is installed, the function will find it out for you.
.EXAMPLE
    .\setup-local-windows.ps1 -sitePhysicalPath "C:\developer\Repos\PHPApp\src" -phpInstallationPath "C:\developer\php"
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory, HelpMessage = "Site physical path")]
    [string]
    $sitePhysicalPath,

    [Parameter(HelpMessage = "PHP installation path, default is C:\PHP")]
    [string]
    $phpInstallationPath = "C:\PHP"
)
begin {
    Write-Host "Preparing..."
    #Requires -RunAsAdministrator
    $ErrorActionPreference = "Stop"
    if (-not (Test-Path -Path $sitePhysicalPath)) {
        Write-Error "Site physical path not found"
    }
    if ($null -eq $phpInstallationPath) {
        $envPath = $env:Path
        If ($null -ne $envPath) {
            foreach ($pathFromEnv in @($envPath.Split([System.IO.Path]::PathSeparator))) {
                $executablePath = Join-Path $pathFromEnv "php.exe"
                If (Test-Path `
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
        Write-Error "PHP not installed"
    }
    Import-Module IISAdministration
    $server = Get-IISServerManager
    $siteName = "usi-php-app"
    $siteUrl = "www.usiphp.net"
}
process {
    Write-Host "Setting up PHP..."
    @(
        "usr",
        "sys-tmp",
        "upload-tmp",
        "session",
        "wsdl-cache",
        "xdebug"
    ) | ForEach-Object {
        $path = Join-Path `
            -Path $phpInstallationPath `
            -ChildPath $_
        New-Item `
            -ItemType Directory `
            -Path $path `
            -Force | Out-Null
    }

    Write-Host "Setting up SSL certificate..."
    Get-ChildItem -Path Cert:\LocalMachine\My | Where-Object {
        $_.Subject -eq "CN=$siteUrl"
    } | Remove-Item
    $certificate = New-SelfSignedCertificate `
        -Subject $siteUrl `
        -DnsName $siteUrl `
        -CertStoreLocation Cert:\LocalMachine\My `
        -NotAfter (Get-Date).AddYears(10)

    Write-Host "Setting up app pool..."
    $site = $server.Sites[$siteName]
    if ($null -ne $site) {
        $server.Sites.Remove($site)
    }
    $appPool = $server.ApplicationPools[$siteName]
    if ($null -ne $appPool) {
        $server.ApplicationPools.Remove($appPool)
    }
    $appPool = $server.ApplicationPools.Add($siteName)

    Write-Host "Setting up site..."
    $site = $server.Sites.Add($siteName, "*:443:$siteUrl", $sitePhysicalPath, $certificate.GetCertHash(), "My")
    $site.ApplicationDefaults.ApplicationPoolName = $appPool.Name

    Write-Host "Setting up FastCGI..."
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
    $environmentVariablesCollection.Add($environmentVariableElement) | Out-Null
    $fastCgiCollection.Add($applicationElement) | Out-Null
    $server.CommitChanges()

    Write-Host "Setting up local DNS..."
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
}