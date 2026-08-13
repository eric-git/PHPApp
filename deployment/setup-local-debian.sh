#!/bin/bash
set -euo pipefail

GREEN="\033[32m"
NC="\033[0m"

echo -e "${GREEN}Preparing...${NC}"

# --- Preconditions ------------------------------------------------------------

# Ensure script is executable
[ -x "$0" ] || chmod +x "$0"

# Must be Debian-based
if [[ ! -f /etc/debian_version ]]; then
    echo "This system is not Debian-based. Exiting..."
    exit 1
fi

# Must run as root
if [[ "$(id -u)" -ne 0 ]]; then
    echo "Please run as root (e.g., sudo ./$(basename "$0")). Exiting..."
    exit 1
fi

# Apache must be installed
if ! dpkg-query -W -f='${Status}' apache2 2>/dev/null | grep -q "install ok installed"; then
    echo "Apache is not installed. Install Apache before running this script. Exiting..."
    exit 1
fi

# --- Resolve document root ----------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

if [[ -z "${1:-}" ]]; then
    DOCUMENT_ROOT="$(realpath "$SCRIPT_DIR/../src")"
else
    DOCUMENT_ROOT="$(realpath "$1")"
fi

if [[ ! -d "$DOCUMENT_ROOT" ]]; then
    echo "Document root directory '$DOCUMENT_ROOT' does not exist. Exiting..."
    exit 1
fi

DOMAIN="linux.usiphp.net"

# --- Install required packages ------------------------------------------------
echo -e "${GREEN}Installing required packages...${NC}"
apt-get update -y
apt-get install -y \
    openssl \
    php-soap \
    php-common \
    php-xml \
    php-xdebug

# --- Enable Apache modules ----------------------------------------------------
echo -e "${GREEN}Enabling required Apache modules...${NC}"
a2enmod rewrite dir deflate ssl

# --- SSL Certificate ----------------------------------------------------------
SSL_DIR="/etc/ssl/$DOMAIN"
mkdir -p "$SSL_DIR"

echo -e "${GREEN}Creating SSL certificate...${NC}"

openssl genpkey -algorithm RSA -out "$SSL_DIR/$DOMAIN.key"

openssl req -new -x509 \
    -key "$SSL_DIR/$DOMAIN.key" \
    -out "$SSL_DIR/$DOMAIN.crt" \
    -days 3650 \
    -subj "/C=AU/ST=State/L=City/O=Organization/OU=Department/CN=$DOMAIN" \
    -addext "subjectAltName=DNS:$DOMAIN"

install -m 0644 "$SSL_DIR/$DOMAIN.crt" /usr/local/share/ca-certificates/
update-ca-certificates

# --- Apache VirtualHost -------------------------------------------------------
echo -e "${GREEN}Updating Apache configuration...${NC}"

VHOST_FILE="/etc/apache2/sites-available/$DOMAIN.conf"

cat <<EOL > "$VHOST_FILE"
ServerName $DOMAIN

<VirtualHost 127.0.0.1:80>
    ServerName $DOMAIN
    DocumentRoot "$DOCUMENT_ROOT"

    <Directory "$DOCUMENT_ROOT">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)\$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</VirtualHost>

<VirtualHost 127.0.0.1:443>
    ServerName $DOMAIN
    DocumentRoot "$DOCUMENT_ROOT"

    SSLEngine on
    SSLCertificateFile "$SSL_DIR/$DOMAIN.crt"
    SSLCertificateKeyFile "$SSL_DIR/$DOMAIN.key"

    <FilesMatch "(\.env|.*\.config)$">
        Require all denied
    </FilesMatch>

    <Directory "$DOCUMENT_ROOT">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorDocument 400 /Utility/Error.php?code=400
    ErrorDocument 401 /Utility/Error.php?code=401
    ErrorDocument 403 /Utility/Error.php?code=403
    ErrorDocument 404 /Utility/Error.php?code=404
    ErrorDocument 405 /Utility/Error.php?code=405
    ErrorDocument 406 /Utility/Error.php?code=406
    ErrorDocument 412 /Utility/Error.php?code=412
    ErrorDocument 431 /Utility/Error.php?code=431
    ErrorDocument 500 /Utility/Error.php?code=500
    ErrorDocument 501 /Utility/Error.php?code=501
    ErrorDocument 502 /Utility/Error.php?code=502

    DirectoryIndex index.php default.php Default.htm Default.asp index.htm index.html iisstart.htm default.aspx

    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</VirtualHost>
EOL

# --- Hosts file ---------------------------------------------------------------
echo -e "${GREEN}Updating /etc/hosts...${NC}"
if ! grep -qw "$DOMAIN" /etc/hosts; then
    echo "127.0.0.1 $DOMAIN" >> /etc/hosts
fi

# --- PHP configuration --------------------------------------------------------
echo -e "${GREEN}Adding PHP configuration...${NC}"

phpVersion="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
PHP_INI="/etc/php/${phpVersion}/apache2/conf.d/99-${DOMAIN}.ini"

{
    echo "open_basedir = ${DOCUMENT_ROOT}:/tmp"
    cat "${SCRIPT_DIR}/php-settings-linux.ini"
} > "$PHP_INI"

chown root:root "$PHP_INI"
chmod 644 "$PHP_INI"

# --- Enable site --------------------------------------------------------------
echo -e "${GREEN}Enabling site and restarting Apache...${NC}"
a2ensite "$DOMAIN.conf"
systemctl restart apache2

echo -e "${GREEN}Setup completed. Access your site at: https://${DOMAIN}${NC}"
