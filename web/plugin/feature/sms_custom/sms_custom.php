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

$custom_id = (int) $_REQUEST['custom_id'];

if ($custom_id && !sms_custom_check_id($custom_id)) {
	auth_block();
}

switch (_OP_) {
	case "sms_custom_list":
		$content .= _dialog() . "
			<h2>" . _('Manage custom') . "</h2>
			" . _button('index.php?app=main&inc=feature_sms_custom&op=sms_custom_add', _('Add SMS custom')) . "
			<div class=table-responsive>
			<table class=playsms-table-list>";
		if (auth_isadmin()) {
			$content .= "
				<thead><tr>
					<th width=15%>" . _('Service name') . "</th>
					<th width=55%>" . _('Service data') . "</th>
					<th width=10%>" . _('Return') . " " . _hint(_('Return as reply')) . "</th>
					<th width=10%>" . _('User') . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>";
		} else {
			$content .= "
				<thead><tr>
					<th width=15%>" . _('Service name') . "</th>
					<th width=65%>" . _('Service data') . "</th>
					<th width=10%>" . _('Return') . " " . _hint(_('Return as reply')) . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>";
		}
		$content .= "<tbody>";
		$db_argv = [];
		if (!auth_isadmin()) {
			$query_user_only = "WHERE uid=?";
			$db_argv[] = $user_config['uid'];
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCustom " . $query_user_only . " ORDER BY service_name, custom_keyword, sms_receiver";
		$db_result = dba_query($db_query, $db_argv);
		while ($db_row = dba_fetch_array($db_result)) {
			$db_row = _display($db_row);
			if ($owner = user_uid2username($db_row['uid'])) {
				$action = "<a href=\"" . _u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_edit&custom_id=' . $db_row['custom_id']) . "\">" . $icon_config['edit'] . "</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete SMS custom ?') . " (" . _('keyword') . ": " . $db_row['custom_keyword'] . ")','" . _u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_del&custom_id=' . $db_row['custom_id']) . "')\">" . $icon_config['delete'] . "</a>";
				$sms_receiver = '';
				if ($db_row['sms_receiver']) {
					$sms_receiver = "<div name=sms_custom_sms_receiver><span class=\"playsms-icon glyphicon glyphicon-inbox\" alt=\"" . _('Receiver number') . "\" title=\"" . _('Receiver number') . "\"></span>" . $db_row['sms_receiver'] . "</div>";
				}
				$custom_url = htmlspecialchars_decode($db_row['custom_url']);
				$custom_return_as_reply = (int) $db_row['custom_return_as_reply'] ? _('Yes') : _('No');
				if (auth_isadmin()) {
					$show_owner = "<td>" . $owner . "</td>";
				}
				$content .= "
					<tr>
						<td>" . $db_row['service_name'] . "</td>
						<td>
							<div name=sms_custom_keywords><span class=\"playsms-icon glyphicon glyphicon-search\" alt=\"" . _('Keywords') . "\" title=\"" . _('Keywords') . "\"></span>" . $db_row['custom_keyword'] . "</div>
							" . $sms_receiver . "
							<div name=sms_custom_url>" . $custom_url . "</div>
						</td>
						<td>" . $custom_return_as_reply . "</td>
						" . $show_owner . "
						<td>$action</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			</div>
			" . _button('index.php?app=main&inc=feature_sms_custom&op=sms_custom_add', _('Add SMS custom'));
		_p($content);
		break;

	case "sms_custom_edit":
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCustom WHERE custom_id=?";
		$db_result = dba_query($db_query, [$custom_id]);
		if (!($db_row = dba_fetch_array($db_result))) {
			$_SESSION['dialog']['danger'][] = _('Unknown error cannot find the data in database');
			header("Location: " . _u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_list'));
			exit();
		}
		$db_row = _display($db_row);

		$edit_custom_uid = $db_row['uid'];
		$edit_service_name = _lastpost('edit_service_name') ? _lastpost('edit_service_name') : $db_row['service_name'];
		$edit_custom_keyword = strtoupper(core_sanitize_alphanumeric($db_row['custom_keyword']));
		$edit_sms_receiver = $db_row['sms_receiver'];
		$edit_custom_url = $db_row['custom_url'];
		$edit_custom_return_as_reply = $db_row['custom_return_as_reply'] == '1' ? 'checked' : '';
		$edit_smsc = $db_row['smsc'];

		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('edit_smsc', $edit_smsc) . "</td></tr>";
		}

		$content .= _dialog() . "
			<h2>" . _('Manage custom') . "</h2>
			<h3>" . _('Edit SMS custom') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_custom&op=sms_custom_edit_yes method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden name=custom_id value=$custom_id>
			<input type=hidden name=edit_custom_keyword value=$edit_custom_keyword>
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _mandatory(_('Service name')) . "</td><td><input type=text size=30 maxlength=255 name=edit_service_name value=\"" . $edit_service_name . "\"></td>
				</tr>
				<tr>
					<td>" . _('SMS custom keywords') . "</td><td>" . $edit_custom_keyword . "</td>
				</tr>
				<tr>
					<td>" . _('Receiver number') . "</td><td>" . $edit_sms_receiver . "</td>
				</tr>
				<tr>
					<td colspan=2>
						" . _('Pass these parameters to custom URL field') . "
						<ul>
							<li>{SERVICENAME} " . _('will be replaced by service name') . "</li>
							<li>{SMSDATETIME} " . _('will be replaced by SMS incoming date/time') . "</li>
							<li>{SMSSENDER} " . _('will be replaced by sender number') . "</li>
							<li>{SMSRECEIVER} " . _('will be replaced by actual receiver number') . "</li>
							<li>{CUSTOMKEYWORD} " . _('will be replaced by custom keyword') . "</li>
							<li>{CUSTOMPARAM} " . _('will be replaced by custom parameter passed to server from SMS') . "</li>
							<li>{CUSTOMRAW} " . _('will be replaced by SMS raw message') . "</li>
						</ul>
						" . _('Example of SMS custom URL') . "
						<ul>
							<li>" . htmlspecialchars('http://example.com/go?s={SERVICENAME}&dt={SMSDATETIME}&fr={SMSSENDER}&to={SMSRECEIVER}&code={CUSTOMKEYWORD}&arg={CUSTOMPARAM}&m={CUSTOMRAW}') . "</li>
						</ul>
					</td>
				</tr>
				<tr>
					<td>" . _mandatory(_('SMS custom URL')) . "</td><td><input type=text maxlength=255 name=edit_custom_url value=\"$edit_custom_url\"></td>
				</tr>
				<tr>
					<td>" . _('Make return as reply') . "</td><td><input type=checkbox name=edit_custom_return_as_reply $edit_custom_return_as_reply></td>
				</tr>
				" . $select_reply_smsc . "
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_sms_custom&op=sms_custom_list');
		_p($content);
		break;

	case "sms_custom_edit_yes":
		$edit_service_name = $_POST['edit_service_name'];
		$edit_custom_return_as_reply = $_POST['edit_custom_return_as_reply'] == 'on' ? 1 : 0;
		$edit_custom_url = $_POST['edit_custom_url'];
		$db_argv = [time(), $edit_service_name, $edit_custom_url, $edit_custom_return_as_reply];
		$query_smsc = "";
		if (auth_isadmin()) {
			$edit_smsc = $_POST['edit_smsc'];
			$query_smsc = ",smsc=?";
			$db_argv[] = $edit_smsc;
		}
		$db_argv[] = $custom_id;
		if ($custom_id && $edit_service_name && $edit_custom_url) {
			$db_query = "UPDATE " . _DB_PREF_ . "_featureCustom SET c_timestamp=?,service_name=?,custom_url=?,custom_return_as_reply=?" . $query_smsc . " WHERE custom_id=?";
			if (dba_affected_rows($db_query, $db_argv)) {
				$_SESSION['dialog']['info'][] = _('SMS custom has been saved');
				_lastpost_empty();
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to save SMS custom');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_edit&custom_id=' . $custom_id));
		exit();

	case "sms_custom_del":
		$db_query = "SELECT custom_keyword FROM " . _DB_PREF_ . "_featureCustom WHERE custom_id=?";
		$db_result = dba_query($db_query, [$custom_id]);
		$db_row = dba_fetch_array($db_result);
		if ($db_row['custom_keyword']) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureCustom WHERE custom_id=?";
			if (dba_affected_rows($db_query, [$custom_id])) {
				$_SESSION['dialog']['info'][] = _('SMS custom has been deleted');
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to delete SMS custom');
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_list'));
		exit();

	case "sms_custom_add":
		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('add_smsc') . "</td></tr>";
		}

		$content .= _dialog() . "
			<h2>" . _('Manage custom') . "</h2>
			<h3>" . _('Add SMS custom') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_custom&op=sms_custom_add_yes method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _mandatory(_('Service name')) . "</td><td><input type=text size=30 maxlength=255 name=add_service_name value=\"" . _lastpost('add_service_name') . "\"></td>
				</tr>
					<tr>
					<td>" . _mandatory(_('SMS custom keywords')) . "</td><td><input type=text size=30 maxlength=255 name=add_custom_keyword value=\"" . _lastpost('add_custom_keyword') . "\"> " . _hint('Multiple keywords seperated by space') . "</td>
				</tr>
				<tr>
					<td>" . _('Receiver number') . "</td><td><input type=text size=30 maxlength=20 name=add_sms_receiver value=\"" . _lastpost('add_sms_receiver') . "\"></td>
				</tr>
				<tr>
					<td colspan=2>" . _('Pass these parameters to custom URL field') . "</td>
				</tr>
				<tr>
					<td colspan=2>
						" . _('Pass these parameters to custom URL field') . "
						<ul>
							<li>{SERVICENAME} " . _('will be replaced by service name') . "</li>
							<li>{SMSDATETIME} " . _('will be replaced by SMS incoming date/time') . "</li>
							<li>{SMSSENDER} " . _('will be replaced by sender number') . "</li>
							<li>{SMSRECEIVER} " . _('will be replaced by receiver number') . "</li>
							<li>{CUSTOMKEYWORD} " . _('will be replaced by custom keyword') . "</li>
							<li>{CUSTOMPARAM} " . _('will be replaced by custom parameter passed to server from SMS') . "</li>
							<li>{CUSTOMRAW} " . _('will be replaced by SMS raw message') . "</li>
						</ul>
						" . _('Example of SMS custom URL') . "
						<ul>
							<li>" . htmlspecialchars('http://example.com/go?s={SERVICENAME}&dt={SMSDATETIME}&fr={SMSSENDER}&to={SMSRECEIVER}&code={CUSTOMKEYWORD}&arg={CUSTOMPARAM}&m={CUSTOMRAW}') . "</li>
						</ul>
					</td>
				</tr>
				<tr>
					<td>" . _mandatory(_('SMS custom URL')) . "</td><td><input type=text maxlength=255 name=add_custom_url value=\"" . _lastpost('add_custom_url') . "\"></td>
				</tr>
				<tr>
					<td>" . _('Make return as reply') . "</td><td><input type=checkbox name=add_custom_return_as_reply></td>
				</tr>
				" . $select_reply_smsc . "
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_sms_custom&op=sms_custom_list');
		_p($content);
		break;

	case "sms_custom_add_yes":
		$add_service_name = trim($_POST['add_service_name']);
		$add_sms_receiver = trim($_POST['add_sms_receiver']);
		$add_custom_return_as_reply = $_POST['add_custom_return_as_reply'] == 'on' ? 1 : 0;
		$add_custom_url = $_POST['add_custom_url'];

		$add_custom_keyword = strtoupper(trim($_POST['add_custom_keyword']));
		$c_keywords = explode(' ', $add_custom_keyword);
		foreach ( $c_keywords as $keyword ) {
			if ($keyword = core_sanitize_alphanumeric($keyword)) {
				if (keyword_isavail($keyword, $add_sms_receiver)) {
					$keywords .= $keyword . ' ';
				} else {
					$_SESSION['dialog']['danger'][] = sprintf(_('Keyword %s is not available'), $keyword);
				}
			}
		}
		$keywords = trim($keywords);

		if (auth_isadmin()) {
			$add_smsc = $_POST['add_smsc'];
		}

		if ($add_service_name && $keywords && $add_custom_url) {
			$db_query = "INSERT INTO " . _DB_PREF_ . "_featureCustom (uid,service_name,custom_keyword,sms_receiver,custom_url,custom_return_as_reply,smsc) VALUES (?,?,?,?,?,?,?)";
			$db_argv = [
				$user_config['uid'],
				$add_service_name,
				$keywords,
				$add_sms_receiver,
				$add_custom_url,
				$add_custom_return_as_reply,
				$add_smsc
			];
			if (dba_insert_id($db_query, $db_argv)) {
				$_SESSION['dialog']['info'][] = sprintf(_('SMS custom with keyword %s has been added'), $keywords);
				_lastpost_empty();
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to add SMS custom');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}

		header("Location: " . _u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_add'));
		exit();
}
