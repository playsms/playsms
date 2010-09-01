<?php
if (!valid()) {
	forcenoaccess();
};

switch ($op) {
	case "sms_answer_view" :
		$quiz_id = $_REQUEST[quiz_id];
		$quiz_answer_query = "SELECT quiz_keyword,quiz_answer FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id = '$quiz_id'";
		$db_answer_result = dba_query($quiz_answer_query);
		$db_answer_row = dba_fetch_array($db_answer_result);
		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
				<h2>Received Answer List for [ $db_answer_row[quiz_keyword] ]</h2>			
				";

		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz_log WHERE quiz_id = '$quiz_id' ORDER BY in_datetime DESC";
		$db_result = dba_query($db_query);
		$content .= "
			<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr>
				<td class=box_title width=4>*</td>
				<td class=box_title width=30%>Datetime</td>
				<td class=box_title width=30%>Sender</td>
				<td class=box_title width=30%>Message</td>
				<td class=box_title width=10%>Status</td>
				<td class=box_title>Action</td>      
			</tr>
			";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";

			if ($db_row[quiz_answer] == $db_answer_row[quiz_answer]) {
				$iscorrect = "<font color=green>Correct</font>";
			} else {
				$iscorrect = "<font color=red>Incorrect</font>";
			}
			$action = "<a href=\"javascript: ConfirmURL('Are you sure you want to delete SMS this answer?','menu.php?inc=feature_sms_quiz&op=sms_answer_del&quiz_id=$quiz_id&answer_id=$db_row[answer_id]')\">$icon_delete</a>";

			$content .= "
				<tr>
					<td class=$td_class>&nbsp;$i.</td>
					<td class=$td_class>$db_row[in_datetime]</td>
					<td class=$td_class>$db_row[quiz_sender]</td>
					<td class=$td_class>$db_row[quiz_answer]</td>
					<td class=$td_class>$iscorrect</td>
					<td class=$td_class>$action</td>	
				</tr>";
		}
		$content .= "</table>";
		echo $content;
		break;

	case "sms_answer_del" :
		$quiz_id = $_REQUEST[quiz_id];
		$answer_id = $_REQUEST[answer_id];
		$db_query = "SELECT answer_id FROM " . _DB_PREF_ . "_featureQuiz_log WHERE answer_id='$answer_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$answer_id = $db_row[answer_id];
		if ($answer_id) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureQuiz_log WHERE answer_id='$answer_id'";
			if (@ dba_affected_rows($db_query)) {
				$error_string = "SMS quiz answer messages has been deleted!";
			}
		}
		header("Location: menu.php?inc=feature_sms_quiz&op=sms_answer_view&quiz_id=$quiz_id&err=" . urlencode($error_string));
		break;

	case "sms_quiz_list" :
		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
				<h2>Manage quiz</h2>
				<p>
				<input type=button value=\"Add SMS quiz\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_quiz&op=sms_quiz_add')\" class=\"button\" />
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
			    	<td class=box_title width=20%>Keyword</td>
				<td class=box_title width=40%>Question</td>
			    	<td class=box_title width=20%>Answer</td>
			    	<td class=box_title width=10%>User</td>	
			    	<td class=box_title width=10%>Status</td>
			    	<td class=box_title>Action</td>
			</tr>
			";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$owner = uid2username($db_row[uid]);
			$quiz_status = "<font color=red>Disable</font>";
			if ($db_row[quiz_enable]) {
				$quiz_status = "<font color=green>Enable</font>";
			}
			$action = "<a href=menu.php?inc=feature_sms_quiz&op=sms_answer_view&quiz_id=$db_row[quiz_id]>$icon_view</a>&nbsp;";
			$action .= "<a href=menu.php?inc=feature_sms_quiz&op=sms_quiz_edit&quiz_id=$db_row[quiz_id]>$icon_edit</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('Are you sure you want to delete SMS quiz keyword `$db_row[quiz_keyword]` with all its choices and votes ?','menu.php?inc=feature_sms_quiz&op=sms_quiz_del&quiz_id=$db_row[quiz_id]')\">$icon_delete</a>";
			$content .= "
					<tr>
						<td class=$td_class>&nbsp;$i.</td>
						<td class=$td_class>$db_row[quiz_keyword]</td>
						<td class=$td_class>$db_row[quiz_question]</td>
						<td class=$td_class>$db_row[quiz_answer]</td>
						<td class=$td_class>$owner</td>
						<td class=$td_class>$quiz_status</td>		
						<td class=$td_class align=center>$action</td>
					</tr>";
		}
		$content .= "</table>";
		echo $content;
		echo "
				<p>
				<input type=button value=\"Add SMS quiz\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_quiz&op=sms_quiz_add')\" class=\"button\" />
				";
		break;

	case "sms_quiz_edit" :
		$quiz_id = $_REQUEST[quiz_id];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id='$quiz_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_quiz_keyword = $db_row[quiz_keyword];
		$edit_quiz_question = $db_row[quiz_question];
		$edit_quiz_answer = $db_row[quiz_answer];
		$edit_quiz_msg_correct = $db_row[quiz_msg_correct];
		$edit_quiz_msg_incorrect = $db_row[quiz_msg_incorrect];
		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
				<h2>Edit SMS quiz</h2>
				<p>
				<form action=menu.php?inc=feature_sms_quiz&op=sms_quiz_edit_yes method=post>
				<input type=hidden name=edit_quiz_id value=\"$quiz_id\">
				<input type=hidden name=edit_quiz_keyword value=\"$edit_quiz_keyword\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>
				<tr>
				<td width=150>SMS quiz</td><td width=5>:</td><td><b>$edit_quiz_keyword</b></td>
				</tr>
				<tr>
				<td>SMS quiz question</td><td>:</td><td><input type=text size=40 maxlength=200 name=edit_quiz_question value=\"$edit_quiz_question\"></td>
			   	</tr>
				<tr>
				<td>SMS quiz answer</td><td>:</td><td><input type=text size=10 maxlength=200 name=edit_quiz_answer value=\"$edit_quiz_answer\"></td>
			   	</tr>
				<tr>
				<td>Message if correct</td><td>:</td><td><input type=text size=40 maxlength=200 name=edit_quiz_msg_correct value=\"$edit_quiz_msg_correct\"></td>
			   	</tr>
				<tr>
				<td>Message if incorrect</td><td>:</td><td><input type=text size=40 maxlength=200 name=edit_quiz_msg_incorrect value=\"$edit_quiz_msg_incorrect\"></td>
			   	</tr>		   	    
			</table>	    
				<p><input type=submit class=button value=\"Save Quiz\">
				</form>
				<br>
			";
		echo $content;

		$db_query = "SELECT quiz_enable FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id='$quiz_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$quiz_status = "<font color=red><b>Disable</b></font>";
		if ($db_row[quiz_enable]) {
			$quiz_status = "<font color=green><b>Enable</b></font>";
		}
		$content = "
				<h2>Enable or disable this quiz</h2>
				<p>
				<p>Current status: $quiz_status
				<p>What do you want to do ?
				<p>- <a href=\"menu.php?inc=feature_sms_quiz&op=sms_quiz_status&quiz_id=$quiz_id&ps=1\">I want to <b>enable</b> this quiz</a>
				<p>- <a href=\"menu.php?inc=feature_sms_quiz&op=sms_quiz_status&quiz_id=$quiz_id&ps=0\">I want to <b>disable</b> this quiz</a>
				<br>
			";
		echo $content;
		break;

	case "sms_quiz_edit_yes" :
		$edit_quiz_id = $_POST[edit_quiz_id];
		$edit_quiz_keyword = $_POST[edit_quiz_keyword];
		$edit_quiz_question = $_POST[edit_quiz_question];
		$edit_quiz_answer = $_POST[edit_quiz_answer];
		$edit_quiz_msg_correct = $_POST[edit_quiz_msg_correct];
		$edit_quiz_msg_incorrect = $_POST[edit_quiz_msg_incorrect];
		if ($edit_quiz_id && $edit_quiz_answer && $edit_quiz_question && $edit_quiz_keyword && $edit_quiz_msg_correct && $edit_quiz_msg_incorrect) {
			$db_query = "
						UPDATE " . _DB_PREF_ . "_featureQuiz
						SET c_timestamp='" . mktime() . "',quiz_keyword='$edit_quiz_keyword',quiz_question='$edit_quiz_question',quiz_answer='$edit_quiz_answer',quiz_msg_correct='$edit_quiz_msg_correct',quiz_msg_incorrect='$edit_quiz_msg_incorrect'
						WHERE quiz_id='$edit_quiz_id' AND uid='$uid'
						";
			if (@ dba_affected_rows($db_query)) {
				$error_string = "SMS quiz with keyword `$edit_quiz_keyword` has been saved";
			}
		} else {
			$error_string = "You must fill all fields!";
		}
		header("Location: menu.php?inc=feature_sms_quiz&op=sms_quiz_edit&quiz_id=$edit_quiz_id&err=" . urlencode($error_string));
		break;

	case "sms_quiz_status" :
		$quiz_id = $_REQUEST[quiz_id];
		$ps = $_REQUEST[ps];
		$db_query = "UPDATE " . _DB_PREF_ . "_featureQuiz SET c_timestamp='" . mktime() . "',quiz_enable='$ps' WHERE quiz_id='$quiz_id'";
		$db_result = @ dba_affected_rows($db_query);
		if ($db_result > 0) {
			$error_string = "This quiz status has been changed!";
		}
		header("Location: menu.php?inc=feature_sms_quiz&op=sms_quiz_edit&quiz_id=$quiz_id&err=" . urlencode($error_string));
		break;

	case "sms_quiz_del" :
		$quiz_id = $_REQUEST[quiz_id];
		$db_query = "SELECT quiz_keyword FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_id='$quiz_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$quiz_keyword = $db_row[quiz_keyword];
		if ($quiz_keyword) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureQuiz WHERE quiz_keyword='$quiz_keyword'";
			if (@ dba_affected_rows($db_query)) {
				$error_string = "SMS quiz `$quiz_keyword` with all its messages has been deleted!";
			}
		}
		header("Location: menu.php?inc=feature_sms_quiz&op=sms_quiz_list&err=" . urlencode($error_string));
		break;

	case "sms_quiz_add" :
		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
				<h2>Add SMS quiz</h2>
				<p>
				<form action=menu.php?inc=feature_sms_quiz&op=sms_quiz_add_yes method=post>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
				<tr>
				<td width=150>SMS quiz keyword</td><td width=5>:</td><td><input type=text size=3 maxlength=10 name=add_quiz_keyword value=\"$add_quiz_keyword\"></td>
				</tr>
				<tr>
				<td>SMS quiz question</td><td>:</td><td><input type=text size=40 maxlength=200 name=add_quiz_question value=\"$add_quiz_question\"></td>
				</tr>
				<tr>
				<td>SMS quiz answer</td><td>:</td><td><input type=text size=12 maxlength=200 name=add_quiz_answer value=\"$add_quiz_answer\"></td>
				</tr>
				<tr>
				<td>Message if correct</td><td>:</td><td><input type=text size=40 maxlength=200 name=add_quiz_msg_correct value=\"$add_quiz_msg_correct\"></td>
				</tr>
				<tr>
				<td>Message if  incorrect</td><td>:</td><td><input type=text size=40 maxlength=200 name=add_quiz_msg_incorrect value=\"$add_quiz_msg_incorrect\"></td>
				</tr>	    
			</table>
				<p><input type=submit class=button value=Add>
				</form>
			";
		echo $content;
		break;

	case "sms_quiz_add_yes" :
		$add_quiz_keyword = strtoupper($_POST[add_quiz_keyword]);
		$add_quiz_question = $_POST[add_quiz_question];
		$add_quiz_answer = strtoupper($_POST[add_quiz_answer]);
		$add_quiz_msg_correct = $_POST[add_quiz_msg_correct];
		$add_quiz_msg_incorrect = $_POST[add_quiz_msg_incorrect];
		if ($add_quiz_keyword && $add_quiz_answer) {
			if (checkavailablekeyword($add_quiz_keyword)) {
				$db_query = "
							INSERT INTO " . _DB_PREF_ . "_featureQuiz (uid,quiz_keyword,quiz_question,quiz_answer,quiz_msg_correct,quiz_msg_incorrect)
							VALUES ('$uid','$add_quiz_keyword','$add_quiz_question','$add_quiz_answer','$add_quiz_msg_correct','$add_quiz_msg_incorrect')
						";
				if ($new_uid = @ dba_insert_id($db_query)) {
					$error_string = "SMS quiz with keyword `$add_quiz_keyword` has been added";
				}
			} else {
				$error_string = "SMS keyword `$add_quiz_keyword` already exists, reserved or use by other feature!";
			}
		} else {
			$error_string = "You must fill all fields!";
		}
		header("Location: menu.php?inc=feature_sms_quiz&op=sms_quiz_add&err=" . urlencode($error_string));
		break;
}
?>
