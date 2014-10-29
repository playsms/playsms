#!/bin/bash

PLAYSMS=$1

if [ -z "$PLAYSMS" ]; then
	echo "Usage: $0 <playSMS installation path>"
	exit 1
fi

./1-update-pot-files.sh $PLAYSMS
./2-merge-existing-po-files.sh $PLAYSMS
./3-regenerating-mo-files.sh $PLAYSMS

exit 0
