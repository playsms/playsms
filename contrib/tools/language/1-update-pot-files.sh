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

touch $PLAYSMS/storage/tmp/application/plugin/language/messages.pot
touch $PLAYSMS/storage/tmp/application/plugin/language/index.html

xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o $PLAYSMS/storage/tmp/application/plugin/language/messages.pot $PLAYSMS/web/init.php
xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o $PLAYSMS/storage/tmp/application/plugin/language/messages.pot $PLAYSMS/web/index.php
find $PLAYSMS/web/lib/ -iname "*.php" -exec xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o $PLAYSMS/storage/tmp/application/plugin/language/messages.pot {} \;
find $PLAYSMS/web/inc/ -iname "*.php" -exec xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o $PLAYSMS/storage/tmp/application/plugin/language/messages.pot {} \;

rm -f /tmp/.lang_folders >/dev/null 2>&1
touch /tmp/.lang_folders

find $PLAYSMS/web/plugin/core/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders
find $PLAYSMS/web/plugin/feature/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders
find $PLAYSMS/web/plugin/gateway/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders
find $PLAYSMS/web/plugin/themes/ -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" >> /tmp/.lang_folders

for i in `cat /tmp/.lang_folders` ; do mkdir -p "$i/language" ; done
for i in `cat /tmp/.lang_folders` ; do rm -f "$i/language/messages.pot" ; done
for i in `cat /tmp/.lang_folders` ; do touch "$i/language/messages.pot" ; done
for i in `cat /tmp/.lang_folders` ; do 
	find $i -iname '*.php' -exec xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o "$i/language/messages.pot" {} \; ; 
	touch "$i/language/index.html"
done

rm -f /tmp/.lang_folders >/dev/null 2>&1

exit 0
