#!/bin/bash

## Username and password of the playsms user you wants to use
L="admin"
P="admin"

##  The path to your input.php file
W="http://localhost/playsms/input.php"

##  The information you wants to get back
##  eg:  uname -a, uptime
M1=`uname -nsr`
M2=`uptime`
M="$M1 $M2"


##  You shouldn't edit the rest of the file


##  Code to use the number of the sender
##  replacing + with %2B (urlencoded form of +)
DF=`echo $1 | sed s/+/%2B/`

##  request input.php, returns the result to sender
$(which lynx) -dump "$W?u=$L&p=$P&ta=pv&to=$DF&msg=$M" >/dev/null 2>&1
