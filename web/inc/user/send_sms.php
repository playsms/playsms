<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case "send_sms":
		$to = $_REQUEST['to'];
		$message = stripslashes($_REQUEST['message']);

		// sender ID
		$sms_from = sendsms_get_sender($username);
		if (!$allow_custom_sender) {
			$allow_custom_sender = 'readonly';
		}

		// SMS footer
		$sms_footer = $core_config['user']['footer'];
		if (!$allow_custom_footer) {
			$allow_custom_footer = 'readonly';
		}

		// message template
		$option_values = "<option value=\"\" default>--"._('Please select template')."--</option>";
		$c_templates = sendsms_get_template();
		for ($i=0;$i<count($c_templates);$i++) {
			$option_values .= "<option value=\"".$c_templates[$i]['text']."\" title=\"".$c_templates[$i]['text']."\">".$c_templates[$i]['title']."</option>";
			$input_values .= "<input type=\"hidden\" name=\"content_".$i."\" value=\"".$c_templates[$i]['text']."\">";
		}
		if ($c_templates[0]) {
			$sms_template = "<div id=msg_template><select name=smstemplate id=msg_template_select onClick=\"SetSmsTemplate();\">$option_values</select></div>";
		}

		// unicode option
		if ($core_config['user']['send_as_unicode']) {
			$option_msg_unicode = 'checked';
		}

		$content = '';
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}

		// build form
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
			'Schedule' => _('Schedule'),
			'Options' => _('Options'),
			'ERROR' => $error_content,
			'HTTP_PATH_BASE' => _HTTP_PATH_BASE_,
			'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
			'sms_from' => $sms_from,
			'sms_footer' => $sms_footer,
			'allow_custom_sender' => $allow_custom_sender,
			'allow_custom_footer' => $allow_custom_footer,
			'to' => $to,
			'sms_template' => $sms_template,
			// 'sms_schedule' => core_display_datetime(core_get_datetime()),
			'sms_schedule' => '',
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
	case "send_sms_yes":
		if ($sms_to = trim($_REQUEST['p_num_text'])) {
			$sms_to = explode(',', $sms_to);
		}
		$sms_sender = trim($_REQUEST['sms_sender']);
		$sms_footer = trim($_REQUEST['sms_footer']);
		$sms_schedule = trim($_REQUEST['sms_schedule']);
		$msg_flash = $_REQUEST['msg_flash'];
		$msg_unicode = $_REQUEST['msg_unicode'];
		$message = $_REQUEST['message'];
		if ($sms_to[0] && $message) {
			$nofooter = true;
			if ($sms_footer) {
				$nofooter = false;
			}
			$sms_type = "text";
			if ($msg_flash == "on") {
				$sms_type = "flash";
			}
			$unicode = "0";
			if ($msg_unicode == "on") {
				$unicode = "1";
			}

			// get destinations
			for ($i=0;$i<count($sms_to);$i++) {
				if (substr(trim($sms_to[$i]), 0, 5) == 'gpid_') {
					if ($c_gpid = substr(trim($sms_to[$i]), 5)) {
						$array_gpid[] = $c_gpid;
					}
				} else {
					$array_sms_to[] = trim($sms_to[$i]);
				}
			}
			
			$sms_queued = 0;
			$sms_failed = 0;

			// sendsms_bc
			if (is_array($array_gpid) && $array_gpid[0]) {
				list($ok_bc,$to_bc,$smslog_id_bc,$queue_bc) = sendsms_bc($username,$array_gpid,$message,$sms_type,$unicode,$nofooter,$sms_footer,$sms_sender,$sms_schedule);
			}
			for ($i=0;$i<count($ok_bc);$i++) {
				if ($ok_bc[$i]) {
					$sms_queued++;
				} else {
					$sms_failed++;
				}
			}

			// sendsms
			if (is_array($array_sms_to) && $array_sms_to[0]) {
				list($ok,$to,$smslog_id,$queue) = sendsms($username,$array_sms_to,$message,$sms_type,$unicode,$nofooter,$sms_footer,$sms_sender,$sms_schedule);
			}
			for ($i=0;$i<count($ok);$i++) {
				if ($ok[$i]) {
					$sms_queued++;
				} else {
					$sms_failed++;
				}
			}
			
			$_SESSION['error_string'] = _('Your SMS has been delivered to queue')." ("._('queued').": ".$sms_queued.", "._('failed').": ".$sms_failed.")";
		} else {
			$_SESSION['error_string'] = _('You must select receiver and your message should not be empty');
		}
		header("Location: index.php?app=menu&inc=send_sms&op=send_sms&message=".urlencode(stripslashes($message)));
		exit();
		break;
}

?>