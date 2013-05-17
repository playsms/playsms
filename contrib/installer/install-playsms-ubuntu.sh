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
	exit 1
fi

. $INSTALLCONF




# ========================================
# DO NOT CHANGE ANYTHING BELOW THIS LINE #
# UNLESS YOU KNOW WHAT YOU'RE DOING      #
# ========================================





clear
echo
echo "playSMS Install Script for Ubuntu"
echo
echo "=================================================================="
echo "WARNING:"
echo "- This install script WILL NOT upgrade currently installed playSMS"
echo "- This install script WILL REMOVE your current playSMS database"
echo "- Please backup before proceeding"
echo "=================================================================="
echo

USERID=$(id -u)
if [ "$USERID" != "0" ]; then
	echo "ERROR: You need to run this script as root"
	echo
	exit 1
fi

echo "INSTALL DATA:"
echo

echo "MySQL username      = $DBUSER"
echo "MySQL password      = $DBPASS"
echo "MySQL database      = $DBNAME"
echo
echo "Web server user     = $WEBSERVERUSER"
echo "Web server group    = $WEBSERVERGROUP"
echo
echo "playSMS source path = $PATHSRC"
echo
echo "playSMS web path    = $PATHWEB"
echo "playSMS log path    = $PATHLOG"
echo "playSMS lib path    = $PATHLIB"
echo "playSMS spool path  = $PATHSPO"
echo "playSMS bin path    = $PATHBIN"
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
echo "Do not press [Control+C] until this script ends"
echo
echo "=================================================================="
echo

sleep 3

echo -n "Start"
set -e
echo -n .
cd $PATHSRC
echo -n .
mkdir -p $PATHWEB $PATHLOG $PATHLIB $PATHSPO
echo -n .
cp -rR web/* $PATHWEB
set +e
echo -n .
mysqladmin -u $DBUSER -p$DBPASS create $DBNAME >/dev/null 2>&1
set -e
echo -n .
mysql -u $DBUSER -p$DBPASS $DBNAME < db/playsms.sql
echo -n .
cp $PATHWEB/config-dist.php $PATHWEB/config.php
echo -n .
sed -i "s/#DBNAME#/$DBNAME/g" $PATHWEB/config.php
echo -n .
sed -i "s/#DBUSER#/$DBUSER/g" $PATHWEB/config.php
echo -n .
sed -i "s/#DBPASS#/$DBPASS/g" $PATHWEB/config.php
echo -n .
sed -i "s|#PATHLOG#|$PATHLOG|g" $PATHWEB/config.php
echo -n .
chown -R $WEBSERVERUSER.$WEBSERVERGROUP $PATHWEB $PATHLOG $PATHLIB $PATHSPO
echo -n .
mkdir -p /etc/default $PATHBIN
echo -n .
cp daemon/linux/etc/playsms-install-script /etc/default/playsms
echo -n .
sed -i "s|#PATHWEB#|$PATHWEB|g" /etc/default/playsms
echo -n .
sed -i "s|#PATHBIN#|$PATHBIN|g" /etc/default/playsms
echo -n .
sed -i "s|#PATHLOG#|$PATHLOG|g" /etc/default/playsms
echo -n .
sed -i "s|#PATHSPO#|$PATHSPO|g" /etc/default/playsms
echo -n .
sed -i "s|#PATHLIB#|$PATHLIB|g" /etc/default/playsms
echo -n .
cp daemon/linux/bin/* $PATHBIN
echo -n .
cp daemon/linux/etc/playsms.init-ubuntu /etc/init.d/playsms
set +e
echo -n .
update-rc.d playsms defaults >/dev/null 2>&1
echo "end"
echo
/etc/init.d/playsms stop >/dev/null 2>&1
sleep 2
/etc/init.d/playsms start
echo

echo "playSMS has been successfully installed on your system"
echo

exit 0
