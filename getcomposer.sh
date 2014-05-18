#!/bin/sh

PATHSRC=$(pwd)

USERID=$(id -u)
if [ "$USERID" != "0" ]; then
	echo
	echo "ERROR: You need to run this script as root"
	echo
	exit 1
fi

echo
echo "Getting composer from https://getcomposer.com"
echo
echo "Please wait while this script downloading composer"
echo

cd /tmp/

php -r "readfile('https://getcomposer.org/installer');" | php >/dev/null 2>&1

if [ -e "composer.phar" ]; then
	rm -f /usr/local/bin/composer /usr/local/bin/composer.phar >/dev/null 2>&1
	ln -s composer.phar composer >/dev/null 2>&1
	mv composer composer.phar /usr/local/bin/ >/dev/null 2>&1
	chmod +x /usr/local/bin/composer /usr/local/bin/composer.phar >/dev/null 2>&1
fi

echo "Composer has been installed"
echo
echo "Pleas wait while composer getting and updating required packages"
echo

if [ -x "/usr/local/bin/composer.phar" ]; then
	cd "$PATHSRC"
	/usr/local/bin/composer.phar update
else
	echo "ERROR: unable to get composer from https://getcomposer.com"
	echo
	exit 1
fi

echo
echo "Composer has been installed and packages has been updated"
echo
exit 0
