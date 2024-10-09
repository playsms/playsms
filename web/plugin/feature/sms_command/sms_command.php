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

if ($plugin_config['sms_command']['allow_user_access']) {
	if (!auth_isvalid()) {
		auth_block();
	}
} else {
	if (!auth_isadmin()) {
		auth_block();
	}
}

$command_id = (int) $_REQUEST['command_id'];

if ($command_id && !sms_command_check_id($command_id)) {
	auth_block();
}

$sms_command_bin = $plugin_config['sms_command']['bin'];

switch (_OP_) {
	case "sms_command_list":
		$content = _dialog() . "
			<h2>" . _('Manage command') . "</h2>
			" . _button('index.php?app=main&inc=feature_sms_command&op=sms_command_add', _('Add SMS command')) . "
			<div class=table-responsive>
			<table class=playsms-table-list>";
		if (auth_isadmin()) {
			$content .= "
				<thead><tr>
					<th width=20%>" . _('Keyword') . "</th>
					<th width=50%>" . _('Exec') . "</th>
					<th width=20%>" . _('User') . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>";
		} else {
			$content .= "
				<thead><tr>
					<th width=20%>" . _('Keyword') . "</th>
					<th width=70%>" . _('Exec') . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>";
		}
		$content .= "<tbody>";
		$db_argv = [];
		if (!auth_isadmin()) {
			$query_user_only = "WHERE uid=?";
			$db_argv[] = $user_config['uid'];
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCommand " . $query_user_only . " ORDER BY command_keyword";
		$db_result = dba_query($db_query, $db_argv);
		while ($db_row = dba_fetch_array($db_result)) {
			$db_row = _display($db_row);
			if ($owner = user_uid2username($db_row['uid'])) {
				$action = "<a href=\"" . _u('index.php?app=main&inc=feature_sms_command&op=sms_command_edit&command_id=' . $db_row['command_id']) . "\">" . $icon_config['edit'] . "</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete SMS command ?') . " (" . _('keyword') . ": " . $db_row['command_keyword'] . ")','" . _u('index.php?app=main&inc=feature_sms_command&op=sms_command_del&command_id=' . $db_row['command_id']) . "')\">" . $icon_config['delete'] . "</a>";
				$command_exec = $sms_command_bin . '/' . $db_row['uid'] . '/' . $db_row['command_exec'];
				if (auth_isadmin()) {
					$show_owner = "<td>" . $owner . "</td>";
				}
				$content .= "
					<tr>
						<td>" . $db_row['command_keyword'] . "</td>
						<td>" . stripslashes($command_exec) . "</td>
						" . $show_owner . "
						<td>$action</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			</div>
			" . _button('index.php?app=main&inc=feature_sms_command&op=sms_command_add', _('Add SMS command'));
		_p($content);
		break;

	case "sms_command_edit":
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCommand WHERE command_id=?";
		$db_result = dba_query($db_query, [$command_id]);
		if (!($db_row = dba_fetch_array($db_result))) {
			$_SESSION['dialog']['danger'][] = _('Unknown error cannot find the data in database');
			header("Location: " . _u('index.php?app=main&inc=feature_sms_command&op=sms_command_list'));
			exit();
		}
		$db_row = _display($db_row);

		$edit_command_uid = $db_row['uid'];
		$edit_command_keyword = strtoupper(core_sanitize_alphanumeric($db_row['command_keyword']));
		$edit_command_exec = stripslashes($db_row['command_exec']);
		$edit_command_exec = str_replace($sms_command_bin . "/", '', $edit_command_exec);
		$edit_command_return_as_reply = ($db_row['command_return_as_reply'] == '1' ? 'checked' : '');
		$edit_smsc = $db_row['smsc'];

		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('edit_smsc', $edit_smsc) . "</td></tr>";
		}

		$content = _dialog() . "
			<h2>" . _('Manage command') . "</h2>
			<h3>" . _('Edit SMS command') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_command&op=sms_command_edit_yes method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden name=command_id value=\"$command_id\">
			<input type=hidden name=edit_command_keyword value=\"$edit_command_keyword\">
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _('SMS command keyword') . "</td><td>" . $edit_command_keyword . "</td>
				</tr>
				<tr>
					<td>" . _('SMS command exec path') . "</td><td>" . $sms_command_bin . '/' . $user_config['uid'] . "</td>
				</tr>
				<tr>
					<td colspan=2>" . _('Pass these parameter to command exec field') . "</td>
				</tr>
				<tr>
					<td colspan=2>
						<ul>
							<li>{SMSDATETIME} " . _('will be replaced by SMS incoming date/time') . "</li>
							<li>{SMSSENDER} " . _('will be replaced by sender number') . "</li>
							<li>{COMMANDKEYWORD} " . _('will be replaced by command keyword') . "</li>
							<li>{COMMANDPARAM} " . _('will be replaced by command parameter passed to server from SMS') . "</li>
							<li>{COMMANDRAW} " . _('will be replaced by SMS raw message') . "</li>
						</ul>
					</td>
				</tr>
				<tr>
					<td>" . _('SMS command exec') . "</td><td><input type=text maxlength=200 name=edit_command_exec value=\"$edit_command_exec\"></td>
				</tr>
				<tr>
					<td>" . _('Make return as reply') . "</td><td><input type=checkbox name=edit_command_return_as_reply $edit_command_return_as_reply></td>
				</tr>
				" . $select_reply_smsc . "
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_sms_command&op=sms_command_list');
		_p($content);
		break;

	case "sms_command_edit_yes":
		$edit_command_return_as_reply = $_POST['edit_command_return_as_reply'] == 'on' ? '1' : '0';
		$edit_command_keyword = strtoupper(core_sanitize_alphanumeric($_POST['edit_command_keyword']));
		$edit_command_exec = $_POST['edit_command_exec'];
		if ($command_id && $edit_command_keyword && $edit_command_exec) {
			$edit_command_exec = str_replace("..", "", $edit_command_exec);
			$edit_command_exec = str_replace("/", "", $edit_command_exec);
			$edit_command_exec = str_replace("\\", "", $edit_command_exec);
			$edit_command_exec = str_replace("|", "", $edit_command_exec);
			$edit_command_exec = str_replace("&", "", $edit_command_exec);
			$edit_command_exec = str_replace("`", "", $edit_command_exec);
			$edit_command_exec = str_ireplace("nohup", "", $edit_command_exec);
			$db_argv = [time(), $edit_command_exec, $edit_command_return_as_reply];
			if (auth_isadmin()) {
				$edit_smsc = $_POST['edit_smsc'];
				$query_smsc = ",smsc=?";
				$db_argv[] = $edit_smsc;
			}
			$db_argv[] = $command_id;
			$db_query = "UPDATE " . _DB_PREF_ . "_featureCommand SET c_timestamp=?,command_exec=?,command_return_as_reply=?" . $query_smsc . " WHERE command_id=?";
			if (dba_affected_rows($db_query, $db_argv)) {
				$c_dir = $sms_command_bin . "/" . $user_config['uid'];
				@shell_exec("mkdir -p \"" . $c_dir . "\"");
				$_SESSION['dialog']['info'][] = _('SMS command has been saved') . " (" . _('keyword') . ": $edit_command_keyword)";
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to save SMS command') . " (" . _('keyword') . ": $edit_command_keyword)";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_command&op=sms_command_edit&command_id=' . $command_id));
		exit();

	case "sms_command_del":
		$db_query = "SELECT command_keyword FROM " . _DB_PREF_ . "_featureCommand WHERE command_id=?";
		$db_result = dba_query($db_query, [$command_id]);
		$db_row = dba_fetch_array($db_result);
		$keyword_name = $db_row['command_keyword'];
		if ($keyword_name) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureCommand WHERE command_keyword=?";
			if (dba_affected_rows($db_query, [$keyword_name])) {
				$_SESSION['dialog']['info'][] = _('SMS command has been deleted') . " (" . _('keyword') . ": $keyword_name)";
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to delete SMS command') . " (" . _('keyword') . ": $keyword_name)";
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_command&op=sms_command_list'));
		exit();

	case "sms_command_add":
		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('add_smsc') . "</td></tr>";
		}

		$content = _dialog() . "
			<h2>" . _('Manage command') . "</h2>
			<h3>" . _('Add SMS command') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_command&op=sms_command_add_yes method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _('SMS command keyword') . "</td><td><input type=text size=10 maxlength=10 name=add_command_keyword value=\"\"></td>
				</tr>
				<tr>
					<td>" . _('SMS command exec path') . "</td><td>" . $sms_command_bin . '/' . $user_config['uid'] . "</td>
				</tr>
				<tr>
					<td colspan=2>" . _('Pass these parameter to command exec field') . "</td>
				</tr>
				<tr>
					<td colspan=2>
						<ul>
							<li>{SMSDATETIME} " . _('will be replaced by SMS incoming date/time') . "</li>
							<li>{SMSSENDER} " . _('will be replaced by sender number') . "</li>
							<li>{COMMANDKEYWORD} " . _('will be replaced by command keyword') . "</li>
							<li>{COMMANDPARAM} " . _('will be replaced by command parameter passed to server from SMS') . "</li>
							<li>{COMMANDRAW} " . _('will be replaced by SMS raw message') . "</li>
						</ul>
					</td>
				</tr>
				<tr>
					<td>" . _('SMS command exec') . "</td><td><input type=text maxlength=200 name=add_command_exec value=\"\"></td>
				</tr>
				<tr>
					<td>" . _('Make return as reply') . "</td><td><input type=checkbox name=add_command_return_as_reply></td>
				</tr>
				" . $select_reply_smsc . "
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_sms_command&op=sms_command_list');
		_p($content);
		break;

	case "sms_command_add_yes":
		$add_command_return_as_reply = $_POST['add_command_return_as_reply'] == 'on' ? '1' : '0';
		$add_command_keyword = strtoupper(core_sanitize_alphanumeric($_POST['add_command_keyword']));
		$add_command_exec = $_POST['add_command_exec'];

		if (auth_isadmin()) {
			$add_smsc = $_POST['add_smsc'];
		}

		if ($add_command_keyword && $add_command_exec) {
			$add_command_exec = str_replace("..", "", $add_command_exec);
			$add_command_exec = str_replace("/", "", $add_command_exec);
			$add_command_exec = str_replace("\\", "", $add_command_exec);
			$add_command_exec = str_replace("|", "", $add_command_exec);
			$add_command_exec = str_replace("&", "", $add_command_exec);
			$add_command_exec = str_replace("`", "", $add_command_exec);
			$add_command_exec = str_ireplace("nohup", "", $add_command_exec);
			if (keyword_isavail($add_command_keyword)) {
				$db_query = "INSERT INTO " . _DB_PREF_ . "_featureCommand (uid,command_keyword,command_exec,command_return_as_reply,smsc) VALUES (?,?,?,?,?)";
				if ($new_uid = dba_insert_id($db_query, [$user_config['uid'], $add_command_keyword, $add_command_exec, $add_command_return_as_reply, $add_smsc])) {
					$c_dir = $sms_command_bin . "/" . (int) $user_config['uid'];
					@shell_exec("mkdir -p \"" . $c_dir . "\"");
					$_SESSION['dialog']['info'][] = _('SMS command has been added') . " (" . _('keyword') . " $add_command_keyword)";
				} else {
					$_SESSION['dialog']['info'][] = _('Fail to add SMS command') . " (" . _('keyword') . ": $add_command_keyword)";
				}
			} else {
				$_SESSION['dialog']['info'][] = _('SMS command already exists, reserved or use by other feature') . " (" . _('keyword') . ": $add_command_keyword)";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_command&op=sms_command_add'));
		exit();
}
