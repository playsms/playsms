#!/bin/bash

PLAYSMS=$1

if [ -z "$PLAYSMS" ]; then
	PLAYSMS=../../..
fi

if [ ! -d "$PLAYSMS/web" ]; then
	echo "Error. Usage: $(basename $0) <playSMS source dir>"
	exit 1
fi

./1-update-pot-files.sh $PLAYSMS
./2-merge-existing-po-files.sh $PLAYSMS
./3-regenerating-mo-files.sh $PLAYSMS
./4-compile-all.sh $PLAYSMS

exit 0
