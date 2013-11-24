<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

if ($board_id = $_REQUEST['board_id']) {
	if (! ($board_id = dba_valid(_DB_PREF_.'_featureBoard', 'board_id', $board_id))) {
		forcenoaccess();
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
			<table class=playsms-table-list>
			<thead><tr>";
		if (isadmin()) {
			$content .= "
				<th width=20%>"._('Keyword')."</th>
				<th width=50%>"._('Forward')."</th>
				<th width=20%>"._('User')."</th>
				<th width=10%>"._('Action')."</th>";
		} else {
			$content .= "
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
				$action = "<a href=index.php?app=menu&inc=feature_sms_board&route=view&op=list&board_id=".$db_row['board_id'].">".$core_config['icon']['view']."</a>&nbsp;";
				$action .= "<a href=index.php?app=menu&inc=feature_sms_board&op=sms_board_edit&board_id=".$db_row['board_id'].">".$core_config['icon']['edit']."</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS board with all its messages ?')." ("._('keyword').": ".$db_row['board_keyword'].")','index.php?app=menu&inc=feature_sms_board&op=sms_board_del&board_id=".$db_row['board_id']."')\">".$core_config['icon']['delete']."</a>";
				if (isadmin()) {
					$option_owner = "<td>$owner</td>";
				}
				$i++;
				$tr_class = ($i % 2) ? "row_odd" : "row_even";
				$content .= "
					<tr class=$tr_class>
						<td>".$db_row['board_keyword']."</td>
						<td>".$db_row['board_forward_email']."</td>
						".$option_owner."
						<td>$action</td>
					</tr>";
			}
		}
		$content .= "</tbody></table>
			"._button('index.php?app=menu&inc=feature_sms_board&op=sms_board_add', _('Add SMS board'));
		echo $content;
		break;
	case "sms_board_edit":
		$db_query = "SELECT * FROM "._DB_PREF_."_featureBoard WHERE board_id='$board_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_board_keyword = $db_row['board_keyword'];
		$edit_email = $db_row['board_forward_email'];
		$edit_css = $db_row['board_css'];
		$edit_template = $db_row['board_pref_template'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage board')."</h2>
			<h3>"._('Edit SMS board')."</h3>
			<form action=index.php?app=menu&inc=feature_sms_board&op=sms_board_edit_yes method=post>
			<input type=hidden name=board_id value=$board_id>
			<input type=hidden name=edit_board_keyword value=$edit_board_keyword>
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>"._('SMS board keyword')."</td><td>".$edit_board_keyword."</td>
			</tr>
			<tr>
				<td>"._('Forward to email')."</td><td><input type=text size=30 name=edit_email value=\"".$edit_email."\"></td>
			</tr>
			<tr>
				<td>"._('CSS URL')."</td><td><input type=text size=30 name=edit_css value=\"".$edit_css."\"></td>
			</tr>
			<tr>
				<td colspan=2>
					"._('Row template')."<br />
					<textarea name=edit_template style='width: 35em; height: 8em;'>".$edit_template."</textarea>
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
		$edit_css = $_POST['edit_css'];
		$edit_template = $_POST['edit_template'];
		if ($board_id) {
			if (!$edit_template) {
				$edit_template = "<div class=sms_board_row>\n";
				$edit_template .= "\t<div class=sender>{SENDER}</div>\n";
				$edit_template .= "\t<div class=datetime>{DATETIME}</div>\n";
				$edit_template .= "\t<div class=message>{MESSAGE}</div>\n";
				$edit_template .= "</div>\n";
			}
			$db_query = "
				UPDATE "._DB_PREF_."_featureBoard
				SET c_timestamp='".mktime()."',board_forward_email='$edit_email',board_css='$edit_css',board_pref_template='$edit_template'
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
			<form action=index.php?app=menu&inc=feature_sms_board&op=sms_board_add_yes method=post>
			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td class=label-sizer>"._('SMS board keyword')."</td><td><input type=text size=30 maxlength=30 name=add_board_keyword value=\"$add_board_keyword\"></td>
			</tr>
			<tr>
				<td>"._('Forward to email')."</td><td><input type=text size=30 name=add_email value=\"$add_email\"></td>
			</tr>
			<tr>
				<td>"._('CSS URL')."</td><td><input type=text size=30 name=add_css value=\"$add_css\"></td>
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
		$add_css = $_POST['add_css'];
		$add_template = $_POST['add_template'];
		if ($add_board_keyword) {
			if (checkavailablekeyword($add_board_keyword)) {
				if (!$add_template) {
					$add_template = "<div class=sms_board_row>\n";
					$add_template .= "\t<div class=sender>{SENDER}</div>\n";
					$add_template .= "\t<div class=datetime>{DATETIME}</div>\n";
					$add_template .= "\t<div class=message>{MESSAGE}</div>\n";
					$add_template .= "</div>\n";
				}
				$db_query = "
					INSERT INTO "._DB_PREF_."_featureBoard (uid,board_keyword,board_forward_email,board_css,board_pref_template)
					VALUES ('$uid','$add_board_keyword','$add_email','$add_css','$add_template')";
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