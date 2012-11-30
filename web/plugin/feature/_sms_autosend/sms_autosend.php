<?php
defined('_SECURE_') or die('Forbidden');
/*
 * Created on Apr 30, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
if (!valid()) {
	forcenoaccess();
};
?>
<script
	type="text/javascript"
	src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/datetimepicker.js"></script>
<?php
switch ($op) {
	case "sms_autosend_list" :
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
								<h2>"._('Manage autosend')."</h2>
								<p>
								<input type=button value=\""._('Add SMS autosend')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_add')\" class=\"button\" />
								<p>
							";
		if (!isadmin()) {
			$query_user_only = "WHERE uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureAutosend $query_user_only ORDER BY autosend_id";
		$db_result = dba_query($db_query);
		$content .= "
							<table cellpadding=1 cellspacing=2 border=0 width=100%>
							<tr>
							    <td class=box_title width=5>*</td>
							    <td class=box_title width=40%>"._('Message')."</td>
								<td class=box_title width=10%>"._('Repeat')."</td>
							   	<td class=box_title width=10%>"._('User')."</td>	
								<td class=box_title width=20%>"._('Send to')."</td>
								<td class=box_title width=10%>"._('Status')."</td>
							    <td class=box_title>"._('Action')."</td>
							</tr>
							";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = uid2username($db_row['uid'])) {
				$i++;
				$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$autosend_status = "<font color=red>"._('Disabled')."</font>";
				$message = $db_row['autosend_message'];
				$send_to = $db_row['autosend_number'];
				$time = $db_row['autosend_time'];

				$db_query = "SELECT autosend_id FROM " . _DB_PREF_ . "_featureAutosend_time WHERE autosend_id = '".$db_row['autosend_id']."'";
				$num_rows = dba_num_rows($db_query);

				if ($num_rows > "1") {
					$repeat = $num_rows;
				} else {
					$repeat = _('Once');
				}

				if ($db_row['autosend_enable']) {
					$autosend_status = "<font color=green>"._('Enabled')."</font>";
				}
				$action = "<a href=index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_view&autosend_id=".$db_row['autosend_id'].">$icon_view</a>&nbsp;";
				$action .= "<a href=index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_edit&autosend_id=".$db_row['autosend_id'].">$icon_edit</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS autosend message ?')."','index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_del&autosend_id=".$db_row['autosend_id']."')\">$icon_delete</a>";
				$content .= "
							<tr>
								<td class=$td_class>&nbsp;$i.</td>
								<td class=$td_class>$message</td>
								<td class=$td_class>$repeat</td>
								<td class=$td_class>$owner</td>
								<td class=$td_class>$send_to</td>	
								<td class=$td_class>$autosend_status</td>									
								<td class=$td_class align=center>$action</td>
							</tr>";
			}
		}
		$content .= "</table>";
		echo $content;
		echo "
								<p>
								<input type=button value=\""._('Add SMS autosend')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_add')\" class=\"button\" />
								</p>
								";
		break;

	case "sms_autosend_view" :
		$autosend_id = $_REQUEST['autosend_id'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "<h2>"._('SMS autosend View')."</h2>";
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureAutosend where autosend_id='$autosend_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$owner = uid2username($db_row['uid']);
		$send_to = $db_row['autosend_number'];

		$autosend_status = "<font color=red>"._('Disabled')."</font>";
		$message = $db_row['autosend_message'];

		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureAutosend_time where autosend_id='$autosend_id'";
		$db_result = dba_query($db_query);
		$num_rows = dba_num_rows($db_query);
		$db_row = dba_fetch_array($db_result);
		$time = $db_row['autosend_time'];

		if ($num_rows > "1") {
			$repeat = $num_rows;
		} else {
			$repeat = _('Once');
		}

		$content .= "
								<table cellpadding=1 cellspacing=2 border=0 width=100%>
									<tr>
									<td width=150>Owner</td><td width=5>:</td><td>$owner</td>		    
									</tr>
									<tr>
									<td width=150>Message</td><td width=5>:</td><td>$message</td>		    
									</tr>
									<tr>
									<td width=150>Send to</td><td width=5>:</td><td>$send_to</td>		    
									</tr>
									<tr>
									<td>Repeat send</td><td width=5>:</td><td>$repeat</td>		    
									</tr>";
		$db_result = dba_query($db_query);
		$i = 1;
		while ($db_row = dba_fetch_array($db_result)) {
			$content .= "<tr>
									<td>"._('Time')." $i</td><td>:</td><td> ".$db_row['autosend_time']."</td>		    
									</tr>
									";
			$i++;
		}
		echo $content;
		break;

	case "sms_autosend_edit" :
		$autosend_id = $_REQUEST['autosend_id'];

		$db_query = "SELECT uid,time_id," . _DB_PREF_ . "_featureAutosend.autosend_id, autosend_message,autosend_number,autosend_time
									FROM " . _DB_PREF_ . "_featureAutosend
									INNER JOIN " . _DB_PREF_ . "_featureAutosend_time
									ON " . _DB_PREF_ . "_featureAutosend.autosend_id =  " . _DB_PREF_ . "_featureAutosend_time.autosend_id
									WHERE " . _DB_PREF_ . "_featureAutosend.autosend_id = '$autosend_id'
									";
		$db_result = dba_query($db_query);

		$db_row = dba_fetch_array($db_result);
		$num_rows = dba_num_rows($db_query);
		$edit_autosend_message = $db_row['autosend_message'];
		$edit_autosend_number = $db_row['autosend_number'];
		$edit_time_id = $db_row['time_id'];
		$edit_autosend_time = $db_row['autosend_time'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
								<h2>"._('Edit SMS autosend')."</h2>
						    <form action=index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_edit_yes method=post>
						    	<input type=hidden name=edit_autosend_id value=$autosend_id>	
							<table width=100% cellpadding=1 cellspacing=2 border=0>
								<tr>
								<td width=150>"._('Message')."</td><td width=5>:</td><td><input type=text size=50 maxlength=200 name=edit_autosend_message value=\"$edit_autosend_message\"></td>
								</tr>
								<tr>
								<td>"._('Repeat send')."</td><td>:</td><td><b>$num_rows</b> times</td>
								</tr>
								<tr>
								<td>"._('Send to')."</td><td>:</td><td><input type=text value=\"$edit_autosend_number\" name=edit_autosend_number size=30></td>		
								</tr>";

		$j = 1;
		$a = 0;
		while ($a < 4) {
			$db_query = "SELECT time_id, autosend_time FROM " . _DB_PREF_ . "_featureAutosend_time WHERE autosend_id = '$autosend_id' order by time_id limit $a,1";
			$db_result = dba_query($db_query);
			$db_row = dba_fetch_array($db_result);
			$edit_autosend_time = $db_row['autosend_time'];
			$content .=
			"<tr>
	  			<td>"._('Sending time')." $j  </td><td>:</td><td><input type=hidden name=edit_time_id[$a] value=\"".$db_row['time_id']."\">
	  				<input type=\"text\" id=\"demo[$a]\" maxlength=\"25\" size=\"20\" name=edit_autosend_time[$a] value=\"$edit_autosend_time\"><a href=\"javascript:NewCal('demo[$a]','yyyymmdd',true,24,'arrow')\">$icon_calendar</a>
	  			</td>
			</tr>";	
			$a++;
			$j++;
		}
		$content .= "</table>
									<p><input type=submit class=button value=\""._('Update')."\">
									</form>";
		echo $content;

		$db_query = "SELECT autosend_enable FROM " . _DB_PREF_ . "_featureAutosend WHERE autosend_id='$autosend_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$autosend_status = "<b><font color=red>"._('Disabled')."</font></b>";
		if ($db_row['autosend_enable']) {
			$autosend_status = "<b><font color=green>"._('Enabled')."</font></b>";
		}
		$content = "
							<h2>"._('Enable or disable this autosend')."</h2>
							<p>
							<p>"._('Current status').": $autosend_status
							<p>"._('What do you want to do ?')."
							<p>- <a href=\"index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_status&autosend_id=$autosend_id&ps=1\">"._('I want to enable this autosend')."</a>
							<p>- <a href=\"index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_status&autosend_id=$autosend_id&ps=0\">"._('I want to disable this autosend')."</a>
							<br>
							";
		echo $content;
		break;

	case "sms_autosend_edit_yes" :
		$edit_autosend_id = $_POST['edit_autosend_id'];
		$edit_autosend_message = $_POST['edit_autosend_message'];
		$edit_autosend_number = $_POST['edit_autosend_number'];
		$edit_autosend_time = $_POST['edit_autosend_time'];
		$edit_time_id = $_POST['edit_time_id'];
		if ($edit_autosend_id && $edit_autosend_message && $edit_autosend_number) {
			if (!isadmin()) {
				$query_user_only = "AND uid='$uid'";
			}
			$db_query = "
							        UPDATE " . _DB_PREF_ . "_featureAutosend
							        SET c_timestamp='" . mktime() . "',autosend_message='$edit_autosend_message',autosend_number='$edit_autosend_number'
									WHERE autosend_id='$edit_autosend_id' $query_user_only
							    	";
			$update_msg = @ dba_affected_rows($db_query);
			$i = 0;
			foreach ($edit_time_id as $value) {
				if ($value) {
					$db_query = "UPDATE " . _DB_PREF_ . "_featureAutosend_time SET c_timestamp='" . mktime() . "',autosend_time='$edit_autosend_time[$i]' WHERE time_id = '$value'";
					$update_time = @ dba_affected_rows($db_query);
					if (!$edit_autosend_time[$i]) {
						$db_query = "DELETE FROM " . _DB_PREF_ . "_featureAutosend_time WHERE time_id = '$value' $query_user_only";
						$delete = @dba_affected_rows($db_query);
					}
				} else
				if ($edit_autosend_time[$i]) {
					$db_query = "INSERT INTO " . _DB_PREF_ . "_featureAutosend_time (autosend_id,autosend_time) VALUES ('$edit_autosend_id','$edit_autosend_time[$i]')";
					$insert = dba_insert_id($db_query);
				}
				$i++;
			}
			if ($update_time | $insert) {
				$_SESSION['error_string'] = _('Autosend time has been saved');
			}

		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_edit&autosend_id=$edit_autosend_id");
		exit();
		break;

	case "sms_autosend_status" :
		$autosend_id = $_REQUEST['autosend_id'];
		$ps = $_REQUEST['ps'];
		$db_query = "UPDATE " . _DB_PREF_ . "_featureAutosend SET c_timestamp='" . mktime() . "',autosend_enable='$ps' WHERE autosend_id='$autosend_id'";
		$db_result = @ dba_affected_rows($db_query);
		if ($db_result > 0) {
			$_SESSION['error_string'] = _('SMS autosend status has been changed');
		}
		header("Location: index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_edit&autosend_id=$autosend_id");
		exit();
		break;

	case "sms_autosend_del" :
		$autosend_id = $_REQUEST['autosend_id'];
		$db_query = "SELECT autosend_id FROM " . _DB_PREF_ . "_featureAutosend WHERE autosend_id='$autosend_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$autosend_id = $db_row['autosend_id'];
		if ($autosend_id) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureAutosend WHERE autosend_id='$autosend_id'";
			if (@ dba_affected_rows($db_query)) {
				$db_query = "DELETE FROM " . _DB_PREF_ . "_featureAutosend_time WHERE  autosend_id='$autosend_id'";
				if ($db_result = dba_affected_rows($db_query)) {
					$_SESSION['error_string'] = _('SMS autosend has been deleted');
				}
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_list");
		exit();
		break;

	case "sms_autosend_add" :
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
							<h2>"._('Add SMS autosend')."</h2><p>
							<input type=button value=\""._('Add time field')."\" onClick=\"javascript:newField()\" class=\"button\" />
							<p>								
							<script language=\"javascript\" type=\"text/javascript\" src=\"datetime/datetimepicker.js\"></script>
						    <form action=index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_add_yes method=post>
							<table width=100% cellpadding=1 cellspacing=2 border=0>
								<tr>
								<td width=150>"._('Message')."</td><td width=5>:</td><td><input type=text size=50 maxlength=200 name=add_autosend_message value=\"$add_autosend_message\"></td>
								</tr>
								<tr>
								<td>"._('Send to')."</td><td>:</td><td><input type=readonly name=add_autosend_number value=$add_autosend_number></td>
								</tr>";

		$i = 0;
		$j = 1;
		while($i<4) {
			$content .=
			"<tr>
	  			<td>"._('Sending time')." $j</td><td>:</td><td>
	  				<input onFocus=\"this.blur()\" type=\"text\" id=\"field[$i]\" maxlength=\"25\" size=\"20\" name=add_autosend_time[$i] value=$add_autosend_time[$i]><a href=\"javascript:NewCal('field[$i]','yyyymmdd',true,24,'arrow')\">$icon_calendar</a>
	  			</td>
			</tr>";	
			$i++;
			$j++;
		}



		$content 	.= "</table>
				<p><input type=submit class=button value="._('Add').">
				</form>
			";
		echo $content;
		break;

	case "sms_autosend_add_yes" :
		$add_autosend_message = $_POST['add_autosend_message'];
		$add_autosend_number = $_POST['add_autosend_number'];
		$add_autosend_time = $_POST['add_autosend_time'];
		if ($add_autosend_message && $add_autosend_number && $add_autosend_time) {
			$db_query = "
									INSERT INTO " . _DB_PREF_ . "_featureAutosend (uid,autosend_message, autosend_number)
									VALUES ('$uid','$add_autosend_message','$add_autosend_number')
									";
			if ($new_uid = @ dba_insert_id($db_query)) {

				foreach ($add_autosend_time as $value) {
					$db_query = "
									INSERT INTO " . _DB_PREF_ . "_featureAutosend_time (autosend_id, autosend_time)
									VALUES ('$new_uid','$value')
									";
					if ($value) {
						$insert = dba_insert_id($db_query);
					}
				}
				if ($insert) {
					$_SESSION['error_string'] = _('SMS autosend has been added');
				} else {
					$db_query = "DELETE FROM " . _DB_PREF_ . "_featureAutosend WHERE autosend_id = '".$db_row['autosend_id']."'";
					$delete = @ dba_affected_rows($db_query);
				}

			} else {
				$_SESSION['error_string'] = _('Fail to add SMS autosend');
			}

		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_autosend&op=sms_autosend_add");
		exit();
		break;
}
?>
