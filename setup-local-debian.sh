#!/bin/bash

# Check if the document root is provided
if [ -z "$1" ]; then
  echo "Usage: $0 /path/to/document/root"
  exit 1
fi

DOCUMENT_ROOT=$1
DOMAIN="usiphp.net"
CERT_DIR="/etc/ssl/$DOMAIN"
CONFIG_FILE="/etc/apache2/sites-available/$DOMAIN.conf"

# Install required modules
sudo apt-get install openssl
sudo apt-get install php-soap
sudo apt-get install php-xsl
sudo apt-get install php-xdebug

# Check and enable mod_rewrite and mod_dir
enable_module_debian() {
  local module=$1
  if ! apache2ctl -M | grep -q "${module}_module"; then
    echo "Enabling $module..."
    sudo a2enmod $module
    sudo systemctl restart apache2
  fi
}
if [ -d /etc/apache2 ]; then
  enable_module_debian "rewrite"
  enable_module_debian "dir"
fi

# Create SSL certificate
mkdir -p $CERT_DIR
openssl genpkey -algorithm RSA -out $CERT_DIR/$DOMAIN.key
openssl req -new -x509 -key $CERT_DIR/$DOMAIN.key -out $CERT_DIR/$DOMAIN.crt -days 3650 -subj "/C=US/ST=State/L=City/O=Organization/OU=Department/CN=$DOMAIN"

# Create the Web site's apache conf
cat <<EOL > $CONFIG_FILE
<VirtualHost *:80>
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

<VirtualHost *:443>
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
</VirtualHost>
EOL

# Add entry to /etc/hosts if not already present
if ! grep -q "127.0.0.1 www.$DOMAIN" /etc/hosts; then
  echo "127.0.0.1 www.$DOMAIN" | sudo tee -a /etc/hosts
fi

# Enable the site configuration
if [ -d /etc/apache2/sites-available ]; then
  sudo a2ensite $DOMAIN.conf
  sudo systemctl restart apache2
fi

echo "Apache configuration with SSL and HTTP to HTTPS redirection has been generated and saved to $CONFIG_FILE"
echo "SSL certificate and key have been generated and saved to $CERT_DIR"
echo "Entry added to /etc/hosts if required"
