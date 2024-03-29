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

if ($quiz_id = (int) $_REQUEST['quiz_id']) {
	$db_table = _DB_PREF_ . '_featureQuiz';
	$conditions = array(
		'quiz_id' => $quiz_id
	);
	if (!auth_isadmin()) {
		$conditions['uid'] = $user_config['uid'];
	}
	$list = dba_search($db_table, 'quiz_id', $conditions);
	if (!($list[0]['quiz_id'] == $quiz_id)) {
		auth_block();
	}
}

switch (_OP_) {
	case "sms_quiz_list":
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Manage quiz') . "</h2>
			" . _button('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_add', _('Add SMS quiz')) . "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>";
		if (auth_isadmin()) {
			$content .= "
				<th width=20%>" . _('Keyword') . "</th>
				<th width=42%>" . _('Question') . "</th>
				<th width=20%>" . _('User') . "</th>
				<th width=10%>" . _('Status') . "</th>
				<th width=8% nowrap>" . _('Action') . "</th>";
			$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz ORDER BY quiz_id";
			$db_result = dba_query($db_query);
		} else {
			$content .= "
				<th width=20%>" . _('Keyword') . "</th>
				<th width=62%>" . _('Question') . "</th>
				<th width=10%>" . _('Status') . "</th>
				<th width=8% nowrap>" . _('Action') . "</th>";
			$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz WHERE uid=? ORDER BY quiz_id";
			$db_result = dba_query($db_query, [$user_config['uid']]);
		}
		$content .= "
			</thead></tr>
			<tbody>";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = user_uid2username($db_row['uid'])) {
				$quiz_status = "<a href=\"" . _u('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_status&quiz_id=' . $db_row['quiz_id'] . '&ps=1') . "\"><span class=status_disabled /></a>";
				if ($db_row['quiz_enable']) {
					$quiz_status = "<a href=\"" . _u('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_status&quiz_id=' . $db_row['quiz_id'] . '&ps=0') . "\"><span class=status_enabled /></a>";
				}
				$action = "<a href=\"" . _u('index.php?app=main&inc=feature_sms_quiz&op=sms_answer_view&quiz_id=' . $db_row['quiz_id']) . "\">" . $icon_config['view'] . "</a>";
				$action .= "<a href=\"" . _u('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_edit&quiz_id=' . $db_row['quiz_id']) . "\">" . $icon_config['edit'] . "</a>";
				$action .= _confirm(
					_('Are you sure you want to delete SMS quiz with all its choices and answers ?') . " (" . _('keyword') . ": " . $db_row['quiz_keyword'] . ")",
					_u('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_del&quiz_id=' . $db_row['quiz_id']),
					'delete'
				);

				if (auth_isadmin()) {
					$option_owner = "<td>$owner</td>";
				}
				$i++;
				$content .= "
					<tr>
						<td>" . $db_row['quiz_keyword'] . "</td>
						<td>" . $db_row['quiz_question'] . "</td>
						" . $option_owner . "
						<td>$quiz_status</td>
						<td nowrap>" . $action . "</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			</div>
			" . _button('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_add', _('Add SMS quiz'));
		_p($content);
		break;

	case "sms_quiz_add":
		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('smsc') . "</td></tr>";
		}
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Manage quiz') . "</h2>
			<h3 class=page-header-subtitle>" . _('Add SMS quiz') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_add_yes method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tr>
				<td class=playsms-label-sizer>" . _('SMS quiz keyword') . "</td><td><input type=text size=10 maxlength=10 name=add_quiz_keyword value=\"$add_quiz_keyword\"></td>
			</tr>
			<tr>
				<td>" . _('SMS quiz question') . "</td><td><input type=text maxlength=100 name=add_quiz_question value=\"$add_quiz_question\"></td>
			</tr>
			<tr>
				<td>" . _('SMS quiz answer') . "</td><td><input type=text maxlength=100 name=add_quiz_answer value=\"$add_quiz_answer\"></td>
			</tr>
			<tr>
				<td>" . _('Reply message on correct') . "</td><td><input type=text maxlength=100 name=add_quiz_msg_correct value=\"$add_quiz_msg_correct\"></td>
			</tr>
			<tr>
				<td>" . _('Reply message on incorrect') . "</td><td><input type=text maxlength=100 name=add_quiz_msg_incorrect value=\"$add_quiz_msg_incorrect\"></td>
			</tr>
			" . $select_reply_smsc . "
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_list');
		_p($content);
		break;

	case "sms_quiz_add_yes":
		$add_quiz_keyword = strtoupper($_POST['add_quiz_keyword']);
		$add_quiz_question = $_POST['add_quiz_question'];
		$add_quiz_answer = strtoupper($_POST['add_quiz_answer']);
		$add_quiz_msg_correct = $_POST['add_quiz_msg_correct'];
		$add_quiz_msg_incorrect = $_POST['add_quiz_msg_incorrect'];
		if ($add_quiz_keyword && $add_quiz_answer) {
			if (keyword_isavail($add_quiz_keyword)) {
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_featureQuiz (uid,quiz_keyword,quiz_question,quiz_answer,quiz_msg_correct,quiz_msg_incorrect,smsc)
					VALUES (?,?,?,?,?,?,?)";
				$db_argv = [
					$user_config['uid'],
					$add_quiz_keyword,
					$add_quiz_question,
					$add_quiz_answer,
					$add_quiz_msg_correct,
					$add_quiz_msg_incorrect,
					auth_isadmin() ? $_REQUEST['smsc'] : ''
				];
				if ($new_uid = dba_insert_id($db_query, $db_argv)) {
					$_SESSION['dialog']['info'][] = _('SMS quiz has been added') . " (" . _('keyword') . ": $add_quiz_keyword)";
				} else {
					$_SESSION['dialog']['info'][] = _('Fail to add SMS quiz') . " (" . _('keyword') . ": $add_quiz_keyword)";
				}
			} else {
				$_SESSION['dialog']['info'][] = _('SMS quiz already exists, reserved or use by other feature') . " (" . _('keyword') . ": $add_quiz_keyword)";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all field');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_add'));
		exit();

	case "sms_quiz_edit":
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id=?";
		$db_result = dba_query($db_query, [$quiz_id]);
		$db_row = dba_fetch_array($db_result);
		$edit_quiz_keyword = $db_row['quiz_keyword'];
		$edit_quiz_question = $db_row['quiz_question'];
		$edit_quiz_answer = $db_row['quiz_answer'];
		$edit_quiz_msg_correct = $db_row['quiz_msg_correct'];
		$edit_quiz_msg_incorrect = $db_row['quiz_msg_incorrect'];
		if (auth_isadmin()) {
			$select_reply_smsc = "<tr><td>" . _('SMSC') . "</td><td>" . gateway_select_smsc('smsc', $db_row['smsc']) . "</td></tr>";
		}
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Manage quiz') . "</h2>
			<h3 class=page-header-subtitle>" . _('Edit SMS quiz') . "</h3>
			<form action=index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_edit_yes method=post>
			" . _CSRF_FORM_ . "
			<input type=hidden name=quiz_id value=\"$quiz_id\">
			<input type=hidden name=edit_quiz_keyword value=\"$edit_quiz_keyword\">
			<table class=playsms-table>
			<tr>
				<td class=playsms-label-sizer>" . _('SMS quiz keyword') . "</td><td>$edit_quiz_keyword</td>
			</tr>
			<tr>
				<td>" . _('SMS quiz question') . "</td><td><input type=text maxlength=100 name=edit_quiz_question value=\"$edit_quiz_question\"></td>
			</tr>
			<tr>
				<td>" . _('SMS quiz answer') . "</td><td><input type=text maxlength=100 name=edit_quiz_answer value=\"$edit_quiz_answer\"></td>
			</tr>
			<tr>
				<td>" . _('Reply message on correct') . "</td><td><input type=text maxlength=100 name=edit_quiz_msg_correct value=\"$edit_quiz_msg_correct\"></td>
			</tr>
			<tr>
				<td>" . _('Reply message on incorrect') . "</td><td><input type=text maxlength=100 name=edit_quiz_msg_incorrect value=\"$edit_quiz_msg_incorrect\"></td>
			</tr>
			" . $select_reply_smsc . "
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>
			" . _back('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_list');
		_p($content);
		break;

	case "sms_quiz_edit_yes":
		$edit_quiz_keyword = $_POST['edit_quiz_keyword'];
		$edit_quiz_question = $_POST['edit_quiz_question'];
		$edit_quiz_answer = $_POST['edit_quiz_answer'];
		$edit_quiz_msg_correct = $_POST['edit_quiz_msg_correct'];
		$edit_quiz_msg_incorrect = $_POST['edit_quiz_msg_incorrect'];
		if ($quiz_id && $edit_quiz_answer && $edit_quiz_question && $edit_quiz_keyword && $edit_quiz_msg_correct && $edit_quiz_msg_incorrect) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_featureQuiz
				SET c_timestamp='" . time() . "',quiz_keyword=?,quiz_question=?,quiz_answer=?,quiz_msg_correct=?,quiz_msg_incorrect=? 
				WHERE quiz_id=?";
			$db_argv = [
				$edit_quiz_keyword,
				$edit_quiz_question,
				$edit_quiz_answer,
				$edit_quiz_msg_correct,
				$edit_quiz_msg_incorrect,
				$quiz_id
			];
			if (dba_affected_rows($db_query, $db_argv)) {
				$_SESSION['dialog']['info'][] = _('SMS quiz has been saved') . " (" . _('keyword') . ": $edit_quiz_keyword)";

				if (auth_isadmin()) {
					$db_query = "UPDATE " . _DB_PREF_ . "_featureQuiz SET smsc=? WHERE quiz_id=?";
					if (dba_affected_rows($db_query, [$_REQUEST['smsc'], $quiz_id]) === 0) {
						_log("Fail to update smsc quiz_id:" . $quiz_id . " smsc:" . $_REQUEST['smsc'], 3, "sms quiz edit");
					}
				}
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to edit SMS quiz') . " (" . _('keyword') . ": $edit_quiz_keyword)";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all field');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_edit&quiz_id=' . $quiz_id));
		exit();

	case "sms_answer_view":
		$quiz_answer_query = "SELECT quiz_keyword,quiz_answer FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id=?";
		$db_answer_result = dba_query($quiz_answer_query, [$quiz_id]);
		$db_answer_row = dba_fetch_array($db_answer_result);
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('Manage quiz') . "</h2>
			<p class=lead>" . _('Received answer list for keyword') . " " . $db_answer_row['quiz_keyword'] . "</p>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=20%>" . _('Datetime') . "</th>
				<th width=32%>" . _('Sender') . "</th>
				<th width=35%>" . _('Answer') . "</th>
				<th width=10%>" . _('Status') . "</th>
				<th width=3% nowrap>" . $icon_config['action'] . "</th>
			</tr></thead>
			<tbody>";
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz_log WHERE quiz_id=? ORDER BY in_datetime DESC";
		$db_result = dba_query($db_query, [$quiz_id]);
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($db_row['quiz_answer'] == $db_answer_row['quiz_answer']) {
				$iscorrect = "<font color=green>" . ucwords(_('correct')) . "</font>";
			} else {
				$iscorrect = "<font color=red>" . ucwords(_('incorrect')) . "</font>";
			}
			$action = _confirm(
				_('Are you sure you want to delete this answer ?'),
				_u('index.php?app=main&inc=feature_sms_quiz&op=sms_answer_del&quiz_id=' . $quiz_id . '&answer_id=' . $db_row['answer_id']),
				'delete'
			);
			$i++;
			$content .= "
				<tr>
					<td>" . $db_row['in_datetime'] . "</td>
					<td>" . $db_row['quiz_sender'] . "</td>
					<td>" . $db_row['quiz_answer'] . "</td>
					<td>" . $iscorrect . "</td>
					<td nowrap>" . $action . "</td>
				</tr>";
		}
		$content .= "</tbody>
			</table>
			</div>
			" . _back('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_list');
		_p($content);
		break;

	case "sms_answer_del":
		$answer_id = $_REQUEST['answer_id'];
		$db_query = "SELECT answer_id FROM " . _DB_PREF_ . "_featureQuiz_log WHERE answer_id=?";
		if (dba_num_rows($db_query, [$answer_id])) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureQuiz_log WHERE answer_id=?";
			if (dba_affected_rows($db_query, [$answer_id])) {
				$_SESSION['dialog']['info'][] = _('SMS quiz answer messages have been deleted');
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_quiz&op=sms_answer_view&quiz_id=' . $quiz_id));
		exit();

	case "sms_quiz_status":
		$ps = $_REQUEST['ps'];
		$db_query = "UPDATE " . _DB_PREF_ . "_featureQuiz SET c_timestamp='" . time() . "',quiz_enable=? WHERE quiz_id=?";
		if (dba_affected_rows($db_query, [$ps, $quiz_id])) {
			$_SESSION['dialog']['info'][] = _('SMS quiz status has been changed');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_list'));
		exit();

	case "sms_quiz_del":
		$db_query = "SELECT quiz_keyword FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id=?";
		$db_result = dba_query($db_query, [$quiz_id]);
		$db_row = dba_fetch_array($db_result);
		if ($quiz_keyword = $db_row['quiz_keyword']) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id=?";
			if (dba_affected_rows($db_query, [$quiz_id])) {
				$_SESSION['dialog']['info'][] = _('SMS quiz with all its messages has been deleted') . " (" . _('keyword') . ": $quiz_keyword)";
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_list'));
		exit();

}