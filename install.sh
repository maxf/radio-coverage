rsync -vu 000-default.conf /etc/apache2/sites-available/
rsync -vu couverture.fcgi clip.py couv.json /var/www/html/
rsync -rvu data /var/www/html/
mkdir -p /var/www/html/logs
chmod a+rwx /var/www/html/logs
service apache2 restart
