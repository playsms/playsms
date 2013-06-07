<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

if ($board_id = $_REQUEST['board_id']) {
	if (! ($board_id = dba_valid(_DB_PREF_.'_featureBoard', 'board_id', $board_id))) {
		forcenoaccess();
	}
}

if ($route = $_REQUEST['route']) {
	$fn = $apps_path['plug'].'/feature/sms_board/'.$route.'.php';
	$fn = core_sanitize_path($fn);
	if (file_exists($fn)) {
		include $fn;
		unset($_SESSION['error_string']);
		exit();
	}
}

switch ($op) {
	case "sms_board_list":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage board')."</h2>
			<p>"._button('index.php?app=menu&inc=feature_sms_board&op=sms_board_add', _('Add SMS board'))."
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=sortable>
			<thead><tr>";
		if (isadmin()) {
			$content .= "
				<th width=4>*</th>
				<th width=20%>"._('Keyword')."</th>
				<th width=50%>"._('Forward')."</th>
				<th width=20%>"._('User')."</th>
				<th width=10%>"._('Action')."</th>";
		} else {
			$content .= "
				<th width=4>*</th>
				<th width=20%>"._('Keyword')."</th>
				<th width=70%>"._('Forward')."</th>
				<th width=10%>"._('Action')."</th>";
		}
		$content .= "
			</tr></thead>
			<tbody>";
		if (! isadmin()) {
			$query_user_only = "WHERE uid='$uid'";
		}
		$db_query = "SELECT * FROM "._DB_PREF_."_featureBoard ".$query_user_only." ORDER BY board_keyword";
		$db_result = dba_query($db_query);
		$i=0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = uid2username($db_row['uid'])) {
				$i++;
				$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$action = "<a href=index.php?app=menu&inc=feature_sms_board&route=view&op=list&board_id=".$db_row['board_id'].">$icon_view</a>&nbsp;";
				$action .= "<a href=index.php?app=menu&inc=feature_sms_board&op=sms_board_edit&board_id=".$db_row['board_id'].">$icon_edit</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS board with all its messages ?')." ("._('keyword').": ".$db_row['board_keyword'].")','index.php?app=menu&inc=feature_sms_board&op=sms_board_del&board_id=".$db_row['board_id']."')\">$icon_delete</a>";
				if (isadmin()) {
					$option_owner = "<td class=$td_class>$owner</td>";
				}
				$content .= "
					<tr>
						<td class=$td_class>&nbsp;$i.</td>
						<td class=$td_class>".$db_row['board_keyword']."</td>
						<td class=$td_class>".$db_row['board_forward_email']."</td>
						".$option_owner."
						<td class=$td_class align=center>$action</td>
					</tr>";
			}
		}
		$content .= "</tbody></table>
			<p>"._button('index.php?app=menu&inc=feature_sms_board&op=sms_board_add', _('Add SMS board'));
		echo $content;
		break;
	case "sms_board_edit":
		$db_query = "SELECT * FROM "._DB_PREF_."_featureBoard WHERE board_id='$board_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_board_keyword = $db_row['board_keyword'];
		$edit_email = $db_row['board_forward_email'];
		$edit_template = $db_row['board_pref_template'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage board')."</h2>
			<h3>"._('Edit SMS board')."</h3>
			<p>
			<form action=index.php?app=menu&inc=feature_sms_board&op=sms_board_edit_yes method=post>
			<input type=hidden name=board_id value=$board_id>
			<input type=hidden name=edit_board_keyword value=$edit_board_keyword>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td width=200>"._('SMS board keyword')."</td><td width=5>:</td><td><b>$edit_board_keyword</b></td>
			</tr>
			<tr>
				<td>"._('Forward to email')."</td><td>:</td><td><input type=text size=30 name=edit_email value=\"$edit_email\"></td>
			</tr>
			<tr>
				<td colspan=3>
				"._('Template').":
				<br><textarea name=edit_template rows=5 cols=60>$edit_template</textarea>
				</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			"._b('index.php?app=menu&inc=feature_sms_board&op=sms_board_list');
		echo $content;
		break;
	case "sms_board_edit_yes":
		$edit_board_keyword = $_POST['edit_board_keyword'];
		$edit_email = $_POST['edit_email'];
		$edit_template = $_POST['edit_template'];
		if ($board_id) {
			if (!$edit_template) {
				$edit_template = "<font color=black size=-1><b>{SENDER}</b></font><br>";
				$edit_template .= "<font color=black size=-2><i>{DATETIME}</i></font><br>";
				$edit_template .= "<font color=black size=-1>{MESSAGE}</font>";
			}
			$db_query = "
				UPDATE "._DB_PREF_."_featureBoard
				SET c_timestamp='".mktime()."',board_forward_email='$edit_email',board_pref_template='$edit_template'
				WHERE board_id='$board_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS board has been saved')." ("._('keyword').": $edit_board_keyword)";
			} else {
				$_SESSION['error_string'] = _('Fail to edit SMS board')." ("._('keyword').": $edit_board_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_board&op=sms_board_edit&board_id=$board_id");
		exit();
		break;
	case "sms_board_del":
		$db_query = "SELECT board_keyword FROM "._DB_PREF_."_featureBoard WHERE board_id='$board_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$board_keyword = $db_row['board_keyword'];
		if ($board_keyword) {
			$db_query = "DELETE FROM "._DB_PREF_."_featureBoard WHERE board_keyword='$board_keyword'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS board with all its messages has been deleted')." ("._('keyword').": $board_keyword)";
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_board&op=sms_board_list");
		exit();
		break;
	case "sms_board_add":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage board')."</h2>
			<h3>"._('Add SMS board')."</h3>
			<p>
			<form action=index.php?app=menu&inc=feature_sms_board&op=sms_board_add_yes method=post>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td width=200>"._('SMS board keyword')."</td><td width=5>:</td><td><input type=text size=30 maxlength=30 name=add_board_keyword value=\"$add_board_keyword\"></td>
			</tr>
			<tr>
				<td colspan=3><p><b>"._('Leave them empty if you dont know what to fill in these boxes below')."</b></td>
			</tr>
			<tr>
				<td>"._('Forward to email')."</td><td>:</td><td><input type=text size=30 name=add_email value=\"$add_email\"></td>
			</tr>
			<tr>
				<td colspan=3>
					<p>"._('Template').":
					<p><textarea name=add_template rows=5 cols=60>$add_template</textarea>
				</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			"._b('index.php?app=menu&inc=feature_sms_board&op=sms_board_list');
		echo $content;
		break;
	case "sms_board_add_yes":
		$add_board_keyword = strtoupper($_POST['add_board_keyword']);
		$add_email = $_POST['add_email'];
		$add_template = $_POST['add_template'];
		if ($add_board_keyword) {
			if (checkavailablekeyword($add_board_keyword)) {
				if (!$add_template) {
					$add_template = "<font color=black size=-1><b>{SENDER}</b></font><br>";
					$add_template .= "<font color=black size=-2><i>{DATETIME}</i></font><br>";
					$add_template .= "<font color=black size=-1>{MESSAGE}</font>";
				}
				$db_query = "
					INSERT INTO "._DB_PREF_."_featureBoard (uid,board_keyword,board_forward_email,board_pref_template)
					VALUES ('$uid','$add_board_keyword','$add_email','$add_template')";
				if ($new_uid = @dba_insert_id($db_query)) {
					$_SESSION['error_string'] = _('SMS board has been added')." ("._('keyword').": $add_board_keyword)";
				} else {
					$_SESSION['error_string'] = _('Fail to add SMS board')." ("._('keyword').": $add_board_keyword)";
				}
			} else {
				$_SESSION['error_string'] = _('SMS keyword already exists, reserved or use by other feature')." ("._('keyword').": $add_board_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_board&op=sms_board_add");
		exit();
		break;
}

?>