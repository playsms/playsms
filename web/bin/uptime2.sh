#!/bin/bash

##  The information you wants to get back
##  eg:  uname -a, uptime
M1=`uname -nsr`
M2=`uptime`
M="$M1 $M2"

echo $M
