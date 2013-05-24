#!/bin/sh
#This script reads a couple of parameters from a config file and allows us to
# send sms by submitting only the number and the text to be send

help() 
{
	echo "$0 -n (destination number)"
	printf "\t-t (text of the message)\n"
}

#We read the parameters via getops
while getopts ":n:t:h" opt; do
  case $opt in
    n)
      #Passem el host de destí
      IP=$OPTARG;
      ;;
    d)
      #Pings que enviem i segons que esperema (tolerancia)
      TOL=$OPTARG;
      ;;
    h)
      #Ajuda
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

if [ ! -e $0.config ] ; then
	printf "ERROR: The config file $0.config is missing, you can use $0.config.dist as"
	printf "a base to configure it\n"
else
	. $0.config
fi


if [ -z "$TOKEN" ] ; then 
	echo "There are missing variables in the config"
fi
