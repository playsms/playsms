#!/bin/bash

PLAYSMS=$1

if [ -z "$PLAYSMS" ]; then
	PLAYSMS=../../..
fi

if [ ! -d "$PLAYSMS/web" ]; then
	echo "Error. Usage: $(basename $0) <playSMS source dir>"
	exit 1
fi

for j in `ls -1 "$PLAYSMS/web/plugin/language/" | grep '_'` ; do
	mkdir -p "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES"
	touch "$PLAYSMS/storage/tmp/application/plugin/index.html"
	touch "$PLAYSMS/storage/tmp/application/plugin/language/index.html"
	touch "$PLAYSMS/storage/tmp/application/plugin/language/$j/index.html"
	touch "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES/index.html"
	touch "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES/messages.po"
	rm "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES/.po_files" >/dev/null 2>&1
	touch "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES/.po_files"
done

rm -f /tmp/.lang_folders >/dev/null 2>&1
touch /tmp/.lang_folders

find $PLAYSMS/web/plugin/core/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders
find $PLAYSMS/web/plugin/feature/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders
find $PLAYSMS/web/plugin/gateway/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders
find $PLAYSMS/web/plugin/themes/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders

for i in `cat /tmp/.lang_folders` ; do
	for j in `ls -1 "$i/language/" | grep '_'` ; do
		echo "$i/language/$j/LC_MESSAGES/messages.po " >> "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES/.po_files"
	done
done

for j in `ls -1 "$PLAYSMS/storage/tmp/application/plugin/language/" | grep '_'` ; do
	PO_FILES=`cat "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES/.po_files"`
	msgcat --force-po --use-first --lang $j -t UTF-8 -s -o "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES/messages.po" $PO_FILES
	msgfmt -vv "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES/messages.po" -o "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES/messages.mo"
	rm -f "$PLAYSMS/storage/tmp/application/plugin/language/$j/LC_MESSAGES/.po_files" >/dev/null 2>&1
done

rm -f /tmp/.lang_folders >/dev/null 2>&1

exit 0
