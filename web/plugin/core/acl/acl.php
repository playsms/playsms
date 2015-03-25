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
	case "acl_list":
		$content = _dialog() . "
			<h2>" . _('Manage ACL') . "</h2>
			<p>" . _button('index.php?app=main&inc=core_acl&op=add', _('Add ACL')) . "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=10%>" . _('ID') . "</th>
				<th width=40%>" . _('Name') . "</th>
				<th width=40%>" . _('Subuser ACL') . "</th>
				<th width=10%>" . _('Action') . "</th>
			</tr></thead>
			<tbody>";
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblACL WHERE flag_deleted='0' ORDER BY name";
		$db_result = dba_query($db_query);
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$action = "<a href=\"" . _u('index.php?app=main&inc=core_acl&route=view&op=user_list&id=' . $db_row['id']) . "\">" . $icon_config['view'] . "</a>&nbsp;";
			$action .= "<a href=\"" . _u('index.php?app=main&inc=core_acl&op=edit&id=' . $db_row['id']) . "\">" . $icon_config['edit'] . "</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete ACL ?') . " (" . _('ACL ID') . ": " . $db_row['id'] . ")','" . _u('index.php?app=main&inc=core_acl&op=del&id=' . $db_row['id']) . "')\">" . $icon_config['delete'] . "</a>";
			$i++;
			$content .= "
					<tr>
						<td>" . $db_row['id'] . "</td>
						<td>" . trim(strtoupper($db_row['name'])) . "</td>
						<td>" . trim(strtoupper($db_row['acl_subuser'])) . "</td>
						<td>" . $action . "</td>
					</tr>";
		}
		$content .= "
			</tbody>
			</table>
			</div>
			" . _button('index.php?app=main&inc=core_acl&op=add', _('Add ACL'));
		_p($content);
		break;
	
	case "add":
		$content = _dialog() . "
			<h2>" . _('Manage ACL') . "</h2>
			<h3>" . _('Add ACL') . "</h3>
			<form action=index.php?app=main&inc=core_acl&op=add_yes method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td class=label-sizer>" . _mandatory(_('Name')) . "</td><td><input type=text maxlength=100 name=name></td>
			</tr>
			<tr>
				<td>" . _('Subuser ACL') . "</td><td><input type=text name=acl_subuser> " . _hint(_('Comma separated for multiple entries')) . "</td>
			</tr>
			<tr>
				<td>" . _('URLs') . "</td><td><textarea rows=5 name=url></textarea><br />" . _hint(_('Comma separated for multiple entries')) . "</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=core_acl&op=acl_list');
		_p($content);
		break;
	
	case "add_yes":
		$name = trim(strtoupper($_POST['name']));
		$acl_subusers = explode(',', trim(strtoupper($_POST['acl_subuser'])));
		foreach ($acl_subusers as $item) {
			$acl_subuser .= ' ' . trim(strtoupper($item)) . ',';
		}
		$acl_subuser = trim(substr($acl_subuser, 0, -1));
		$url = trim($_POST['url']);
		if ($name) {
			$db_query = "
				INSERT INTO " . _DB_PREF_ . "_tblACL (c_timestamp,name,acl_subuser,url,flag_deleted)
				VALUES ('" . mktime() . "','" . $name . "','" . $acl_subuser . "','" . $url . "','0')";
			if ($new_id = @dba_insert_id($db_query)) {
				$_SESSION['dialog']['info'][] = _('New ACL been added');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to add new ACL');
			}
		} else {
			$_SESSION['dialog']['info'][] = _('Mandatory fields must not be empty');
		}
		header("Location: " . _u('index.php?app=main&inc=core_acl&op=add'));
		exit();
		break;
	
	case "edit":
		$id = (int) $_REQUEST['id'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblACL WHERE flag_deleted='0' AND id='" . $id . "'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$content = _dialog() . "
			<h2>" . _('Manage ACL') . "</h2>
			<h3>" . _('Edit ACL') . "</h3>
			<form action=index.php?app=main&inc=core_acl&op=edit_yes method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden name=id value='" . $id . "'>
			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td class=label-sizer>" . _('ACL ID') . "</td><td>" . $id . "</td>
			</tr>
			<tr>
				<td>" . _('Name') . "</td><td>" . strtoupper($db_row['name']) . "</td>
			</tr>
			<tr>
				<td>" . _('Subuser ACL') . "</td><td><input type=text name=acl_subuser value='" . strtoupper($db_row['acl_subuser']) . "'> " . _hint(_('Comma separated for multiple entries')) . "</td>
			</tr>
			<tr>
				<td>" . _('URLs') . "</td><td><textarea rows=5 name=url>" . $db_row['url'] . "</textarea><br />" . _hint(_('Comma separated for multiple entries')) . "</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=core_acl&op=acl_list');
		_p($content);
		break;
	
	case "edit_yes":
		$id = (int) $_POST['id'];
		$name = trim(strtoupper($_POST['name']));
		$acl_subusers = explode(',', trim(strtoupper($_POST['acl_subuser'])));
		foreach ($acl_subusers as $item) {
			$acl_subuser .= ' ' . trim(strtoupper($item)) . ',';
		}
		$acl_subuser = trim(substr($acl_subuser, 0, -1));
		$url = trim($_POST['url']);
		if ($id) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_tblACL SET c_timestamp='" . mktime() . "',acl_subuser='" . $acl_subuser . "',url='" . $url . "'
				WHERE id='" . $id . "'";
			if ($new_id = @dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('ACL been edited');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to edit ACL');
			}
		} else {
			$_SESSION['dialog']['info'][] = _('Mandatory fields must not be empty');
		}
		header("Location: " . _u('index.php?app=main&inc=core_acl&op=edit&id=' . $id));
		exit();
		break;
	
	case "del":
		$id = $_REQUEST['id'];
		if ($id && dba_isexists(_DB_PREF_ . "_tblACL", array(
			'id' => $id 
		), 'AND')) {
			$db_query = "UPDATE " . _DB_PREF_ . "_tblACL SET c_timestamp='" . mktime() . "', flag_deleted='1' WHERE id='$id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('ACL has been deleted');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to delete ACL');
			}
		} else {
			auth_block();
		}
		header("Location: " . _u('index.php?app=main&inc=core_acl&op=acl_list'));
		exit();
		break;
}
