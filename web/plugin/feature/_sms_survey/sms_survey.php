<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

// routing
if (($route = $_REQUEST['route']) && ($route == 'members')) {
	include $core_config['apps_path']['plug'].'/feature/sms_survey/sms_survey_members.php';
	exit();
}
if (($route = $_REQUEST['route']) && ($route == 'questions')) {
	include $core_config['apps_path']['plug'].'/feature/sms_survey/sms_survey_questions.php';
	exit();
}

// error messages
$error_content = '';
if ($err = $_SESSION['error_string']) {
	$error_content = "<div class=error_string>$err</div>";
}

// main
switch ($op) {
	case 'list':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= "
			<form method='post' action='index.php?app=menu&inc=feature_sms_survey&op=add'>
			<p><input class='button' type='submit' value='"._('Add survey')."'></p>
			</form>
			<table width='100%' cellpadding='1' cellspacing='2' border='0' class='sortable'>
			<tr>
				<td class='box_title' width='4'>*</td>
				<td class='box_title' width='10%'>"._('User')."</td>
				<td class='box_title' width='10%'>"._('Keyword')."</td>
				<td class='box_title' width='30%'>"._('Title')."</td>
				<td class='box_title' width='10%'>"._('Members')."</td>
				<td class='box_title' width='10%'>"._('Questions')."</td>
				<td class='box_title' width='10%'>"._('Status')."</td>
				<td class='box_title' width='10%'>"._('Started')."</td>
				<td class='box_title' width='10%'>"._('Action')."</td>
			</tr>
		";
		$data = sms_survey_getdataall();
		for ($i=0;$i<count($data);$i++) {
			$c_sid = $data[$i]['id'];
			$c_status = $data[$i]['status'] ? "<a href='index.php?app=menu&inc=feature_sms_survey&op=disable&sid=".$c_sid."'><font color='green'>"._('enabled')."</font></a>" : "<a href='index.php?app=menu&inc=feature_sms_survey&op=enable&sid=".$c_sid."'><font color='red'>"._('disabled')."</font></a>";
			if ($data[$i]['status']) {
				$c_status = $data[$i]['running']==2 ? "<a href='index.php?app=menu&inc=feature_sms_survey&op=disable&sid=".$c_sid."'><font color='blue'>"._('completed')."</font></a>" : "<a href='index.php?app=menu&inc=feature_sms_survey&op=enable&sid=".$c_sid."'><font color='green'>"._('enabled')."</font></a>";
			}
			if ($data[$i]['started']) {
				$c_status = _('N/A');
			}
			$c_started = $data[$i]['started'] ? "<a href='index.php?app=menu&inc=feature_sms_survey&op=stop&sid=".$c_sid."'><font color='green'>"._('yes')."</font></a>" : "<a href='index.php?app=menu&inc=feature_sms_survey&op=start&sid=".$c_sid."'><font color='red'>"._('no')."</font></a>";
			if (! $data[$i]['started']) {
				$c_started = $data[$i]['running']==2 ? "<a href='index.php?app=menu&inc=feature_sms_survey&op=start&sid=".$c_sid."'><font color='red'>"._('restart')."</font></a>" : "<a href='index.php?app=menu&inc=feature_sms_survey&op=start&sid=".$c_sid."'><font color='red'>"._('no')."</font></a>";
			}
			if (! $data[$i]['status']) {
				$c_started = _('N/A');
			}
			$c_members = count(sms_survey_getmembers($c_sid));
			$c_members = "<a href='index.php?app=menu&inc=feature_sms_survey&route=members&op=members&sid=".$c_sid."'>".$c_members."</a>";
			$c_questions = count(sms_survey_getquestions($c_sid));
			$c_questions = "<a href='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions&sid=".$c_sid."'>".$c_questions."</a>";
			$c_action = "<a href='index.php?app=menu&inc=feature_sms_survey&op=edit&sid=".$c_sid."'>".$icon_edit."</a> ";
			if (! $data[$i]['status']) {
				$c_action .= "<a href='index.php?app=menu&inc=feature_sms_survey&op=del&sid=".$c_sid."'>".$icon_delete."</a> ";
			}
			$td_class = (($i+1) % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr class='".$td_class."'>
					<td align='center'>".($i+1).".</td>
					<td align='center'>".uid2username($data[$i]['uid'])."</td>
					<td align='center'>".$data[$i]['keyword']."</td>
					<td align='center'>".$data[$i]['title']."</td>
					<td align='center'>".$c_members."</td>
					<td align='center'>".$c_questions."</td>
					<td align='center'>".$c_status."</td>
					<td align='center'>".$c_started."</td>
					<td align='center'>".$c_action."</td>
				</tr>
			";
		}
		$content .= "
			</table>
			<form method='post' action='index.php?app=menu&inc=feature_sms_survey&op=add'>
			<p><input class='button' type='submit' value='"._('Add survey')."'></p>
			</form>
		";
		echo $content;
		break;
	case 'add':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Add survey').'</h3><p />';
		$content .= "
			<form method='post' action='index.php?app=menu&inc=feature_sms_survey&op=add_submit'>
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Keyword')."</td><td>:</td><td><input type='text' name='keyword' maxlength='20' size='20'></td></tr>
			<tr><td>"._('Title')."</td><td>:</td><td><input type='text' name='title' maxlength='100' size='40'></td></tr>
			</table>
			<p><input class='button' type='submit' value='"._('Submit')."'></p>
			</form>
		";
		echo $content;
		break;
	case 'add_submit':
		$keyword = $_REQUEST['keyword'];
		$title = $_REQUEST['title'];
		if ($keyword && $title) {
			if (sms_survey_dataadd($keyword, $title)) {
				$_SESSION['error_string'] = _('Survey has been added')." ("._('keyword').": ".$keyword.")";
			} else {
				$_SESSION['error_string'] = _('Fail to add survey')." ("._('keyword').": ".$keyword.")";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&op=add");
		exit();
		break;
	case 'edit':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Edit survey').'</h3><p />';
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		$title = $data['title'];
		$content .= "
			<form method='post' action='index.php?app=menu&inc=feature_sms_survey&op=edit_submit'>
			<input type='hidden' name='sid' value='$sid'>
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Keyword')."</td><td>:</td><td>".$keyword."</td></tr>
			<tr><td>"._('Title')."</td><td>:</td><td><input type='text' name='title' value='$title' maxlength='100' size='40'></td></tr>
			</table>
			<p><input class='button' type='submit' value='"._('Submit')."'></p>
			</form>
		";
		echo $content;
		break;
	case 'edit_submit':
		$sid = $_REQUEST['sid'];
		$keyword = $_REQUEST['keyword'];
		$title = $_REQUEST['title'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		if ($sid && $keyword && $title) {
			if (sms_survey_dataedit($sid, $keyword, $title)) {
				$_SESSION['error_string'] = _('Survey has been edited')." ("._('keyword').": ".$keyword.")";
			} else {
				$_SESSION['error_string'] = _('Fail to edit survey')." ("._('keyword').": ".$keyword.")";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&op=edit&sid=".$sid);
		exit();
		break;
	case 'del':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Delete survey').'</h3><p />';
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		$title = $data['title'];
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
		$content .= "
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Keyword')."</td><td>:</td><td>".$keyword."</td></tr>
			<tr><td>"._('Title')."</td><td>:</td><td>".$title."</td></tr>
			<tr><td>"._('Members')."</td><td>:</td><td>".$c_members."</td></tr>
			<tr><td>"._('Questions')."</td><td>:</td><td>".$c_questions."</td></tr>
			<tr><td>"._('Status')."</td><td>:</td><td>".$c_status."</td></tr>
			<tr><td>"._('Started')."</td><td>:</td><td>".$c_started."</td></tr>
			</table>
			<p>"._('Are you sure you want to delete this survey ?')."</p>
			<form method='post' action='index.php?app=menu&inc=feature_sms_survey&op=del_submit'>
			<input type='hidden' name='sid' value='$sid'>
			<p><input class='button' type='submit' value='"._('Yes')."'></p>
			</form>
			<form method='post'action='index.php?app=menu&inc=feature_sms_survey&op=list'>
			<p><input class='button' type='submit' value='"._('Cancel')."'></p>
			</form>
		";
		echo $content;
		break;
	case 'del_submit':
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		if ($sid && $keyword) {
			if (sms_survey_datadel($sid)) {
				$_SESSION['error_string'] = _('Survey has been deleted')." ("._('keyword').": ".$keyword.")";
			} else {
				$_SESSION['error_string'] = _('Fail to delete survey')." ("._('keyword').": ".$keyword.")";
			}
		} else {
			$_SESSION['error_string'] = _('Survey does not exists');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&op=list&sid=".$sid);
		exit();
		break;
	case 'enable':
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		if ($sid && $keyword) {
			if (sms_survey_dataenable($sid)) {
				$_SESSION['error_string'] = _('Survey has been enabled')." ("._('keyword').": ".$keyword.")";
			} else {
				$_SESSION['error_string'] = _('Fail to enable survey')." ("._('keyword').": ".$keyword.")";
			}
		} else {
			$_SESSION['error_string'] = _('Survey does not exists');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&op=list&sid=".$sid);
		exit();
		break;
	case 'disable':
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		if ($sid && $keyword) {
			if (sms_survey_datadisable($sid)) {
				$_SESSION['error_string'] = _('Survey has been disabled')." ("._('keyword').": ".$keyword.")";
			} else {
				$_SESSION['error_string'] = _('Fail to disable survey')." ("._('keyword').": ".$keyword.")";
			}
		} else {
			$_SESSION['error_string'] = _('Survey does not exists');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&op=list&sid=".$sid);
		exit();
		break;
	case 'start':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Start survey').'</h3><p />';
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		$title = $data['title'];
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
		$content .= "
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Keyword')."</td><td>:</td><td>".$keyword."</td></tr>
			<tr><td>"._('Title')."</td><td>:</td><td>".$title."</td></tr>
			<tr><td>"._('Members')."</td><td>:</td><td>".$c_members."</td></tr>
			<tr><td>"._('Questions')."</td><td>:</td><td>".$c_questions."</td></tr>
			<tr><td>"._('Status')."</td><td>:</td><td>".$c_status."</td></tr>
			<tr><td>"._('Started')."</td><td>:</td><td>".$c_started."</td></tr>
			</table>
			<p>"._('Are you sure you want to start this survey ?')."</p>
			<form method='post' action='index.php?app=menu&inc=feature_sms_survey&op=start_submit'>
			<input type='hidden' name='sid' value='$sid'>
			<p><input class='button' type='submit' value='"._('Yes')."'></p>
			</form>
			<form method='post'action='index.php?app=menu&inc=feature_sms_survey&op=list'>
			<p><input class='button' type='submit' value='"._('Cancel')."'></p>
			</form>
		";
		echo $content;
		break;
	case 'start_submit':
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		if ($sid && $keyword) {
			if (sms_survey_datastart($sid)) {
				$_SESSION['error_string'] = _('Survey has been started')." ("._('keyword').": ".$keyword.")";
			} else {
				$_SESSION['error_string'] = _('Fail to start survey')." ("._('keyword').": ".$keyword.")";
			}
		} else {
			$_SESSION['error_string'] = _('Survey does not exists');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&op=list&sid=".$sid);
		exit();
		break;
	case 'stop':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Stop survey').'</h3><p />';
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		$title = $data['title'];
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
		$content .= "
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Keyword')."</td><td>:</td><td>".$keyword."</td></tr>
			<tr><td>"._('Title')."</td><td>:</td><td>".$title."</td></tr>
			<tr><td>"._('Members')."</td><td>:</td><td>".$c_members."</td></tr>
			<tr><td>"._('Questions')."</td><td>:</td><td>".$c_questions."</td></tr>
			<tr><td>"._('Status')."</td><td>:</td><td>".$c_status."</td></tr>
			<tr><td>"._('Started')."</td><td>:</td><td>".$c_started."</td></tr>
			</table>
			<p>"._('Are you sure you want to stop this survey ?')."</p>
			<form method='post' action='index.php?app=menu&inc=feature_sms_survey&op=stop_submit'>
			<input type='hidden' name='sid' value='$sid'>
			<p><input class='button' type='submit' value='"._('Yes')."'></p>
			</form>
			<form method='post'action='index.php?app=menu&inc=feature_sms_survey&op=list'>
			<p><input class='button' type='submit' value='"._('Cancel')."'></p>
			</form>
		";
		echo $content;
		break;
	case 'stop_submit':
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		if ($sid && $keyword) {
			if (sms_survey_datastop($sid)) {
				$_SESSION['error_string'] = _('Survey has been stoped')." ("._('keyword').": ".$keyword.")";
			} else {
				$_SESSION['error_string'] = _('Fail to stop survey')." ("._('keyword').": ".$keyword.")";
			}
		} else {
			$_SESSION['error_string'] = _('Survey does not exists');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&op=list&sid=".$sid);
		exit();
		break;
}

?>