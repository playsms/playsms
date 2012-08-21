#!/bin/bash

TMPFILE=`mktemp`

echo "Channel: Zap/8/w$1" > $TMPFILE
echo "MaxRetries: 2" >> $TMPFILE
echo "RetryTime: 60" >> $TMPFILE
echo "WaitTime: 20" >> $TMPFILE
echo "Context: from-internal" >> $TMPFILE
echo "Extension: *1011" >> $TMPFILE

sleep 2

chown asterisk.asterisk $TMPFILE
chmod 666 $TMPFILE

mv $TMPFILE /var/spool/asterisk/outgoing/

echo "Dials $1"
