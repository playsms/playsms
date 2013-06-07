<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

if ($autoreply_id = $_REQUEST['autoreply_id']) {
	if (! ($autoreply_id = dba_valid(_DB_PREF_.'_featureAutoreply', 'autoreply_id', $autoreply_id))) {
		forcenoaccess();
	}
}

switch ($op) {
	case "sms_autoreply_list":
		$content .= "
			<h2>"._('Manage autoreply')."</h2>
			<p>"._button('index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_add', _('Add SMS autoreply'));
		$content .= "<table cellpadding=1 cellspacing=2 border=0 width=100% class=sortable><thead><tr>";
		if (isadmin()) {
			$content .= "
				<th width=4>*</th>
				<th width=20%>"._('Keyword')."</th>
				<th width=70%>"._('User')."</th>
				<th width=10%>"._('Action')."</th>";
		} else {
			$content .= "
				<th width=4>*</th>
				<th width=90%>"._('Keyword')."</th>
				<th width=10%>"._('Action')."</th>";
		}
		$content .= "</tr></thead><tbody>";
		if (! isadmin()) {
			$query_user_only = "WHERE uid='$uid'";
		}
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply ".$query_user_only." ORDER BY autoreply_keyword";
		$db_result = dba_query($db_query);
		$i=0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = uid2username($db_row['uid'])) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$action = "<a href=index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id=".$db_row['autoreply_id'].">$icon_manage</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS autoreply ?')." ("._('keyword').": ".$db_row['autoreply_keyword'].")','index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_del&autoreply_id=".$db_row['autoreply_id']."')\">$icon_delete</a>";
			if (isadmin()) {
				$option_owner = "<td class=$td_class>$owner</td>";
			}
			$content .= "
				<tr>
					<td class=$td_class>&nbsp;$i.</td>
					<td class=$td_class>".$db_row['autoreply_keyword']."</td>
					".$option_owner."
					<td class=$td_class align=center>$action</td>
				</tr>";
			}
		}
		$content .= "</tbody>
			</table>
			<p>"._button('index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_add', _('Add SMS autoreply'));
		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "sms_autoreply_manage":
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$manage_autoreply_keyword = $db_row['autoreply_keyword'];
		$o_uid = $db_row['uid'];
		$content .= "
			<h2>"._('Manage autoreply')."</h2>
			<p>
			<p>"._('SMS autoreply keyword').": <b>$manage_autoreply_keyword</b>
			<p>"._button('index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_scenario_add&autoreply_id='.$autoreply_id, _('Add SMS autoreply scenario'))."
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=sortable><thead><tr>";
		if (isadmin()) {
			$content .= "
				<th width=4>*</th>
				<th width=20%>"._('Param')."</th>
				<th width=50%>"._('Return')."</th>
				<th width=20%>"._('User')."</th>
				<th width=10%>"._('Action')."</th>";
		} else {
			$content .= "
				<th width=4>*</th>
				<th width=20%>"._('Param')."</th>
				<th width=70%>"._('Return')."</th>
				<th width=10%>"._('Action')."</th>";
		}
		$content .= "</tr></thead><tbody>";
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id' ORDER BY autoreply_scenario_param1";
		$db_result = dba_query($db_query);
		$j=0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = uid2username($o_uid)) {
				$j++;
				$td_class = ($j % 2) ? "box_text_odd" : "box_text_even";
				$list_of_param = "";
				for ($i=1;$i<=7;$i++) {
					$list_of_param .= $db_row['autoreply_scenario_param'.$i]."&nbsp;";
				}
				$action = "<a href=index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_scenario_edit&autoreply_id=$autoreply_id&autoreply_scenario_id=".$db_row['autoreply_scenario_id'].">$icon_edit</a>";
				$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete this SMS autoreply scenario ?')."','index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_scenario_del&autoreply_id=$autoreply_id&autoreply_scenario_id=".$db_row['autoreply_scenario_id']."')\">$icon_delete</a>";
				if (isadmin()) {
					$option_owner = "<td class=$td_class>$owner</td>";
				}
				$content .= "
					<tr>
						<td class=$td_class>&nbsp;$j.</td>
						<td class=$td_class>$list_of_param</td>
						<td class=$td_class>".$db_row['autoreply_scenario_result']."</td>
						".$option_owner."
						<td class=$td_class align=center>$action</td>
					</tr>";
			}
		}
		$content .= "
			</table>
			</form>
			<p>"._button('index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_scenario_add&autoreply_id='.$autoreply_id, _('Add SMS autoreply scenario'))."
			<p>"._b('index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_list');
		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "sms_autoreply_del":
		$db_query = "SELECT autoreply_keyword FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($keyword_name = $db_row['autoreply_keyword']) {
			$db_query = "DELETE FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_keyword='$keyword_name'";
			if (@dba_affected_rows($db_query)) {
				$db_query = "DELETE FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id'";
				dba_query($db_query);
				$_SESSION['error_string'] = _('SMS autoreply has been deleted')." ("._('keyword').": $keyword_name)";
			} else {
				$_SESSION['error_string'] = _('Fail to delete SMS autoreply')." ("._('keyword').": $keyword_name";
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_list");
		exit();
		break;
	case "sms_autoreply_add":
		$content .= "
			<h2>"._('Manage autoreply')."</h2>
			<h3>"._('Add SMS autoreply')."</h3>
			<p>
			<form action=index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_add_yes method=post>
			<p>"._('SMS autoreply keyword')." : <input type=text size=10 maxlength=10 name=add_autoreply_keyword value=\"$add_autoreply_keyword\">
			<p><input type=submit class=button value="._('Save').">
			</form>
			<p>"._b('index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_list');
		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "sms_autoreply_add_yes":
		$add_autoreply_keyword = trim(strtoupper($_POST['add_autoreply_keyword']));
		if ($add_autoreply_keyword) {
			if (checkavailablekeyword($add_autoreply_keyword)) {
				$db_query = "INSERT INTO "._DB_PREF_."_featureAutoreply (uid,autoreply_keyword) VALUES ('$uid','$add_autoreply_keyword')";
				if ($new_uid = @dba_insert_id($db_query)) {
					$_SESSION['error_string'] = _('SMS autoreply keyword has been added')." ("._('keyword').": $add_autoreply_keyword)";
				} else {
					$_SESSION['error_string'] = _('Fail to add SMS autoreply')." ("._('keyword').": $add_autoreply_keyword)";
				}
			} else {
				$_SESSION['error_string'] = _('SMS keyword already exists, reserved or use by other feature')." ("._('keyword').": $add_autoreply_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_add");
		exit();
		break;
		// scenario
	case "sms_autoreply_scenario_del":
		$_SESSION['error_string'] = _('Fail to delete SMS autoreply scenario');
		if ($autoreply_id && ($autoreply_scenario_id = $_REQUEST['autoreply_scenario_id'])) {
			$db_query = "DELETE FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id' AND autoreply_scenario_id='$autoreply_scenario_id'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS autoreply scenario has been deleted');
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id=".$autoreply_id."");
		exit();
		break;
	case "sms_autoreply_scenario_add":
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$autoreply_keyword = $db_row['autoreply_keyword'];
		$content .= "
			<h2>"._('Manage autoreply')."</h2>
			<h3>"._('Add SMS autoreply scenario')."</h3>
			<p>
			<p>"._('SMS autoreply keyword').": <b>$autoreply_keyword</b>
			<p>
			<form action=index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_scenario_add_yes method=post>
			<input type=hidden name=autoreply_id value=\"$autoreply_id\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>";
		for ($i=1;$i<=7;$i++) {
			$content .= "
				<tr>
					<td width=190>"._('SMS autoreply scenario parameter')." $i</td><td>:</td><td><input type=text size=20 maxlength=20 name=\"add_autoreply_scenario_param".$i."\" value=\"".${"add_autoreply_scenario_param".$i}."\">\n</td>
				</tr>";
		}
		$content .= "
			<tr>
				<td>"._('SMS autoreply scenario replies with')."</td><td>:</td><td><input type=text size=40 name=add_autoreply_scenario_result value=\"$add_autoreply_scenario_result\"></td>
			</tr>
			</table>
			<p><input type=submit class=button value="._('Save').">
			</form>
			<p>"._b('index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id='.$autoreply_id);
		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "sms_autoreply_scenario_add_yes":
		$add_autoreply_scenario_result = $_POST['add_autoreply_scenario_result'];
		for ($i=1;$i<=7;$i++) {
			${"add_autoreply_scenario_param".$i} = trim(strtoupper($_POST['add_autoreply_scenario_param'.$i]));
		}
		if ($add_autoreply_scenario_result) {
			for ($i=1;$i<=7;$i++) {
				$autoreply_scenario_param_list .= "autoreply_scenario_param$i,";
			}
			for ($i=1;$i<=7;$i++) {
				$autoreply_scenario_keyword_param_entry .= "'".${"add_autoreply_scenario_param".$i}."',";
			}
			$db_query = "
				INSERT INTO "._DB_PREF_."_featureAutoreply_scenario 
				(autoreply_id,".$autoreply_scenario_param_list."autoreply_scenario_result) VALUES ('$autoreply_id',$autoreply_scenario_keyword_param_entry'$add_autoreply_scenario_result')";
			if ($new_uid = dba_insert_id($db_query)) {
				$_SESSION['error_string'] = _('SMS autoreply scenario has been added');
			} else {
				$_SESSION['error_string'] = _('Fail to add SMS autoreply scenario');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_scenario_add&autoreply_id=$autoreply_id");
		exit();
		break;
	case "sms_autoreply_scenario_edit":
		$autoreply_scenario_id = $_REQUEST['autoreply_scenario_id'];
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$autoreply_keyword = $db_row['autoreply_keyword'];
		$content .= "
			<h2>"._('Manage autoreply')."</h2>
			<h3>"._('Edit SMS autoreply scenario')."</h3>
			<p>
			<p>"._('SMS autoreply keyword').": <b>$autoreply_keyword</b>
			<p>
			<form action=index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_scenario_edit_yes method=post>
			<input type=hidden name=autoreply_id value=\"$autoreply_id\">
			<input type=hidden name=autoreply_scenario_id value=\"$autoreply_scenario_id\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>";
		$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id' AND autoreply_scenario_id='$autoreply_scenario_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		for ($i=1;$i<=7;$i++) {
			${"edit_autoreply_scenario_param".$i} = $db_row['autoreply_scenario_param'.$i];
		}
		for ($i=1;$i<=7;$i++) {
			$content .= "
				<tr>
					<td width=190>"._('SMS autoreply scenario parameter')." $i</td><td>:</td><td><input type=text size=20 maxlength=20 name=edit_autoreply_scenario_param$i value=\"".${"edit_autoreply_scenario_param".$i}."\">\n</td>
				</tr>";
		}
		$edit_autoreply_scenario_result = $db_row['autoreply_scenario_result'];
		$content .= "
			<tr>
				<td>"._('SMS autoreply scenario replies with')."</td><td>:</td><td><input type=text size=40 name=edit_autoreply_scenario_result value=\"$edit_autoreply_scenario_result\"></td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			<p>"._b('index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id='.$autoreply_id);
		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "sms_autoreply_scenario_edit_yes":
		$autoreply_scenario_id = $_POST['autoreply_scenario_id'];
		$edit_autoreply_scenario_result = $_POST['edit_autoreply_scenario_result'];
		for ($i=1;$i<=7;$i++) {
			${"edit_autoreply_scenario_param".$i} = trim(strtoupper($_POST['edit_autoreply_scenario_param'.$i]));
		}
		if ($edit_autoreply_scenario_result) {
			for ($i=1;$i<=7;$i++) {
				$autoreply_scenario_param_list .= "autoreply_scenario_param".$i."='".${"edit_autoreply_scenario_param".$i}."',";
			}
			$db_query = "
				UPDATE "._DB_PREF_."_featureAutoreply_scenario 
				SET c_timestamp='".mktime()."',".$autoreply_scenario_param_list."autoreply_scenario_result='$edit_autoreply_scenario_result' 
				WHERE autoreply_id='$autoreply_id' AND autoreply_scenario_id='$autoreply_scenario_id'";
			if ($db_result = @dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS autoreply scenario has been edited');
			} else {
				$_SESSION['error_string'] = _('Fail to edit SMS autoreply scenario');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_autoreply&op=sms_autoreply_scenario_edit&autoreply_id=$autoreply_id&autoreply_scenario_id=$autoreply_scenario_id");
		exit();
		break;
}

?>