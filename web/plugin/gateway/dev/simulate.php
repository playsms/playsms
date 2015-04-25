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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

if (!auth_isadmin()) {
	auth_block();
}

switch (_OP_) {
	case "simulate" :
		$sender = '629876543210';
		$receiver = '1234';
		$datetime = core_get_datetime();
		
		$content .= _dialog() . "
			<h2>" . _('Simulate incoming SMS') . "</h2>
			<form action=\"index.php?app=main&inc=gateway_dev&route=simulate&op=simulate_yes\" method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
				<tbody>
				<tr><td class=label-sizer>" . _('Message') . "</td><td><input type=text name=message value=\"$message\" maxlength=250></td></tr>
				<tr><td>" . _('Sender') . "</td><td><input type=text name=sender value=\"$sender\" maxlength=20></td></tr>
				<tr><td>" . _('Receiver') . "</td><td><input type=text name=receiver value=\"$receiver\" maxlength=20></td></tr>
				<tr><td>" . _('Date/Time') . "</td><td><input type=text name=datetime value=\"" . core_display_datetime($datetime) . "\" maxlength=20></td></tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Submit') . "\">
			</form>";
		_p($content);
		break;
	case "simulate_yes" :
		$sms_sender = ($_REQUEST['sender'] ? $_REQUEST['sender'] : '629876543210');
		$sms_receiver = ($_REQUEST['receiver'] ? $_REQUEST['receiver'] : '1234');
		$sms_datetime = ($_REQUEST['datetime'] ? $_REQUEST['datetime'] : core_get_datetime());
		$message = ($_REQUEST['message'] ? $_REQUEST['message'] : _('This is a test incoming SMS message'));
		$message = htmlspecialchars_decode($message);
		
		if (trim($sms_sender) && trim($sms_receiver) && trim($sms_datetime) && trim($message)) {
			recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, 'dev');
			$err[] = "Sender ID: " . $sms_sender;
			$err[] = "Receiver number: " . $sms_receiver;
			$err[] = "Sent: " . $sms_datetime;
			$err[] = "Message: " . stripslashes($message);
			_log(print_r($err, TRUE), 3, "dev incoming");
			$_SESSION['dialog']['info'][] = $err;
		} else {
			$_SESSION['dialog']['info'][] = _('Fail to simulate incoming SMS');
		}
		
		header("Location: " . _u('index.php?app=main&inc=gateway_dev&route=simulate&op=simulate'));
		exit();
		break;
}
