#!/bin/sh
#This script reads a couple of parameters from a config file and allows us to
# send sms by submitting only the number and the text to be send

help() 
{
	echo "$0 -n (destination number)"
	printf "\t-t (text of the message)\n"
}


VERB=0

#We read the parameters via getops
while getopts ":n:t:vh" opt; do
  case $opt in
    n)
      #Phone number
      NUMBER=$OPTARG;
      ;;
    t)
      #Text of the sms we will send
      TEXT=$OPTARG;
      ;;
    v)
      #Enable the verbose mode
      VERB=1
      ;;
    h)
      #Help
      help;
      exit 0;
      ;;
    \?)
      echo "Option not valid: -$OPTARG" >&2
      help
      exit 1
      ;;
    :)
      echo "The option -$OPTARG needs a parameter " >&2
      help
      exit 1
      ;;
  esac
done

verb () { 
	if [ $VERB -eq 1 ]; then 
		echo "$@" 
	fi 
} 

DIR=`dirname $0`
if [ ! -e $DIR/$0.config ] ; then
	printf "ERROR: The config file $0.config is missing, you can use $0.config.dist as"
	printf "a base to configure it\n"
else
	. $DIR/$0.config
fi


if [ -z "$TOKEN" -o -z "$SITE" -o -z "$USERNAME" ] ; then 
	echo "ERROR: There are missing variables in the config"
	exit 1
fi

if [ -z "$NUMBER" -o -z "$TEXT" ] ; then
	echo "ERROR: There are missing variables from the command line"
	exit 1
fi

TEXT=`echo -n $TEXT | tr ' ' '+'`
TEXT=`echo -n $TEXT |recode html..ascii`
#Constructing the URL for the message
URL="${SITE}/index.php?app=webservices&ta=pv&u=$USERNAME&h=$TOKEN&to=$NUMBER&msg=$TEXT"
verb $URL
