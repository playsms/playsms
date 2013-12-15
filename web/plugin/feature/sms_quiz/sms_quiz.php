<?php
defined('_SECURE_') or die('Forbidden');
if (!valid()){auth_block();};

if ($quiz_id = $_REQUEST['quiz_id']) {
	if (! ($quiz_id = dba_valid(_DB_PREF_.'_featureQuiz', 'quiz_id', $quiz_id))) {
		auth_block();
	}
}

switch ($op) {
	case "sms_quiz_list" :
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
				<h2>"._('Manage quiz')."</h2>
				"._button('index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_add', _('Add SMS quiz'));
		$content .= "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>";
		if (auth_isadmin()) {
			$content .= "
				<th width=20%>"._('Keyword')."</th>
				<th width=40%>"._('Question')."</th>
				<th width=20%>"._('User')."</th>
				<th width=10%>"._('Status')."</th>
				<th width=10%>"._('Action')."</th>";
		} else {
			$content .= "
				<th width=20%>"._('Keyword')."</th>
				<th width=60%>"._('Question')."</th>
				<th width=10%>"._('Status')."</th>
				<th width=10%>"._('Action')."</th>";
		}
		$content .= "
			</thead></tr>
			<tbody>";
		$i = 0;
		if (! auth_isadmin()) {
			$query_user_only = "WHERE uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz ".$query_user_only." ORDER BY quiz_id";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = uid2username($db_row['uid'])) {
				$quiz_status = "<a href=\"index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_status&quiz_id=".$db_row['quiz_id']."&ps=1\"><span class=status_disabled /></a>";
				if ($db_row['quiz_enable']) {
					$quiz_status = "<a href=\"index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_status&quiz_id=".$db_row['quiz_id']."&ps=0\"><span class=status_enabled /></a>";
				}
				$action = "<a href=index.php?app=menu&inc=feature_sms_quiz&op=sms_answer_view&quiz_id=".$db_row['quiz_id'].">".$core_config['icon']['view']."</a>&nbsp;";
				$action .= "<a href=index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_edit&quiz_id=".$db_row['quiz_id'].">".$core_config['icon']['edit']."</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS quiz with all its choices and answers ?')." ("._('keyword').": ".$db_row['quiz_keyword'].")','index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_del&quiz_id=".$db_row['quiz_id']."')\">".$core_config['icon']['delete']."</a>";
				if (auth_isadmin()) {
					$option_owner = "<td>$owner</td>";
				}
				$i++;
				$content .= "
					<tr>
						<td>".$db_row['quiz_keyword']."</td>
						<td>".$db_row['quiz_question']."</td>
						".$option_owner."
						<td>$quiz_status</td>
						<td>$action</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			</div>
			"._button('index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_add', _('Add SMS quiz'));
		echo $content;
		break;
	case "sms_quiz_add" :
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage quiz')."</h2>
			<h3>"._('Add SMS quiz')."</h3>
			<form action=index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_add_yes method=post>
			"._CSRF_FORM_."
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>"._('SMS quiz keyword')."</td><td><input type=text size=10 maxlength=10 name=add_quiz_keyword value=\"$add_quiz_keyword\"></td>
			</tr>
			<tr>
				<td>"._('SMS quiz question')."</td><td><input type=text size=30 maxlength=100 name=add_quiz_question value=\"$add_quiz_question\"></td>
			</tr>
			<tr>
				<td>"._('SMS quiz answer')."</td><td><input type=text size=30 maxlength=100 name=add_quiz_answer value=\"$add_quiz_answer\"></td>
			</tr>
			<tr>
				<td>"._('Reply message on correct')."</td><td><input type=text size=30 maxlength=100 name=add_quiz_msg_correct value=\"$add_quiz_msg_correct\"></td>
			</tr>
			<tr>
				<td>"._('Reply message on incorrect')."</td><td><input type=text size=30 maxlength=100 name=add_quiz_msg_incorrect value=\"$add_quiz_msg_incorrect\"></td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			"._b('index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_list');
		echo $content;
		break;
	case "sms_quiz_add_yes" :
		$add_quiz_keyword = strtoupper($_POST['add_quiz_keyword']);
		$add_quiz_question = $_POST['add_quiz_question'];
		$add_quiz_answer = strtoupper($_POST['add_quiz_answer']);
		$add_quiz_msg_correct = $_POST['add_quiz_msg_correct'];
		$add_quiz_msg_incorrect = $_POST['add_quiz_msg_incorrect'];
		if ($add_quiz_keyword && $add_quiz_answer) {
			if (checkavailablekeyword($add_quiz_keyword)) {
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_featureQuiz (uid,quiz_keyword,quiz_question,quiz_answer,quiz_msg_correct,quiz_msg_incorrect)
					VALUES ('$uid','$add_quiz_keyword','$add_quiz_question','$add_quiz_answer','$add_quiz_msg_correct','$add_quiz_msg_incorrect')";
				if ($new_uid = @ dba_insert_id($db_query)) {
					$_SESSION['error_string'] = _('SMS quiz has been added')." ("._('keyword').": $add_quiz_keyword)";
				} else {
					$_SESSION['error_string'] = _('Fail to add SMS quiz')." ("._('keyword').": $add_quiz_keyword)";
				}
			} else {
				$_SESSION['error_string'] = _('SMS quiz already exists, reserved or use by other feature')." ("._('keyword').": $add_quiz_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all field');
		}
		header("Location: index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_add");
		exit();
		break;
	case "sms_quiz_edit" :
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id='$quiz_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_quiz_keyword = $db_row['quiz_keyword'];
		$edit_quiz_question = $db_row['quiz_question'];
		$edit_quiz_answer = $db_row['quiz_answer'];
		$edit_quiz_msg_correct = $db_row['quiz_msg_correct'];
		$edit_quiz_msg_incorrect = $db_row['quiz_msg_incorrect'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage quiz')."</h2>
			<h3>"._('Edit SMS quiz')."</h3>
			<form action=index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_edit_yes method=post>
			"._CSRF_FORM_."
			<input type=hidden name=quiz_id value=\"$quiz_id\">
			<input type=hidden name=edit_quiz_keyword value=\"$edit_quiz_keyword\">
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>"._('SMS quiz keyword')."</td><td>$edit_quiz_keyword</td>
			</tr>
			<tr>
				<td>"._('SMS quiz question')."</td><td><input type=text size=30 maxlength=100 name=edit_quiz_question value=\"$edit_quiz_question\"></td>
			</tr>
			<tr>
				<td>"._('SMS quiz answer')."</td><td><input type=text size=30 maxlength=100 name=edit_quiz_answer value=\"$edit_quiz_answer\"></td>
			</tr>
			<tr>
				<td>"._('Reply message on correct')."</td><td><input type=text size=30 maxlength=100 name=edit_quiz_msg_correct value=\"$edit_quiz_msg_correct\"></td>
			</tr>
			<tr>
				<td>"._('Reply message on incorrect')."</td><td><input type=text size=30 maxlength=100 name=edit_quiz_msg_incorrect value=\"$edit_quiz_msg_incorrect\"></td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			"._b('index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_list');
		echo $content;
		break;
	case "sms_quiz_edit_yes" :
		$edit_quiz_keyword = $_POST['edit_quiz_keyword'];
		$edit_quiz_question = $_POST['edit_quiz_question'];
		$edit_quiz_answer = $_POST['edit_quiz_answer'];
		$edit_quiz_msg_correct = $_POST['edit_quiz_msg_correct'];
		$edit_quiz_msg_incorrect = $_POST['edit_quiz_msg_incorrect'];
		if ($quiz_id && $edit_quiz_answer && $edit_quiz_question && $edit_quiz_keyword && $edit_quiz_msg_correct && $edit_quiz_msg_incorrect) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_featureQuiz
				SET c_timestamp='" . mktime() . "',quiz_keyword='$edit_quiz_keyword',quiz_question='$edit_quiz_question',quiz_answer='$edit_quiz_answer',quiz_msg_correct='$edit_quiz_msg_correct',quiz_msg_incorrect='$edit_quiz_msg_incorrect'
				WHERE quiz_id='$quiz_id'";
			if (@ dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS quiz has been saved')." ("._('keyword').": $edit_quiz_keyword)";
			} else {
				$_SESSION['error_string'] = _('Fail to edit SMS quiz')." ("._('keyword').": $edit_quiz_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all field');
		}
		header("Location: index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_edit&quiz_id=$quiz_id");
		exit();
		break;
	case "sms_answer_view" :
		$quiz_answer_query = "SELECT quiz_keyword,quiz_answer FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id='$quiz_id'";
		$db_answer_result = dba_query($quiz_answer_query);
		$db_answer_row = dba_fetch_array($db_answer_result);
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage quiz')."</h2>
			<h3>"._('Received answer list for keyword')." ".$db_answer_row['quiz_keyword']."</h3>";
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz_log WHERE quiz_id='$quiz_id' ORDER BY in_datetime DESC";
		$db_result = dba_query($db_query);
		$content .= "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=30%>"._('Datetime')."</th>
				<th width=20%>"._('Sender')."</th>
				<th width=30%>"._('Answer')."</th>
				<th width=10%>"._('Status')."</th>
				<th width=10%>"._('Action')."</th>
			</tr></thead>
			<tbody>";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($db_row['quiz_answer'] == $db_answer_row['quiz_answer']) {
				$iscorrect = "<font color=green>"._('correct')."</font>";
			} else {
				$iscorrect = "<font color=red>"._('incorrect')."</font>";
			}
			$action = "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete this answer ?')."','index.php?app=menu&inc=feature_sms_quiz&op=sms_answer_del&quiz_id=$quiz_id&answer_id=".$db_row['answer_id']."')\">".$core_config['icon']['delete']."</a>";
			$i++;
			$content .= "
				<tr>
					<td>".$db_row['in_datetime']."</td>
					<td>".$db_row['quiz_sender']."</td>
					<td>".$db_row['quiz_answer']."</td>
					<td>$iscorrect</td>
					<td>$action</td>
				</tr>";
		}
		$content .= "</tbody>
			</table>
			</div>
			"._b('index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_list');
		echo $content;
		break;
	case "sms_answer_del" :
		$answer_id = $_REQUEST['answer_id'];
		$db_query = "SELECT answer_id FROM " . _DB_PREF_ . "_featureQuiz_log WHERE answer_id='$answer_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($answer_id = $db_row['answer_id']) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureQuiz_log WHERE answer_id='$answer_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS quiz answer messages has been deleted');
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_quiz&op=sms_answer_view&quiz_id=$quiz_id");
		exit();
		break;
	case "sms_quiz_status" :
		$ps = $_REQUEST['ps'];
		$db_query = "UPDATE " . _DB_PREF_ . "_featureQuiz SET c_timestamp='" . mktime() . "',quiz_enable='$ps' WHERE quiz_id='$quiz_id'";
		$db_result = @ dba_affected_rows($db_query);
		if ($db_result > 0) {
			$_SESSION['error_string'] = _('SMS quiz status has been changed');
		}
		header("Location: index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_list");
		exit();
		break;
	case "sms_quiz_del" :
		$db_query = "SELECT quiz_keyword FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id='$quiz_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($quiz_keyword = $db_row['quiz_keyword']) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id='$quiz_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS quiz with all its messages has been deleted')." ("._('keyword').": $quiz_keyword)";
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_list");
		exit();
		break;
}

?>