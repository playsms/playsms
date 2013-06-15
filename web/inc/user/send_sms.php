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
			$sms_template = "<p><select name=\"smstemplate\" onClick=\"SetSmsTemplate();\">$option_values</select>";
		}

		// unicode option
		if ($core_config['user']['send_as_unicode']) {
			$option_msg_unicode = 'checked';
		}

		$content = '';
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<form name=\"fm_smstemplate\">$input_values</form>
			<h2>"._('Send SMS')."</h2>
			<p>
			<form name=\"fm_sendsms\" id=\"fm_sendsms\" action=\"index.php?app=menu&inc=send_sms&op=sendsmstopv_yes\" method=\"POST\">
			<p>"._('SMS sender ID').": $sms_from
			<p>"._('SMS footer').": $sms_footer
			<p>";

		if ($bulk == 1) {
			$content .= _button('index.php?app=menu&inc=send_sms&op=sendsmstopv&bulk=2', _('View numbers'));
		} else if ($bulk == 2){
			$content .= _button('index.php?app=menu&inc=send_sms&op=sendsmstopv&bulk=1', _('View groups'));
		}

		$content .= "
			<p>
			<table cellpadding=1 cellspacing=0 border=0>
			<tbody>
			<tr>
				<td nowrap>
					"._('Phonebook').":<br>
					<select name=\"p_num_dump[]\" size=\"8\" style=\"width: 200px\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['p_num_dump[]'],this.form['p_num[]'])\">$list_of_number</select>
				</td>
				<td width=10>&nbsp;</td>
				<td align=center valign=middle>
					<input type=\"button\" class=\"button\" value=\"&gt;&gt;\" onclick=\"moveSelectedOptions(this.form['p_num_dump[]'],this.form['p_num[]'])\"><br>
					<input type=\"button\" class=\"button\" value=\""._('All')." &gt;&gt;\" onclick=\"moveAllOptions(this.form['p_num_dump[]'],this.form['p_num[]'])\"><br>
					<input type=\"button\" class=\"button\" value=\"&lt;&lt;\" onclick=\"moveSelectedOptions(this.form['p_num[]'],this.form['p_num_dump[]'])\"><br>
					<input type=\"button\" class=\"button\" value=\""._('All')." &lt;&lt;\" onclick=\"moveAllOptions(this.form['p_num[]'],this.form['p_num_dump[]'])\">
				</td>
				<td width=10>&nbsp;</td>
				<td nowrap>
					"._('Send to').":<br>
					<select name=\"p_num[]\" size=\"8\" style=\"width: 200px\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['p_num[]'],this.form['p_num_dump[]'])\"></select>
				</td>
			</tr>
			</tbody>
			</table>
			<p>"._('Send to').":<br><input type=text size=30 maxlength=250 name=p_num_text value=\"".$to."\">
			$sms_template
			<p>"._('Message').":
			<br><textarea cols=\"55\" rows=\"3\" onFocus=\"SmsSetCounter();\" onClick=\"SmsSetCounter();\" onkeypress=\"SmsSetCounter();\" onblur=\"SmsSetCounter();\" onKeyUp=\"SmsSetCounter();\" name=\"message\" id=\"ta_sms_content\">".$message."</textarea>
			<br><input type=\"text\" id=txtcount name=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.frmSendSms.message.focus();\" readonly>
			<input type=\"hidden\" value=\"".$core_config['user']['opt']['sms_footer_length']."\" name=\"footerlen\"> 
			<input type=\"hidden\" value=\"".$core_config['user']['opt']['per_sms_length']."\" name=\"maxchar\"> 
			<input type=\"hidden\" value=\"".$core_config['user']['opt']['per_sms_length_unicode']."\" name=\"maxchar_unicode\"> 
			<input type=\"hidden\" value=\"".$core_config['user']['opt']['max_sms_length']."\" name=\"hiddcount\"> 
			<input type=\"hidden\" value=\"".$core_config['user']['opt']['max_sms_length_unicode']."\" name=\"hiddcount_unicode\"> 
			<p>
			<table>
			<tr>
				<td valign=center><input type=checkbox name=msg_flash></td>
				<td valign=center>"._('Flash message')."</td>
				<td valign=center width=10>&nbsp;</td>
				<td valign=center><input type=checkbox name=msg_unicode ".$option_msg_unicode." onClick=\"SmsSetCounter();\" onkeypress=\"SmsSetCounter();\" onblur=\"SmsSetCounter();\"></td>
				<td valign=center>"._('Unicode message')."</td>
			</tr>
			</table>
			<p><input type=submit class=button value='"._('Send')."' onClick=\"selectAllOptions(this.form['p_num[]'])\"> 
			</form>";
		$content .= "
			<script type=\"text/javascript\" language=\"JavaScript\">
				document.forms['fm_sendsms'].elements['message'].focus();
			</script>";
		echo $content;
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