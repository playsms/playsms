#!/bin/bash

USERID=$(id -u)
if [ "$USERID" = "0" ]; then
	echo
	echo "You are running this installation script as root"
	echo
	echo "Aborting installation, please run as normal Linux user"
	echo
	exit 1
fi

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
echo "playSMS web URL     = $URLWEB"
echo "playSMS web path    = $PATHWEB"
echo "playSMS bin path    = $PATHBIN"
echo "playSMS log path    = $PATHLOG"
echo "playSMS storage     = $PATHSTR"
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
if [ -d "$PATHWEB" ] && [ -f "$PATHWEB/init.php" ] && [ -f "$PATHWEB/appsetup.php" ]; then
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
	echo "ERROR: database '$DBNAME' not found or user have no permission to access '$DBNAME'"
	echo
	echo "Database '$DBNAME' must be created prior to running this installation script"
	echo 
	echo "Also note that database user '$DBUSER' must be granted permission"
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
mkdir -p $PATHWEB $PATHLOG $PATHSTR $PATHBIN

if [ ! -d "$PATHWEB" ]; then
	echo "ERROR: unable to create $PATHWEB"
	echo
	exit 1
fi

if [ ! -d "$PATHLOG" ]; then
	echo "ERROR: unable to create $PATHLOG"
	echo
	exit 1
fi

if [ ! -d "$PATHSTR" ]; then
	echo "ERROR: unable to create $PATHSTR"
	echo
	exit 1
fi

if [ ! -d "$PATHBIN" ]; then
	echo "ERROR: unable to create $PATHBIN"
	echo
	exit 1
fi

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

cp $PATHWEB/appsetup-dist.php $PATHWEB/appsetup.php
echo -n .
sed -i "s|#PATHLOG#|$PATHLOG|g" $PATHWEB/appsetup.php
echo -n .
sed -i "s|#PATHBIN#|$PATHBIN|g" $PATHWEB/appsetup.php
echo -n .
sed -i "s|#PATHSTR#|$PATHSTR|g" $PATHWEB/appsetup.php
echo -n .
sed -i "s|#PATHWEB#|$PATHWEB|g" $PATHWEB/appsetup.php
echo -n .
sed -i "s|#URLWEB#|$URLWEB|g" $PATHWEB/appsetup.php
echo -n .

rm -f $PATHBIN/playsmsd
cp -rR $PATHSRC/daemon/linux/bin/playsmsd.php $PATHBIN/playsmsd
chmod 700 $PATHBIN/playsmsd
echo -n .
echo "done"
echo

touch $PATHLOG/playsms.log >/dev/null 2>&1
chmod 664 $PATHLOG/playsms.log >/dev/null 2>&1

touch $PATHLOG/audit.log >/dev/null 2>&1
chmod 664 $PATHLOG/audit.log >/dev/null 2>&1

export PLAYSMS_WEB="$PATHWEB"

echo "Checking installation..."
$PATHBIN/playsmsd check
sleep 3
echo

echo "Restarting playSMS daemon..."
$PATHBIN/playsmsd restart
sleep 3
echo

echo "Checking playSMS daemon status..."
$PATHBIN/playsmsd status
sleep 3
echo

echo "playSMS install script finished"
echo
echo "Please review installation log above before testing"
echo

exit 0
