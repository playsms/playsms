#!/bin/bash
#This script reads a couple of parameters from a config file and allows us to
# send sms by submitting only the number and the text to be send

help() 
{
	echo "$0 -n (destination number)"
	printf "\t-t (text of the message)\n"
}

is_err()
{
	case "$1" in
	OK_[0-9]*)
		#ALL ok
		exit 0
		;;
	"ERR_100")
		echo "ERR 100: authentication failed";
		exit 1
		;;
	"ERR_103")
		echo "ERR 103 : not enough credit for this operation"
		exit 1
		;;
	"ERR_201")
		echo "ERR 201: destination number or message is empty";
		exit 1
		;;
	*)
		echo "ERROR CODE --$1-- not known, please add to the array"
		exit 1
		;;
	esac
	
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

if [ ! -e /usr/bin/curl ] ; then
	echo "ERROR: We can't find curl in the system"
	echo "#apt-get install curl"
	exit 1
fi

if [ ! -e $0.config ] ; then
	printf "ERROR: The config file $0.config is missing, you can use $0.config.dist as"
	printf " a base to configure it\n"
	exit 1
else
	. $0.config
fi


if [ -z "$TOKEN" -o -z "$SITE" -o -z "$USERNAME" ] ; then 
	echo "ERROR: There are missing variables in the config"
	exit 1
fi

if [ -z "$NUMBER" -o -z "$TEXT" ] ; then
	echo "ERROR: There are missing variables from the command line"
	exit 1
fi

#We need to replace the spaces with + signs
#TEXT=`echo -n $TEXT | tr ' ' '+'`
#Constructing the URL for the message
#URL="${SITE}/index.php?app=webservices&ta=pv&u=$USERNAME&h=$TOKEN&to=$NUMBER&msg=$TEXT"
FETCH="${SITE}/index.php \
	-d app=webservices
	-d ta=pv \
	-d u=$USERNAME \
	-d h=$TOKEN \
	-d to=$NUMBER"


verb $FETCH
OUT=`curl -s -G $FETCH --data-urlencode "msg=$TEXT"`
OUT=`echo -n $OUT | tr ' ' '_' |cut -d',' -f1`
#we error out if there's an error sending
is_err $OUT
