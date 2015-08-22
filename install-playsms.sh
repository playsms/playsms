#!/bin/bash

INSTALLCONF="./install.conf"

if [ -n "$1" ]; then
	if [ -e "$1" ]; then
		INSTALLCONF=$1
	fi
fi

if [ ! -e "$INSTALLCONF" ]; then
	echo
	echo "ERROR: unable to find install.conf"
	echo
	echo "Please rename install.conf.dist to install.conf"
	echo "    cp install.conf.dist install.conf"
	echo
	echo "Edit install.conf to suit your system configuration"
	echo "    vi install.conf"
	echo
	echo "Please re-run this script once install.conf edited and saved"
	echo "    ./install-playsms.sh"
	echo
	exit 1
fi

. $INSTALLCONF




# ========================================
# DO NOT CHANGE ANYTHING BELOW THIS LINE #
# UNLESS YOU KNOW WHAT YOU'RE DOING      #
# ========================================





clear
echo
echo "playSMS Install Script for Ubuntu (Debian based)"
echo
echo "=================================================================="
echo "WARNING:"
echo "- This install script WILL NOT upgrade currently installed playSMS"
echo "- This install script WILL REMOVE your current playSMS database"
echo "- This install script is compatible ONLY with playSMS version 1.2"
echo "- Please BACKUP before proceeding"
echo "=================================================================="
echo

USERID=$(id -u)
if [ "$USERID" = "0" ]; then
	echo "You ARE running this installation script as root"
	echo "That means you need to make sure that you know what you're doing"
	echo
	echo "=================================================================="
	echo
	echo "Proceed ?"
	echo
	confirm=
	while [ -z $confirm ]
	do
		read -p "When you're ready press [y/Y] or press [Control+C] to cancel " confirm
		if [[ $confirm == 'y' ]]; then
			break
		fi
		if [[ $confirm == 'Y' ]]; then
			break
		fi
		confirm=
	done
	echo
	echo "=================================================================="
	echo
else
	echo "You are NOT running this installation script as root"
	echo "That means you need to make sure that this Linux user has"
	echo "permission to create necessary directories"
	echo
	echo "=================================================================="
	echo
	echo "Proceed ?"
	echo
	confirm=
	while [ -z $confirm ]
	do
		read -p "When you're ready press [y/Y] or press [Control+C] to cancel " confirm
		if [[ $confirm == 'y' ]]; then
			break
		fi
		if [[ $confirm == 'Y' ]]; then
			break
		fi
		confirm=
	done
	echo
	echo "=================================================================="
	echo
fi

echo "INSTALL DATA:"
echo

echo "MySQL username      = $DBUSER"
echo "MySQL password      = $DBPASS"
echo "MySQL database      = $DBNAME"
echo "MySQL host          = $DBHOST"
echo "MySQL port          = $DBPORT"
echo
echo "Web server user     = $WEBSERVERUSER"
echo "Web server group    = $WEBSERVERGROUP"
echo
echo "playSMS source path = $PATHSRC"
echo
echo "playSMS web path    = $PATHWEB"
echo "playSMS lib path    = $PATHLIB"
echo "playSMS bin path    = $PATHBIN"
echo "playSMS log path    = $PATHLOG"
echo
echo "playSMS conf path   = $PATHCONF"
echo

echo "=================================================================="
echo
echo "Please read and confirm INSTALL DATA above"
echo
confirm=
while [ -z $confirm ]
do
	read -p "When you're ready press [y/Y] or press [Control+C] to cancel " confirm
	if [[ $confirm == 'y' ]]; then
		break
	fi
	if [[ $confirm == 'Y' ]]; then
		break
	fi
	confirm=
done
echo
echo "=================================================================="
echo

sleep 1

echo "Are you sure ?"
echo
echo "Please read and check again the INSTALL DATA above"
echo
confirm=
while [ -z $confirm ]
do
	read -p "When you're ready press [y/Y] or press [Control+C] to cancel " confirm
	if [[ $confirm == 'y' ]]; then
		break
	fi
	if [[ $confirm == 'Y' ]]; then
		break
	fi
	confirm=
done
echo
echo "=================================================================="
echo
echo "Installation is in progress"
echo
echo "DO NOT press [Control+C] until this script ends"
echo
echo "=================================================================="
echo

sleep 3

echo "Getting composer from https://getcomposer.com"
echo
echo "Please wait while the install script downloading composer"
echo

php -r "readfile('https://getcomposer.org/installer');" | php >/dev/null 2>&1

