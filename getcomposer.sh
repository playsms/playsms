#!/bin/sh

PATHSRC=$(pwd)

echo
echo "Getting composer from https://getcomposer.org"
echo
echo "Please wait while this script downloading composer"
echo

rm -f ./composer ./composer.phar

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'ed0feb545ba87161262f2d45a633e34f591ebb3381f2e0063c345ebea4d228dd0043083717770234ec00c5a9f9593792') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"

if [ -e "./composer.phar" ]; then
	ln -s ./composer.phar ./composer >/dev/null 2>&1
	chmod +x ./composer.phar >/dev/null 2>&1
fi

echo "Composer has been installed"
echo
echo "Please wait while composer getting and updating required packages"
echo

if [ -x "./composer.phar" ]; then
	cd "$PATHSRC"
	./composer.phar update
	exit $?
else
	echo "ERROR: unable to get composer from https://getcomposer.org"
	exit 1
fi
