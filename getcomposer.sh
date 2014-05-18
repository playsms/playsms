#!/bin/sh

USERID=$(id -u)
if [ "$USERID" != "0" ]; then
	echo
	echo "ERROR: You need to run this script as root"
	echo
	exit 1
fi

php -r "readfile('https://getcomposer.org/installer');" | php

if [ -e "composer.phar" ]; then
	rm -f /usr/local/bin/composer /usr/local/bin/composer.phar >/dev/null 2>&1
	ln -s composer.phar composer
	mv composer composer.phar /usr/local/bin/
fi

echo
echo "Composer has been installed"
echo
exit 0
