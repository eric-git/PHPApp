# PHP App

This application is a simple PHP implementation to help users of USI system to achieve:

- Calling ATO's STS to obtain a SAML security token
- Calling USI Web Service using the security token obtained
- If a [local development site](https://www.usiphp.net) is needed, please follow the below instructions

## Prerequisites

- Ensure PHP has been installed
- For Windows OS:
  - Ensure IIS feature has been enabled
  - Enable [CGI feature](https://learn.microsoft.com/en-us/iis/configuration/system.webserver/cgi)
  - Install [URL Rewrite module](https://www.iis.net/downloads/microsoft/url-rewrite) on IIS
  - Install [PowerShell 7+](https://learn.microsoft.com/en-us/powershell/scripting/install/installing-powershell-on-windows?view=powershell-7.4)
  - Update php.ini using [the settings](./php-settings-windows.ini) provided
- For Debian based Linux OS:
  - Ensure the [Apache](https://httpd.apache.org/) package is installed
  - Ensure PHP and Apache has been setup
  - Update php.ini using [the settings](./php-settings-debian.ini) provided

## Local development setup on Windows OS

You may setup the project on a Windows PC using IIS. Please run `setup-local-windows.ps1` with administration privilege to setup your local development environment.

- You may use the following command to get details of the command:
  ```powershell
  Get-Help ".\setup-local-windows.ps1" -Full
  ```
- XDebug Windows binaries can be downloaded [here](https://xdebug.org/download). Please place the binary under `<PHP installation folder>\ext`, and rename it to `php_xdebug.dll`

## Local development setup on Debian based Linux OS

You may setup the project on a Debian based Linux OS, Please run `setup-local-debian.sh` to setup your local development environment.

```sh
sudo ./setup-local-debian.sh "path/to/src/folder/of/the/project"
```

## Issues

For any issues, requests and/or discussion, please raise them on GitHub.
