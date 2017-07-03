export LC_ALL=C

apt-get update && apt-get -y upgrade
apt-get -y install python-dev  software-properties-common python-software-properties python-pip apache2 libapache2-mod-fcgid

add-apt-repository -y ppa:ubuntugis/ppa
apt-get update
apt-get install -y gdal-bin libgdal-dev python-numpy-dev

pip install -U pip
apt-get install -y --reinstall build-essential
pip install rasterio flup requests
