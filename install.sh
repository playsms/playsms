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
	echo "    ./install.sh"
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
echo "playSMS Install Script"
echo
echo "==================================================================="
echo "WARNING:"
echo "- This install script WILL NOT backup currently installed playSMS"
echo "- This install script WILL NOT upgrade currently installed playSMS"
echo "- This install script WILL REMOVE your current playSMS database"
echo "- This install script is compatible ONLY with playSMS version 1.4.4"
echo "- This install script designed to work only on Linux OS"
echo "- Please BACKUP before proceeding"
echo "==================================================================="
echo

USERID=$(id -u)
if [ "$USERID" = "0" ]; then
	echo "You ARE running this installation script as root"
	echo
	echo "That means you need to make sure that you know what you're doing"
	echo
	echo "==================================================================="
	echo
	echo "Proceed ?"
	echo
	confirm=
	while [ -z $confirm ]
	do
		echo "When you're ready press [y/Y] or press [Control+C] to cancel"
		read -p "> " confirm
		if [[ $confirm == 'y' ]]; then
			break
		fi
		if [[ $confirm == 'Y' ]]; then
			break
		fi
		confirm=
	done
	echo
	echo "==================================================================="
	echo
else
	echo "You are NOT running this installation script as root"
	echo
	echo "That means you need to make sure that this Linux user has"
	echo "permission to create necessary directories"
	echo
	echo "==================================================================="
	echo
	echo "Proceed ?"
	echo
	confirm=
	while [ -z $confirm ]
	do
		echo "When you're ready press [y/Y] or press [Control+C] to cancel"
		read -p "> " confirm
		if [[ $confirm == 'y' ]]; then
			break
		fi
		if [[ $confirm == 'Y' ]]; then
			break
		fi
		confirm=
	done
	echo
	echo "==================================================================="
	echo
fi

PLAYSMSSRCVER=$(cat $PATHSRC/VERSION.txt)

echo "INSTALL DATA:"
echo

echo "Admin username      = admin"
echo "Admin password      = $ADMINPASSWORD"
echo "MySQL username      = $DBUSER"
echo "MySQL password      = $DBPASS"
echo "MySQL database      = $DBNAME"
echo "MySQL host          = $DBHOST"
echo "MySQL port          = $DBPORT"

if [ "$USERID" = "0" ]; then
echo "Web server user     = $WEBSERVERUSER"
echo "Web server group    = $WEBSERVERGROUP"
fi

echo "playSMS web URL     = $URLWEB"
echo "playSMS web path    = $PATHWEB"
echo "playSMS bin path    = $PATHBIN"
echo "playSMS log path    = $PATHLOG"
echo "playSMS storage     = $PATHSTR"
echo "playSMS conf path   = $PATHCONF"
echo "playSMS source path = $PATHSRC"
echo "playSMS version     = $PLAYSMSSRCVER"
echo

echo "==================================================================="
echo
echo "Please read and confirm INSTALL DATA above"
echo
confirm=
while [ -z $confirm ]
do
	echo "When you're ready press [y/Y] or press [Control+C] to cancel"
	read -p "> " confirm
	if [[ $confirm == 'y' ]]; then
		break
	fi
	if [[ $confirm == 'Y' ]]; then
		break
	fi
	confirm=
done
echo
echo "==================================================================="
echo
sleep 1

echo "Checking $PATHWEB..."
echo
if [ -d "$PATHWEB" ] && [ -f "$PATHWEB/init.php" ] && [ -f "$PATHWEB/config.php" ]; then
	echo "ERROR: playSMS found installed on $PATHWEB"
	echo
	echo "Please backup and remove/empty $PATHWEB before proceeding"
	echo
	exit 1
fi
sleep 1

echo "Checking $PATHSTR..."
echo
if [ -d "$PATHSTR" ] && [ -d "$PATHSTR/composer" ] && [ -d "$PATHSTR/custom" ] && [ -d "$PATHSTR/tmp" ]; then
	echo "ERROR: playSMS found installed on $PATHSTR"
	echo
	echo "Please backup and remove/empty $PATHSTR before proceeding"
	echo
	exit 1
