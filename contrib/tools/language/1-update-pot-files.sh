#!/bin/bash

PLAYSMS=$1

if [ -z "$PLAYSMS" ]; then
	echo "Usage: $0 <playSMS installation path>"
	exit 1
fi

CWD=$(pwd)

##Common strings
cd $PLAYSMS/plugin
touch language/messages.pot
touch language/index.html
xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o language/messages.pot ../init.php
xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o language/messages.pot ../index.php
find ../lib/ -iname "*.php" -exec xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o language/messages.pot {} \;
find ../inc/ -iname "*.php" -exec xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o language/messages.pot {} \;
find themes/common/ -iname "*.php" -exec xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o language/messages.pot {} \;
cd $CWD

##Themes,plugins and tools strings
cd $PLAYSMS/plugin
find . -type d -name "language" | grep -v "grep" | sed -e "s/\/[^\/]*$//" > /tmp/.lang_folders
for i in `cat /tmp/.lang_folders` ; do mkdir -p "$i/language" ; done
for i in `cat /tmp/.lang_folders` ; do rm -f "$i/language/messages.pot" ; done
for i in `cat /tmp/.lang_folders` ; do touch "$i/language/messages.pot" ; done
for i in `cat /tmp/.lang_folders` ; do 
	find $i -iname '*.php' -exec xgettext -L PHP --omit-header --no-location --sort-output --from-code=utf-8 -j -o "$i/language/messages.pot" {} \; ; 
	touch "$i/language/index.html"
done
rm /tmp/.lang_folders
cd $CWD

exit 0
