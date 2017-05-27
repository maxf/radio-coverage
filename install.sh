rsync -vu test.fcgi clip2.py couv.json /var/www/html/
rsync -rvu data /var/www/html/
service apache2 restart
