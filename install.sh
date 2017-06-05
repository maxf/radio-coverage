rsync -vu 000-default.conf /etc/apache2/sites-available/
rsync -vu couverture.fcgi clip.py couv.json /var/www/html/
rsync -rvu data /var/www/html/
service apache2 restart
