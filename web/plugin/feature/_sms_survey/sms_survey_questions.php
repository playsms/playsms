<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

// error messages
$error_content = '';
if ($err = $_SESSION['error_string']) {
	$error_content = "<div class=error_string>$err</div>";
}

// main
switch ($op) {
	case 'questions':
	case 'questions_add':
	case 'questions_edit':
	case 'questions_del':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}

		if ($op == 'questions_add') {
			$content .= '<h3>'._('Add question').'</h3><p />';
		} else if ($op == 'questions_edit') {
			$content .= '<h3>'._('Edit question').'</h3><p />';
		} else if ($op == 'questions_del') {
			$content .= '<h3>'._('Delete question').'</h3><p />';
		} else if ($op == 'questions') {
			$content .= '<h3>'._('Question list').'</h3><p />';
		}

		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		$title = $data['title'];
		$c_user = uid2username($data['uid']);
		$c_members = count(sms_survey_getmembers($sid));
		$c_members = "<a href='index.php?app=menu&inc=feature_sms_survey&route=members&op=members&sid=".$sid."'>".$c_members."</a>";
		$c_questions = count(sms_survey_getquestions($sid));
		$c_questions = "<a href='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions&sid=".$sid."'>".$c_questions."</a>";
		$c_status = $data['status'] ? "<font color='green'>"._('enabled')."</font>" : "<font color='red'>"._('disabled')."</font>";
		if ($data['status']) {
			$c_status = $data['running']==2 ? "<font color='blue'>"._('completed')."</font>" : "<font color='green'>"._('enabled')."</font>";
		}
		$c_started = $data['started'] ? "<font color='green'>"._('yes')."</font>" : "<font color='red'>"._('no')."</font>";
		if (! $data['started']) {
			$c_started = $data['running']==2 ? "<font color='red'>"._('restart')."</font>" : "<font color='red'>"._('no')."</font>";
		}
		if (! $data['status']) {
			$buttons = "
				<table cellpadding='1' cellspacing='2' border='0'>
				<tr>
					<td>
						<form method='post' action='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions_add&sid=".$sid."'>
						<input class='button' type='submit' value='"._('Add question')."'>
						</form>
					</td>
				</tr>
				</table>
			";
		} else {
			$buttons = "";
		}

		if ($op == 'questions_add') {
			$add_question = "
				<p>&nbsp</p>
				<form method='post' action='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions_add_submit'>
				<input type='hidden' name='sid' value='$sid'>
				<table cellpadding='1' cellspacing='2' border='0'>
				<tr><td>"._('Question')."</td><td>:</td><td><input type='text' name='question' maxlength='140' size='100'></td></tr>
				</table>
				<p><input class='button' type='submit' value='"._('Submit')."'></p>
				</form>
				<p>&nbsp</p>
			";
			$buttons = "";
		}

		if ($op == 'questions_edit') {
			$qid = $_REQUEST['qid'];
			$q = sms_survey_getquestionbyid($qid);
			$edit_question = "
				<p>&nbsp</p>
				<form method='post' action='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions_edit_submit'>
				<input type='hidden' name='sid' value='$sid'>
				<input type='hidden' name='qid' value='$qid'>
				<table cellpadding='1' cellspacing='2' border='0'>
				<tr><td>"._('Question')."</td><td>:</td><td><input type='text' name='question' value='".$q['question']."' maxlength='140' size='100'></td></tr>
				</table>
				<p><input class='button' type='submit' value='"._('Submit')."'></p>
				</form>
				<p>&nbsp</p>
			";
			$buttons = "";
		}

		if ($op == 'questions_del') {
			$qid = $_REQUEST['qid'];
			$q = sms_survey_getquestionbyid($qid);
			$edit_question = "
				<p>&nbsp</p>
				<form method='post' action='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions_del_submit'>
				<input type='hidden' name='sid' value='$sid'>
				<input type='hidden' name='qid' value='$qid'>
				<table cellpadding='1' cellspacing='2' border='0'>
				<tr><td>"._('Question')."</td><td>:</td><td>".$q['question']."</td></tr>
				</table>
				<p><input class='button' type='submit' value='"._('Submit')."'></p>
				</form>
				<p>&nbsp</p>
			";
			$buttons = "";
		}

		$content .= "
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('User')."</td><td>:</td><td>".$c_user."</td></tr>
			<tr><td>"._('Keyword')."</td><td>:</td><td>".$keyword."</td></tr>
			<tr><td>"._('Title')."</td><td>:</td><td>".$title."</td></tr>
			<tr><td>"._('Members')."</td><td>:</td><td>".$c_members."</td></tr>
			<tr><td>"._('Questions')."</td><td>:</td><td>".$c_questions."</td></tr>
			<tr><td>"._('Status')."</td><td>:</td><td>".$c_status."</td></tr>
			<tr><td>"._('Started')."</td><td>:</td><td>".$c_started."</td></tr>
			</table>
			<!-- add question -->
			".$add_question."
			".$edit_question."
			".$del_question."
			<!-- buttons -->
			".$buttons."
			<table width='100%' cellpadding='1' cellspacing='2' border='0' class='sortable'>
			<tr>
				<td class='box_title' width='4'>*</td>
				<td class='box_title' width='90%'>"._('Question')."</td>
				<td class='box_title' width='10%'>"._('Action')."</td>
			</tr>
		";
		$questions = sms_survey_getquestions($sid);
		for ($i=0;$i<count($questions);$i++) {
			$c_qid = $questions[$i]['id'];
			$c_question = htmlspecialchars($questions[$i]['question']);
			if (! $data['status']) {
				$c_action = "<a href='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions_edit&sid=".$sid."&qid=".$c_qid."'>".$icon_edit."</a> ";
				$c_action .= "<a href='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions_del&sid=".$sid."&qid=".$c_qid."'>".$icon_delete."</a> ";
			}
			$td_class = (($i+1) % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr class='".$td_class."'>
					<td align='center'>".($i+1).".</td>
					<td align='center'>".$c_question."</td>
					<td align='center'>".$c_action."</td>
				</tr>
			";
		}
		$content .= "
			</table>
			<!-- buttons -->
			".$buttons;
		echo $content;
		break;
	case 'questions_add_submit':
		$sid = $_REQUEST['sid'];
		$question = $_REQUEST['question'];
		if ($sid && $question) {
			if (sms_survey_questionsadd($sid, $question)) {
				$_SESSION['error_string'] = _('Question has been added');
			} else {
				$_SESSION['error_string'] = _('Fail to add question');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions&sid=".$sid);
		exit();
		break;
	case 'questions_edit_submit':
		$sid = $_REQUEST['sid'];
		$qid = $_REQUEST['qid'];
		$question = $_REQUEST['question'];
		if ($sid && $qid && $question) {
			if (sms_survey_questionsedit($sid, $qid, $question)) {
				$_SESSION['error_string'] = _('Question has been edited');
			} else {
				$_SESSION['error_string'] = _('Fail to edit question');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions&sid=".$sid);
		exit();
		break;
	case 'questions_del_submit':
		$sid = $_REQUEST['sid'];
		$qid = $_REQUEST['qid'];
		if ($sid && $qid) {
			if (sms_survey_questionsdel($sid, $qid)) {
				$_SESSION['error_string'] = _('Question has been deleted');
			} else {
				$_SESSION['error_string'] = _('Fail to delete question');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions&sid=".$sid);
		exit();
		break;
}

?>