fi
sleep 1

echo "Checking $PATHBIN/playsmsd..."
echo
if [ -f "$PATHBIN/playsmsd" ]; then
	echo "WARNING: playSMS daemon found installed on $PATHBIN"
	echo
	confirm=
	while [ -z $confirm ]
	do
		echo "To continue and replace $PATHBIN/playsmsd press [y/Y] or press [Control+C] to cancel"
		read -p "> " confirm
		if [[ $confirm == 'y' ]]; then
			break
		fi
		if [[ $confirm == 'Y' ]]; then
			break
		fi
		confirm=
	done
	echo
fi
sleep 1

echo "Checking database..."
echo
FOUND_DATABASE=`mysql -u$DBUSER -p$DBPASS -h$DBHOST --skip-column-names --batch -e "SHOW DATABASES LIKE '$DBNAME'" | wc -l`
if [ "$FOUND_DATABASE" == "1" ]; then
	echo "Found database '$DBNAME'"
	echo
	echo "This install script will empty database '$DBNAME'"
	echo
	confirm=
	while [ -z $confirm ]
	do
		echo "To continue emptying database '$DBNAME' press [y/Y] or press [Control+C] to cancel"
		read -p "> " confirm
		if [[ $confirm == 'y' ]]; then
			break
		fi
		if [[ $confirm == 'Y' ]]; then
			break
		fi
		confirm=
	done
	echo
else
	echo "ERROR: database '$DBNAME' not found"
	echo
	echo "Please create database '$DBNAME' before proceeding"
	echo 
	echo "Please note that database user '$DBUSER' must be granted permission"
	echo "SELECT, INSERT, UPDATE, DELETE to database '$DBNAME'"
	echo
	exit 1
fi
sleep 1

echo "==================================================================="
echo
sleep 1

echo "Are you sure ?"
echo
echo "Please read and check again the INSTALL DATA above"
echo
confirm=
while [ -z $confirm ]
do
	echo "When you're ready press [y/Y] or press [Control+C] to cancel"
	read -p "> " confirm
	if [[ $confirm == 'y' ]]; then
		break
	fi
	if [[ $confirm == 'Y' ]]; then
		break
	fi
	confirm=
done
echo
echo "==================================================================="
echo
echo "Installation is in progress"
echo
echo -n "DO NOT press [Control+C] until this script ends"
sleep 1
echo -n .
sleep 1
echo -n .
sleep 1
echo -n .
sleep 1
echo
echo
echo "==================================================================="
echo
sleep 1

set +e
echo "Setup database..."
echo
mysql -u$DBUSER -p$DBPASS -h$DBHOST -P$DBPORT $DBNAME < $PATHSRC/db/playsms.sql
ADMINPASSWORD=$(echo -n $ADMINPASSWORD | md5sum | cut -d' ' -f1)
mysql -u$DBUSER -p$DBPASS -h$DBHOST -P$DBPORT $DBNAME -e "UPDATE playsms_tblUser SET password='$ADMINPASSWORD',salt='' WHERE uid=1"
set -e

