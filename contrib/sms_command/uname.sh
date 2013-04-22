#!/bin/bash

## Username and password of the playsms user you wants to use
L="admin"
P="admin"

##  The path to your playSMS, with trailing slash
W="http://localhost/playsms/"

##  The information you wants to get back
##  eg: uname -a, uptime
M=`uname -nsr`


##  You shouldn't edit the rest of the file


##  Code to use the number of the sender
##  replacing + with %2B (urlencoded form of +)
DF=`echo $1 | sed s/+/%2B/`

##  request webservices, returns the result to sender
$(which lynx) -dump "$W?app=webservices&u=$L&p=$P&ta=pv&to=$DF&msg=$M" >/dev/null 2>&1
