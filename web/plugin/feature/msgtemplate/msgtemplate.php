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

$gpid = (int) $_REQUEST['gpid'];
$pid = (int) $_REQUEST['pid'];
$tid = (int) $_REQUEST['tid'];

if ($tid) {
	if (!(dba_valid(_DB_PREF_ . '_featureMsgtemplate', 'tid', $tid))) {
		auth_block();
	}
}

switch (_OP_) {
	case "list":
		$fm_name = "fm_smstemp";
		$content = _dialog() . "
			<h2>" . _('Message template') . "</h2>
			<form id=$fm_name name=$fm_name action='index.php?app=main&inc=feature_msgtemplate&op=actions' method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=actions_box>
			<div class=pull-left><a href='" . _u('index.php?app=main&inc=feature_msgtemplate&op=add') . "'>" . $icon_config['add'] . "</a></div>
			<div class=pull-right>
				<a href='#' onClick=\"return SubmitConfirm('" . _('Are you sure you want to delete these items ?') . "', '" . $fm_name . "');\">" . $icon_config['delete'] . "</a>
			</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=30%>" . _('Name') . "</th>
				<th width=65%>" . _('Content') . "</th>
				<th width=5%><input type=checkbox onclick=CheckUncheckAll(document." . $fm_name . ")></th>
			</tr></thead>
			<tbody>";
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureMsgtemplate WHERE uid=? ORDER BY t_title";
		$db_result = dba_query($db_query, [$user_config['uid']]);
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$db_row = _display($db_row);
			$tid = $db_row['tid'];
			$temp_title = $db_row['t_title'];
			$temp_text = nl2br($db_row['t_text']);
			$i++;
			$content .= "
				<tr>
					<td><a href='" . _u('index.php?app=main&inc=feature_msgtemplate&op=edit&tid=' . $tid) . "'>" . $temp_title . "</a></td>
					<td>$temp_text</td>
					<td><input type=checkbox name=chkid" . $i . "></td>
					<input type=hidden name=chkid_value" . $i . " value='" . $db_row['tid'] . "'>
				</tr>";
		}
		$content .= "
			</tbody>
			</table>
			</div>
			<input type='hidden' name='item_count' value='$i'>
			</form>
			<div class=text-info>
				<p>" . _('Notes') . "</p>
				<ul>
					<li>#NAME# " . _('will be replaced with the name listed in phonebook') . "</li>
					<li>#NUM# " . _('will be replaced with the phone number listed in phonebook') . "</li>
				</ul>
			</div>
		";
		_p($content);
		break;

	case "add":
		$content = _dialog() . "
			<h2>" . _('Message template') . "</h2>
			<h3>" . _('Add message template') . "</h3>
			<form action='index.php?app=main&inc=feature_msgtemplate&op=actions&go=add' method=POST>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _('Message template name') . "</td><td><input type=text maxlength=100 name=t_title></td>
			</tr>
			<tr>
				<td>" . _('Message template content') . "</td><td><textarea rows=5 name=t_text></textarea></td>
			</tr>	
			</table>	
			<p><input type='submit' class='button' value='" . _('Save') . "'></p>
			</form>
			<div class=text-info>
				<p>" . _('Notes') . "</p>
				<ul>
					<li>#NAME# " . _('will be replaced with the name listed in phonebook') . "</li>
					<li>#NUM# " . _('will be replaced with the phone number listed in phonebook') . "</li>
				</ul>
			</div>
			" . _back('index.php?app=main&inc=feature_msgtemplate&op=list');
		_p($content);
		break;

	case "edit":
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureMsgtemplate WHERE uid=? AND tid=?";
		$db_result = dba_query($db_query, [$user_config['uid'], $tid]);
		$db_row = _display(dba_fetch_array($db_result));
		$content = _dialog() . "
			<h2>" . _('Message template') . "</h2>
			<h3>" . _('Edit message template') . "</h3>
			<form action='index.php?app=main&inc=feature_msgtemplate&op=actions&go=edit' method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=item_count value='" . $i . "'>
			<input type=hidden name=tid value='" . $tid . "'>
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _('Message template name') . "</td><td><input type=text maxlength=100 name=t_title value='" . $db_row['t_title'] . "'></td>
			</tr>
			<tr>
				<td>" . _('Message template content') . "</td><td><textarea rows=5 name=t_text>" . $db_row['t_text'] . "</textarea></td>
			</tr>
			</table>
			<input type='hidden' name='item_count' value='$i'>
			<p><input type='submit' class='button' value='" . _('Save') . "'></p>
			</form>
			<div class=text-info>
				<p>" . _('Notes') . "</p>
				<ul>
					<li>#NAME# " . _('will be replaced with the name listed in phonebook') . "</li>
					<li>#NUM# " . _('will be replaced with the phone number listed in phonebook') . "</li>
				</ul>
			</div>
			" . _back('index.php?app=main&inc=feature_msgtemplate&op=list');
		_p($content);
		break;

	case "actions":
		$go = $_REQUEST['go'];
		switch ($go) {
			case "add":
				$t_title = $_POST['t_title'];
				$t_text = $_POST['t_text'];
				if ($t_title && $t_text) {
					$db_query = "INSERT INTO " . _DB_PREF_ . "_featureMsgtemplate (uid,t_title,t_text) VALUES (?,?,?)";
					$db_result = dba_insert_id($db_query, [$user_config['uid'], $t_title, $t_text]);
					if ($db_result > 0) {
						$_SESSION['dialog']['info'][] = _('Message template has been saved');
					} else {
						$_SESSION['dialog']['info'][] = _('Fail to add message template');
					}
				} else {
					$_SESSION['dialog']['info'][] = _('You must fill all fields');
				}
				header("Location: " . _u('index.php?app=main&inc=feature_msgtemplate&op=add'));
				exit();

			case "edit":
				$t_title = $_POST['t_title'];
				$t_text = $_POST['t_text'];
				if ($t_title && $t_text) {
					$db_query = "UPDATE " . _DB_PREF_ . "_featureMsgtemplate SET c_timestamp=?, t_title=?, t_text=? WHERE uid=? AND tid=?";
					$db_result = dba_affected_rows($db_query, [time(), $t_title, $t_text, $user_config['uid'], $tid]);
					if ($db_result > 0) {
						$_SESSION['dialog']['info'][] = _('Message template has been edited');
					} else {
						$_SESSION['dialog']['info'][] = _('Fail to edit message template');
					}
				} else {
					$_SESSION['dialog']['info'][] = _('You must fill all fields');
				}
				header("Location: " . _u('index.php?app=main&inc=feature_msgtemplate&op=list'));
				exit();

			case "delete":
				$item_count = $_POST['item_count'];
				for ($i = 1; $i <= $item_count; $i++) {
					$chkid[$i] = $_POST['chkid' . $i];
					$chkid_value[$i] = $_POST['chkid_value' . $i];
				}
				for ($i = 1; $i <= $item_count; $i++) {
					if (($chkid[$i] == 'on') && $chkid_value[$i]) {
						$db_query = "DELETE FROM " . _DB_PREF_ . "_featureMsgtemplate WHERE uid=? AND tid=?";
						$db_result = dba_affected_rows($db_query, [$user_config['uid'], $chkid_value[$i]]);
					}
				}
				$_SESSION['dialog']['info'][] = _('Selected message template has been deleted');
				header("Location: " . _u('index.php?app=main&inc=feature_msgtemplate&op=list'));
				exit();
		}
}
