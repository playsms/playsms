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
		touch "$i/language/$j/index.html"
		touch "$i/language/$j/LC_MESSAGES/index.html"
		msgmerge "$i/language/$j/LC_MESSAGES/messages.po" "$i/language/messages.pot" > "$i/language/$j/LC_MESSAGES/messages.po.tmp"
		mv "$i/language/$j/LC_MESSAGES/messages.po.tmp" "$i/language/$j/LC_MESSAGES/messages.po"
		echo -n "$i/language/$j/LC_MESSAGES/messages.po"
		msgattrib --no-obsolete "$i/language/$j/LC_MESSAGES/messages.po" -o "$i/language/$j/LC_MESSAGES/messages.po"
	done
done
echo

cd $CWD

exit 0
