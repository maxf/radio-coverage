rsync -vu 000-default.conf /etc/apache2/sites-available/
rsync -vu test.fcgi clip2.py couv.json /var/www/html/
rsync -rvu data /var/www/html/
service apache2 restart
