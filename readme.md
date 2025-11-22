# PHP App

This application is a simple PHP implementation to help users of USI system to achieve:

- Calling ATO's STS to obtain a SAML security token
- Calling USI Web Service using the security token obtained
- If a local Website (https://windows.usiphp.net for a Windows OS, or https://linux.usiphp.net for a Debian-based OS) is needed, please follow the below instructions

## Prerequisites

- Ensure PHP 8.x has been installed
- For Windows OS:
  - Ensure the IIS feature has been enabled
  - Enable [CGI feature](https://learn.microsoft.com/en-us/iis/configuration/system.webserver/cgi)
  - Install [URL Rewrite module](https://www.iis.net/downloads/microsoft/url-rewrite) on IIS
  - Install [PowerShell 7+](https://learn.microsoft.com/en-us/powershell/scripting/install/installing-powershell-on-windows)
- For Debian-based Linux OS:
  - Ensure the [Apache](https://httpd.apache.org/) package is installed
  - Ensure package [libapache2-mod-php](https://packages.debian.org/sid/libapache2-mod-php) has been installed

## Local development setup on Windows OS

You may setup the project on a Windows PC using IIS. Please run [setup-local-windows.ps1](deployment/setup-local-windows.ps1) with administration privilege to setup your local development environment.

- You may use the following command to get details of the command:
  ```powershell
  Get-Help ".\setup-local-windows.ps1" -Full
  ```
- XDebug Windows binaries can be downloaded [here](https://xdebug.org/download). Please place the binary under `<PHP installation folder>\ext`, and rename it to `php_xdebug.dll`
- Ensure the attribute `scriptProcessor` for FastCgiModule points to the actual PHP installation path in web.config. By default, it is C:\PHP\php-cgi.exe.

## Local development setup on Debian-based Linux OS

You may setup the project on a Debian-based Linux OS, Please run [setup-local-debian.sh](deployment/setup-local-debian.sh) to setup your local development environment.

```sh
sudo ./setup-local-debian.sh "path/to/src/folder/of/the/project"
```

## Issues

For any issues, requests and/or discussions, please raise them on GitHub.
