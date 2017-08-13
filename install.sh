cp 000-default.conf /etc/apache2/sites-available/
cp couverture.fcgi clip.py couv.json config.json index.html /var/www/html/
rsync -rvu data /var/www/html/
mkdir -p /var/www/html/jobs
chmod a+rwx /var/www/html/jobs
service apache2 restart
