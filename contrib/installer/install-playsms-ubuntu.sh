#!/bin/bash

# INSTALL DATA
# ============


# Please change install data below to suit your system configurations

# Please do not change variable name, you may change only the value

# MySQL database username
DBUSER="root"

# MySQL database password
DBPASS="password"

# MySQL database name
DBNAME="playsms"

# Web server's user, for example apache2 user by default is www-data
WEBSERVERUSER="www-data"

# Web server's group, for example apache2 group by default is www-data
WEBSERVERGROUP="www-data"

# Path to playSMS web files
PATHWEB="/var/www/playsms"

# Path to playSMS log files
PATHLOG="/var/log/playsms"

# Path to playSMS lib files, used by feature SMS command
PATHLIB="/var/lib/playsms"

# Path to playSMS spool files, used by gnokii gateway
PATHSPO="/var/spool/playsms"

# Path to playSMS extracted source files
PATHSRC="/usr/local/src/playsms-0.9.8"


# END OF INSTALL DATA
# ===================





# ========================================
# DO NOT CHANGE ANYTHING BELOW THIS LINE #
# UNLESS YOU KNOW WHAT YOU'RE DOING      #
# ========================================





echo
echo "playSMS Install Script for Ubuntu"
echo "================================="
echo

USERID=$(id -u)
if [ "$USERID" != "0" ]; then
	echo "ERROR: You need to run this script as root"
	echo
	
	exit 1
fi

echo "Install data:"
echo

echo "MySQL username      = $DBUSER"
echo "MySQL password      = $DBPASS"
echo "MySQL database      = $DBNAME"
echo
echo "Web server user     = $WEBSERVERUSER"
echo "Web server group    = $WEBSERVERGROUP"
echo
echo "playSMS web path    = $PATHWEB"
echo "playSMS log path    = $PATHLOG"
echo "playSMS lib path    = $PATHLIB"
echo "playSMS spool path  = $PATHSPO"
echo
echo "playSMS source path = $PATHSRC"
echo

echo "=================================================================="
echo
echo "Please read and confirm install data above"
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
echo "Please read and check again the install data above"
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
chown -R $WEBSERVERUSER.$WEBSERVERGROUP $PATHWEB $PATHLOG $PATHLIB $PATHSPO
echo -n .
mkdir -p /etc/default /usr/local/bin
echo -n .
cp daemon/linux/etc/playsms /etc/default/
echo -n .
cp daemon/linux/bin/* /usr/local/bin/
echo -n .
cp daemon/linux/etc/playsms.init-ubuntu /etc/init.d/playsms
set +e
echo -n .
update-rc.d playsms defaults >/dev/null 2>&1
echo "end"
echo
/etc/init.d/playsms restart
echo

echo "playSMS has been successfully installed on your system"
echo

exit 0
