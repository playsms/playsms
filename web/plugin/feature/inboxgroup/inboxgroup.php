<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php
if(!isadmin()){forcenoaccess();};

// routing
if (($route = $_REQUEST['route']) && ($route == 'members')) {
	include $core_config['apps_path']['plug'].'/feature/inboxgroup/inboxgroup_members.php';
	exit();
}
if (($route = $_REQUEST['route']) && ($route == 'catchall')) {
	include $core_config['apps_path']['plug'].'/feature/inboxgroup/inboxgroup_catchall.php';
	exit();
}

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
	case 'list':
		$content = '<h2>'._('Group inbox').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= "
			<form method='post' action='index.php?app=menu&inc=feature_inboxgroup&op=add'>
			<p><input class='button' type='submit' value='"._('Add receiver number')."'></p>
			</form>
			<table width='100%' cellpadding='1' cellspacing='2' border='0' class='sortable'>
			<tr>
				<td class='box_title' width='4'>*</td>
				<td class='box_title' width='20%'>"._('Receiver number')."</td>
				<td class='box_title' width='40%'>"._('Keywords')."</td>
				<td class='box_title' width='10%'>"._('Members')."</td>
				<td class='box_title' width='10%'>"._('Catch-all')."</td>
				<td class='box_title' width='10%'>"._('Status')."</td>
				<td class='box_title' width='10%'>"._('Action')."</td>
			</tr>
		";
		$data = inboxgroup_getdataall();
		for ($i=0;$i<count($data);$i++) {
			$c_rid = $data[$i]['id'];
			$c_status = $data[$i]['status'] ? "<a href='index.php?app=menu&inc=feature_inboxgroup&op=disable&rid=".$c_rid."'><font color='green'>"._('enabled')."</font></a>" : "<a href='index.php?app=menu&inc=feature_inboxgroup&op=enable&rid=".$c_rid."'><font color='red'>"._('disabled')."</font></a>";
			$c_members = count(inboxgroup_getmembers($c_rid));
			$c_members = "<a href='index.php?app=menu&inc=feature_inboxgroup&route=members&op=members&rid=".$c_rid."'>".$c_members."</a>";
			$c_catchall = count(inboxgroup_getcatchall($c_rid));
			$c_catchall = "<a href='index.php?app=menu&inc=feature_inboxgroup&route=catchall&op=catchall&rid=".$c_rid."'>".$c_catchall."</a>";
			$c_action = "<a href='index.php?app=menu&inc=feature_inboxgroup&op=edit&rid=".$c_rid."'>".$icon_edit."</a> ";
			$c_action .= "<a href='index.php?app=menu&inc=feature_inboxgroup&op=del&rid=".$c_rid."'>".$icon_delete."</a> ";
			$td_class = (($i+1) % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr class='".$td_class."'>
					<td align='center'>".($i+1).".</td>
					<td align='center'>".$data[$i]['in_receiver']."</td>
					<td align='center'>".$data[$i]['keywords']."</td>
					<td align='center'>".$c_members."</td>
					<td align='center'>".$c_catchall."</td>
					<td align='center'>".$c_status."</td>
					<td align='center'>".$c_action."</td>
				</tr>
			";
		}
		$content .= "
			</table>
			<form method='post' action='index.php?app=menu&inc=feature_inboxgroup&op=add'>
			<p><input class='button' type='submit' value='"._('Add receiver number')."'></p>
			</form>
		";
		echo $content;
		break;
	case 'add':
		$content = '<h2>'._('Group inbox').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Add receiver number').'</h3><p />';
		$content .= "
			<form method='post' action='index.php?app=menu&inc=feature_inboxgroup&op=add_submit'>
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Receiver number')."</td><td>:</td><td><input type='text' name='in_receiver' maxlength='20' size='20'> &nbsp; ("._('For example a short code').")</td></tr>
			<tr><td>"._('Keywords')."</td><td>:</td><td><input type='text' name='keywords' maxlength='100' size='40'> &nbsp; ("._('Seperate with comma for multiple items').")</td></tr>
			<tr><td>"._('Description')."</td><td>:</td><td><input type='text' name='description' maxlength='100' size='40'></td></tr>
			</table>
			<p><input class='button' type='submit' value='"._('Submit')."'></p>
			</form>
		";
		echo $content;
		break;
	case 'add_submit':
		$in_receiver = $_REQUEST['in_receiver'];
		$keywords = $_REQUEST['keywords'];
		$description = $_REQUEST['description'];
		if ($in_receiver && $keywords && $description) {
			if (inboxgroup_dataadd($in_receiver, $keywords, $description)) {
				$error_string = _('Receiver number has been added')." ("._('Number').": ".$in_receiver.")";
			} else {
				$error_string = _('Fail to add receiver number')." ("._('Number').": ".$in_receiver.")";
			}
		} else {
			$error_string = _('You must fill all fields');
		}
		$errid = logger_set_error_string($error_string);
		header("Location: index.php?app=menu&inc=feature_inboxgroup&op=add&errid=".$errid);
		break;
	case 'edit':
		$content = '<h2>'._('Group inbox').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Edit receiver number').'</h3><p />';
		$rid = $_REQUEST['rid'];
		$data = inboxgroup_getdatabyid($rid);
		$in_receiver = $data['in_receiver'];
		$keywords = $data['keywords'];
		$description = $data['description'];
		$selected_1 = $data['exclusive'] ? 'selected' : '' ;
		if (! $selected_1) { $selected_0 = 'selected'; };
		$option_exclusive = "<option value='1' ".$selected_1.">"._('yes')."</option><option value='0' ".$selected_0.">"._('no')."</option>";
		$content .= "
			<form method='post' action='index.php?app=menu&inc=feature_inboxgroup&op=edit_submit'>
			<input type='hidden' name='rid' value='$rid'>
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Receiver number')."</td><td>:</td><td>".$in_receiver."</td></tr>
			<tr><td>"._('Keywords')."</td><td>:</td><td><input type='text' name='keywords' value='$keywords' maxlength='100' size='40'> &nbsp; ("._('Seperate with comma for multiple items').")</td></tr>
			<tr><td>"._('Description')."</td><td>:</td><td><input type='text' name='description' value='$description' maxlength='100' size='40'></td></tr>
			<tr><td>"._('Exclusive')."</td><td>:</td><td><select name='exclusive'>".$option_exclusive."</select> ("._('Restrict sender to regular members or catch-all members only').")</td></tr>
			</table>
			<p><input class='button' type='submit' value='"._('Submit')."'></p>
			</form>
		";
		echo $content;
		break;
	case 'edit_submit':
		$rid = $_REQUEST['rid'];
		$keywords = $_REQUEST['keywords'];
		$description = $_REQUEST['description'];
		$exclusive = $_REQUEST['exclusive'];
		$data = inboxgroup_getdatabyid($rid);
		$in_receiver = $data['in_receiver'];
		if ($rid && $in_receiver && $keywords && $description) {
			if (inboxgroup_dataedit($rid, $keywords, $description, $exclusive)) {
				$error_string = _('Receiver number has been edited')." ("._('Number').": ".$in_receiver.")";
			} else {
				$error_string = _('Fail to edit receiver number')." ("._('Number').": ".$in_receiver.")";
			}
		} else {
			$error_string = _('You must fill all fields');
		}
		$errid = logger_set_error_string($error_string);
		header("Location: index.php?app=menu&inc=feature_inboxgroup&op=edit&rid=".$rid."&errid=".$errid);
		break;
	case 'del':
		$content = '<h2>'._('Group inbox').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= '<h3>'._('Delete receiver number').'</h3><p />';
		$rid = $_REQUEST['rid'];
		$data = inboxgroup_getdatabyid($rid);
		$in_receiver = $data['in_receiver'];
		$keywords = $data['keywords'];
		$description = $data['description'];
		$c_members = count(inboxgroup_getmembers($rid));
		$c_members = "<a href='index.php?app=menu&inc=feature_inboxgroup&route=members&op=members&rid=".$rid."'>".$c_members."</a>";
		$c_catchall = count(inboxgroup_getcatchall($rid));
		$c_catchall = "<a href='index.php?app=menu&inc=feature_inboxgroup&route=catchall&op=catchall&rid=".$rid."'>".$c_catchall."</a>";
		$c_status = $data
		['status'] ? "<font color='green'>"._('enabled')."</font>" : "<font color='red'>"._('disabled')."</font>";
		$content .= "
			<table cellpadding='1' cellspacing='2' border='0'>
			<tr><td>"._('Receiver number')."</td><td>:</td><td>".$in_receiver."</td></tr>
			<tr><td>"._('Keywords')."</td><td>:</td><td>".$keywords."</td></tr>
			<tr><td>"._('Description')."</td><td>:</td><td>".$description."</td></tr>
			<tr><td>"._('Members')."</td><td>:</td><td>".$c_members."</td></tr>
			<tr><td>"._('Catch-all')."</td><td>:</td><td>".$c_catchall."</td></tr>
			<tr><td>"._('Status')."</td><td>:</td><td>".$c_status."</td></tr>
			</table>
			<p>"._('Are you sure you want to delete this receiver number ?')."</p>
			<form method='post' action='index.php?app=menu&inc=feature_inboxgroup&op=del_submit'>
			<input type='hidden' name='rid' value='$rid'>
			<p><input class='button' type='submit' value='"._('Yes')."'></p>
			</form>
			<form method='post'action='index.php?app=menu&inc=feature_inboxgroup&op=list'>
			<p><input class='button' type='submit' value='"._('Cancel')."'></p>
			</form>
		";
		echo $content;
		break;
	case 'del_submit':
		$rid = $_REQUEST['rid'];
		$data = inboxgroup_getdatabyid($rid);
		$in_receiver = $data['in_receiver'];
		if ($rid && $in_receiver) {
			if (inboxgroup_datadel($rid)) {
				$error_string = _('Receiver number has been deleted')." ("._('Number').": ".$in_receiver.")";
			} else {
				$error_string = _('Fail to delete receiver number')." ("._('Number').": ".$in_receiver.")";
			}
		} else {
			$error_string = _('Receiver number does not exists');
		}
		$errid = logger_set_error_string($error_string);
		header("Location: index.php?app=menu&inc=feature_inboxgroup&op=list&rid=".$rid."&errid=".$errid);
		break;
	case 'enable':
		$rid = $_REQUEST['rid'];
		$data = inboxgroup_getdatabyid($rid);
		$in_receiver = $data['in_receiver'];
		if ($rid && $in_receiver) {
			if (inboxgroup_dataenable($rid)) {
				$error_string = _('Receiver number has been enabled')." ("._('Number').": ".$in_receiver.")";
			} else {
				$error_string = _('Fail to enable receiver number')." ("._('Number').": ".$in_receiver.")";
			}
		} else {
			$error_string = _('Receiver number does not exists');
		}
		$errid = logger_set_error_string($error_string);
		header("Location: index.php?app=menu&inc=feature_inboxgroup&op=list&rid=".$rid."&errid=".$errid);
		break;
	case 'disable':
		$rid = $_REQUEST['rid'];
		$data = inboxgroup_getdatabyid($rid);
		$in_receiver = $data['in_receiver'];
		if ($rid && $in_receiver) {
			if (inboxgroup_datadisable($rid)) {
				$error_string = _('Receiver number has been disabled')." ("._('Number').": ".$in_receiver.")";
			} else {
				$error_string = _('Fail to disable receiver number')." ("._('Number').": ".$in_receiver.")";
			}
		} else {
			$error_string = _('Receiver number does not exists');
		}
		$errid = logger_set_error_string($error_string);
		header("Location: index.php?app=menu&inc=feature_inboxgroup&op=list&rid=".$rid."&errid=".$errid);
		break;
}

?>