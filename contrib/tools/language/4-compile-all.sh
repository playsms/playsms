#!/bin/bash

PLAYSMS=$1

if [ -z "$PLAYSMS" ]; then
	echo "Usage: $0 <playSMS installation path>"
	exit 1
fi

CWD=$(pwd)

cd $PLAYSMS/plugin

for j in `ls -1 "$PLAYSMS/plugin/language/" | grep '_'` ; do
	mkdir -p "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES"
	touch "$PLAYSMS/storage/plugin/index.html"
	touch "$PLAYSMS/storage/plugin/language/index.html"
	touch "$PLAYSMS/storage/plugin/language/$j/index.html"
	touch "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES/index.html"
	touch "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES/messages.po"
	rm "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES/.po_files" >/dev/null 2>&1
	touch "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES/.po_files"
done

find . -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" > /tmp/.lang_folders
for i in `cat /tmp/.lang_folders` ; do
	for j in `ls -1 "$i/language/" | grep '_'` ; do
		echo "$i/language/$j/LC_MESSAGES/messages.po " >> "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES/.po_files"
	done
done

for j in `ls -1 "$PLAYSMS/plugin/language/" | grep '_'` ; do
	PO_FILES=`cat "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES/.po_files"`
	msgcat --force-po --use-first --lang $j -t UTF-8 -s -o "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES/messages.po" $PO_FILES
	msgfmt -vv "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES/messages.po" -o "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES/messages.mo"
	rm -f "$PLAYSMS/storage/plugin/language/$j/LC_MESSAGES/.po_files" >/dev/null 2>&1
done
rm /tmp/.lang_folders
cd $CWD

exit 0
