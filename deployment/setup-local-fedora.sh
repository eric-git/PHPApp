#!/bin/bash
set -e
GREEN="\033[32m"
NC="\033[0m"

echo -e "${GREEN}Preparing...${NC}"
if [ ! -x "$0" ]; then
    chmod +x "$0"
fi

# Check Fedora presence
if [[ ! -f /etc/fedora-release ]]; then
  echo -e "This is not a Fedora system. Exiting..."
  exit 1
fi

# Require root
if [ "$(id -u)" -ne 0 ]; then
    echo -e "Please run as root (use sudo ./script.sh). Exiting..."
    exit 1
fi

# Check Apache installation
if ! rpm -q httpd >/dev/null 2>&1; then
  echo -e "Apache (httpd) is not installed. Please install it before running this script. Exiting..."
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
if [ -z "${1:-}" ]; then
  DOCUMENT_ROOT="$(realpath "$SCRIPT_DIR/../src")"
else
  DOCUMENT_ROOT="$(realpath "$1")"
fi
if [ ! -d "$DOCUMENT_ROOT" ]; then
  echo -e "Document root directory '$DOCUMENT_ROOT' does not exist. Exiting..."
  exit 1
fi

DOMAIN="linux.usiphp.net"

echo -e "${GREEN}Installing required packages...${NC}"
dnf install -y \
      openssl \
      php-soap \
      php-xml \
      php-xdebug

echo -e "${GREEN}Creating SSL certificate...${NC}"
mkdir -p /etc/pki/tls/$DOMAIN
openssl genpkey \
  -algorithm RSA \
  -out /etc/pki/tls/$DOMAIN/$DOMAIN.key
openssl req \
  -new \
  -x509 \
  -key /etc/pki/tls/$DOMAIN/$DOMAIN.key \
  -out /etc/pki/tls/$DOMAIN/$DOMAIN.crt \
  -days 3650 \
  -subj "/C=AU/ST=State/L=City/O=Organization/OU=Department/CN=$DOMAIN" \
  -addext "subjectAltName=DNS:$DOMAIN"

echo -e "${GREEN}Updating Apache configuration...${NC}"
cat <<EOL > /etc/httpd/conf.d/$DOMAIN.conf
<VirtualHost *:80>
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
<VirtualHost *:443>
    ServerName $DOMAIN
    DocumentRoot "$DOCUMENT_ROOT"
    SSLEngine on
    SSLCertificateFile "/etc/pki/tls/$DOMAIN/$DOMAIN.crt"
    SSLCertificateKeyFile "/etc/pki/tls/$DOMAIN/$DOMAIN.key"
    <FilesMatch "(\.env|.*\.config)$">
        Require all denied
    </FilesMatch>
    <Directory "$DOCUMENT_ROOT">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorDocument 404 /Utility/Error.php?code=404
    DirectoryIndex index.php index.html
</VirtualHost>
EOL

echo -e "${GREEN}Updating hosts...${NC}"
if ! grep -qw "${DOMAIN}" /etc/hosts; then
    echo "127.0.0.1 ${DOMAIN}" >> /etc/hosts
fi

echo -e "${GREEN}Adding PHP configuration...${NC}"
cat <<EOL > /etc/php.d/99-${DOMAIN}.ini
open_basedir = ${DOCUMENT_ROOT}:/tmp
EOL
cat "${SCRIPT_DIR}/php-settings-fedora.ini" >> /etc/php.d/99-${DOMAIN}.ini
chown root:root /etc/php.d/99-${DOMAIN}.ini
chmod 644 /etc/php.d/99-${DOMAIN}.ini

echo -e "${GREEN}Restarting Apache...${NC}"
systemctl restart httpd

echo -e "${GREEN}Setup completed. You can access the site at https://${DOMAIN}${NC}"