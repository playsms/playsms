<?php
/*
 * sendmail_mail.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/PHPlibrary/mimemessage/sendmail_mail.php,v 1.1 2002/09/30 04:37:26 mlemos Exp $
 *
 *
 */

require_once("email_message.php");
require_once("sendmail_message.php");

$message_object=new sendmail_message_class;
$message_object->delivery_mode=SENDMAIL_DELIVERY_DEFAULT; /*  Mode of delivery of the message. Supported modes are:
*  SENDMAIL_DELIVERY_DEFAULT     - Default mode
*  SENDMAIL_DELIVERY_INTERACTIVE - Deliver synchronously waiting for remote server response.
*  SENDMAIL_DELIVERY_BACKGROUND  - Deliver asynchronously without waiting for delivery success response.
*  SENDMAIL_DELIVERY_QUEUE       - Leave message on the queue to be delivered later when the queue is run
*  SENDMAIL_DELIVERY_DEFERRED    - Queue without even performing database lookup maps.
*/
$message_object->sendmail_arguments="";                   /* Additional sendmail command line arguments */

Function sendmail_mail($to,$subject,$message,$additional_headers="",$additional_parameters="")
{
	global $message_object;

	return($message_object->Mail($to,$subject,$message,$additional_headers,$additional_parameters));
}

?>