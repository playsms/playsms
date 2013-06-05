<?php
/*
 * sendmail_message.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/PHPlibrary/mimemessage/sendmail_message.php,v 1.8 2003/01/15 05:18:42 mlemos Exp $
 *
 *
 */

define("SENDMAIL_DELIVERY_DEFAULT",     "");
define("SENDMAIL_DELIVERY_INTERACTIVE", "i");
define("SENDMAIL_DELIVERY_BACKGROUND",  "b");
define("SENDMAIL_DELIVERY_QUEUE",       "q");
define("SENDMAIL_DELIVERY_DEFERRED",    "d");

class sendmail_message_class extends email_message_class
{
	var $sendmail_path="/usr/lib/sendmail";
	var $line_break="\n";
	var $delivery_mode=SENDMAIL_DELIVERY_DEFAULT;
	var $sendmail_arguments="";

	Function SendMail($to,$subject,$body,$headers,$return_path)
	{
		$command=$this->sendmail_path." -t";
		switch($this->delivery_mode)
		{
			case SENDMAIL_DELIVERY_DEFAULT:
			case SENDMAIL_DELIVERY_INTERACTIVE:
			case SENDMAIL_DELIVERY_BACKGROUND:
			case SENDMAIL_DELIVERY_QUEUE:
			case SENDMAIL_DELIVERY_DEFERRED:
				break;
			default:
				return($this->OutputError("it was specified an unknown sendmail delivery mode"));
		}
		if($this->delivery_mode!=SENDMAIL_DELIVERY_DEFAULT)
		$command.=" -O DeliveryMode=".$this->delivery_mode;
		if(strlen($return_path))
		$command.=" -f '".preg_replace("/'/", "'\\''",$return_path)."'";
		if(strlen($this->sendmail_arguments))
		$command.=" ".$this->sendmail_arguments;
		if(!($pipe=popen($command,"w")))
		return($this->OutputError("it was not possible to open sendmail input pipe"));
		if(!fputs($pipe,"To: $to\n")
		|| !fputs($pipe,"Subject: $subject\n")
		|| ($headers!=""
		&& !fputs($pipe,"$headers\n"))
		|| !fputs($pipe,"\n$body"))
		return($this->OutputError("it was not possible to write sendmail input pipe"));
		pclose($pipe);
		return("");
	}
};

?>