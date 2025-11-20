#!/bin/bash
set -e

# Check if the system is Debian-based
if [[ ! -f /etc/debian_version ]]; then
  echo -e "This is not a Debian-based system. Exiting..."
  exit 1
fi

if [ "$(id -u)" -ne 0 ]; then
    echo -e "Please run as root (use sudo)."
    exit 1
fi

# Check if Apache is installed
if ! dpkg-query -W -f='${Status}' apache2 2>/dev/null | grep -q "install ok installed"; then
  echo -e "Apache is not installed. Please install Apache before running this script."
  exit 1
fi

# Check if the document root is provided
if [ -z "$1" ]; then
  echo -e "Usage: $0 /path/to/document/root"
  exit 1
fi

GREEN="\033[32m"
NC="\033[0m"
DOCUMENT_ROOT=$1
DOMAIN="usiphp.net"
CERT_DIR="/etc/ssl/$DOMAIN"
CONFIG_FILE="/etc/apache2/sites-available/$DOMAIN.conf"
TMP_CONFIG_FILE="/tmp/$DOMAIN.conf"

# Install required packages
echo -e "${GREEN}Installing required packages...${NC}"
sudo apt-get install -y \
      openssl \
      php-soap \
      php-xsl \
      php-xdebug

# Enable required modules
echo -e "${GREEN}Enabling required modules...${NC}"
sudo a2enmod \
      rewrite \
      dir \
      deflate \
      ssl

# Create SSL certificate
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

# Create the Web site's apache conf
echo -e "${GREEN}Updating Apache configuration...${NC}"
cat <<EOL > $TMP_CONFIG_FILE
ServerName localhost
<VirtualHost 127.0.0.1:80>
    ServerName www.$DOMAIN
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
    ServerName www.$DOMAIN
    DocumentRoot "$DOCUMENT_ROOT"
    SSLEngine on
    SSLCertificateFile "$CERT_DIR/$DOMAIN.crt"
    SSLCertificateKeyFile "$CERT_DIR/$DOMAIN.key"
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
    <IfModule mod_php.c>
        php_value zlib.output_compression On
        php_value zlib.output_compression_level 6
    </IfModule>
</VirtualHost>
EOL
sudo mv $TMP_CONFIG_FILE $CONFIG_FILE

# Add entry to /etc/hosts if not already present
echo -e "${GREEN}Updating hosts...${NC}"
if ! grep -qw "www.${DOMAIN}" /etc/hosts; then
    echo "127.0.0.1 www.${DOMAIN}" | sudo tee -a /etc/hosts
fi

# Enable the site
echo -e "${GREEN}Enabling the site...${NC}"
sudo a2ensite $DOMAIN.conf
sudo systemctl restart apache2
