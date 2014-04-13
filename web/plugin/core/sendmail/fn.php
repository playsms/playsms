<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');

/**
 * Send email service.
 *
 * Parameters are as follows:
 *	$data = array(
 * 		'mail_from_name'	=> '',
 *		'mail_from'		=> '',
 *		'mail_reply_name'	=> '',
 *		'mail_reply'		=> '',
 *		'mail_error_name'	=> '',
 *		'mail_error'		=> '',
 *		'mail_to_name'		=> '',
 *		'mail_to'		=> '',
 *		'mail_cc_name'		=> '',
 *		'mail_cc'		=> '',
 *		'mail_bcc_name'		=> '',
 *		'mail_bcc'		=> '',
 *		'mail_subject'		=> '',
 *		'mail_body'		=> '',
 *		'mail_charset'		=> '',
 *		'smtp_relm'		=> '',
 *		'smtp_user'		=> '',
 *		'smtp_pass'		=> '',
 *		'smtp_host'		=> '',
 *		'smtp_port'		=> '',
 *		'smtp_debug'		=> '',
 *		'smtp_direct'		=> '',
 *		'attachment_data'	=> '',
 *		'attachment_name'	=> '',
 *		'attachment_type'	=> ''
 *	);
 *
 * @param array
 * @return boolean
 */
function sendmail($data = array()) {
	global $core_config;
	
	if (is_array($data)) {
		$mail_from_name = $data['mail_from_name'];
		$mail_from = $data['mail_from'];
		$mail_reply_name = $data['mail_reply_name'];
		$mail_reply = $data['mail_reply'];
		$mail_error_name = $data['mail_error_name'];
		$mail_error = $data['mail_error'];
		$mail_to_name = $data['mail_to_name'];
		$mail_to = $data['mail_to'];
		$mail_cc_name = $data['mail_cc_name'];
		$mail_cc = $data['mail_cc'];
		$mail_bcc_name = $data['mail_bcc_name'];
		$mail_bcc = $data['mail_bcc'];
		$mail_subject = $data['mail_subject'];
		$mail_body = $data['mail_body'];
		$mail_charset = $data['mail_charset'];
		$smtp_relm = $data['smtp_relm'];
		$smtp_user = $data['smtp_user'];
		$smtp_pass = $data['smtp_pass'];
		$smtp_host = $data['smtp_host'];
		$smtp_port = $data['smtp_port'];
		$smtp_debug = $data['smtp_debug'];
		$smtp_direct = $data['smtp_direct'];
		$attachment_data = $data['attachment_data'];
		$attachment_name = $data['attachment_name'];
		$attachment_type = $data['attachment_type'];
	}
	
	logger_print("start from:" . $mail_from . " to:" . $mail_to . " subject:" . $mail_subject, 2, "sendmail");
	if (!class_exists(email_message_class)) {
		include_once $core_config['apps_path']['plug'] . "/core/sendmail/lib/external/mimemessage/email_message.php";
	}
	if (!class_exists(smtp_message_class)) {
		include_once $core_config['apps_path']['plug'] . "/core/sendmail/lib/external/mimemessage/smtp_message.php";
	}
	if (!class_exists(smtp_class)) {
		include_once $core_config['apps_path']['plug'] . "/core/sendmail/lib/external/mimemessage/smtp/smtp.php";
	}
	
	$from_name = $mail_from_name;
	$from_address = $mail_from;
	$reply_name = $mail_reply_name;
	$reply_address = $mail_reply;
	$error_delivery_name = $mail_error_name;
	$error_delivery_address = $mail_error;
	$to_name = $mail_to_name;
	$to_address = $mail_to;
	$cc_name = $mail_cc_name;
	$cc_address = $mail_cc;
	$bcc_name = $mail_bcc_name;
	$bcc_address = $mail_bcc;
	$subject = $mail_subject;
	$text_message = $mail_body;
	
	$email_message = new smtp_message_class;
	$email_message->localhost = 'localhost';
	$email_message->smtp_realm = ($smtp_relm ? $smtp_relm : _SMTP_RELM_);
	$email_message->smtp_user = ($smtp_user ? $smtp_user : _SMTP_USER_);
	$email_message->smtp_password = ($smtp_pass ? $smtp_pass : _SMTP_PASS_);
	$email_message->smtp_host = ($smtp_host ? $smtp_host : _SMTP_HOST_);
	$email_message->smtp_port = ($smtp_port ? $smtp_port : _SMTP_PORT_);
	$email_message->smtp_debug = ($smtp_debug ? 1 : 0);
	$email_message->smtp_direct_delivery = ($smtp_direct ? 1 : 0);
	
	// default charset sets to UTF-8 (emmanuel)
	$email_message->default_charset = "UTF-8";
	
	$email_message->SetEncodedEmailHeader("From", $from_address, $from_name);
	$email_message->SetEncodedEmailHeader("To", $to_address, $to_name);
	if ($cc_address) {
		$email_message->SetEncodedEmailHeader("Cc", $cc_address, $cc_name);
	}
	if ($bcc_address) {
		$email_message->SetEncodedEmailHeader("Bcc", $bcc_address, $bcc_name);
	}
	if ($reply_address) {
		$email_message->SetEncodedEmailHeader("Reply-To", $reply_address, $reply_name);
	}
	if ($error_delivery_address) {
		$email_message->SetEncodedEmailHeader("Errors-To", $error_delivery_address, $error_delivery_name);
	}
	$email_message->SetEncodedHeader("Subject", $subject);
	$email_message->AddQuotedPrintableTextPart($email_message->WrapText($text_message));
	
	/*
	 *  Set the Return-Path header to define the envelope sender address to which bounced messages are delivered.
	 *  If you are using Windows, you need to use the smtp_message_class to set the return-path address.
	*/
	if (defined("PHP_OS") && strcmp(substr(PHP_OS, 0, 3) , "WIN") && $error_delivery_address) {
		$email_message->SetHeader("Return-Path", $error_delivery_address);
	}
	
	if ($attachment_data && $attachment_name && $attachment_type) {
		$file_attachment = array(
			'Data' => $attachment_data,
			'Name' => $attachment_name,
			'Content-Type' => $attachment_type,
			'Disposition' => 'attachment'
		);
		$email_message->AddFilePart($file_attachment);
	}
	
	/*
	 *  The message is now ready to be assembled and sent.
	 *  Notice that most of the functions used before this point may fail due to
	 *  programming errors in your script. You may safely ignore any errors until
	 *  the message is sent to not bloat your scripts with too much error checking.
	*/
	$error = $email_message->Send();
	
	if (strcmp($error, "")) {
		logger_print("end with error:" . $error, 2, "sendmail");
		return false;
	} else {
		logger_print("end from:" . $mail_from . " to:" . $mail_to . " subject:" . $mail_subject, 2, "sendmail");
		return true;
	}
}
