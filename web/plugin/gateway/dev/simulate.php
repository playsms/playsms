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
	case "simulate":
		$tpl = [
			'name' => 'simulate',
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Simulate incoming SMS' => _('Simulate incoming SMS'),
				'Gateway' => _('Gateway'),
				'Message' => _mandatory(_('Message')),
				'Sender' => _('Sender'),
				'Receiver' => _('Receiver'),
				'Date/Time' => _('Date/Time'),
				'Submit' => _('Submit'),
				'HINT_MESSAGE_LENGTH' => _hint(_('Max. length 320 characters')),
				'HINT_DATE_TIME_FORMAT' => _hint(_('Format: YYYY-MM-DD HH:ii:ss')),
				'gateway_name' => $plugin_config['dev']['name'],
				'placeholder_message' => _('This is a test incoming SMS message'),
				'placeholder_sender' => '629876543210',
				'placeholder_receiver' => '1234',
				'placeholder_datetime' => core_get_datetime(),
			]
		];
		_p(tpl_apply($tpl));
		break;

	case "simulate_yes":
		$sms_sender = isset($_REQUEST['sender']) && $_REQUEST['sender'] ? core_sanitize_sender($_REQUEST['sender']) : '629876543210';
		$sms_receiver = isset($_REQUEST['receiver']) && $_REQUEST['receiver'] ? core_sanitize_sender($_REQUEST['receiver']) : '1234';
		$sms_datetime = isset($_REQUEST['datetime']) && $_REQUEST['datetime'] ? $_REQUEST['datetime'] : core_get_datetime();
		$message = isset($_REQUEST['message']) && $_REQUEST['message'] ? $_REQUEST['message'] : _('This is a test incoming SMS message');

		if (trim($sms_sender) && trim($sms_receiver) && trim($sms_datetime) && trim($message)) {
			if (recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, 'dev')) {
				$debug_msg[] = "Sender ID: " . $sms_sender;
				$debug_msg[] = "Receiver number: " . $sms_receiver;
				$debug_msg[] = "Sent: " . $sms_datetime;
				$debug_msg[] = "Message: " . stripslashes($message);
				_log(print_r($debug_msg, true), 3, "dev simulate");
				$_SESSION['dialog']['info'][] = $debug_msg;
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to receiver incoming SMS');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('Fail to simulate incoming SMS');
		}

		header("Location: " . _u('index.php?app=main&inc=gateway_dev&route=simulate&op=simulate'));
		exit();
}
