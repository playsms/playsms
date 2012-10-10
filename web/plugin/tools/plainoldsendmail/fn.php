<?php
defined('_SECURE_') or die('Forbidden');

function plainoldsendmail_hook_sendmail($mail_from,$mail_to,$mail_subject="",$mail_body="") {
	global $apps_path;
	logger_print("start from:".$mail_from." to:".$mail_to." subject:".$mail_subject, 2, "plainoldsendmail");
	if (!class_exists(email_message_class)) {
		include_once $apps_path['plug']."/tools/plainoldsendmail/lib/external/mimemessage/email_message.php";
	}
	if (!class_exists(smtp_message_class)) {
		include_once $apps_path['plug']."/tools/plainoldsendmail/lib/external/mimemessage/smtp_message.php";
	}
	if (!class_exists(smtp_class)) {
		include_once $apps_path['plug']."/tools/plainoldsendmail/lib/external/mimemessage/smtp/smtp.php";
	}

	$from_name			= $mail_from_name;
	$from_address		= $mail_from;
	$reply_name			= $from_name;
	$reply_address		= $from_address;
	$error_delivery_name	= $from_name;
	$error_delivery_address	= $from_address;
	$to_name			= $mail_to_name;
	$to_address			= $mail_to;
	$cc_name			= $mail_cc_name;
	$cc_address			= $mail_cc;
	$bcc_name			= $mail_bcc_name;
	$bcc_address		= $mail_bcc;
	$subject			= $mail_subject;
	$text_message		= $mail_body;

	$email_message = new smtp_message_class;
	$email_message->localhost		= "localhost";
	$email_message->smtp_realm		= _SMTP_RELM_;
	$email_message->smtp_user		= _SMTP_USER_;
	$email_message->smtp_password	= _SMTP_PASS_;
	$email_message->smtp_host		= _SMTP_HOST_;
	$email_message->smtp_port		= _SMTP_PORT_;
	$email_message->smtp_debug		= 0;
	$email_message->smtp_direct_delivery = 0;

	// default charset sets to UTF-8 (emmanuel)
	$email_message->default_charset	= "UTF-8";

	$email_message->SetEncodedEmailHeader("To",$to_address,$to_name);
	if ($cc_address)
	$email_message->SetEncodedEmailHeader("Cc",$cc_address,$cc_name);
	if ($bcc_address)
	$email_message->SetEncodedEmailHeader("Bcc",$bcc_address,$bcc_name);
	$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
	$email_message->SetEncodedEmailHeader("Reply-To",$reply_address,$reply_name);
	$email_message->SetEncodedEmailHeader("Errors-To",$error_delivery_address,$error_delivery_name);
	$email_message->AddQuotedPrintableTextPart($email_message->WrapText($text_message));
	/*
	 *  Set the Return-Path header to define the envelope sender address to which bounced messages are delivered.
	 *  If you are using Windows, you need to use the smtp_message_class to set the return-path address.
	 */
	if(defined("PHP_OS") && strcmp(substr(PHP_OS,0,3),"WIN"))
	$email_message->SetHeader("Return-Path",$error_delivery_address);
	$email_message->SetEncodedHeader("Subject",$subject);

	if ($attachment && $filename && $contenttype) {
		$file_attachment=array(
	    "Data"=>"$attachment",
	    "Name"=>"$filename",
	    "Content-Type"=>"$contenttype",
	    "Disposition"=>"attachment");
		$email_message->AddFilePart($file_attachment);
	}

	/*
	 *  The message is now ready to be assembled and sent.
	 *  Notice that most of the functions used before this point may fail due to
	 *  programming errors in your script. You may safely ignore any errors until
	 *  the message is sent to not bloat your scripts with too much error checking.
	 */
	$error = $email_message->Send();
	//print_r($email_message);
	if (strcmp ($error, "")) {
		logger_print("end with error:".$error, 2, "plainoldsendmail");
		return false;
	} else {
		logger_print("end from:".$mail_from." to:".$mail_to." subject:".$mail_subject, 2, "plainoldsendmail");
		return true;
	}
}

?>