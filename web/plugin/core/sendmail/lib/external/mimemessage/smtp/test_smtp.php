<?php
/*
 * test_smtp.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/PHPlibrary/test_smtp.php,v 1.7 2002/08/12 05:57:15 mlemos Exp $
 *
 */

require("smtp.php");

$smtp=new smtp_class;

/*
 * If you need to use the direct delivery mode and this is running under
 * Windows or any other platform that does not have enabled the MX
 * resolution function GetMXRR() , you need to include code that emulates
 * that function so the class knows which SMTP server it should connect
 * to deliver the message directly to the recipient SMTP server.
 */
if(!function_exists("GetMXRR"))
{
	/*
	 * If possible specify in this array the address of at least on local
	 * DNS that may be queried from your network.
	 */
	$_NAMESERVERS=array();
	include("getmxrr.php");
}
/*
 * If GetMXRR function is available but it is not functional, to use
 * the direct delivery mode, you may use a replacement function.
 */
/*
 else
 {
 $_NAMESERVERS=array();
 if(count($_NAMESERVERS)==0)
 Unset($_NAMESERVERS);
 include("rrcompat.php");
 $smtp->getmxrr="_getmxrr";
 }
 */

$smtp->host_name=getenv("HOSTNAME"); /* relay SMTP server address */
$smtp->localhost="localhost"; /* this computer address */
$from=getenv("USER")."@".$smtp->host_name;
$to="mlemos@acm.org";
$smtp->direct_delivery=0; /* Set to 1 to deliver directly to the recepient SMTP server */
$smtp->debug=0; /* Set to 1 to output the communication with the SMTP server */
$smtp->user=""; /* Set to the user name if the server requires authetication */
$smtp->realm=""; /* Set to the authetication realm, usually the authentication user e-mail domain */
$smtp->password=""; /* Set to the authetication password */
if($smtp->SendMessage(
$from,
array(
$to
),
array(
			"From: $from",
			"To: $to",
			"Subject: Testing Manuel Lemos' SMTP class",
			"Date: ".strftime("%a, %d %b %Y %H:%M:%S %Z")
),
		"Hello $to,\n\nIt is just to let you know that your SMTP class is working just fine.\n\nBye.\n"))
echo "Message sent to $to OK.\n";
else
echo "Cound not send the message to $to.\nError: ".$smtp->error."\n"
?>