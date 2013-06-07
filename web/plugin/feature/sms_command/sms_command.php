<?php
defined('_SECURE_') or die('Forbidden');
if (!valid()) { forcenoaccess(); };

if ($command_id = $_REQUEST['command_id']) {
	if (! ($command_id = dba_valid(_DB_PREF_.'_featureCommand', 'command_id', $command_id))) {
		forcenoaccess();
	}
}

$sms_command_bin = $plugin_config['feature']['sms_command']['bin'];

switch ($op) {
	case "sms_command_list":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>" . _('Manage command') . "</h2>
			<p>"._button('index.php?app=menu&inc=feature_sms_command&op=sms_command_add', _('Add SMS command'));
		if (! isadmin()) {
			$query_user_only = "WHERE uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCommand ".$query_user_only." ORDER BY command_keyword";
		$db_result = dba_query($db_query);
		$content .= "<table cellpadding=1 cellspacing=2 border=0 width=100% class=sortable>";
		if (isadmin()) {
			$content .= "
				<thead><tr>
					<th width=4>*</th>
					<th width=20%>" . _('Keyword') . "</th>
					<th width=50%>" . _('Exec') . "</th>
					<th width=20%>" . _('User') . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>";
		} else {
			$content .= "
				<thead><tr>
					<th width=4>*</th>
					<th width=20%>" . _('Keyword') . "</th>
					<th width=70%>" . _('Exec') . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>";
		}
		$content .= "<tbody>";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = uid2username($db_row['uid'])) {
				$i++;
				$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$action = "<a href=index.php?app=menu&inc=feature_sms_command&op=sms_command_edit&command_id=" . $db_row['command_id'] . ">$icon_edit</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete SMS command ?') . " (" . _('keyword') . ": " . $db_row['command_keyword'] . ")','index.php?app=menu&inc=feature_sms_command&op=sms_command_del&command_id=" . $db_row['command_id'] . "')\">$icon_delete</a>";
				$command_exec = $sms_command_bin.'/'.$db_row['uid'].'/'.$db_row['command_exec'];
				if (isadmin()) {
					$show_owner = "<td class=$td_class>".$owner."</td>";
				}
				$content .= "
					<tr>
						<td class=$td_class>&nbsp;$i.</td>
						<td class=$td_class>" . $db_row['command_keyword'] . "</td>
						<td class=$td_class>" . stripslashes($command_exec) . "</td>
						".$show_owner."
						<td class=$td_class align=center>$action</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			<p>"._button('index.php?app=menu&inc=feature_sms_command&op=sms_command_add', _('Add SMS command'));
		echo $content;
		break;
	case "sms_command_edit":
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCommand WHERE command_id='$command_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_command_uid = $db_row['uid'];
		$edit_command_keyword = $db_row['command_keyword'];
		$edit_command_exec = stripslashes($db_row['command_exec']);
		$edit_command_exec = str_replace($sms_command_bin . "/", '', $edit_command_exec);
		$edit_command_return_as_reply = ( $db_row['command_return_as_reply'] == '1' ? 'checked' : '' );
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>" . _('Manage command') . "</h2>
			<h3>" . _('Edit SMS command') . "</h3>
			<p>
			<form action=index.php?app=menu&inc=feature_sms_command&op=sms_command_edit_yes method=post>
			<input type=hidden name=command_id value=$command_id>
			<input type=hidden name=edit_command_keyword value=$edit_command_keyword>
			<p>" . _('SMS command keyword') . ": <b>$edit_command_keyword</b>
			<p>" . _('Pass these parameter to command exec field') . ":
			<p><b>{SMSDATETIME}</b> " . _('will be replaced by SMS incoming date/time') . "
			<p><b>{SMSSENDER}</b> " . _('will be replaced by sender number') . "
			<p><b>{COMMANDKEYWORD}</b> " . _('will be replaced by command keyword') . "
			<p><b>{COMMANDPARAM}</b> " . _('will be replaced by command parameter passed to server from SMS') . "
			<p><b>{COMMANDRAW}</b> " . _('will be replaced by SMS raw message') . "
			<p>" . _('SMS command exec path') . ": <b>" . $sms_command_bin.'/'.$edit_command_uid . "</b>
			<p>" . _('SMS command exec') . ": <input type=text size=40 name=edit_command_exec value=\"$edit_command_exec\">
			<p>" . _('Make return as reply') . " : <input type=checkbox name=edit_command_return_as_reply $edit_command_return_as_reply></p>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			<p>"._b('index.php?app=menu&inc=feature_sms_command&op=sms_command_list');
		echo $content;
		break;
	case "sms_command_edit_yes":
		$edit_command_return_as_reply = ( $_POST['edit_command_return_as_reply'] == 'on' ? '1' : '0' );
		$edit_command_keyword = $_POST['edit_command_keyword'];
		$edit_command_exec = $_POST['edit_command_exec'];
		if ($command_id && $edit_command_keyword && $edit_command_exec) {
			$edit_command_exec = str_replace("../", "", $edit_command_exec);
			$edit_command_exec = str_replace("..\\", "", $edit_command_exec);
			$edit_command_exec = str_replace("/", "", $edit_command_exec);
			$edit_command_exec = str_replace("\\", "", $edit_command_exec);
			$edit_command_exec = str_replace("|", "", $edit_command_exec);
			$db_query = "UPDATE " . _DB_PREF_ . "_featureCommand SET c_timestamp='" . mktime() . "',command_exec='$edit_command_exec',command_return_as_reply='$edit_command_return_as_reply' WHERE command_keyword='$edit_command_keyword'";
			if (@dba_affected_rows($db_query)) {
				$c_dir = $sms_command_bin."/".$uid;
				@shell_exec("mkdir -p \"".$c_dir."\"");
				$_SESSION['error_string'] = _('SMS command has been saved') . " (" . _('keyword') . ": $edit_command_keyword)";
			} else {
				$_SESSION['error_string'] = _('Fail to save SMS command') . " (" . _('keyword') . ": $edit_command_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_edit&command_id=$command_id");
		exit();
		break;
	case "sms_command_del":
		$db_query = "SELECT command_keyword FROM " . _DB_PREF_ . "_featureCommand WHERE command_id='$command_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$keyword_name = $db_row['command_keyword'];
		if ($keyword_name) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureCommand WHERE command_keyword='$keyword_name'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS command has been deleted') . " (" . _('keyword') . ": $keyword_name)";
			} else {
				$_SESSION['error_string'] = _('Fail to delete SMS command') . " (" . _('keyword') . ": $keyword_name)";
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_list");
		exit();
		break;
	case "sms_command_add":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>" . _('Manage command') . "</h2>
			<h3>" . _('Add SMS command') . "</h3>
			<p>
			<form action=index.php?app=menu&inc=feature_sms_command&op=sms_command_add_yes method=post>
			<p>" . _('SMS command keyword') . ": <input type=text size=10 maxlength=10 name=add_command_keyword value=\"$add_command_keyword\">
			<p>" . _('Pass these parameter to command exec field') . ":
			<p><b>{SMSDATETIME}</b> " . _('will be replaced by SMS incoming date/time') . "
			<p><b>{SMSSENDER}</b> " . _('will be replaced by sender number') . "
			<p><b>{COMMANDKEYWORD}</b> " . _('will be replaced by command keyword') . "
			<p><b>{COMMANDPARAM}</b> " . _('will be replaced by command parameter passed to server from SMS') . "
			<p><b>{COMMANDRAW}</b> " . _('will be replaced by SMS raw message') . "
			<p>" . _('SMS command exec path') . ": <b>" . $sms_command_bin.'/'.$core_config['user']['uid'] . "</b>
			<p>" . _('SMS command exec') . ": <input type=text size=40 maxlength=200 name=add_command_exec value=\"$add_command_exec\">
			<p>" . _('Make return as reply') . " : <input type=checkbox name=add_command_return_as_reply></p>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			"._b('index.php?app=menu&inc=feature_sms_command&op=sms_command_list');
		echo $content;
		break;
	case "sms_command_add_yes":
		$add_command_return_as_reply = ( $_POST['add_command_return_as_reply'] == 'on' ? '1' : '0' );
		$add_command_keyword = strtoupper($_POST['add_command_keyword']);
		$add_command_exec = $_POST['add_command_exec'];
		if ($add_command_keyword && $add_command_exec) {
			$add_command_exec = $add_command_exec;
			$add_command_exec = str_replace("/", "", $add_command_exec);
			$add_command_exec = str_replace("|", "", $add_command_exec);
			$add_command_exec = str_replace("\\", "", $add_command_exec);
			if (checkavailablekeyword($add_command_keyword)) {
				$db_query = "INSERT INTO " . _DB_PREF_ . "_featureCommand (uid,command_keyword,command_exec,command_return_as_reply) VALUES ('$uid','$add_command_keyword','$add_command_exec','$add_command_return_as_reply')";
				if ($new_uid = @dba_insert_id($db_query)) {
					$c_dir = $sms_command_bin."/".$uid;
					@shell_exec("mkdir -p \"".$c_dir."\"");
					$_SESSION['error_string'] = _('SMS command has been added') . " (" . _('keyword') . " $add_command_keyword)";
				} else {
					$_SESSION['error_string'] = _('Fail to add SMS command') . " (" . _('keyword') . ": $add_command_keyword)";
				}
			} else {
				$_SESSION['error_string'] = _('SMS command already exists, reserved or use by other feature') . " (" . _('keyword') . ": $add_command_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_add");
		exit();
		break;
}

?>