if [ -e "./composer.phar" ]; then
	#rm -f /usr/local/bin/composer /usr/local/bin/composer.phar >/dev/null 2>&1
	rm -f ./composer >/dev/null 2>&1
	ln -s ./composer.phar ./composer >/dev/null 2>&1
	#mv composer composer.phar /usr/local/bin/ >/dev/null 2>&1
	#chmod +x /usr/local/bin/composer /usr/local/bin/composer.phar >/dev/null 2>&1
	chmod +x ./composer.phar >/dev/null 2>&1

	echo "Composer is ready in this folder"
	echo
	echo "Pleas wait while composer getting and updating required packages"
	echo

	if [ -x "./composer.phar" ]; then
		./composer.phar update
	else
		echo "ERROR: unable to get composer from https://getcomposer.com"
		echo
		exit 1
	fi

	echo
	echo "Composer has been installed and packages has been updated"
	echo
else
	echo "ERROR: unable to get composer from https://getcomposer.com"
	echo
	exit 1
fi

sleep 3

echo -n "Start"
set -e
echo -n .
mkdir -p $PATHWEB $PATHLIB $PATHLOG
echo -n .
cp -rf web/* $PATHWEB
set +e
echo -n .
mysqladmin -u $DBUSER -p$DBPASS -h $DBHOST -P $DBPORT create $DBNAME >/dev/null 2>&1
set -e
echo -n .
mysql -u $DBUSER -p$DBPASS -h $DBHOST -P $DBPORT $DBNAME < db/playsms.sql >/dev/null 2>&1
echo -n .
cp $PATHWEB/config-dist.php $PATHWEB/config.php
echo -n .
sed -i "s/#DBHOST#/$DBHOST/g" $PATHWEB/config.php
echo -n .
sed -i "s/#DBPORT#/$DBPORT/g" $PATHWEB/config.php
echo -n .
sed -i "s/#DBNAME#/$DBNAME/g" $PATHWEB/config.php
echo -n .
sed -i "s/#DBUSER#/$DBUSER/g" $PATHWEB/config.php
echo -n .
sed -i "s/#DBPASS#/$DBPASS/g" $PATHWEB/config.php
echo -n .
sed -i "s|#PATHLOG#|$PATHLOG|g" $PATHWEB/config.php
echo -n .

if [ "$USERID" = "0" ]; then
	chown -R $WEBSERVERUSER.$WEBSERVERGROUP $PATHWEB $PATHLIB $PATHLOG
	echo -n .
fi

mkdir -p $PATHCONF $PATHBIN
echo -n .
touch $PATHCONF/playsmsd.conf
echo -n .
echo "PLAYSMS_PATH=\"$PATHWEB\"" > $PATHCONF/playsmsd.conf
echo "PLAYSMS_LIB=\"$PATHLIB\"" >> $PATHCONF/playsmsd.conf
echo "PLAYSMS_BIN=\"$PATHBIN\"" >> $PATHCONF/playsmsd.conf
echo "PLAYSMS_LOG=\"$PATHLOG\"" >> $PATHCONF/playsmsd.conf
echo "DAEMON_SLEEP=\"1\"" >> $PATHCONF/playsmsd.conf
echo "ERROR_REPORTING=\"E_ALL ^ (E_NOTICE | E_WARNING)\"" >> $PATHCONF/playsmsd.conf
chmod 644 $PATHCONF/playsmsd.conf
echo -n .
cp -rR daemon/linux/bin/playsmsd.php $PATHBIN/playsmsd
chmod 755 $PATHBIN/playsmsd
echo -n .
echo "end"
echo
$PATHBIN/playsmsd $PATHCONF/playsmsd.conf check
sleep 3
echo
$PATHBIN/playsmsd $PATHCONF/playsmsd.conf start
sleep 3
echo
$PATHBIN/playsmsd $PATHCONF/playsmsd.conf status
sleep 3
echo

echo
echo "playSMS has been installed on your system"
echo
echo
echo "Your playSMS daemon script operational guide:"
echo 
echo "- To start it : playsmsd $PATHCONF/playsmsd.conf start"
echo "- To stop it  : playsmsd $PATHCONF/playsmsd.conf stop"
echo "- To check it : playsmsd $PATHCONF/playsmsd.conf check"
echo

cp install.conf install.conf.backup >/dev/null 2>&1

echo
echo
echo "ATTENTION"
echo "========="
echo
echo "When message \"unable to start playsmsd\" occurred above, please check:"
echo
echo "1. Possibly theres an issue with composer updates, try to run: \"./composer update\""
echo "2. Manually run playsmsd, \"playsmsd $PATHCONF/playsmsd.conf start\", and then \"playsmsd $PATHCONF/playsmsd.conf status\""
echo
echo

exit 0
