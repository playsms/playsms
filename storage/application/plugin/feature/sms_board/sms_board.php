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

if ($board_id = $_REQUEST['board_id']) {
	if (!($board_id = dba_valid(_DB_PREF_ . '_featureBoard', 'board_id', $board_id))) {
		auth_block();
	}
}

switch (_OP_) {
	case "sms_board_list":
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Manage board') . "</h2>
			<p>" . _button('index.php?app=main&inc=feature_sms_board&op=sms_board_add', _('Add SMS board')) . "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>";
		if (auth_isadmin()) {
			$content .= "
				<th width=20%>" . _('Keyword') . "</th>
				<th width=52%>" . _('Forward') . "</th>
				<th width=20%>" . _('User') . "</th>
				<th width=8%>" . _('Action') . "</th>";
		} else {
			$content .= "
				<th width=20%>" . _('Keyword') . "</th>
				<th width=72%>" . _('Forward') . "</th>
				<th width=8% nowrap>" . _('Action') . "</th>";
		}
		$content .= "
			</tr></thead>
			<tbody>";
		if (auth_isadmin()) {
			$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureBoard ORDER BY board_keyword";
			$db_result = dba_query($db_query);
		} else {
			$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureBoard WHERE uid=? ORDER BY board_keyword";
			$db_result = dba_query($db_query, [$user_config['uid']]);
		}
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = user_uid2username($db_row['uid'])) {
				$action = "<a href=\"" . _u('index.php?app=main&inc=feature_sms_board&route=view&op=list&board_id=' . $db_row['board_id']) . "\">" . $icon_config['view'] . "</a>";
				$action .= "<a href=\"" . _u('index.php?app=main&inc=feature_sms_board&op=sms_board_edit&board_id=' . $db_row['board_id']) . "\">" . $icon_config['edit'] . "</a>";
				$action .= _confirm(
					_('Are you sure you want to delete SMS board with all its messages ?') . " (" . _('keyword') . ": " . $db_row['board_keyword'] . ")",
					_u('index.php?app=main&inc=feature_sms_board&op=sms_board_del&board_id=' . $db_row['board_id']),
					'delete'
				);
				if (auth_isadmin()) {
					$option_owner = "<td>$owner</td>";
				}
				$i++;
				$content .= "
					<tr>
						<td>" . $db_row['board_keyword'] . "</td>
						<td>" . $db_row['board_forward_email'] . "</td>
						" . $option_owner . "
						<td nowrap>" . $action . "</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			</div>
			" . _button('index.php?app=main&inc=feature_sms_board&op=sms_board_add', _('Add SMS board'));
		_p($content);
		break;

	case "sms_board_edit":
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureBoard WHERE board_id=?";
		$db_result = dba_query($db_query, [$board_id]);
		$db_row = dba_fetch_array($db_result);
		$edit_board_keyword = $db_row['board_keyword'];
		$edit_board_reply = $db_row['board_reply'];
		$edit_email = $db_row['board_forward_email'];
		$edit_css = $db_row['board_css'];
		$edit_template = $db_row['board_pref_template'];
		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('smsc', $db_row['smsc']) . "</td></tr>";
		}

		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Manage board') . "</h2>
			<h3 class=page-header-subtitle>" . _('Edit SMS board') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_board&op=sms_board_edit_yes method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden name=board_id value=$board_id>
			<input type=hidden name=edit_board_keyword value=$edit_board_keyword>
			<table class=playsms-table>
			<tbody>
			<tr>
				<td class=playsms-label-sizer>" . _('SMS board keyword') . "</td><td>" . $edit_board_keyword . "</td>
			</tr>
			<tr>
				<td>" . _('SMS board reply message') . "</td><td><input type=text maxlength=100 name=edit_board_reply value=\"" . $edit_board_reply . "\"></td>
			</tr>
			<tr>
				<td>" . _('Forward to email') . "</td><td><input type=text name=edit_email value=\"" . $edit_email . "\"></td>
			</tr>
			<tr>
				<td>" . _('CSS URL') . "</td><td><input type=text name=edit_css value=\"" . $edit_css . "\"></td>
			</tr>
			<tr>
				<td>" . _('Row template') . "</td><td><textarea style='height: 10em' name=edit_template>" . $edit_template . "</textarea></td>
			</tr>
			" . $select_reply_smsc . "
			</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_sms_board&op=sms_board_list');
		_p($content);
		break;

	case "sms_board_edit_yes":
		$edit_board_keyword = $_POST['edit_board_keyword'];
		$edit_board_reply = trim($_POST['edit_board_reply']);
		$edit_email = $_POST['edit_email'];
		$edit_css = $_POST['edit_css'];
		$edit_template = $_POST['edit_template'];
		if ($board_id) {
			if (!$edit_template) {
				$edit_template = "<div class=sms_board_row>\n";
				$edit_template .= "\t<div class=sender>{SENDER}</div>\n";
				$edit_template .= "\t<div class=datetime>{DATETIME}</div>\n";
				$edit_template .= "\t<div class=message>{MESSAGE}</div>\n";
				$edit_template .= "</div>\n";
			}
			$db_query = "
				UPDATE " . _DB_PREF_ . "_featureBoard
				SET c_timestamp='" . time() . "',board_reply=?,board_forward_email=?,board_css=?,board_pref_template=?
				WHERE board_id=?";
			$db_argv = [
				$edit_board_reply,
				$edit_email,
				$edit_css,
				$edit_template,
				$board_id
			];
			if (dba_affected_rows($db_query, $db_argv)) {
				$_SESSION['dialog']['info'][] = _('SMS board has been saved') . " (" . _('keyword') . ": $edit_board_keyword)";

				if (auth_isadmin()) {
					$db_query = "UPDATE " . _DB_PREF_ . "_featureBoard SET smsc=? WHERE board_id=?";
					if (dba_affected_rows($db_query, [$_REQUEST['smsc'], $board_id]) === 0) {
						_log("Fail to update smsc board_id:" . $board_id . " smsc:" . $_REQUEST['smsc'], 3, "sms board edit");
					}
				}
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to edit SMS board') . " (" . _('keyword') . ": $edit_board_keyword)";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_board&op=sms_board_edit&board_id=' . $board_id));
		exit();

	case "sms_board_del":
		$db_query = "SELECT board_keyword FROM " . _DB_PREF_ . "_featureBoard WHERE board_id=?";
		$db_result = dba_query($db_query, [$board_id]);
		$db_row = dba_fetch_array($db_result);
		$board_keyword = $db_row['board_keyword'];
		if ($board_keyword) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureBoard WHERE board_keyword=?";
			if (@dba_affected_rows($db_query, [$board_keyword])) {
				$_SESSION['dialog']['info'][] = _('SMS board with all its messages has been deleted') . " (" . _('keyword') . ": $board_keyword)";
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_board&op=sms_board_list'));
		exit();

	case "sms_board_add":
		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('smsc') . "</td></tr>";
		}

		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Manage board') . "</h2>
			<h3 class=page-header-subtitle>" . _('Add SMS board') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_board&op=sms_board_add_yes method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
			<tbody>
			<tr>
				<td class=playsms-label-sizer>" . _('SMS board keyword') . "</td><td><input type=text maxlength=30 name=add_board_keyword value=\"$add_board_keyword\"></td>
			</tr>
			<tr>
				<td>" . _('SMS board reply message') . "</td><td><input type=text maxlength=100 name=add_board_reply></td>
			</tr>
			<tr>
				<td>" . _('Forward to email') . "</td><td><input type=text name=add_email value=\"$add_email\"></td>
			</tr>
			<tr>
				<td>" . _('CSS URL') . "</td><td><input type=text name=add_css value=\"$add_css\"></td>
			</tr>
			" . $select_reply_smsc . "
			</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_sms_board&op=sms_board_list');
		_p($content);
		break;

	case "sms_board_add_yes":
		$add_board_keyword = strtoupper($_POST['add_board_keyword']);
		$add_board_reply = trim($_POST['add_board_reply']);
		$add_email = $_POST['add_email'];
		$add_css = $_POST['add_css'];
		$add_template = $_POST['add_template'];

		if (auth_isadmin()) {
			$smsc = $_POST['smsc'];
		}

		if ($add_board_keyword) {
			if (keyword_isavail($add_board_keyword)) {
				if (!$add_template) {
					$add_template = "<div class=sms_board_row>\n";
					$add_template .= "\t<div class=sender>{SENDER}</div>\n";
					$add_template .= "\t<div class=datetime>{DATETIME}</div>\n";
					$add_template .= "\t<div class=message>{MESSAGE}</div>\n";
					$add_template .= "</div>\n";
				}
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_featureBoard (uid,board_keyword,board_reply,board_forward_email,board_css,board_pref_template,smsc)
					VALUES (?,?,?,?,?,?,?)";
				$db_argv = [
					$user_config['uid'],
					$add_board_keyword,
					$add_board_reply,
					$add_email,
					$add_css,
					$add_template,
					$smsc
				];
				if ($new_uid = @dba_insert_id($db_query, $db_argv)) {
					$_SESSION['dialog']['info'][] = _('SMS board has been added') . " (" . _('keyword') . ": $add_board_keyword)";
				} else {
					$_SESSION['dialog']['info'][] = _('Fail to add SMS board') . " (" . _('keyword') . ": $add_board_keyword)";
				}
			} else {
				$_SESSION['dialog']['info'][] = _('SMS keyword already exists, reserved or use by other feature') . " (" . _('keyword') . ": $add_board_keyword)";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_board&op=sms_board_add'));
		exit();
}