echo -n "Copying files."
echo -n .
mkdir -p $PATHWEB $PATHLOG $PATHSTR
echo -n .
cp -rf $PATHSRC/web/* $PATHWEB
echo -n .
cp -rf $PATHSRC/storage/* $PATHSTR
echo -n .
echo

echo
echo "Getting composer..."
echo

php -r "readfile('https://getcomposer.org/installer');" | php -q

echo "Move composer.phar to $PATHSTR/composer/"
echo
mkdir -p "$PATHSTR/composer"
mv composer.phar "$PATHSTR/composer/" >/dev/null 2>&1
cp storage/composer/composer.json.dist "$PATHSTR/composer/composer.json" >/dev/null 2>&1

if [ -e "$PATHSTR/composer/composer.json" ]; then
	chmod -x "$PATHSTR/composer/composer.json"
else
	echo "ERROR: unable to find composer.json on storage directory"
	echo
	exit 1
fi

if [ -e "$PATHSTR/composer/composer.phar" ]; then
	chmod +x "$PATHSTR/composer/composer.phar"

	if [ -x "$PATHSTR/composer/composer.phar" ]; then
		echo "Getting packages..."
		echo
		$PATHSTR/composer/composer.phar --working-dir="$PATHSTR/composer/" update
		echo
	else
		echo "ERROR: unable to get composer from https://getcomposer.com"
		echo
		exit 1
	fi
else
	echo "ERROR: unable to get composer from https://getcomposer.com"
	echo
	exit 1
fi

cp $PATHSTR/custom/application/configs/config-dist.php $PATHSTR/custom/application/configs/config.php
echo -n .
sed -i "s|#DBHOST#|$DBHOST|g" $PATHSTR/custom/application/configs/config.php
echo -n .
sed -i "s|#DBPORT#|$DBPORT|g" $PATHSTR/custom/application/configs/config.php
echo -n .
sed -i "s|#DBNAME#|$DBNAME|g" $PATHSTR/custom/application/configs/config.php
echo -n .
sed -i "s|#DBUSER#|$DBUSER|g" $PATHSTR/custom/application/configs/config.php
echo -n .
sed -i "s|#DBPASS#|$DBPASS|g" $PATHSTR/custom/application/configs/config.php
echo -n .

cp $PATHWEB/config-dist.php $PATHWEB/config.php
echo -n .
sed -i "s|#PATHLOG#|$PATHLOG|g" $PATHWEB/config.php
echo -n .
sed -i "s|#PATHBIN#|$PATHBIN|g" $PATHWEB/config.php
echo -n .
sed -i "s|#PATHSTR#|$PATHSTR|g" $PATHWEB/config.php
echo -n .
sed -i "s|#PATHWEB#|$PATHWEB|g" $PATHWEB/config.php
echo -n .
sed -i "s|#URLWEB#|$URLWEB|g" $PATHWEB/config.php
echo -n .

if [ "$USERID" = "0" ]; then
	chown -R $WEBSERVERUSER.$WEBSERVERGROUP $PATHLOG/* $PATHSTR/tmp $PATHWEB/*
	echo -n .
fi

mkdir -p $PATHCONF $PATHBIN
echo -n .
touch $PATHCONF/playsmsd.conf
echo -n .
echo "PLAYSMS_URL=\"$URLWEB\"" > $PATHCONF/playsmsd.conf
echo "PLAYSMS_WEB=\"$PATHWEB\"" > $PATHCONF/playsmsd.conf
echo "PLAYSMS_STR=\"$PATHSTR\"" >> $PATHCONF/playsmsd.conf
echo "PLAYSMS_LOG=\"$PATHLOG\"" >> $PATHCONF/playsmsd.conf
echo "PLAYSMS_BIN=\"$PATHBIN\"" >> $PATHCONF/playsmsd.conf
echo "DAEMON_SLEEP=\"1\"" >> $PATHCONF/playsmsd.conf
echo "ERROR_REPORTING=\"E_ALL ^ (E_NOTICE | E_WARNING)\"" >> $PATHCONF/playsmsd.conf
chmod 644 $PATHCONF/playsmsd.conf
echo -n .
rm -f $PATHBIN/playsmsd
cp -rR $PATHSRC/daemon/linux/bin/playsmsd.php $PATHBIN/playsmsd
chmod 700 $PATHBIN/playsmsd
echo -n .
echo "done"
echo

echo "Checking installation..."
$PATHBIN/playsmsd $PATHCONF/playsmsd.conf check
sleep 3
echo

echo "Restarting playSMS daemon..."
$PATHBIN/playsmsd $PATHCONF/playsmsd.conf restart
sleep 3
echo

echo "Checking playSMS daemon status..."
$PATHBIN/playsmsd $PATHCONF/playsmsd.conf status
sleep 3
echo

echo "playSMS install script finished"
echo
echo "Please review installation log above before testing"
echo

cp install.conf install.conf.backup >/dev/null 2>&1

exit 0
