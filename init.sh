apt-get update && apt-get -y upgrade
apt-get -y install python-dev  software-properties-common python-software-properties python-pip

add-apt-repository -y ppa:ubuntugis/ppa
apt-get update
apt-get install -y gdal-bin libgdal-dev python-numpy-dev

pip install -U pip
apt-get install -y --reinstall build-essential
pip install rasterio
