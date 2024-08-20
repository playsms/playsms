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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email service.
 *
 * Parameters are as follows:
 *	$data = array(
 * 		'mail_from_name'	=> '',
 *		'mail_from'			=> '',
 *		'mail_reply_name'	=> '',
 *		'mail_reply'		=> '',
 *		'mail_error_name'	=> '',
 *		'mail_error'		=> '',
 *		'mail_to_name'		=> '',
 *		'mail_to'			=> '',
 *		'mail_cc_name'		=> '',
 *		'mail_cc'			=> '',
 *		'mail_bcc_name'		=> '',
 *		'mail_bcc'			=> '',
 *		'mail_subject'		=> '',
 *		'mail_body'			=> '',
 *		'mail_body_text'	=> '',
 *		'mail_charset'		=> '',
 *		'smtp_relm'			=> '',
 *		'smtp_user'			=> '',
 *		'smtp_pass'			=> '',
 *		'smtp_host'			=> '',
 *		'smtp_port'			=> '',
 *		'smtp_secure'		=> '',
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
function sendmail($data = [])
{
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
		$mail_body_alternative = $data['mail_body_alternative'];
		$mail_body_format = strtolower($data['mail_body_format']); // plain, html, default html
		$mail_charset = $data['mail_charset'];
		$smtp_relm = $data['smtp_relm'];
		$smtp_user = $data['smtp_user'];
		$smtp_pass = $data['smtp_pass'];
		$smtp_host = $data['smtp_host'];
		$smtp_port = (int) $data['smtp_port'];
		$smtp_secure = strtolower($data['smtp_secure']); // no, tls, ssl, default no
		$smtp_debug = (boolean) $data['smtp_debug']; // true, false, default false
		$smtp_direct = $data['smtp_direct'];
		$attachment_data = $data['attachment_data'];
		$attachment_name = $data['attachment_name'];
		$attachment_type = $data['attachment_type'];
	} else {
		_log("error no data", 2, "sendmail");

		return false;
	}

	_log("start from:" . $mail_from . " to:" . $mail_to . " subject:" . $mail_subject, 2, "sendmail");

	// Instantiation and passing `true` enables exceptions
	$mail = new PHPMailer(true);

	try {
		// Server settings
		$mail->SMTPDebug = ($smtp_debug ? SMTP::DEBUG_CONNECTION : SMTP::DEBUG_SERVER); // Enable verbose debug output
		$mail->isSMTP(); // Send using SMTP
		$mail->Host = ($smtp_host ? $smtp_host : _SMTP_HOST_); // Set the SMTP server to send through
		$mail->Port = ($smtp_port ? $smtp_port : _SMTP_PORT_); // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

		if ($smtp_user) {
			$mail->SMTPAuth = true; // Enable SMTP authentication
			$mail->Username = ($smtp_user ? $smtp_user : _SMTP_USER_); // SMTP username
			$mail->Password = ($smtp_pass ? $smtp_pass : _SMTP_PASS_); // SMTP password
		}

		if ($smtp_secure == 'tls') {
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
		} else if ($smtp_secure == 'ssl') {
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable SSL encryption
		}

		// Recipients
		$mail->setFrom($mail_from, $mail_from_name);
		$mail->addAddress($mail_to, $mail_to_name); // Add a recipient
		if ($mail_reply) {
			$mail->addReplyTo($mail_reply, $mail_reply_name);

		}
		if ($mail_cc) {
			$mail->addCC($mail_cc, $mail_cc_name);
		}
		if ($mail_bcc) {
			$mail->addBCC($mail_bcc, $mail_bcc_name);
		}

		// Content
		$mail->Subject = $mail_subject;
		$mail->Body = $mail_body;

		if ($mail_body_format == 'plain') {
			$mail->isHTML(false);
		} else {
			$mail->isHTML(true);
			if ($mail_body_alternative) {
				$mail->AltBody = $mail_body_alternative;
			}
		}

		// Send it
		$mail->send();

		_log("sent from:" . $mail_from . " to:" . $mail_to . " subject:" . $mail_subject, 2, "sendmail");

		return true;
	} catch (Exception $e) {
		_log("end with error:" . $mail->ErrorInfo, 2, "sendmail");

		return false;
	}
}