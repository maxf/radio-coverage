# radio-coverage

How to use this

## Using vagrant

- clone this repository
- copy the GeoTIFF files in the `./data` subdir
- run:

```
vagrant up
vagrant ssh
```

Go to the install section

## On a server

You will need a fresh instance of an ubuntu trusty64 server. Then just ssh into it and continue

## Install

On the server or the vagrant VM:

```
cd /vagrant
sudo ./init.sh
sudo ./install.sh
exit
```

then edit `test.sh` and set the 2 URLs to, respectively, the callback URL and the server URL. Then:

```
./test.sh
```

This should immediately return a message. A few minutes letter the callback URL will receive an HTTP POST request with the results passed.
