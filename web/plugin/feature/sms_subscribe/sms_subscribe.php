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

if (!auth_isvalid()) {
	auth_block();
}

if ($subscribe_id = (int) $_REQUEST['subscribe_id']) {
	$db_table = _DB_PREF_ . '_featureSubscribe';
	$conditions = array(
		'subscribe_id' => $subscribe_id 
	);
	if (!auth_isadmin()) {
		$conditions['uid'] = $user_config['uid'];
	}
	$list = dba_search($db_table, 'subscribe_id', $conditions);
	if (!($list[0]['subscribe_id'] == $subscribe_id)) {
		auth_block();
	}
}

switch (_OP_) {
	case "sms_subscribe_list":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>" . _('Manage subscribe') . "</h2>
			" . _button('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_add', _('Add SMS subscribe'));
		$content .= "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>";
		if (auth_isadmin()) {
			$content .= "
				<th width=20%>" . _('Keyword') . "</th>
				<th width=20%>" . _('Members') . "</th>
				<th width=20%>" . _('Messages') . "</th>
				<th width=20%>" . _('User') . "</th>
				<th width=10%>" . _('Status') . "</th>
				<th width=10%>" . _('Action') . "</th>";
		} else {
			$content .= "
				<th width=20%>" . _('Keyword') . "</th>
				<th width=30%>" . _('Members') . "</th>
				<th width=30%>" . _('Messages') . "</th>
				<th width=10%>" . _('Status') . "</th>
				<th width=10%>" . _('Action') . "</th>";
		}
		$content .= "
			</tr></thead>
			<tbody>";
		$i = 0;
		if (!auth_isadmin()) {
			$query_user_only = "WHERE uid='" . $user_config['uid'] . "'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe " . $query_user_only . " ORDER BY subscribe_id";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = user_uid2username($db_row['uid'])) {
				$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '" . $db_row['subscribe_id'] . "'";
				$members = @dba_num_rows($db_query);
				if (!$members) {
					$members = 0;
				}
				$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id = '" . $db_row['subscribe_id'] . "'";
				$messages = @dba_num_rows($db_query);
				if (!$messages) {
					$messages = 0;
				}
				$subscribe_status = "<a href=\"" . _u('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_status&subscribe_id=' . $db_row['subscribe_id'] . '&ps=1') . "\"><span class=status_disabled /></a>";
				if ($db_row['subscribe_enable']) {
					$subscribe_status = "<a href=\"" . _u('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_status&subscribe_id=' . $db_row['subscribe_id'] . '&ps=0') . "\"><span class=status_enabled /></a>";
				}
				$action = "<a href=\"" . _u('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_edit&subscribe_id=' . $db_row['subscribe_id']) . "\">" . $icon_config['edit'] . "</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete SMS subscribe ?') . " (" . _('keyword') . ": " . $db_row['subscribe_keyword'] . ")','" . _u('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_del&subscribe_id=' . $db_row['subscribe_id']) . "')\">" . $icon_config['delete'] . "</a>";
				if (auth_isadmin()) {
					$option_owner = "<td>$owner</td>";
				}
				$i++;
				$content .= "
					<tr>
						<td>" . $db_row['subscribe_keyword'] . "</td>
						<td><a href=\"" . _u('index.php?app=main&inc=feature_sms_subscribe&op=mbr_list&subscribe_id=' . $db_row['subscribe_id']) . "\">" . $members . "</a></td>
						<td><a href=\"" . _u('index.php?app=main&inc=feature_sms_subscribe&op=msg_list&subscribe_id=' . $db_row['subscribe_id']) . "\">" . $messages . "</a></td>
						" . $option_owner . "
						<td>$subscribe_status</td>
						<td>$action</td>
					</tr>";
			}
		}
		$content .= "</tbody>
			</table>
			</div>
			" . _button('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_add', _('Add SMS subscribe'));
		_p($content);
		break;
	
	case "sms_subscribe_status":
		$ps = $_REQUEST['ps'];
		$db_query = "UPDATE " . _DB_PREF_ . "_featureSubscribe SET c_timestamp='" . mktime() . "',subscribe_enable='$ps' WHERE subscribe_id='$subscribe_id'";
		$db_result = @dba_affected_rows($db_query);
		if ($db_result > 0) {
			$_SESSION['dialog']['info'][] = _('SMS subscribe status has been changed');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_list'));
		exit();
		break;
	
	case "sms_subscribe_add":
		$max_length = $core_config['main']['max_sms_length'];
		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('smsc') . "</td></tr>";
		}
		if ($err = TRUE) {
			$content = _dialog();
		}
		$add_forward_param = 'BC';
		$select_durations = _select('add_duration', $plugin_config['sms_subscribe']['durations']);
		$content .= "
			<link rel='stylesheet' type='text/css' href=" . _HTTP_PATH_THEMES_ . "/common/jscss/sms_subscribe.css />
			<h2>" . _('Manage subscribe') . "</h2>
			<h3>" . _('Add SMS subscribe') . "</h3>
			<form name=\"form_subscribe_add\" id=\"form_subscribe_add\" action=index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_add_yes method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _('SMS subscribe keyword') . "</td><td><input type=text size=10 maxlength=10 name=add_subscribe_keyword value=\"$add_subscribe_keyword\"></td>
			</tr>
			<tr>
				<td class=label-sizer>" . _('SMS subscribe parameter') . "</td><td>	<input type=text size=10 maxlength=20 name=add_subscribe_param value=\"$add_subscribe_param\"></td>
			</tr>
			<tr>
				<td>" . _('SMS subscribe reply') . "</td>
				<td>
					<textarea maxlength=\"140\" name=\"add_subscribe_msg\" id=\"add_subscribe_msg\" value=\"\" cols=\"35\" rows=\"3\"
						onClick=\"SmsSetCounter_Abstract('add_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\"
						onkeypress=\"SmsSetCounter_Abstract('add_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\"
						onblur=\"SmsSetCounter_Abstract('add_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\"
						onKeyUp=\"SmsSetCounter_Abstract('add_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\"
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_add', 'add_subscribe_msg');\"
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_add');\"></textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" id=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_add.add_subscribe_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length'] . "\" name=\"hiddcount\" id=\"hiddcount\">
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length_unicode'] . "\" name=\"hiddcount_unicode\" id=\"hiddcount_unicode\">
				</td>
			</tr>
			<tr>
				<td>" . _('SMS unsubscribe parameter') . "</td>
				
				<td>
					<input type=text size=10 maxlength=20 name=add_unsubscribe_param value=\"$add_unsubscribe_param\">
				</td>
			</tr>
			<tr>
				<td>" . _('SMS unsubscribe reply') . "</td>
				<td>
					<textarea maxlength=\"140\" name=\"add_unsubscribe_msg\" id=\"add_unsubscribe_msg\" value=\"\" cols=\"35\" rows=\"3\"
						onClick=\"SmsSetCounter_Abstract('add_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\"
						onkeypress=\"SmsSetCounter_Abstract('add_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\"
						onblur=\"SmsSetCounter_Abstract('add_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\"
						onKeyUp=\"SmsSetCounter_Abstract('add_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\"
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_add', 'add_unsubscribe_msg');\"
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_add');\"></textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount_un\" id=\"txtcount_un\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_add.add_unsubscribe_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length'] . "\" name=\"hiddcount_un\" id=\"hiddcount_un\">
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length_unicode'] . "\" name=\"hiddcount_unicode_un\" id=\"hiddcount_unicode_un\">
				</td>
			</tr>
			<tr>
				<td>" . _('SMS forward parameter') . "</td>				
				<td>
					<input type=text size=10 maxlength=20 name=add_forward_param value=\"$add_forward_param\">
				</td>
			</tr>
			<tr>
				<td>" . _('Subscribe duration') . "</td>				
				<td>" . $select_durations . "</td>
			</tr>
			<tr>
				<td>" . _('Subscription expired reply') . "</td>
				<td>
					<textarea maxlength=\"140\" name=\"add_expire_msg\" id=\"add_expire_msg\" value=\"\" cols=\"35\" rows=\"3\"
						onClick=\"SmsSetCounter_Abstract('add_expire_msg','txtcount_ex','hiddcount_ex','hiddcount_unicode_ex');\"
						onkeypress=\"SmsSetCounter_Abstract('add_expire_msg','txtcount_ex','hiddcount_ex','hiddcount_unicode_ex');\"
						onblur=\"SmsSetCounter_Abstract('add_expire_msg','txtcount_ex','hiddcount_ex','hiddcount_unicode_ex');\"
						onKeyUp=\"SmsSetCounter_Abstract('add_expire_msg','txtcount_ex','hiddcount_ex','hiddcount_unicode_ex');\"
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_add', 'add_expire_msg');\"
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_add');\"></textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount_ex\" id=\"txtcount_ex\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_add.add_expire_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length'] . "\" name=\"hiddcount_ex\" id=\"hiddcount_ex\">
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length_unicode'] . "\" name=\"hiddcount_unicode_ex\" id=\"hiddcount_unicode_ex\">
				</td>
			</tr>
			<tr>
				<td>" . _('Unknown format reply') . "</td>
				<td>
					<textarea maxlength=\"140\" name=\"add_unknown_format_msg\" id=\"add_unknown_format_msg\" value=\"\" cols=\"35\" rows=\"3\"
						onClick=\"SmsSetCounter_Abstract('add_unknown_format_msg','txtcount_uk','hiddcount_uk','hiddcount_unicode_uk');\"
						onkeypress=\"SmsSetCounter_Abstract('add_unknown_format_msg','txtcount_uk','hiddcount_uk','hiddcount_unicode_uk');\"
						onblur=\"SmsSetCounter_Abstract('add_unknown_format_msg','txtcount_uk','hiddcount_uk','hiddcount_unicode_uk');\"
						onKeyUp=\"SmsSetCounter_Abstract('add_unknown_format_msg','txtcount_uk','hiddcount_uk','hiddcount_unicode_uk');\"
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_add', 'add_unknown_format_msg');\"
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_add');\"></textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount_uk\" id=\"txtcount_uk\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_add.add_unknown_format_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length'] . "\" name=\"hiddcount_uk\" id=\"hiddcount_uk\">
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length_unicode'] . "\" name=\"hiddcount_unicode_uk\" id=\"hiddcount_unicode_uk\">
				</td>
			</tr>
			<tr>
				<td>" . _('Already a member reply') . "</td>
				<td>
					<textarea maxlength=\"140\" name=\"add_already_member_msg\" id=\"add_already_member_msg\" value=\"\" cols=\"35\" rows=\"3\"
						onClick=\"SmsSetCounter_Abstract('add_already_member_msg','txtcount_am','hiddcount_am','hiddcount_unicode_am');\"
						onkeypress=\"SmsSetCounter_Abstract('add_already_member_msg','txtcount_am','hiddcount_am','hiddcount_unicode_am');\"
						onblur=\"SmsSetCounter_Abstract('add_already_member_msg','txtcount_am','hiddcount_am','hiddcount_unicode_am');\"
						onKeyUp=\"SmsSetCounter_Abstract('add_already_member_msg','txtcount_am','hiddcount_am','hiddcount_unicode_am');\"
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_add', 'add_already_member_msg');\"
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_add');\"></textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount_am\" id=\"txtcount_am\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_add.add_already_member_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length'] . "\" name=\"hiddcount_am\" id=\"hiddcount_am\">
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length_unicode'] . "\" name=\"hiddcount_unicode_am\" id=\"hiddcount_unicode_am\">
				</td>
			</tr>
			" . $select_reply_smsc . "
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			<p>" . _back('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_list');
		_p($content);
		break;
	
	case "sms_subscribe_add_yes":
		$add_subscribe_keyword = strtoupper($_POST['add_subscribe_keyword']);
		$add_subscribe_msg = $_POST['add_subscribe_msg'];
		$add_unsubscribe_msg = $_POST['add_unsubscribe_msg'];
		$add_subscribe_param = strtoupper($_POST['add_subscribe_param']);
		$add_unsubscribe_param = strtoupper($_POST['add_unsubscribe_param']);
		$add_forward_param = strtoupper(($_POST['add_forward_param'] ? $_POST['add_forward_param'] : 'BC'));
		$add_unknown_format_msg = $_POST['add_unknown_format_msg'];
		$add_already_member_msg = $_POST['add_already_member_msg'];
		$add_expire_msg = $_POST['add_expire_msg'];
		$add_duration = (int) $_POST['add_duration'];
		if (auth_isadmin()) {
			$smsc = $_REQUEST['smsc'];
		}
		if ($add_subscribe_keyword && $add_subscribe_msg && $add_unsubscribe_msg && $add_forward_param && $add_unknown_format_msg && $add_already_member_msg) {
			if (checkavailablekeyword($add_subscribe_keyword)) {
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_featureSubscribe (uid,subscribe_keyword,subscribe_msg,unsubscribe_msg, subscribe_param, unsubscribe_param, forward_param, unknown_format_msg, already_member_msg,smsc,duration,expire_msg)
					VALUES ('" . $user_config['uid'] . "','$add_subscribe_keyword','$add_subscribe_msg','$add_unsubscribe_msg','$add_subscribe_param','$add_unsubscribe_param','$add_forward_param','$add_unknown_format_msg','$add_already_member_msg','$smsc','$add_duration','$add_expire_msg')";
				if ($new_uid = @dba_insert_id($db_query)) {
					$_SESSION['dialog']['info'][] = _('SMS subscribe has been added') . " (" . _('keyword') . ": $add_subscribe_keyword)";
				} else {
					$_SESSION['dialog']['info'][] = _('Fail to add SMS subscribe') . " (" . _('keyword') . ": $add_subscribe_keyword)";
				}
			} else {
				$_SESSION['dialog']['info'][] = _('SMS subscribe already exists, reserved or use by other feature') . " (" . _('keyword') . ": $add_subscribe_keyword)";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_add'));
		exit();
		break;
	
	case "sms_subscribe_edit":
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_subscribe_keyword = $db_row['subscribe_keyword'];
		$edit_subscribe_msg = $db_row['subscribe_msg'];
		$edit_unsubscribe_msg = $db_row['unsubscribe_msg'];
		$edit_subscribe_param = $db_row['subscribe_param'];
		$edit_unsubscribe_param = $db_row['unsubscribe_param'];
		$edit_forward_param = $db_row['forward_param'];
		$max_length = $core_config['main']['max_sms_length'];
		$edit_unknown_format_msg = $db_row['unknown_format_msg'];
		$edit_already_member_msg = $db_row['already_member_msg'];
		$edit_expire_msg = $db_row['expire_msg'];
		$select_durations = _select('edit_duration', $plugin_config['sms_subscribe']['durations'], $db_row['duration']);
		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('smsc', $db_row['smsc']) . "</td></tr>";
		}
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<link rel='stylesheet' type='text/css' href=" . _HTTP_PATH_THEMES_ . "/common/jscss/sms_subscribe.css />
			<h2>" . _('Manage subscribe') . "</h2>
			<h3>" . _('Edit SMS subscribe') . "</h3>
			<form name=\"form_subscribe_edit\" id=\"form_subscribe_edit\" action=index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_edit_yes method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden name=subscribe_id value=\"$subscribe_id\">
			<input type=hidden name=edit_subscribe_keyword value=\"$edit_subscribe_keyword\">
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _('SMS subscribe keyword') . "</td><td>$edit_subscribe_keyword</td>
			</tr>
			<tr>
				<td class=label-sizer>" . _('SMS subscribe parameter') . "</td>
				
				<td>
					<input type=text size=10 maxlength=20 name=edit_subscribe_param value=\"$edit_subscribe_param\">
				</td>
			</tr>
			<tr>
				<td>" . _('SMS subscribe reply') . "</td>
				<td>
					<textarea maxlength=\"140\" name=\"edit_subscribe_msg\" id=\"edit_subscribe_msg\" value=\"\" cols=\"35\" rows=\"3\"
						onClick=\"SmsSetCounter_Abstract('edit_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\"
						onkeypress=\"SmsSetCounter_Abstract('edit_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\"
						onblur=\"SmsSetCounter_Abstract('edit_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\"
						onKeyUp=\"SmsSetCounter_Abstract('edit_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\"
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_edit', 'edit_subscribe_msg');\"
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_edit');\">$edit_subscribe_msg</textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" id=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_edit.edit_subscribe_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length'] . "\" name=\"hiddcount\" id=\"hiddcount\">
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length_unicode'] . "\" name=\"hiddcount_unicode\" id=\"hiddcount_unicode\">
				</td>
			</tr>
			<tr>
				<td class=label-sizer>" . _('SMS unsubscribe parameter') . "</td>
				
				<td>
					<input type=text size=10 maxlength=20 name=edit_unsubscribe_param value=\"$edit_unsubscribe_param\">
				</td>
			</tr>
			<tr>
				<td>" . _('SMS unsubscribe reply') . "</td>
				<td>
					<textarea maxlength=\"140\" name=\"edit_unsubscribe_msg\" id=\"edit_unsubscribe_msg\" value=\"\" cols=\"35\" rows=\"3\"
						onClick=\"SmsSetCounter_Abstract('edit_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\"
						onkeypress=\"SmsSetCounter_Abstract('edit_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\"
						onblur=\"SmsSetCounter_Abstract('edit_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\"
						onKeyUp=\"SmsSetCounter_Abstract('edit_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\"
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_edit', 'edit_unsubscribe_msg');\"
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_edit');\">$edit_unsubscribe_msg</textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount_un\" id=\"txtcount_un\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_edit.edit_unsubscribe_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length'] . "\" name=\"hiddcount_un\" id=\"hiddcount_un\">
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length_unicode'] . "\" name=\"hiddcount_unicode_un\" id=\"hiddcount_unicode_un\">
				</td>
			</tr>
			<tr>
				<td class=label-sizer>" . _('SMS forward parameter') . "</td>				
				<td>
					<input type=text size=10 maxlength=20 name=edit_forward_param value=\"$edit_forward_param\">
				</td>
			</tr>
			<tr>
				<td>" . _('Subscribe duration') . "</td>				
				<td>" . $select_durations . "</td>
			</tr>
			<tr>
				<td>" . _('Subscription expired reply') . "</td>
				<td>
					<textarea maxlength=\"140\" name=\"edit_expire_msg\" id=\"edit_expire_msg\" value=\"\" cols=\"35\" rows=\"3\"
						onClick=\"SmsSetCounter_Abstract('edit_expire_msg','txtcount_ex','hiddcount_ex','hiddcount_unicode_ex');\"
						onkeypress=\"SmsSetCounter_Abstract('edit_expire_msg','txtcount_ex','hiddcount_ex','hiddcount_unicode_ex');\"
						onblur=\"SmsSetCounter_Abstract('edit_expire_msg','txtcount_ex','hiddcount_ex','hiddcount_unicode_ex');\"
						onKeyUp=\"SmsSetCounter_Abstract('edit_expire_msg','txtcount_ex','hiddcount_ex','hiddcount_unicode_ex');\"
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_add', 'edit_expire_msg');\"
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_add');\">$edit_expire_msg</textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount_ex\" id=\"txtcount_ex\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_add.edit_expire_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length'] . "\" name=\"hiddcount_ex\" id=\"hiddcount_ex\">
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length_unicode'] . "\" name=\"hiddcount_unicode_ex\" id=\"hiddcount_unicode_ex\">
				</td>
			</tr>
			<tr>
				<td>" . _('Unknown format reply') . "</td>
				<td>
					<textarea maxlength=\"140\" name=\"edit_unknown_format_msg\" id=\"edit_unknown_format_msg\" value=\"\" cols=\"35\" rows=\"3\"
						onClick=\"SmsSetCounter_Abstract('edit_unknown_format_msg','txtcount_uk','hiddcount_uk','hiddcount_unicode_uk');\"
						onkeypress=\"SmsSetCounter_Abstract('edit_unknown_format_msg','txtcount_uk','hiddcount_uk','hiddcount_unicode_uk');\"
						onblur=\"SmsSetCounter_Abstract('edit_unknown_format_msg','txtcount_uk','hiddcount_uk','hiddcount_unicode_uk');\"
						onKeyUp=\"SmsSetCounter_Abstract('edit_unknown_format_msg','txtcount_uk','hiddcount_uk','hiddcount_unicode_uk');\"
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_add', 'edit_unknown_format_msg');\"
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_add');\">$edit_unknown_format_msg</textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount_uk\" id=\"txtcount_uk\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_add.edit_unknown_format_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length'] . "\" name=\"hiddcount_uk\" id=\"hiddcount_uk\">
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length_unicode'] . "\" name=\"hiddcount_unicode_uk\" id=\"hiddcount_unicode_uk\">
				</td>
			</tr>
			<tr>
				<td>" . _('Already a member reply') . "</td>
				<td>
					<textarea maxlength=\"140\" name=\"edit_already_member_msg\" id=\"edit_already_member_msg\" value=\"\" cols=\"35\" rows=\"3\"
						onClick=\"SmsSetCounter_Abstract('edit_already_member_msg','txtcount_am','hiddcount_am','hiddcount_unicode_am');\"
						onkeypress=\"SmsSetCounter_Abstract('edit_already_member_msg','txtcount_am','hiddcount_am','hiddcount_unicode_am');\"
						onblur=\"SmsSetCounter_Abstract('edit_already_member_msg','txtcount_am','hiddcount_am','hiddcount_unicode_am');\"
						onKeyUp=\"SmsSetCounter_Abstract('edit_already_member_msg','txtcount_am','hiddcount_am','hiddcount_unicode_am');\"
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_add', 'edit_already_member_msg');\"
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_add');\">$edit_already_member_msg</textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount_am\" id=\"txtcount_am\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_add.edit_already_member_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length'] . "\" name=\"hiddcount_am\" id=\"hiddcount_am\">
					<input type=\"hidden\" value=\"" . $core_config['main']['max_sms_length_unicode'] . "\" name=\"hiddcount_unicode_am\" id=\"hiddcount_unicode_am\">
				</td>
			</tr>
			" . $select_reply_smsc . "
		</table>
		<p><input type=submit class=button value=\"" . _('Save') . "\">
		</form>
		<p>" . _back('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_list');
		_p($content);
		break;
	
	case "sms_subscribe_edit_yes":
		$edit_subscribe_keyword = strtoupper($_POST['edit_subscribe_keyword']);
		$edit_subscribe_msg = $_POST['edit_subscribe_msg'];
		$edit_unsubscribe_msg = $_POST['edit_unsubscribe_msg'];
		$edit_subscribe_param = strtoupper($_POST['edit_subscribe_param']);
		$edit_unsubscribe_param = strtoupper($_POST['edit_unsubscribe_param']);
		$edit_forward_param = strtoupper(($_POST['edit_forward_param'] ? $_POST['edit_forward_param'] : 'BC'));
		$edit_forward_param = strtoupper($_POST['edit_forward_param']);
		$edit_unknown_format_msg = $_POST['edit_unknown_format_msg'];
		$edit_already_member_msg = $_POST['edit_already_member_msg'];
		$edit_expire_msg = $_POST['edit_expire_msg'];
		$edit_duration = (int) $_POST['edit_duration'];
		if (auth_isadmin()) {
			$smsc = $_REQUEST['smsc'];
			$smsc_sql = ",smsc='$smsc'";
		}
		if ($subscribe_id && $edit_subscribe_keyword && $edit_subscribe_msg && $edit_unsubscribe_msg && $edit_forward_param && $edit_unknown_format_msg && $edit_already_member_msg) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_featureSubscribe
				SET c_timestamp='" . mktime() . "', subscribe_keyword='$edit_subscribe_keyword', subscribe_msg='$edit_subscribe_msg',
					unsubscribe_msg='$edit_unsubscribe_msg', subscribe_param='$edit_subscribe_param', unsubscribe_param='$edit_unsubscribe_param',
					forward_param='$edit_forward_param', unknown_format_msg='$edit_unknown_format_msg', already_member_msg='$edit_already_member_msg',
					duration='$edit_duration',expire_msg='$edit_expire_msg'
					" . $smsc_sql . "
				WHERE subscribe_id='$subscribe_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('SMS subscribe has been saved') . " (" . _('keyword') . ": $edit_subscribe_keyword)";
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to edit SMS subscribe') . " (" . _('keyword') . ": $edit_subscribe_keyword)";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_edit&subscribe_id=' . $subscribe_id));
		exit();
		break;
	
	case "sms_subscribe_del":
		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_keyword = $db_row['subscribe_keyword'];
		if ($subscribe_keyword) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id'";
			if (@dba_affected_rows($db_query)) {
				$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id'";
				$del_msg = dba_affected_rows($db_query);
				$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id='$subscribe_id'";
				$del_member = dba_affected_rows($db_query);
				$_SESSION['dialog']['info'][] = _('SMS subscribe with all its messages and members has been deleted') . " (" . _('keyword') . ": $subscribe_keyword)";
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_list'));
		exit();
		break;
	
	case "mbr_list":
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '$subscribe_id' ORDER BY member_since DESC";
		$db_result = dba_query($db_query);
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>" . _('Manage subscribe') . "</h2>
			<h3>" . _('Member list for keyword') . " $subscribe_name</h3>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=50%>" . _('Phone number') . "</th>
				<th width=40%>" . _('Member join datetime') . "</th>
				<th width=10%>" . _('Action') . "</th>
			</tr></thead>
			<tbody>";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$action = "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete this member ?') . "','" . _u('index.php?app=main&inc=feature_sms_subscribe&op=mbr_del&subscribe_id=' . $subscribe_id . '&mbr_id=' . $db_row['member_id']) . "')\">" . $icon_config['delete'] . "</a>";
			$i++;
			$content .= "
				<tr>
					<td>" . $db_row['member_number'] . "</td>
					<td>" . $db_row['member_since'] . "</td>
					<td>$action</td>
					</tr>";
		}
		$content .= "
			</tbody>
			</table>
			</div>
			<p>" . _back('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_list');
		_p($content);
		break;
	
	case "mbr_del":
		if ($subscribe_id && ($mbr_id = $_REQUEST['mbr_id'])) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id='$subscribe_id' AND member_id='$mbr_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Member has been deleted');
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_subscribe&op=mbr_list&subscribe_id=' . $subscribe_id));
		exit();
		break;
	
	case "msg_list":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>" . _('Manage subscribe') . "</h2>
			<h3>" . _('SMS messages list for keyword') . " $subscribe_name</h3>
			<p>" . _button('index.php?app=main&inc=feature_sms_subscribe&op=msg_add&&subscribe_id=' . $subscribe_id, _('Add message')) . "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=40%>" . _('Message') . "</th>
				<th width=20%>" . _('Created') . "</th>
				<th width=20%>" . _('Last update') . "</th>
				<th width=10%>" . _('Sent') . "</th>
				<th width=10%>" . _('Action') . "</th>
			</tr></thead>
			<tbody>";
		$i = 0;
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$action = "<a href=\"" . _u('index.php?app=main&inc=feature_sms_subscribe&op=msg_view&subscribe_id=' . $db_row['subscribe_id'] . '&msg_id=' . $db_row['msg_id']) . "\">" . $icon_config['view'] . "</a>&nbsp;";
			$action .= "<a href=\"" . _u('index.php?app=main&inc=feature_sms_subscribe&op=msg_edit&subscribe_id=' . $subscribe_id . '&msg_id=' . $db_row['msg_id']) . "\">" . $icon_config['edit'] . "</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete this message?') . "','" . _u('index.php?app=main&inc=feature_sms_subscribe&op=msg_del&subscribe_id=' . $subscribe_id . '&msg_id=' . $db_row['msg_id']) . "')\">" . $icon_config['delete'] . "</a>";
			$i++;
			$content .= "
				<tr>
					<td>" . $db_row['msg'] . "</td>
					<td>" . core_display_datetime($db_row['create_datetime']) . "</td>
					<td>" . core_display_datetime($db_row['update_datetime']) . "</td>
					<td>" . $db_row['counter'] . "</td>
					<td>$action</td>
					</tr>";
		}
		$content .= "
			</tbody>
			</table>
			</div>
			<p>" . _button('index.php?app=main&inc=feature_sms_subscribe&op=msg_add&&subscribe_id=' . $subscribe_id, _('Add message')) . "
			<p>" . _back('index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_list');
		_p($content);
		break;
	
	case "msg_edit":
		$msg_id = $_REQUEST['msg_id'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id' AND msg_id = '$msg_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_mbr_msg = $db_row['msg'];
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>" . _('Manage subscribe') . "</h2>
			<h3>" . _('Edit message') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_subscribe&op=msg_edit_yes method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden value=$subscribe_id name=subscribe_id>
			<input type=hidden value=$msg_id name=msg_id>
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _('SMS subscribe keyword') . "</td><td>$subscribe_name</td>
			</tr>
			<tr>
				<td colspan=2>
					" . _('Message body') . "<br />
					<textarea name=edit_mbr_msg rows=5 cols=60>$edit_mbr_msg</textarea>
				</td>
			</tr>
			</table>
			<input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			<p>" . _back('index.php?app=main&inc=feature_sms_subscribe&op=msg_list&subscribe_id=' . $subscribe_id);
		_p($content);
		break;
	
	case "msg_edit_yes":
		$edit_mbr_msg = $_POST['edit_mbr_msg'];
		$msg_id = $_POST['msg_id'];
		if ($subscribe_id && $edit_mbr_msg && $msg_id) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_featureSubscribe_msg set c_timestamp='" . mktime() . "', msg='$edit_mbr_msg',update_datetime='" . core_get_datetime() . "'
				WHERE subscribe_id='$subscribe_id' AND msg_id ='$msg_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Message has been edited');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to edit message');
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_subscribe&op=msg_edit&subscribe_id=' . $subscribe_id . '&msg_id=' . $msg_id));
		exit();
		break;
	
	case "msg_add":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe where subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_name = $db_row['subscribe_keyword'];
		$content .= "
			<h2>" . _('Manage subscribe') . "</h2>
			<h3>" . _('Add message') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_subscribe&op=msg_add_yes method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden value=$subscribe_id name=subscribe_id>
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _('SMS subscribe keyword') . "</td><td>$subscribe_name</td>
			</tr>
			<tr>
				<td colspan=2>
					" . _('Message body') . "<br />
					<textarea name=add_mbr_message rows=5 cols=60></textarea>
				</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			<p>" . _back('index.php?app=main&inc=feature_sms_subscribe&op=msg_list&subscribe_id=' . $subscribe_id);
		_p($content);
		break;
	
	case "msg_add_yes":
		$add_mbr_message = $_POST['add_mbr_message'];
		if ($subscribe_id && $add_mbr_message) {
			$dt = core_get_datetime();
			$db_query = "
				INSERT INTO " . _DB_PREF_ . "_featureSubscribe_msg (subscribe_id,msg,create_datetime,update_datetime)
				VALUES ('$subscribe_id','$add_mbr_message','$dt','$dt')";
			if ($new_uid = @dba_insert_id($db_query)) {
				$_SESSION['dialog']['info'][] = _('Message has been added');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to add message');
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_subscribe&op=msg_add&subscribe_id=' . $subscribe_id));
		exit();
		break;
	
	case "msg_del":
		$msg_id = $_REQUEST['msg_id'];
		if ($msg_id) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id' AND msg_id='$msg_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Message has been deleted');
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_subscribe&op=msg_list&subscribe_id=' . $subscribe_id));
		exit();
		break;
	
	case "msg_view":
		$list = dba_search(_DB_PREF_ . '_featureSubscribe', 'subscribe_keyword', array(
			'subscribe_id' => $subscribe_id 
		));
		$subscribe_name = $list[0]['subscribe_keyword'];
		$msg_id = $_REQUEST['msg_id'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id' AND msg_id='$msg_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$message = $db_row['msg'];
		$counter = $db_row['counter'];
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>" . _('Manage subscribe') . "</h2>
			<h3>" . _('Message detail') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_subscribe&op=msg_send method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden value=$message name=msg>
			<input type=hidden value=$subscribe_id name=subscribe_id>
			<input type=hidden value=$msg_id name=msg_id>
			<table class=playsms-table>
			<tr><td class=label-sizer>" . _('SMS subscribe keyword') . "</td><td>$subscribe_name</td></tr>
			<tr><td>" . _('Message ID') . "</td><td>" . $msg_id . "</td></tr>
			<tr><td>" . _('Message') . "</td><td>" . $message . "</td></tr>
			<tr><td>" . _('Sent') . "</td><td>" . $counter . "</td></tr>
			</table>
			<br />
			<p>" . _('Send this message to all members') . "</p>
			<p><input type=submit value=\"" . _('Send') . "\" class=\"button\" />
			</form>
			<p>" . _back('index.php?app=main&inc=feature_sms_subscribe&op=msg_list&subscribe_id=' . $subscribe_id);
		_p($content);
		break;
	
	case "msg_send":
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$smsc = $db_row['smsc'];
		$c_uid = $db_row['uid'];
		$username = user_uid2username($c_uid);
		
		$msg_id = $_POST['msg_id'];
		$db_query = "SELECT msg, counter FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id' AND msg_id='$msg_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$message = addslashes($db_row['msg']);
		$counter = $db_row['counter'];
		
		$db_query = "SELECT member_number FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$sms_to = '';
		if ($message && $subscribe_id) {
			while ($db_row = dba_fetch_array($db_result)) {
				if ($member_number = $db_row['member_number']) {
					$sms_to[] = $member_number;
				}
			}
			if ($sms_to[0]) {
				$unicode = core_detect_unicode($message);
				$message = addslashes($message);
				list($ok, $to, $smslog_id, $queue) = sendsms_helper($username, $sms_to, $message, 'text', $unicode, $smsc, TRUE);
				if ($ok[0]) {
					$counter++;
					dba_update(_DB_PREF_ . '_featureSubscribe_msg', array(
						'counter' => $counter 
					), array(
						'subscribe_id' => $subscribe_id,
						'msg_id' => $msg_id 
					));
					$_SESSION['dialog']['info'][] .= _('Your SMS has been delivered to queue') . "<br>";
				} else {
					$_SESSION['dialog']['info'][] .= _('Fail to send SMS') . "<br>";
				}
			} else {
				$_SESSION['dialog']['info'][] = _('You have no member');
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_subscribe&op=msg_view&msg_id=' . $msg_id . '&subscribe_id=' . $subscribe_id));
		exit();
		break;
}
