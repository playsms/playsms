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
		} else {
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
			<!-- buttons -->
			".$buttons."
			<table width='100%' cellpadding='1' cellspacing='2' border='0' class='sortable'>
			<tr>
				<td class='box_title' width='4'>*</td>
				<td class='box_title' width='50%'>"._('Mobile')."</td>
				<td class='box_title' width='50%'>"._('Name')."</td>
			</tr>
		";
		$members = sms_survey_getmembers($sid);
		for ($i=0;$i<count($members);$i++) {
			$c_mobile = $members[$i]['mobile'];
			$c_name = htmlspecialchars($members[$i]['name']);
			$td_class = (($i+1) % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr class='".$td_class."'>
					<td align='center'>".($i+1).".</td>
					<td align='center'>".$c_mobile."</td>
					<td align='center'>".$c_name."</td>
				</tr>
			";
		}
		$content .= "
			</table>
			<!-- buttons -->
			".$buttons;
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
		";
		// upload member list from CSV files
		// will replace members with same mobile number
		$content .= "
			<form action=\"index.php?app=menu&inc=feature_sms_survey&route=members&op=members_add_submit\" enctype=\"multipart/form-data\" method=\"post\">
			<input type=hidden name='sid' value='".$sid."'>
		    	<p>"._('Please select CSV file')." ("._('format : keyword, mobile, name').")</p>
		    	<p><input type=\"file\" name=\"fncsv\">
			<p>"._('Press submit button to add members from CSV file')."</p>
		    	<p><input type=\"submit\" class=\"button\" value=\""._('Submit')."\">
			</form>
		";
		echo $content;
		break;
	case 'members_add_submit':
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		if ($sid && $keyword) {
			$filename = $_FILES['fncsv']['name'];
			$fn = $_FILES['fncsv']['tmp_name'];
			$fs = $_FILES['fncsv']['size'];
			if (($fs == filesize($fn)) && file_exists($fn)) {
				if (($fd = fopen($fn, 'r')) !== FALSE) {
					$_SESSION['error_string'] = "";
					while (($data = fgetcsv($fd, $fs, ',')) !== FALSE) {
						$c_keyword = trim(strtoupper($data[0]));
						$c_mobile = sendsms_getvalidnumber(trim($data[1]));
						$c_name = htmlspecialchars(trim($data[2]));
						if (($keyword == $c_keyword) && $c_keyword && $c_mobile) {
							if (sms_survey_membersadd($sid, $c_mobile, $c_name)) {
								$_SESSION['error_string'] .= _('Member has been added')." ("._('Keyword').": ".$c_keyword.", "._('mobile').": ".$c_mobile.", "._('name').": ".$c_name." )<br />";
							} else {
								$_SESSION['error_string'] .= _('Fail to add member')." ("._('Keyword').": ".$c_keyword.", "._('mobile').": ".$c_mobile.", "._('name').": ".$c_name." )<br />";
							}
						} else {
							if ($c_mobile) {
								$_SESSION['error_string'] .= _('Keyword does not match')." ("._('Keyword').": ".$c_keyword.", "._('mobile').": ".$c_mobile.", "._('name').": ".$c_name." )<br />";
							} else if ($c_keyword) {
								$_SESSION['error_string'] .= _('Mobile number not exists')." ("._('Keyword').": ".$c_keyword.", "._('mobile').": ".$c_mobile.", "._('name').": ".$c_name." )<br />";
							}
						}
					}
				}
			}
		} else {
			$_SESSION['error_string'] = _('Keyword does not exists');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&route=members&op=members&sid=".$sid);
		exit();
		break;
	case 'members_delete':
		$content = '<h2>'._('SMS Survey').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Remove member').'</h3><p />';
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
		";
		$list_of_members = '';
		// get members
		$members = sms_survey_getmembers($sid);
		for ($i=0;$i<count($members);$i++) {
			$c_id = $members[$i]['id'];
			$c_mobile = $members[$i]['mobile'];
			$c_name = $members[$i]['name'];
			$list_of_users .= "<option value='".$c_id."'>".$c_mobile." ".$c_name."</option>";
		}
		$content .= "
			<form action=\"index.php?app=menu&inc=feature_sms_survey&route=members&op=members_delete_submit\" method=\"post\">
			<input type=hidden name='sid' value='".$sid."'>
			<table cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td nowrap>
					"._('Current members').":<br />
		    			<select name=\"members_dump[]\" size=\"10\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['members_dump[]'],this.form['members[]'])\">$list_of_users</select>
				</td>
				<td width=10>&nbsp;</td>
				<td align=center valign=middle>
					<input type=\"button\" class=\"button\" value=\"&gt;&gt;\" onclick=\"moveSelectedOptions(this.form['members_dump[]'],this.form['members[]'])\"><br><br>
					<input type=\"button\" class=\"button\" value=\""._('All')." &gt;&gt;\" onclick=\"moveAllOptions(this.form['members_dump[]'],this.form['members[]'])\"><br><br>
					<input type=\"button\" class=\"button\" value=\"&lt;&lt;\" onclick=\"moveSelectedOptions(this.form['members[]'],this.form['members_dump[]'])\"><br><br>
					<input type=\"button\" class=\"button\" value=\""._('All')." &lt;&lt;\" onclick=\"moveAllOptions(this.form['members[]'],this.form['members_dump[]'])\">
				</td>		
				<td width=10>&nbsp;</td>
				<td nowrap>
				    "._('Selected members').":<br>
				    <select name=\"members[]\" size=\"10\" multiple=\"multiple\" onDblClick=\"moveSelectedOptions(this.form['members[]'],this.form['members_dump[]'])\"></select>
				</td>
			</tr>
			</table>
			<p>"._('Press submit button to remove selected members from member list')."</p>
			<p><input class='button' type='submit' value='Submit' onClick=\"selectAllOptions(this.form['members[]'])\"></p>
			</form>
		";
		echo $content;
		break;
	case 'members_delete_submit':
		$sid = $_REQUEST['sid'];
		$data = sms_survey_getdatabyid($sid);
		$keyword = $data['keyword'];
		if ($sid && $keyword) {
			$members = $_REQUEST['members'];
			for ($i=0;$i<count($members);$i++) {
				$c_id = $members[$i];
				$member = sms_survey_getmemberbyid($c_id);
				if (sms_survey_membersdel($sid, $c_id)) {
					$_SESSION['error_string'] .= _('Member has been deleted')." ("._('Mobile').": ".$member['mobile'].", "._('name').": ".$member['name'].")<br />";
				} else {
					$_SESSION['error_string'] .= _('Fail to delete member')." ("._('Mobile').": ".$member['mobile'].", "._('name').": ".$member['name'].")<br />";
				}
			}
		} else {
			$_SESSION['error_string'] = _('Receiver number does not exists');
		}
		header("Location: index.php?app=menu&inc=feature_sms_survey&route=members&op=members&sid=".$sid);
		exit();
		break;
}

?>