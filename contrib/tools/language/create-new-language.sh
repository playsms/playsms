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
	echo "Above example will create new file playsms-language-id_ID.tar.gz"
	echo "containing new language files based on en_US, if the language already"
	echo "has some translations they will be preserved"
	echo
	exit 1
fi

CWD=$(pwd)

TMP=$(mktemp -d)
TMPLANG=$(mktemp)

cd $PLAYSMS
find . -type d -name "language" | sed -e "s/\/[^\/]*$//" > $TMPLANG
for i in `cat $TMPLANG` ; do
	mkdir -p "$TMP/$i/language/$LANG"
	cp -rR $i/language/en_US/* $TMP/$i/language/$LANG/
	cp -rR $i/language/messages.pot $TMP/$i/language/
done

rm $TMPLANG

cd $CWD

find $TMP -type f -name '*.mo' -exec rm {} \;

mv $TMP playsms-language-$LANG
tar -zcvf playsms-language-$LANG.tar.gz playsms-language-$LANG
rm -rf playsms-language-$LANG

exit 0
