<?php
/*
 * smtp_mail.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/PHPlibrary/mimemessage/smtp_mail.php,v 1.1 2002/09/30 04:37:26 mlemos Exp $
 *
 *
 */

require_once("email_message.php");
require_once("smtp_message.php");
require_once("smtp.php");

$message_object=new smtp_message_class;
$smtp_object->localhost="localhost";   /* This computer address */
$smtp_object->smtp_host="localhost";   /* SMTP server address */
$smtp_object->smtp_direct_delivery=0;  /* Deliver directly to the recipients destination SMTP server */
$smtp_object->smtp_exclude_address=""; /* In directly deliver mode, the DNS may return the IP of a sub-domain of the default domain for domains that do not exist. If that is your case, set this variable with that sub-domain address. */
/*
 * If you use the direct delivery mode and the GetMXRR is not functional, you need to use a replacement function.
 */

/*
 $_NAMESERVERS=array();
 include("rrcompat.php");
 $smtp_object->smtp_getmxrr="_getmxrr";
 */
$smtp_object->smtp_user="";            /* authentication user name */
$smtp_object->smtp_realm="";           /* authentication realm */
$smtp_object->smtp_password="";        /* authentication password */
$smtp_object->smtp_debug=0;            /* Output dialog with SMTP server */

Function smtp_mail($to,$subject,$message,$additional_headers="",$additional_parameters="")
{
	global $message_object;

	return($message_object->Mail($to,$subject,$message,$additional_headers,$additional_parameters));
}

?>