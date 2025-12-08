#!/bin/bash
set -euo pipefail
GREEN="\033[32m"
NC="\033[0m"

echo -e "${GREEN}Preparing...${NC}"
if [ ! -x "$0" ]; then
    chmod +x "$0"
fi
if [[ ! -f /etc/debian_version ]]; then
  echo -e "This is not a Debian-based system. Exiting..."
  exit 1
fi
if [ "$(id -u)" -ne 0 ]; then
    echo -e "Please run as root (use sudo ./$(basename "$0")). Exiting..."
    exit 1
fi
if ! dpkg-query -W -f='${Status}' apache2 2>/dev/null | grep -q "install ok installed"; then
  echo -e "Apache is not installed. Please install Apache before running this script. Exiting..."
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
apt-get install -y \
      openssl \
      php-soap \
      php-common \
      php-xml \
      php-xdebug

echo -e "${GREEN}Enabling required modules...${NC}"
a2enmod \
      rewrite \
      dir \
      deflate \
      ssl

echo -e "${GREEN}Creating SSL certificate...${NC}"
mkdir -p /etc/ssl/$DOMAIN
openssl genpkey \
  -algorithm RSA \
  -out /etc/ssl/$DOMAIN/$DOMAIN.key
openssl req \
  -new \
  -x509 \
  -key /etc/ssl/$DOMAIN/$DOMAIN.key \
  -out /etc/ssl/$DOMAIN/$DOMAIN.crt \
  -days 3650 \
  -subj "/C=AU/ST=State/L=City/O=Organization/OU=Department/CN=$DOMAIN" \
  -addext "subjectAltName=DNS:$DOMAIN"
install -m 0644 /etc/ssl/$DOMAIN/$DOMAIN.crt /usr/local/share/ca-certificates/
update-ca-certificates

echo -e "${GREEN}Updating Apache configuration...${NC}"
cat <<EOL > /etc/apache2/sites-available/$DOMAIN.conf
ServerName localhost
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
    SSLCertificateFile "/etc/ssl/$DOMAIN/$DOMAIN.crt"
    SSLCertificateKeyFile "/etc/ssl/$DOMAIN/$DOMAIN.key"
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

echo -e "${GREEN}Updating hosts...${NC}"
if ! grep -qw "${DOMAIN}" /etc/hosts; then
    echo "127.0.0.1 ${DOMAIN}" >> /etc/hosts
fi

echo -e "${GREEN}Adding PHP configuration...${NC}"
phpVersion=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
cat <<EOL > /etc/php/${phpVersion}/apache2/conf.d/99-${DOMAIN}.ini
open_basedir = ${DOCUMENT_ROOT}:/tmp
EOL
cat "${SCRIPT_DIR}/php-settings-linux.ini" >> /etc/php/${phpVersion}/apache2/conf.d/99-${DOMAIN}.ini
chown root:root /etc/php/${phpVersion}/apache2/conf.d/99-${DOMAIN}.ini
chmod 644 /etc/php/${phpVersion}/apache2/conf.d/99-${DOMAIN}.ini

echo -e "${GREEN}Enabling the site...${NC}"
a2ensite $DOMAIN.conf
systemctl restart apache2

echo -e "${GREEN}Setup completed. You can access the site at https://${DOMAIN}${NC}"
