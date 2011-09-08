<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};
if (!valid()) {
	forcenoaccess();
};

switch ($op) {
	case "sms_answer_view" :
		$quiz_id = $_REQUEST['quiz_id'];
		$quiz_answer_query = "SELECT quiz_keyword,quiz_answer FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id = '$quiz_id'";
		$db_answer_result = dba_query($quiz_answer_query);
		$db_answer_row = dba_fetch_array($db_answer_result);
		if ($err) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
				<h2>"._('Received answer list for keyword')." ".$db_answer_row['quiz_keyword']."</h2>			
				";

		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz_log WHERE quiz_id = '$quiz_id' ORDER BY in_datetime DESC";
		$db_result = dba_query($db_query);
		$content .= "
			<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr>
				<td class=box_title width=5>*</td>
				<td class=box_title width=30%>"._('Datetime')."</td>
				<td class=box_title width=30%>"._('Sender')."</td>
				<td class=box_title width=30%>"._('Message')."</td>
				<td class=box_title width=10%>"._('Status')."</td>
				<td class=box_title>"._('Action')."</td>
			</tr>
			";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";

			if ($db_row['quiz_answer'] == $db_answer_row['quiz_answer']) {
				$iscorrect = "<font color=green>"._('Correct')."</font>";
			} else {
				$iscorrect = "<font color=red>"._('Incorrect')."</font>";
			}
			$action = "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete this answer ?')."','index.php?app=menu&inc=feature_sms_quiz&op=sms_answer_del&quiz_id=$quiz_id&answer_id=".$db_row['answer_id']."')\">$icon_delete</a>";

			$content .= "
				<tr>
					<td class=$td_class>&nbsp;$i.</td>
					<td class=$td_class>".$db_row['in_datetime']."</td>
					<td class=$td_class>".$db_row['quiz_sender']."</td>
					<td class=$td_class>".$db_row['quiz_answer']."</td>
					<td class=$td_class>$iscorrect</td>
					<td class=$td_class>$action</td>	
				</tr>";
		}
		$content .= "</table>";
		echo $content;
		break;

	case "sms_answer_del" :
		$quiz_id = $_REQUEST['quiz_id'];
		$answer_id = $_REQUEST['answer_id'];
		$db_query = "SELECT answer_id FROM " . _DB_PREF_ . "_featureQuiz_log WHERE answer_id='$answer_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$answer_id = $db_row['answer_id'];
		if ($answer_id) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureQuiz_log WHERE answer_id='$answer_id'";
			if (@ dba_affected_rows($db_query)) {
				$error_string = _('SMS quiz answer messages has been deleted');
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_quiz&op=sms_answer_view&quiz_id=$quiz_id&err=" . urlencode($error_string));
		break;

	case "sms_quiz_list" :
		if ($err) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
				<h2>"._('Manage quiz')."</h2>
				<p>
				<input type=button value=\""._('Add SMS quiz')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_add')\" class=\"button\" />
				<p>
				";
		if (!isadmin()) {
			$query_user_only = "WHERE uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz $query_user_only ORDER BY quiz_id";
		$db_result = dba_query($db_query);
		$content .= "
			<table cellpadding=1 cellspacing=2 border=0 width=100%>
			<tr>
			   	<td class=box_title>*</td>
			    	<td class=box_title width=20%>"._('Keyword')."</td>
				<td class=box_title width=40%>"._('Question')."</td>
			    	<td class=box_title width=20%>"._('Answer')."</td>
			    	<td class=box_title width=10%>"._('User')."</td>	
			    	<td class=box_title width=10%>"._('Status')."</td>
			    	<td class=box_title>"._('Action')."</td>
			</tr>
			";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$owner = uid2username($db_row['uid']);
			$quiz_status = "<font color=red>"._('Disabled')."</font>";
			if ($db_row['quiz_enable']) {
				$quiz_status = "<font color=green>"._('Enabled')."</font>";
			}
			$action = "<a href=index.php?app=menu&inc=feature_sms_quiz&op=sms_answer_view&quiz_id=".$db_row['quiz_id'].">$icon_view</a>&nbsp;";
			$action .= "<a href=index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_edit&quiz_id=".$db_row['quiz_id'].">$icon_edit</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS quiz with all its choices and answers ?')." ("._('keyword').": `".$db_row['quiz_keyword']."`)','index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_del&quiz_id=".$db_row['quiz_id']."')\">$icon_delete</a>";
			$content .= "
					<tr>
						<td class=$td_class>&nbsp;$i.</td>
						<td class=$td_class>".$db_row['quiz_keyword']."</td>
						<td class=$td_class>".$db_row['quiz_question']."</td>
						<td class=$td_class>".$db_row['quiz_answer']."</td>
						<td class=$td_class>$owner</td>
						<td class=$td_class>$quiz_status</td>		
						<td class=$td_class align=center>$action</td>
					</tr>";
		}
		$content .= "</table>";
		echo $content;
		echo "
				<p>
				<input type=button value=\""._('Add SMS quiz')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_add')\" class=\"button\" />
				";
		break;

	case "sms_quiz_edit" :
		$quiz_id = $_REQUEST['quiz_id'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id='$quiz_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_quiz_keyword = $db_row['quiz_keyword'];
		$edit_quiz_question = $db_row['quiz_question'];
		$edit_quiz_answer = $db_row['quiz_answer'];
		$edit_quiz_msg_correct = $db_row['quiz_msg_correct'];
		$edit_quiz_msg_incorrect = $db_row['quiz_msg_incorrect'];
		if ($err) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
				<h2>"._('Edit SMS quiz')."</h2>
				<p>
				<form action=index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_edit_yes method=post>
				<input type=hidden name=edit_quiz_id value=\"$quiz_id\">
				<input type=hidden name=edit_quiz_keyword value=\"$edit_quiz_keyword\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>
				<tr>
				<td width=150>"._('SMS quiz keyword')."</td><td width=5>:</td><td><b>$edit_quiz_keyword</b></td>
				</tr>
				<tr>
				<td>"._('SMS quiz question')."</td><td>:</td><td><input type=text size=40 maxlength=200 name=edit_quiz_question value=\"$edit_quiz_question\"></td>
			   	</tr>
				<tr>
				<td>"._('SMS quiz answer')."</td><td>:</td><td><input type=text size=10 maxlength=200 name=edit_quiz_answer value=\"$edit_quiz_answer\"></td>
			   	</tr>
				<tr>
				<td>"._('Message when correct')."</td><td>:</td><td><input type=text size=40 maxlength=200 name=edit_quiz_msg_correct value=\"$edit_quiz_msg_correct\"></td>
			   	</tr>
				<tr>
				<td>"._('Message when incorrect')."</td><td>:</td><td><input type=text size=40 maxlength=200 name=edit_quiz_msg_incorrect value=\"$edit_quiz_msg_incorrect\"></td>
			   	</tr>		   	    
			</table>	    
				<p><input type=submit class=button value=\""._('Save')."\">
				</form>
				<br>
			";
		echo $content;

		$db_query = "SELECT quiz_enable FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id='$quiz_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$quiz_status = "<b><font color=red>"._('Disabled')."</font></b>";
		if ($db_row['quiz_enable']) {
			$quiz_status = "<b><font color=green>"._('Enabled')."</font></b>";
		}
		$content = "
				<h2>"._('Enable or disable this quiz')."</h2>
				<p>
				<p>"._('Current status').": $quiz_status
				<p>"._('What do you want to do ?')."
				<p>- <a href=\"index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_status&quiz_id=$quiz_id&ps=1\">"._('I want to enable this quiz')."</a>
				<p>- <a href=\"index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_status&quiz_id=$quiz_id&ps=0\">"._('I want to disable this quiz')."</a>
				<br>
			";
		echo $content;
		break;

	case "sms_quiz_edit_yes" :
		$edit_quiz_id = $_POST['edit_quiz_id'];
		$edit_quiz_keyword = $_POST['edit_quiz_keyword'];
		$edit_quiz_question = $_POST['edit_quiz_question'];
		$edit_quiz_answer = $_POST['edit_quiz_answer'];
		$edit_quiz_msg_correct = $_POST['edit_quiz_msg_correct'];
		$edit_quiz_msg_incorrect = $_POST['edit_quiz_msg_incorrect'];
		if ($edit_quiz_id && $edit_quiz_answer && $edit_quiz_question && $edit_quiz_keyword && $edit_quiz_msg_correct && $edit_quiz_msg_incorrect) {
			$db_query = "
						UPDATE " . _DB_PREF_ . "_featureQuiz
						SET c_timestamp='" . mktime() . "',quiz_keyword='$edit_quiz_keyword',quiz_question='$edit_quiz_question',quiz_answer='$edit_quiz_answer',quiz_msg_correct='$edit_quiz_msg_correct',quiz_msg_incorrect='$edit_quiz_msg_incorrect'
						WHERE quiz_id='$edit_quiz_id' AND uid='$uid'
						";
			if (@ dba_affected_rows($db_query)) {
				$error_string = _('SMS quiz has been saved')." ("._('keyword').": `$edit_quiz_keyword`)";
			}
		} else {
			$error_string = _('You must fill all field');
		}
		header("Location: index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_edit&quiz_id=$edit_quiz_id&err=" . urlencode($error_string));
		break;

	case "sms_quiz_status" :
		$quiz_id = $_REQUEST['quiz_id'];
		$ps = $_REQUEST['ps'];
		$db_query = "UPDATE " . _DB_PREF_ . "_featureQuiz SET c_timestamp='" . mktime() . "',quiz_enable='$ps' WHERE quiz_id='$quiz_id'";
		$db_result = @ dba_affected_rows($db_query);
		if ($db_result > 0) {
			$error_string = _('SMS quiz status has been changed');
		}
		header("Location: index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_edit&quiz_id=$quiz_id&err=" . urlencode($error_string));
		break;

	case "sms_quiz_del" :
		$quiz_id = $_REQUEST['quiz_id'];
		$db_query = "SELECT quiz_keyword FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id='$quiz_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$quiz_keyword = $db_row['quiz_keyword'];
		if ($quiz_keyword) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_keyword='$quiz_keyword'";
			if (@ dba_affected_rows($db_query)) {
				$error_string = _('SMS quiz with all its messages has been deleted')." ("._('keyword').": `$quiz_keyword`)";
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_list&err=" . urlencode($error_string));
		break;

	case "sms_quiz_add" :
		if ($err) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
				<h2>"._('Add SMS quiz')."</h2>
				<p>
				<form action=index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_add_yes method=post>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
				<tr>
				<td width=150>"._('SMS quiz keyword')."</td><td width=5>:</td><td><input type=text size=3 maxlength=10 name=add_quiz_keyword value=\"$add_quiz_keyword\"></td>
				</tr>
				<tr>
				<td>"._('SMS quiz question')."</td><td>:</td><td><input type=text size=40 maxlength=200 name=add_quiz_question value=\"$add_quiz_question\"></td>
				</tr>
				<tr>
				<td>"._('SMS quiz answer')."</td><td>:</td><td><input type=text size=12 maxlength=200 name=add_quiz_answer value=\"$add_quiz_answer\"></td>
				</tr>
				<tr>
				<td>"._('Message when correct')."</td><td>:</td><td><input type=text size=40 maxlength=200 name=add_quiz_msg_correct value=\"$add_quiz_msg_correct\"></td>
				</tr>
				<tr>
				<td>"._('Message when incorrect')."</td><td>:</td><td><input type=text size=40 maxlength=200 name=add_quiz_msg_incorrect value=\"$add_quiz_msg_incorrect\"></td>
				</tr>	    
			</table>
				<p><input type=submit class=button value=\""._('Add')."\">
				</form>
			";
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
							VALUES ('$uid','$add_quiz_keyword','$add_quiz_question','$add_quiz_answer','$add_quiz_msg_correct','$add_quiz_msg_incorrect')
						";
				if ($new_uid = @ dba_insert_id($db_query)) {
					$error_string = _('SMS quiz has been added')." ("._('keyword').": `$add_quiz_keyword`)";
				}
			} else {
				$error_string = _('SMS quiz already exists, reserved or use by other feature')." ("._('keyword').": `$add_quiz_keyword`)";
			}
		} else {
			$error_string = _('You must fill all field');
		}
		header("Location: index.php?app=menu&inc=feature_sms_quiz&op=sms_quiz_add&err=" . urlencode($error_string));
		break;
}
?>