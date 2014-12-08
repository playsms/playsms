#!/bin/bash

PLAYSMS=$1

if [ -z "$PLAYSMS" ]; then
	echo "Usage: $0 <playSMS installation path>"
	exit 1
fi

CWD=$(pwd)

cd $PLAYSMS
find . -type d -name "language" | sed -e "s/\/[^\/]*$//" > /tmp/.lang_folders
for i in `cat /tmp/.lang_folders` ; do
	for j in `ls -1 "$i/language/" | grep '_'` ; do
		mkdir -p "$i/language/$j/LC_MESSAGES"
		touch "$i/language/$j/LC_MESSAGES/messages.po"
		msgfmt -vv "$i/language/$j/LC_MESSAGES/messages.po" -o "$i/language/$j/LC_MESSAGES/messages.mo"
	done
done

cd $CWD

exit 0
