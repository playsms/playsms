<?php
defined('_SECURE_') or die('Forbidden');
if (!auth_isvalid()) { auth_block(); };

if ($custom_id = $_REQUEST['custom_id']) {
	if (! ($custom_id = dba_valid(_DB_PREF_.'_featureCustom', 'custom_id', $custom_id))) {
		auth_block();
	}
}

switch (_OP_) {
	case "sms_custom_list":
		$content .= _dialog() . "
			<h2>" . _('Manage custom') . "</h2>
			"._button('index.php?app=main&inc=feature_sms_custom&op=sms_custom_add', _('Add SMS custom'));
		if (! auth_isadmin()) {
			$query_user_only = "WHERE uid='".$user_config['uid']."'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCustom ".$query_user_only." ORDER BY custom_keyword";
		$db_result = dba_query($db_query);
		$content .= "
			<div class=table-responsive>
			<table class=playsms-table-list>";
		if (auth_isadmin()) {
			$content .= "
				<thead><tr>
					<th width=20%>" . _('Keyword') . "</th>
					<th width=50%>" . _('URL') . "</th>
					<th width=20%>" . _('User') . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>";
		} else {
			$content .= "
				<thead><tr>
					<th width=20%>" . _('Keyword') . "</th>
					<th width=70%>" . _('URL') . "</th>
					<th width=10%>" . _('Action') . "</th>
				</tr></thead>";
		}
		$content .= "<tbody>";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = user_uid2username($db_row['uid'])) {
				$action = "<a href=\""._u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_edit&custom_id='.$db_row['custom_id'])."\">".$icon_config['edit']."</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete SMS custom ?') . " (" . _('keyword') . ": " . $db_row['custom_keyword'] . ")','"._u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_del&custom_id='.$db_row['custom_id'])."')\">".$icon_config['delete']."</a>";
				$custom_url = $db_row['custom_url'];
				if (auth_isadmin()) {
					$show_owner = "<td>".$owner."</td>";
				}
				$i++;
				$content .= "
					<tr>
						<td>" . $db_row['custom_keyword'] . "</td>
						<td>" . $custom_url . "</td>
						".$show_owner."
						<td>$action</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			</div>
			"._button('index.php?app=main&inc=feature_sms_custom&op=sms_custom_add', _('Add SMS custom'));
		_p($content);
		break;
	case "sms_custom_edit":
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCustom WHERE custom_id='$custom_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_custom_uid = $db_row['uid'];
		$edit_custom_keyword = $db_row['custom_keyword'];
		$edit_custom_url = $db_row['custom_url'];
		$edit_custom_return_as_reply = ( $db_row['custom_return_as_reply'] == '1' ? 'checked' : '' );
		$content .= _dialog() . "
			<h2>" . _('Manage custom') . "</h2>
			<h3>" . _('Edit SMS custom') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_custom&op=sms_custom_edit_yes method=post>
			"._CSRF_FORM_."
			<input type=hidden name=custom_id value=$custom_id>
			<input type=hidden name=edit_custom_keyword value=$edit_custom_keyword>
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>"._('SMS custom keyword') . "</td><td>".$edit_custom_keyword."</td>
				</tr>
				<tr>
					<td colspan=2>"._('Pass these parameters to custom URL field')."</td>
				</tr>
				<tr>
					<td colspan=2>
						<ul>
							<li>{SMSDATETIME} " . _('will be replaced by SMS incoming date/time') . "</li>
							<li>{SMSSENDER} " . _('will be replaced by sender number') . "</li>
							<li>{CUSTOMKEYWORD} " . _('will be replaced by custom keyword') . "</li>
							<li>{CUSTOMPARAM} " . _('will be replaced by custom parameter passed to server from SMS') . "</li>
							<li>{CUSTOMRAW} " . _('will be replaced by SMS raw message') . "</li>
						</ul>
					</td>
				</tr>
				<tr>
					<td>"._('SMS custom URL')."</td><td><input type=text maxlength=200 name=edit_custom_url value=\"$edit_custom_url\"></td>
				</tr>
				<tr>
					<td>"._('Make return as reply')."</td><td><input type=checkbox name=edit_custom_return_as_reply $edit_custom_return_as_reply></td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			"._back('index.php?app=main&inc=feature_sms_custom&op=sms_custom_list');
		_p($content);
		break;
	case "sms_custom_edit_yes":
		$edit_custom_return_as_reply = ( $_POST['edit_custom_return_as_reply'] == 'on' ? '1' : '0' );
		$edit_custom_keyword = $_POST['edit_custom_keyword'];
		$edit_custom_url = $_POST['edit_custom_url'];
		if ($custom_id && $edit_custom_keyword && $edit_custom_url) {
			$db_query = "UPDATE " . _DB_PREF_ . "_featureCustom SET c_timestamp='" . mktime() . "',custom_url='$edit_custom_url',custom_return_as_reply='$edit_custom_return_as_reply' WHERE custom_keyword='$edit_custom_keyword'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('SMS custom has been saved') . " (" . _('keyword') . ": $edit_custom_keyword)";
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to save SMS custom') . " (" . _('keyword') . ": $edit_custom_keyword)";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: "._u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_edit&custom_id='.$custom_id));
		exit();
		break;
	case "sms_custom_del":
		$db_query = "SELECT custom_keyword FROM " . _DB_PREF_ . "_featureCustom WHERE custom_id='$custom_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$keyword_name = $db_row['custom_keyword'];
		if ($keyword_name) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureCustom WHERE custom_keyword='$keyword_name'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('SMS custom has been deleted') . " (" . _('keyword') . ": $keyword_name)";
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to delete SMS custom') . " (" . _('keyword') . ": $keyword_name)";
			}
		}
		header("Location: "._u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_list'));
		exit();
		break;
	case "sms_custom_add":
		$content .= _dialog() . "
			<h2>" . _('Manage custom') . "</h2>
			<h3>" . _('Add SMS custom') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_custom&op=sms_custom_add_yes method=post>
			"._CSRF_FORM_."
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>"._('SMS custom keyword') . "</td><td><input type=text size=10 maxlength=10 name=add_custom_keyword value=\"$add_custom_keyword\"></td>
				</tr>
				<tr>
					<td colspan=2>"._('Pass these parameters to custom URL field')."</td>
				</tr>
				<tr>
					<td colspan=2>
						<ul>
							<li>{SMSDATETIME} " . _('will be replaced by SMS incoming date/time') . "</li>
							<li>{SMSSENDER} " . _('will be replaced by sender number') . "</li>
							<li>{CUSTOMKEYWORD} " . _('will be replaced by custom keyword') . "</li>
							<li>{CUSTOMPARAM} " . _('will be replaced by custom parameter passed to server from SMS') . "</li>
							<li>{CUSTOMRAW} " . _('will be replaced by SMS raw message') . "</li>
						</ul>
					</td>
				</tr>
				<tr>
					<td>"._('SMS custom URL')."</td><td><input type=text maxlength=200 name=add_custom_url value=\"$add_custom_url\"></td>
				</tr>
				<tr>
					<td>"._('Make return as reply')."</td><td><input type=checkbox name=add_custom_return_as_reply></td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			"._back('index.php?app=main&inc=feature_sms_custom&op=sms_custom_list');
		_p($content);
		break;
	case "sms_custom_add_yes":
		$add_custom_return_as_reply = ( $_POST['add_custom_return_as_reply'] == 'on' ? '1' : '0' );
		$add_custom_keyword = strtoupper($_POST['add_custom_keyword']);
		$add_custom_url = $_POST['add_custom_url'];
		if ($add_custom_keyword && $add_custom_url) {
			if (checkavailablekeyword($add_custom_keyword)) {
				$db_query = "INSERT INTO " . _DB_PREF_ . "_featureCustom (uid,custom_keyword,custom_url,custom_return_as_reply) VALUES ('".$user_config['uid']."','$add_custom_keyword','$add_custom_url','$add_custom_return_as_reply')";
				if ($new_uid = @dba_insert_id($db_query)) {
					$_SESSION['dialog']['info'][] = _('SMS custom has been added') . " (" . _('keyword') . " $add_custom_keyword)";
				} else {
					$_SESSION['dialog']['info'][] = _('Fail to add SMS custom') . " (" . _('keyword') . ": $add_custom_keyword)";
				}
			} else {
				$_SESSION['dialog']['info'][] = _('SMS custom already exists, reserved or use by other feature') . " (" . _('keyword') . ": $add_custom_keyword)";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: "._u('index.php?app=main&inc=feature_sms_custom&op=sms_custom_add'));
		exit();
		break;
}

?>
