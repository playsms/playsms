<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

if ($poll_id = $_REQUEST['poll_id']) {
	if (! ($poll_id = dba_valid(_DB_PREF_.'_featurePoll', 'poll_id', $poll_id))) {
		auth_block();
	}
}

switch (_OP_) {
	case "sms_poll_list":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage poll')."</h2>
			"._button('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_add', _('Add SMS poll'));
		$content .= "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>";
		if (auth_isadmin()) {
			$content .= "
				<th width=20%>"._('Keyword')."</th>
				<th width=40%>"._('Title')."</th>
				<th width=20%>"._('User')."</th>
				<th width=10%>"._('Status')."</th>
				<th width=10%>"._('Action')."</th>";
		} else {
			$content .= "
				<th width=20%>"._('Keyword')."</th>
				<th width=60%>"._('Title')."</th>
				<th width=10%>"._('Status')."</th>
				<th width=10%>"._('Action')."</th>";
		}
		$content .= "
			</tr></thead>
			<tbody>";
		$i=0;
		if (! auth_isadmin()) {
			$query_user_only = "WHERE uid='$uid'";
		}
		$db_query = "SELECT * FROM "._DB_PREF_."_featurePoll ".$query_user_only." ORDER BY poll_id";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = user_uid2username($db_row['uid'])) {
				$poll_status = "<a href=\""._u('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_status&poll_id='.$db_row['poll_id'].'&ps=1')."\"><span class=status_disabled /></a>";
				if ($db_row['poll_enable']) {
					$poll_status = "<a href=\""._u('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_status&poll_id='.$db_row['poll_id'].'&ps=0')."\"><span class=status_enabled /></a>";
				}
				$action = "<a href=\""._u('index.php?app=menu&inc=feature_sms_poll&route=view&op=list&poll_id='.$db_row['poll_id'])."\">".$icon_config['view']."</a>&nbsp;";
				$action .= "<a href=\""._u('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_edit&poll_id='.$db_row['poll_id'])."\">".$icon_config['edit']."</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS poll with all its choices and votes ?')." ("._('keyword').": ".$db_row['poll_keyword'].")','"._u('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_del&poll_id='.$db_row['poll_id'])."')\">".$icon_config['delete']."</a>";
				if (auth_isadmin()) {
					$option_owner = "<td>$owner</td>";
				}
				$i++;
				$content .= "
					<tr>
						<td>".$db_row['poll_keyword']."</td>
						<td>".$db_row['poll_title']."</td>
						".$option_owner."
						<td>$poll_status</td>
						<td>$action</td>
					</tr>";
			}
		}
		$content .= "
			</tbody>
			</table>
			</div>
			"._button('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_add', _('Add SMS poll'));
		_p($content);
		break;
	case "sms_poll_view":
		$db_query = "SELECT poll_keyword FROM "._DB_PREF_."_featurePoll WHERE poll_id='$poll_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$poll_keyword = $db_row['poll_keyword'];
		header("Location: index.php?app=webservices&ta=sms_poll&keyword=".$poll_keyword);
		exit();
		break;
	case "sms_poll_edit":
		$db_query = "SELECT * FROM "._DB_PREF_."_featurePoll WHERE poll_id='$poll_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_poll_title = $db_row['poll_title'];
		$edit_poll_keyword = $db_row['poll_keyword'];
		$edit_poll_message_valid = $db_row['poll_message_valid'];
		$edit_poll_message_invalid = $db_row['poll_message_invalid'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage poll')."</h2>
			<h3>"._('Edit SMS poll')."</h3>
			<form action=index.php?app=menu&inc=feature_sms_poll&op=sms_poll_edit_yes method=post>
			"._CSRF_FORM_."
			<input type=hidden name=poll_id value=\"$poll_id\">
			<input type=hidden name=edit_poll_keyword value=\"$edit_poll_keyword\">
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>"._('SMS poll keyword')."</td><td>$edit_poll_keyword</td>
			</tr>
			<tr>
				<td>"._('SMS poll title')."</td><td><input type=text size=30 maxlength=100 name=edit_poll_title value=\"$edit_poll_title\"></td>
			</tr>
			<tr>
				<td>"._('Reply message on valid answer')."</td><td><input type=text size=30 maxlength=100 name=\"edit_poll_message_valid\" value=\"$edit_poll_message_valid\"></td>
			</tr>
			<tr>
				<td>"._('Reply message on invalid answer')."</td><td><input type=text size=30 maxlength=100 name=\"edit_poll_message_invalid\" value=\"$edit_poll_message_invalid\"></td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			<br />
			<h3>"._('Edit SMS poll choices')."</h3>";
		$db_query = "SELECT choice_id,choice_title,choice_keyword FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' ORDER BY choice_keyword";
		$db_result = dba_query($db_query);
		$content .= "
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=20%>"._('Choice keyword')."</th>
				<th width=70%>"._('Title')."</th>
				<th width=10%>"._('Action')."</th>
			</tr></thead>
			<tbody>";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$choice_id = $db_row['choice_id'];
			$choice_keyword = $db_row['choice_keyword'];
			$choice_title = $db_row['choice_title'];
			$i++;
			$content .= "
				<tr>
					<td>$choice_keyword</td>
					<td>$choice_title</td>
					<td><a href=\"javascript:ConfirmURL('"._('Are you sure you want to delete choice ?')." ("._('title').": ".addslashes($choice_title).", "._('keyword').": ".$choice_keyword.")','"._u('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_choice_del&poll_id='.$poll_id.'&choice_id='.$choice_id)."');\">".$icon_config['delete']."</a></td>
				</tr>";	
		}
		$content .= "
			</tbody>
			</table>
			</div>
			<br />
			<p>"._('Add choice to this poll')."
			<form action=\"index.php?app=menu&inc=feature_sms_poll&op=sms_poll_choice_add\" method=post>
			"._CSRF_FORM_."
			<input type=hidden name=poll_id value=\"$poll_id\">
			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td class=label-sizer>"._('Choice keyword')."</td><td><input type=text size=3 maxlength=10 name=add_choice_keyword></td>
			</tr>
			<tr>
				<td>"._('Choice title')."</td><td><input type=text size=30 maxlength=250 name=add_choice_title></td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Add')."\">
			</form>
			"._back('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_list');
		_p($content);
		break;
	case "sms_poll_edit_yes":
		$edit_poll_keyword = strtoupper($_POST['edit_poll_keyword']);
		$edit_poll_title = $_POST['edit_poll_title'];
		$edit_poll_message_valid = $_POST['edit_poll_message_valid'];
		$edit_poll_message_invalid = $_POST['edit_poll_message_invalid'];
		if ($poll_id && $edit_poll_title && $edit_poll_keyword && $edit_poll_message_valid && $edit_poll_message_invalid) {
			$db_query = "
				UPDATE "._DB_PREF_."_featurePoll
				SET c_timestamp='".mktime()."',poll_title='$edit_poll_title',poll_keyword='$edit_poll_keyword', poll_message_valid='$edit_poll_message_valid', poll_message_invalid='$edit_poll_message_invalid'
				WHERE poll_id='$poll_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS poll with has been saved')." ("._('keyword').": $edit_poll_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_poll&op=sms_poll_edit&poll_id=$poll_id");
		exit();
		break;
	case "sms_poll_status":
		$ps = $_REQUEST['ps'];
		$db_query = "UPDATE "._DB_PREF_."_featurePoll SET c_timestamp='".mktime()."',poll_enable='$ps' WHERE poll_id='$poll_id'";
		$db_result = @dba_affected_rows($db_query);
		if ($db_result > 0) {
			$_SESSION['error_string'] = _('SMS poll status has been changed');
		}
		header("Location: index.php?app=menu&inc=feature_sms_poll&op=sms_poll_list");
		exit();
		break;
	case "sms_poll_del":
		$db_query = "SELECT poll_keyword FROM "._DB_PREF_."_featurePoll WHERE poll_id='$poll_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($poll_keyword = $db_row['poll_keyword']) {
			$db_query = "DELETE FROM "._DB_PREF_."_featurePoll WHERE poll_id='$poll_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS poll with all its messages has been deleted')." ("._('keyword').": $poll_keyword)";
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_poll&op=sms_poll_list");
		exit();
		break;
	case "sms_poll_choice_add":
		$add_choice_title = $_POST['add_choice_title'];
		$add_choice_keyword = strtoupper($_POST['add_choice_keyword']);
		if ($poll_id && $add_choice_title && $add_choice_keyword) {
			$db_query = "SELECT choice_id FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' AND choice_keyword='$add_choice_keyword'";
			$db_result = @dba_num_rows($db_query);
			if (!$db_result) {
				$db_query = "
					INSERT INTO "._DB_PREF_."_featurePoll_choice 
					(poll_id,choice_title,choice_keyword)
					VALUES ('$poll_id','$add_choice_title','$add_choice_keyword')";
				if ($db_result = @dba_insert_id($db_query)) {
					$_SESSION['error_string'] = _('Choice has been added')." ("._('keyword').": $add_choice_keyword)";
				}
			} else {
				$_SESSION['error_string'] = _('Choice already exists')." ("._('keyword').": $add_choice_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_poll&op=sms_poll_edit&poll_id=$poll_id");
		exit();
		break;
	case "sms_poll_choice_del":
		$choice_id = $_REQUEST['choice_id'];
		$db_query = "SELECT choice_keyword FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' AND choice_id='$choice_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$choice_keyword = $db_row['choice_keyword'];
		$_SESSION['error_string'] = _('Fail to delete SMS poll choice')." ("._('keyword').": $choice_keyword)";
		if ($poll_id && $choice_id && $choice_keyword) {
			$db_query = "DELETE FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' AND choice_id='$choice_id'";
			if (@dba_affected_rows($db_query)) {
				$db_query = "DELETE FROM "._DB_PREF_."_featurePoll_log WHERE poll_id='$poll_id' AND choice_id='$choice_id'";
				dba_query($db_query);
				$_SESSION['error_string'] = _('SMS poll choice and all its voters has been deleted')." ("._('keyword').": $choice_keyword)";
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_poll&op=sms_poll_edit&poll_id=$poll_id");
		exit();
		break;
	case "sms_poll_add":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage poll')."</h2>
			<h3>"._('Add SMS poll')."</h3>
			<form action=\"index.php?app=menu&inc=feature_sms_poll&op=sms_poll_add_yes\" method=\"post\">
			"._CSRF_FORM_."
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>"._('SMS poll keyword')."</td><td><input type=text size=10 maxlength=10 name=add_poll_keyword value=\"$add_poll_keyword\"></td>
			</tr>
			<tr>
				<td>"._('SMS poll title')."</td><td><input type=text size=30 maxlength=100 name=add_poll_title value=\"$add_poll_title\"></td>
			</tr>	 
			<tr>
				<td>"._('Reply message on valid answer')."</td><td><input type=text size=30 maxlength=100 name=\"add_poll_message_valid\" value=\"$add_poll_message_valid\"></td>
			</tr>	
			<tr>
				<td>"._('Reply message when invalid answer')."</td><td><input type=text size=30 maxlength=100 name=\"add_poll_message_invalid\" value=\"$add_poll_message_invalid\"></td>
			</tr>	   
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			"._back('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_list');
		_p($content);
		break;
	case "sms_poll_add_yes":
		$add_poll_keyword = strtoupper($_POST['add_poll_keyword']);
		$add_poll_title = $_POST['add_poll_title'];
		$add_poll_message_valid = $_POST['add_poll_message_valid'];
		$add_poll_message_invalid = $_POST['add_poll_message_invalid'];
		if ($add_poll_title && $add_poll_keyword && $add_poll_message_valid && $add_poll_message_invalid) {
			if (checkavailablekeyword($add_poll_keyword)) {
				$db_query = "
					INSERT INTO "._DB_PREF_."_featurePoll (uid,poll_keyword,poll_title,poll_message_valid,poll_message_invalid)
					VALUES ('$uid','$add_poll_keyword','$add_poll_title','$add_poll_message_valid','$add_poll_message_invalid')";
				if ($new_poll_id = @dba_insert_id($db_query)) {
					$_SESSION['error_string'] = _('SMS poll has been added')." ("._('keyword').": $add_poll_keyword)";
				} else {
					$_SESSION['error_string'] = _('Fail to add SMS poll')." ("._('keyword').": $add_poll_keyword)";
				}
			} else {
				$_SESSION['error_string'] = _('SMS poll already exists, reserved or use by other feature')." ("._('keyword').": $add_poll_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		if ($new_poll_id) {
			header("Location: index.php?app=menu&inc=feature_sms_poll&op=sms_poll_edit&poll_id=".$new_poll_id);
		} else {
			header("Location: index.php?app=menu&inc=feature_sms_poll&op=sms_poll_add");
		}
		exit();
		break;
}

?>