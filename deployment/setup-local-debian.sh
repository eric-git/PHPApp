#!/bin/bash
set -e
GREEN="\033[32m"
NC="\033[0m"

echo -e "${GREEN}Preparing...${NC}"
if [ ! -x "$0" ]; then
    sudo chmod +x "$0"
fi
if [[ ! -f /etc/debian_version ]]; then
  echo -e "This is not a Debian-based system. Exiting..."
  exit 1
fi
if [ "$(id -u)" -ne 0 ]; then
    echo -e "Please run as root (use sudo). Exiting..."
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
CERT_DIR="/etc/ssl/$DOMAIN"
CONFIG_FILE="/etc/apache2/sites-available/$DOMAIN.conf"
TMP_CONFIG_FILE="/tmp/$DOMAIN.tmp"

echo -e "${GREEN}Installing required packages...${NC}"
sudo apt-get install -y \
      openssl \
      php-soap \
      php-xsl \
      php-xdebug

echo -e "${GREEN}Enabling required modules...${NC}"
sudo a2enmod \
      rewrite \
      dir \
      deflate \
      ssl

echo -e "${GREEN}Creating SSL certificate...${NC}"
mkdir -p $CERT_DIR
sudo openssl genpkey \
  -algorithm RSA \
  -out $CERT_DIR/$DOMAIN.key
sudo openssl req \
  -new \
  -x509 \
  -key $CERT_DIR/$DOMAIN.key \
  -out $CERT_DIR/$DOMAIN.crt \
  -days 3650 \
  -subj "/C=AU/ST=State/L=City/O=Organization/OU=Department/CN=$DOMAIN" \
  -addext "subjectAltName=DNS:$DOMAIN"
sudo cp $CERT_DIR/$DOMAIN.crt /usr/local/share/ca-certificates/$DOMAIN.crt
sudo update-ca-certificates --fresh

echo -e "${GREEN}Updating Apache configuration...${NC}"
cat <<EOL > $TMP_CONFIG_FILE
ServerName localhost
<VirtualHost 127.0.0.1:80>
    ServerName $DOMAIN
    DocumentRoot "$DOCUMENT_ROOT"
    <Directory "$DOCUMENT_ROOT">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{HTTPS} off
        RewriteRule ^(.*)\$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
    </IfModule>
</VirtualHost>
<VirtualHost 127.0.0.1:443>
    ServerName $DOMAIN
    DocumentRoot "$DOCUMENT_ROOT"
    SSLEngine on
    SSLCertificateFile "$CERT_DIR/$DOMAIN.crt"
    SSLCertificateKeyFile "$CERT_DIR/$DOMAIN.key"
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
    <IfModule mod_dir.c>
        DirectoryIndex index.php default.php Default.htm Default.asp index.htm index.html iisstart.htm default.aspx
    </IfModule>
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
    </IfModule>
</VirtualHost>
EOL
sudo mv $TMP_CONFIG_FILE $CONFIG_FILE

echo -e "${GREEN}Updating hosts...${NC}"
if ! grep -qw "${DOMAIN}" /etc/hosts; then
    echo "127.0.0.1 ${DOMAIN}" | sudo tee -a /etc/hosts
fi

echo -e "${GREEN}Adding PHP configuration...${NC}"
phpVersion=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
customIni="/etc/php/${phpVersion}/apache2/conf.d/99-${DOMAIN}.ini"
echo "open_basedir = ${DOCUMENT_ROOT}:/tmp" > $TMP_CONFIG_FILE
cat "${SCRIPT_DIR}/php-settings-debian.ini" >> $TMP_CONFIG_FILE
sudo mv $TMP_CONFIG_FILE $customIni
sudo chown root:root $customIni
sudo chmod 644 $customIni

echo -e "${GREEN}Enabling the site...${NC}"
sudo a2ensite $DOMAIN.conf
sudo systemctl restart apache2

echo -e "${GREEN}Setup completed. You can access the site at https://${DOMAIN}${NC}"