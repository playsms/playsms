#!/bin/sh

echo
echo "Getting composer from https://getcomposer.com"
echo
echo "Please wait while this script downloading composer"
echo

php -r "readfile('https://getcomposer.org/installer');" | php >/dev/null 2>&1

if [ -e "composer.phar" ]; then
	chmod +x ./composer.phar >/dev/null 2>&1
else
	echo "ERROR: unable to get composer from https://getcomposer.com"
	echo
	exit 1
fi

echo "Composer has been installed"
echo
echo "Please wait while composer getting and updating required packages"
echo

if [ -x "./composer.phar" ]; then
	./composer.phar update
else
	echo "ERROR: unable to run composer.phar"
	echo
	exit 1
fi

echo
echo "Composer has been installed and packages have been updated"
echo
exit 0
