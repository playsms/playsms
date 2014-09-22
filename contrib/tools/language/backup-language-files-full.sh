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
	echo "Above example will backup language files id_ID and copy them to an archive file"
	echo "playsms-language-id_ID-backup.tar.gz"
	echo
	echo "Please note that this script will backup .mo, .pot and .po files"
	echo
	exit 1
fi

CWD=$(pwd)

TMP=$(mktemp -d)
TMPLANG=$(mktemp)

cd $PLAYSMS
find . -type d -name "language" | sed -e "s/\/[^\/]*$//" > $TMPLANG
for i in `cat $TMPLANG` ; do
	mkdir -p "$i/language/$LANG"
	mkdir -p "$TMP/$i/language/$LANG"
	cp -rR $i/language/messages.pot $TMP/$i/language/
	cp -rR $i/language/$LANG $TMP/$i/language/
done

find $TMP -type f -name '*.html' -exec rm {} \;

cd $CWD

mv $TMP playsms-language-$LANG-full
tar -zcvf playsms-language-$LANG-backup-full.tar.gz playsms-language-$LANG-full
rm -rf playsms-language-$LANG-full

exit 0
