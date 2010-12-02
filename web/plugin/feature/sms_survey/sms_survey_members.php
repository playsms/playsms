<?php
if(!isadmin()){forcenoaccess();};

// error messages
$error_content = '';
if ($errid) {
	$err = logger_get_error_string($errid);
}
if ($err) {
	$error_content = "<div class=error_string>$err</div>";
}

// main
switch ($op) {
	case 'members':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Member list').'</h3><p />';
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		$title = $data['title'];
		$c_members = count(sms_survey_getmembers($sid));
		$c_members = "<a href='index.php?app=menu&inc=feature_sms_survey&route=members&op=members&sid=".$sid."'>".$c_members."</a>";
		$c_questions = count(sms_survey_getquestions($sid));
		$c_questions = "<a href='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions&sid=".$sid."'>".$c_questions."</a>";
		$c_status = $data['status'] ? "<font color='green'>"._('enabled')."</font>" : "<font color='red'>"._('disabled')."</font>";
		$c_started = $data['started'] ? "<font color='green'>"._('yes')."</font>" : "<font color='red'>"._('no')."</font>";
		$content .= "
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Keyword')."</td><td>:</td><td>".$keyword."</td></tr>
			<tr><td>"._('Title')."</td><td>:</td><td>".$title."</td></tr>
			<tr><td>"._('Members')."</td><td>:</td><td>".$c_members."</td></tr>
			<tr><td>"._('Questions')."</td><td>:</td><td>".$c_questions."</td></tr>
			<tr><td>"._('Status')."</td><td>:</td><td>".$c_status."</td></tr>
			<tr><td>"._('Started')."</td><td>:</td><td>".$c_started."</td></tr>
			</table>
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr>
				<td>
					<form method='post' action='index.php?app=menu&inc=feature_sms_survey&route=members&op=members_add&sid=".$sid."'>
					<input class='button' type='submit' value='"._('Add member')."'>
					</form>
				</td>
				<td>
					<form method='post' action='index.php?app=menu&inc=feature_sms_survey&route=members&op=members_delete&sid=".$sid."'>
					<input class='button' type='submit' value='"._('Delete member')."'>
					</form>
				</td>
			</tr>
			</table>
			<table width='100%' cellpadding='1' cellspacing='2' border='0' class='sortable'>
			<tr>
				<td class='box_title' width='4'>*</td>
				<td class='box_title' width='50%'>"._('Name')."</td>
				<td class='box_title' width='50%'>"._('Mobile')."</td>
			</tr>
		";
		$members = sms_survey_getmembers($sid);
		for ($i=0;$i<count($members);$i++) {
			$c_name = $members[$i]['name'];
			$c_mobile = $members[$i]['mobile'];
			$td_class = (($i+1) % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr class='".$td_class."'>
					<td align='center'>".($i+1).".</td>
					<td align='center'>".$c_name."</td>
					<td align='center'>".$c_mobile."</td>
				</tr>
			";
		}
		$content .= "
			</table>
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr>
				<td>
					<form method='post' action='index.php?app=menu&inc=feature_sms_survey&route=members&op=members_add&sid=".$sid."'>
					<input class='button' type='submit' value='"._('Add member')."'>
					</form>
				</td>
				<td>
					<form method='post' action='index.php?app=menu&inc=feature_sms_survey&route=members&op=members_delete&sid=".$sid."'>
					<input class='button' type='submit' value='"._('Delete member')."'>
					</form>
				</td>
			</tr>
			</table>
		";
		echo $content;
		break;
	case 'members_add':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Add member').'</h3><p />';
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		$title = $data['title'];
		$c_members = count(sms_survey_getmembers($sid));
		$c_members = "<a href='index.php?app=menu&inc=feature_sms_survey&route=members&op=members&sid=".$sid."'>".$c_members."</a>";
		$c_questions = count(sms_survey_getquestions($sid));
		$c_questions = "<a href='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions&sid=".$sid."'>".$c_questions."</a>";
		$c_status = $data['status'] ? "<font color='green'>"._('enabled')."</font>" : "<font color='red'>"._('disabled')."</font>";
		$c_started = $data['started'] ? "<font color='green'>"._('yes')."</font>" : "<font color='red'>"._('no')."</font>";
		$content .= "
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Keyword')."</td><td>:</td><td>".$keyword."</td></tr>
			<tr><td>"._('Title')."</td><td>:</td><td>".$title."</td></tr>
			<tr><td>"._('Members')."</td><td>:</td><td>".$c_members."</td></tr>
			<tr><td>"._('Questions')."</td><td>:</td><td>".$c_questions."</td></tr>
			<tr><td>"._('Status')."</td><td>:</td><td>".$c_status."</td></tr>
			<tr><td>"._('Started')."</td><td>:</td><td>".$c_started."</td></tr>
			</table>
		";
		$content .= "
			<form action=\"index.php?app=menu&inc=feature_sms_survey&route=members&op=members_add_submit\" method=\"post\">
			<input type=hidden name='sid' value='".$sid."'>
			<p>"._('Press submit button to add selected users to member list')."</p>
			<p><input class='button' type='submit' value='Submit' onClick=\"selectAllOptions(this.form['uids[]'])\"></p>
			</form>
		";
		echo $content;
		break;
	case 'members_add_submit':
		$sid = $_REQUEST['sid'];
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$in_receiver = $data['in_receiver'];
		if ($sid && $in_receiver) {
			$uids = $_REQUEST['uids'];
			for ($i=0;$i<count($uids);$i++) {
				$c_uid = $uids[$i];
				$c_username = uid2username($c_uid);
				if (sms_survey_membersadd($sid, $c_uid)) {
					$error_string .= _('Member has been added')." ("._('Username').": ".$c_username.")<br />";
				} else {
					$error_string .= _('Fail to add member')." ("._('Username').": ".$c_username.")<br />";
				}
			}
		} else {
			$error_string = _('Receiver number does not exists');
		}
		$errid = logger_set_error_string($error_string);
		header("Location: index.php?app=menu&inc=feature_sms_survey&route=members&op=members&sid=".$sid."&errid=".$errid);
		break;
	case 'members_delete':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Remove member').'</h3><p />';
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$in_receiver = $data['in_receiver'];
		$keyword = $data['keyword'];
		$title = $data['title'];
		$c_members = count(sms_survey_getmembers($sid));
		$c_members = "<a href='index.php?app=menu&inc=feature_sms_survey&route=members&op=members&sid=".$sid."'>".$c_members."</a>";
		$c_questions = count(sms_survey_getquestions($sid));
		$c_questions = "<a href='index.php?app=menu&inc=feature_sms_survey&route=questions&op=questions&sid=".$sid."'>".$c_questions."</a>";
		$c_status = $data['status'] ? "<font color='green'>"._('enabled')."</font>" : "<font color='red'>"._('disabled')."</font>";
		$content .= "
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Receiver number')."</td><td>:</td><td>".$in_receiver."</td></tr>
			<tr><td>"._('Keyword')."</td><td>:</td><td>".$keyword."</td></tr>
			<tr><td>"._('Title')."</td><td>:</td><td>".$title."</td></tr>
			<tr><td>"._('Members')."</td><td>:</td><td>".$c_members."</td></tr>
			<tr><td>"._('Questions')."</td><td>:</td><td>".$c_questions."</td></tr>
			<tr><td>"._('Status')."</td><td>:</td><td>".$c_status."</td></tr>
			</table>
		";
		$list_of_members = '';
		// get members
		$users = sms_survey_getmembers($sid);
		for ($i=0;$i<count($users);$i++) {
			$c_uid = $users[$i]['uid'];
			$c_username = uid2username($c_uid);
			$c_name = username2name($c_username);
			$c_mobile = username2mobile($c_username);
			$list_of_users .= "<option value='".$c_uid."'>".$c_name." ".$c_mobile."</option>";
		}
		$content .= "
			<form action=\"index.php?app=menu&inc=feature_sms_survey&route=members&op=members_delete_submit\" method=\"post\">
			<input type=hidden name='sid' value='".$sid."'>
			<table cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td nowrap>
					"._('Current members').":<br />
		    			<select name=\"uids_dump[]\" size=\"10\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['uids_dump[]'],this.form['uids[]'])\">$list_of_users</select>
				</td>
				<td width=10>&nbsp;</td>
				<td align=center valign=middle>
					<input type=\"button\" class=\"button\" value=\"&gt;&gt;\" onclick=\"moveSelectedOptions(this.form['uids_dump[]'],this.form['uids[]'])\"><br><br>
					<input type=\"button\" class=\"button\" value=\""._('All')." &gt;&gt;\" onclick=\"moveAllOptions(this.form['uids_dump[]'],this.form['uids[]'])\"><br><br>
					<input type=\"button\" class=\"button\" value=\"&lt;&lt;\" onclick=\"moveSelectedOptions(this.form['uids[]'],this.form['uids_dump[]'])\"><br><br>
					<input type=\"button\" class=\"button\" value=\""._('All')." &lt;&lt;\" onclick=\"moveAllOptions(this.form['uids[]'],this.form['uids_dump[]'])\">
				</td>		
				<td width=10>&nbsp;</td>
				<td nowrap>
				    "._('Selected members').":<br>
				    <select name=\"uids[]\" size=\"10\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['uids[]'],this.form['uids_dump[]'])\"></select>
				</td>
			</tr>
			</table>
			<p>"._('Press submit button to remove selected members from member list')."</p>
			<p><input class='button' type='submit' value='Submit' onClick=\"selectAllOptions(this.form['uids[]'])\"></p>
			</form>
		";
		echo $content;
		break;
	case 'members_delete_submit':
		$sid = $_REQUEST['sid'];
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$in_receiver = $data['in_receiver'];
		if ($sid && $in_receiver) {
			$uids = $_REQUEST['uids'];
			for ($i=0;$i<count($uids);$i++) {
				$c_uid = $uids[$i];
				$c_username = uid2username($c_uid);
				if (sms_survey_membersdel($sid, $c_uid)) {
					$error_string .= _('Member has been deleted')." ("._('Username').": ".$c_username.")<br />";
				} else {
					$error_string .= _('Fail to delete member')." ("._('Username').": ".$c_username.")<br />";
				}
			}
		} else {
			$error_string = _('Receiver number does not exists');
		}
		$errid = logger_set_error_string($error_string);
		header("Location: index.php?app=menu&inc=feature_sms_survey&route=members&op=members&sid=".$sid."&errid=".$errid);
		break;
}

?>