#!/bin/bash

PLAYSMS=$1
LANG=$2

ERR=0

if [ -z "$PLAYSMS" ]; then
	ERR=1
fi

if [ -z "$LANG" ]; then
	ERR=1
fi

if [ "$ERR" = "1" ]; then
	echo
	echo "Usage   : $0 <playSMS installation path> <language>"
	echo
	echo "Example : $0 /var/www/playsms id_ID"
	echo
	echo "Above example will restore the language pack called either"
	echo "playsms-language-id_ID.tar.gz or playsms-language-id_ID.bak.tar.gz"
	echo
	echo
	exit 1
fi

CWD=$(pwd)
ls -1
if [ -e playsms-language-$LANG.tar.gz ] ; then
	LANGFILE=playsms-language-$LANG.tar.gz
elif [ -e playsms-language-$LANG-backup.tar.gz ] ; then
	LANGFILE=playsms-language-$LANG-backup.tar.gz
else
	echo "We couldn't find the language file"
	exit 1
fi

tar -xvzf $LANGFILE --strip-components 2 -C $PLAYSMS/plugin/

exit 0
