rsync -vu 000-default.conf /etc/apache2/sites-available/
rsync -vu couverture.fcgi clip.py couv.json index.html /var/www/html/
rsync -rvu data /var/www/html/
mkdir -p /var/www/html/jobs
chmod a+rwx /var/www/html/jobs
service apache2 restart
