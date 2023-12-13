sudo chown -R alfonso:www-data /home/alfonso/www/Adopool
sudo chown -R alfonso:www-data /home/alfonso/www/Adopool/APIRest
find /home/alfonso/www/Adopool/APIRest -type d -exec chmod 775 {} \;
find /home/alfonso/www/Adopool/APIRest -type f -exec chmod 775 {} \;

sudo chown -R alfonso:www-data /var/www/Adopool/APIRest
find /var/www/Adopool -type d -exec chmod 775 {} \;
find /var/www/Adopool/APIRest -type d -exec chmod 775 {} \;
find /var/www/Adopool/APIRest -type f -exec chmod 775 {} \;


