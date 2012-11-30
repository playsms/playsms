<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$dst_p_num = urlencode($_REQUEST['dst_p_num']);
$dst_gp_code = urlencode($_REQUEST['dst_gp_code']);

switch ($op) {
	case "sendsmstopv":
		$message = stripslashes($_REQUEST['message']);

		$rows = phonebook_getgroupbyuid($uid, "gp_name");
		foreach ($rows as $key => $db_row) {
			if ($c_count = phonebook_getmembercountbyid($db_row['gpid'])) {
				$list_of_number .= "<option value=\"gpid_".$db_row['gpid']."\" $selected>"._('Group').": ".$db_row['gp_name']." (".$db_row['gp_code'].")(".$c_count.")</option>";
			}
		}

		$rows = phonebook_getsharedgroup($uid);
		foreach ($rows as $key => $db_row) {
			$c_uid = $db_row['uid'];
			if ($c_username = uid2username($c_uid)) {
				if ($c_count = phonebook_getmembercountbyid($db_row['gpid'])) {
					$list_of_number .= "<option value=\"gpid_".$db_row['gpid']."\" $selected>"._('Group').": ".$db_row['gp_name']." (".$db_row['gp_code'].")(".$c_count.") - "._('shared by')." ".$c_username."</option>";
				}
			}
		}

		$rows = phonebook_getdatabyuid($uid, "p_desc");
		foreach ($rows as $key => $db_row) {
			$list_of_number .= "<option value=\"".$db_row['p_num']."\" $selected>".$db_row['p_desc']." ".$db_row['p_num']."</option>";
		}

		$rows = phonebook_getsharedgroup($uid);
		foreach ($rows as $key => $db_row) {
			$c_gpid = $db_row['gpid'];
			$c_uid = $db_row['uid'];
			if ($c_username = uid2username($c_uid)) {
				$i = 0;
				$rows = phonebook_getdatabyid($c_gpid);
				foreach ($rows as $key => $db_row1) {
					$list_of_number .= "<option value=\"".$db_row1['p_num']."\" $selected>".$db_row1['p_desc']." ".$db_row1['p_num']." ("._('shared by')." ".$c_username.")</option>";
				}
			}
		}

		$sms_from = sendsms_get_sender($username);

		$sms_footer = $core_config['user']['footer'];
		if (! $sms_footer) {
			$sms_footer = "<i>"._('not set')."</i>";
		}

		$option_values = "<option value=\"\" default>--"._('Please select template')."--</option>";
		$c_templates = sendsms_get_template();
		for ($i=0;$i<count($c_templates);$i++) {
			$option_values .= "<option value=\"".$c_templates[$i]['text']."\">".$c_templates[$i]['title']."</option>";
			$input_values .= "<input type=\"hidden\" name=\"content_".$i."\" value=\"".$c_templates[$i]['text']."\">";
		}
		if ($c_templates[0]) {
			$sms_template = "
				<p><select name=\"smstemplate\">$option_values</select>
				<input type=\"button\" onClick=\"SetSmsTemplate();\" name=\"nb\" value=\""._('Use')."\" class=\"button\">";
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
			<p>
			<table cellpadding=1 cellspacing=0 border=0>
			<tr>
				<td nowrap>
					"._('To').":<br>
					<select name=\"p_num_dump[]\" size=\"10\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['p_num_dump[]'],this.form['p_num[]'])\">$list_of_number</select>
				</td>
				<td width=10>&nbsp;</td>
				<td align=center valign=middle>
					<input type=\"button\" class=\"button\" value=\"&gt;&gt;\" onclick=\"moveSelectedOptions(this.form['p_num_dump[]'],this.form['p_num[]'])\"><br><br>
					<input type=\"button\" class=\"button\" value=\""._('All')." &gt;&gt;\" onclick=\"moveAllOptions(this.form['p_num_dump[]'],this.form['p_num[]'])\"><br><br>
					<input type=\"button\" class=\"button\" value=\"&lt;&lt;\" onclick=\"moveSelectedOptions(this.form['p_num[]'],this.form['p_num_dump[]'])\"><br><br>
					<input type=\"button\" class=\"button\" value=\""._('All')." &lt;&lt;\" onclick=\"moveAllOptions(this.form['p_num[]'],this.form['p_num_dump[]'])\">
				</td>
				<td width=10>&nbsp;</td>
				<td nowrap>
					"._('Send to').":<br>
					<select name=\"p_num[]\" size=\"10\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['p_num[]'],this.form['p_num_dump[]'])\"></select>
				</td>
			</tr>
			</table>
			<p>"._('Or').": <input type=text size=20 maxlength=20 name=p_num_text value=\"$dst_p_num\">
			$sms_template
			<p>"._('Message').":
			<br><textarea cols=\"39\" rows=\"5\" onClick=\"SmsSetCounter();\" onkeypress=\"SmsSetCounter();\" onblur=\"SmsSetCounter();\" onKeyUp=\"SmsSetCounter();\" name=\"message\" id=\"ta_sms_content\">$message</textarea>
			<br><input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.frmSendSms.message.focus();\" readonly>
			<input type=\"hidden\" value=\"".$core_config['user']['opt']['sms_footer_length']."\" name=\"footerlen\"> 
			<input type=\"hidden\" value=\"".$core_config['user']['opt']['per_sms_length']."\" name=\"maxchar\"> 
			<input type=\"hidden\" value=\"".$core_config['user']['opt']['per_sms_length_unicode']."\" name=\"maxchar_unicode\"> 
			<input type=\"hidden\" value=\"".$core_config['user']['opt']['max_sms_length']."\" name=\"hiddcount\"> 
			<input type=\"hidden\" value=\"".$core_config['user']['opt']['max_sms_length_unicode']."\" name=\"hiddcount_unicode\"> 
			<p><input type=checkbox name=msg_flash> "._('Send as flash message')."
			<p><input type=checkbox name=msg_unicode onClick=\"SmsSetCounter();\" onkeypress=\"SmsSetCounter();\" onblur=\"SmsSetCounter();\"> "._('Send as unicode message (http://www.unicode.org)')."
			<p><input type=submit class=button value='"._('Send')."' onClick=\"selectAllOptions(this.form['p_num[]'])\"> 
			</form>";
		echo $content;
		break;
	case "sendsmstopv_yes":
		$p_num = $_POST['p_num'];
		if (!$p_num[0]) {
			$p_num = explode(",", $_POST['p_num_text']);
		}
		$sms_to = $p_num;
		$msg_flash = $_POST['msg_flash'];
		$msg_unicode = $_POST['msg_unicode'];
		$message = $_POST['message'];
		if ($sms_to[0] && $message) {
			$sms_type = "text";
			if ($msg_flash == "on") {
				$sms_type = "flash";
			}
			$unicode = "0";
			if ($msg_unicode == "on") {
				$unicode = "1";
			}
			list($ok,$to,$smslog_id,$queue) = sendsms_pv($username,$sms_to,$message,$sms_type,$unicode);
			if (count($ok) <= 5) {
				for ($i=0;$i<count($ok);$i++) {
					if ($ok[$i]) {
						$_SESSION['error_string'] .= _('Your SMS has been delivered to queue')." ("._('to').": ".$to[$i].")<br>";
					} else {
						$_SESSION['error_string'] .= _('Fail to sent SMS')." ("._('to').": ".$to[$i].")<br>";
					}
				}
			} else {
				$sms_queued = 0;
				$sms_failed = 0;
				for ($i=0;$i<count($ok);$i++) {
					if ($ok[$i]) {
						$sms_queued++;
					} else {
						$sms_failed++;
					}
				}
				$_SESSION['error_string'] = _('Your SMS has been delivered to queue')." ("._('queued').": ".$sms_queued.", "._('failed').": ".$sms_failed.")";
			}
		} else {
			$_SESSION['error_string'] = _('You must select receiver and your message should not be empty');
		}
		header("Location: index.php?app=menu&inc=send_sms&op=sendsmstopv&message=".urlencode(stripslashes($message)));
		exit();
		break;
}

?>