<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

if (! ($bulk = $_REQUEST['bulk'])) {
	$bulk = $_SESSION['tmp']['sendsms']['bulk'];
}

switch ($op) {
	case "sendsmstopv":
		$to = $_REQUEST['to'];
		$message = stripslashes($_REQUEST['message']);

		// sender ID
		$sms_from = sendsms_get_sender($username);
		if (! $sms_from) {
			$sms_from = "<i>"._('not set')."</i>";
		}

		// SMS footer
		$sms_footer = $core_config['user']['footer'];
		if (! $sms_footer) {
			$sms_footer = "<i>"._('not set')."</i>";
		}

		// groups and numbers
		if ($bulk == 1) {
			$rows = phonebook_getgroupbyuid($uid, "gp_name");
			foreach ($rows as $key => $db_row) {
				if ($c_count = phonebook_getmembercountbyid($db_row['gpid'])) {
					$value = $db_row['gp_name']." (".$db_row['gp_code'].")(".$c_count.")";
					$list_of_number .= "<option value=\"gpid_".$db_row['gpid']."\" title=\"".$value."\" $selected>".$value."</option>";
				}
			}
			$rows = phonebook_getsharedgroup($uid);
			foreach ($rows as $key => $db_row) {
				$c_uid = $db_row['uid'];
				if ($c_username = uid2username($c_uid)) {
					if ($c_count = phonebook_getmembercountbyid($db_row['gpid'])) {
						$value = $db_row['gp_name']." (".$db_row['gp_code'].")(".$c_count.")";
						$list_of_number .= "<option value=\"gpid_".$db_row['gpid']."\" title=\"".$value."\" $selected>".$value."</option>";
					}
				}
			}
			$_SESSION['tmp']['sendsms']['bulk'] = 1;
		} else if ($bulk == 2) {
			$rows = phonebook_getdatabyuid($uid, "p_desc");
			foreach ($rows as $key => $db_row) {
				$value = $db_row['p_desc']." (".$db_row['p_num'].")";
				$list_of_number .= "<option value=\"".$db_row['p_num']."\" title=\"".$value."\" $selected>".$value."</option>";
			}
			$rows = phonebook_getsharedgroup($uid);
			foreach ($rows as $key => $db_row) {
				$c_gpid = $db_row['gpid'];
				$c_uid = $db_row['uid'];
				if ($c_username = uid2username($c_uid)) {
					$i = 0;
					$rows = phonebook_getdatabyid($c_gpid);
					foreach ($rows as $key => $db_row1) {
						$value = $db_row1['p_desc']." (".$db_row1['p_num'].")";
						$list_of_number .= "<option value=\"".$db_row1['p_num']."\" title=\"".$value."\" $selected>".$value."</option>";
					}
				}
			}
			$_SESSION['tmp']['sendsms']['bulk'] = 2;
		}

		// message template
		$option_values = "<option value=\"\" default>--"._('Please select template')."--</option>";
		$c_templates = sendsms_get_template();
		for ($i=0;$i<count($c_templates);$i++) {
			$option_values .= "<option value=\"".$c_templates[$i]['text']."\" title=\"".$c_templates[$i]['text']."\">".$c_templates[$i]['title']."</option>";
			$input_values .= "<input type=\"hidden\" name=\"content_".$i."\" value=\"".$c_templates[$i]['text']."\">";
		}
		if ($c_templates[0]) {
			$sms_template = "<div id=msg_template><select name=\"smstemplate\" onClick=\"SetSmsTemplate();\">$option_values</select></div>";
		}

		// unicode option
		if ($core_config['user']['send_as_unicode']) {
			$option_msg_unicode = 'checked';
		}

		if ($bulk == 1) {
			$button_view .= _button('index.php?app=menu&inc=send_sms&op=sendsmstopv&bulk=2', _('View numbers'));
			$c_title = _('Phonebook groups');
		} else if ($bulk == 2){
			$button_view .= _button('index.php?app=menu&inc=send_sms&op=sendsmstopv&bulk=1', _('View groups'));
			$c_title = _('Phonebook numbers');
		}

		$content = '';
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}

		unset($tpl);
		$tpl = array(
		    'name' => 'send_sms',
		    'var' => array(
			'Send SMS' => _('Send SMS'),
			'SMS sender ID' => _('SMS sender ID'),
			'SMS footer' => _('SMS footer'),
			'Send to' => _('Send to'),
			'Message' => _('Message'),
			'Flash message' => _('Flash message'),
			'Unicode message' => _('Unicode message'),
			'Send' => _('Send'),
			'ERROR' => $error_content,
			'BUTTON_VIEW' => $button_view,
			'sms_from' => $sms_from,
			'sms_footer' => $sms_footer,
			'c_title' => $c_title,
			'list_of_number' => $list_of_number,
			'to' => $to,
			'sms_template' => $sms_template,
			'message' => $message,
			'sms_footer_length' => $core_config['user']['opt']['sms_footer_length'],
			'per_sms_length' => $core_config['user']['opt']['per_sms_length'],
			'per_sms_length_unicode' => $core_config['user']['opt']['per_sms_length_unicode'],
			'max_sms_length' => $core_config['user']['opt']['max_sms_length'],
			'max_sms_length_unicode' => $core_config['user']['opt']['max_sms_length_unicode'],
			'option_msg_unicode' => $option_msg_unicode
		    )
		);
		echo tpl_apply($tpl);
		break;
	case "sendsmstopv_yes":
		if ($sms_to = trim($_REQUEST['p_num_text'])) {
			$sms_to = explode(',', $sms_to);
		}
		if ($_REQUEST['p_num'][0]) {
			if (is_array($sms_to) && $sms_to[0]) {
				$sms_to = array_merge($sms_to, $_REQUEST['p_num']);
			} else {
				$sms_to = $_REQUEST['p_num'];
			}
		}
		$msg_flash = $_REQUEST['msg_flash'];
		$msg_unicode = $_REQUEST['msg_unicode'];
		$message = $_REQUEST['message'];
		if ($sms_to[0] && $message) {
			$sms_type = "text";
			if ($msg_flash == "on") {
				$sms_type = "flash";
			}
			$unicode = "0";
			if ($msg_unicode == "on") {
				$unicode = "1";
			}
			for ($i=0;$i<count($sms_to);$i++) {
				if (substr(trim($sms_to[$i]), 0, 5) == 'gpid_') {
					if ($c_gpid = substr(trim($sms_to[$i]), 5)) {
						$array_gpid[] = $c_gpid;
					}
				} else {
					$array_sms_to[] = $sms_to[$i];
				}
			}
			if (is_array($array_sms_to) && $array_sms_to[0]) {
				list($ok,$to,$smslog_id,$queue) = sendsms($username,$array_sms_to,$message,$sms_type,$unicode);
			}
			if (is_array($array_gpid) && $array_gpid[0]) {
				list($ok_bc,$to_bc,$smslog_id_bc,$queue_bc) = sendsms_bc($username,$array_gpid,$message,$sms_type,$unicode);
			}
			$sms_queued = 0;
			$sms_failed = 0;
			for ($i=0;$i<count($ok);$i++) {
				if ($ok[$i]) {
					$sms_queued++;
				} else {
					$sms_failed++;
				}
			}
			for ($i=0;$i<count($ok_bc);$i++) {
				if ($ok_bc[$i]) {
					$sms_queued++;
				} else {
					$sms_failed++;
				}
			}
			$_SESSION['error_string'] = _('Your SMS has been delivered to queue')." ("._('queued').": ".$sms_queued.", "._('failed').": ".$sms_failed.")";
		} else {
			$_SESSION['error_string'] = _('You must select receiver and your message should not be empty');
		}
		header("Location: index.php?app=menu&inc=send_sms&op=sendsmstopv&message=".urlencode(stripslashes($message)));
		exit();
		break;
}

?>