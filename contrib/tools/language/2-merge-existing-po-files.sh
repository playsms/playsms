#!/bin/bash

PLAYSMS=$1

if [ -z "$PLAYSMS" ]; then
	PLAYSMS=../../..
fi

if [ ! -d "$PLAYSMS/web" ]; then
	echo "Error. Usage: $(basename $0) <playSMS source dir>"
	exit 1
fi

set -e

rm -f /tmp/.lang_folders >/dev/null 2>&1
touch /tmp/.lang_folders

find $PLAYSMS/web/plugin/core/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders
find $PLAYSMS/web/plugin/feature/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders
find $PLAYSMS/web/plugin/gateway/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders
find $PLAYSMS/web/plugin/themes/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders

for i in `cat /tmp/.lang_folders` ; do
	for j in `ls -1 "$i/language/" | grep '_'` ; do
		mkdir -p "$i/language/$j/LC_MESSAGES"
		touch "$i/language/$j/LC_MESSAGES/messages.po"
		touch "$i/language/$j/index.html"
		touch "$i/language/$j/LC_MESSAGES/index.html"
		msgmerge "$i/language/$j/LC_MESSAGES/messages.po" "$i/language/messages.pot" > "$i/language/$j/LC_MESSAGES/messages.po.tmp"
		mv "$i/language/$j/LC_MESSAGES/messages.po.tmp" "$i/language/$j/LC_MESSAGES/messages.po"
		echo -n "$i/language/$j/LC_MESSAGES/messages.po"
		msgattrib --no-obsolete --previous "$i/language/$j/LC_MESSAGES/messages.po" -o "$i/language/$j/LC_MESSAGES/messages.po"
	done
done
echo

rm -f /tmp/.lang_folders >/dev/null 2>&1

exit 0
