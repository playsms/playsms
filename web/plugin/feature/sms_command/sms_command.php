<?php
defined('_SECURE_') or die('Forbidden');
if (!valid()) { forcenoaccess(); };

switch ($op) {
	case "sms_command_alarm_group_add_yes":
		
		$group_code = $_POST['add_group_code'];
		if(isset($_GET['alarm_id'])){
			$alarm_id = $_GET['alarm_id'];	
			if($group_code){
				$uid = username2uid($username);
				$gpid = phonebook_groupcode2id($uid, $group_code);
				
				//Verify if group is private! If is private, verify if $uid = group owner
				if(private_group($gpid)){
					$user_id = gid2uid($gpid);
					if($user_id != $uid){
						$gpid = '';
					}
				}
				
				if($gpid){
					$db_query = "INSERT INTO "._DB_PREF_."_featureCommand_Alarm_group_id (alarm_id, gpid) VALUES ($alarm_id, $gpid)";
					if ($new_uid = @dba_insert_id($db_query))
					{
						$error_string = _('Alarm Group has been added')." ("._('Group Code')." `$group_code`)";
					}else{
						$error_string = _('Fail to add Alarm Group')."(1.1) ("._('Group Code').": `$group_code`)";
					}	
				}else{
					//$error_string = "alarmid: " . $alarm_id . " user: ".$username . " uid: ". $uid . "gpid:".$gpid . "gpid1:".$gpid1;
					$error_string = _('Fail to add Alarm Group')."(1.2) ("._('Group Code').": `$group_code`)";
				}
				
			}else{
				$error_string = _('You must fill all fields, correctly');	
			}
			
		}else{
			$error_string = _('Fail to add Alarm Group')."(2) ("._('Alarm Code').": `$group_code`)";
		}	
		
		header ("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_edit&alarm_id=$alarm_id&err=".urlencode($error_string));
		break;
		
	case "sms_command_alarm_group_del":
		$group_id = $_REQUEST['group_id'];
		$alarm_id  = $_REQUEST['alarm_id'];
		
		$db_query = "DELETE FROM "._DB_PREF_."_featureCommand_Alarm_group_id WHERE alarm_id='$alarm_id' AND gpid='$group_id'";
		if (@dba_affected_rows($db_query))
		{
			$error_string = _('SMS Alarm Group has been deleted');
		}
		else
		{
			$error_string = _('Fail to delete Alarm Group');
		}
		
		header ("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_edit&alarm_id=$alarm_id&err=".urlencode($error_string));
		break;
		
	case "sms_command_alarm_list":
		$command_id = -1;
		if(isset($_GET['command_id']))
			$command_id = $_GET['command_id'];
			
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage alarm')."</h2>
			<p>
			<input type=button value=\""._('Add SMS alarm')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_add&command_id=$command_id')\" class=\"button\" />
			";
		if (!isadmin())
		{
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "SELECT * FROM "._DB_PREF_."_featureCommand_Alarm WHERE command_id=$command_id $query_user_only ORDER BY alarm_id DESC";
		$db_result = dba_query($db_query);
		$content .= "
			<table cellpadding=1 cellspacing=2 border=0 width=100%>
			<tr>
				<td class=box_title width=5>*</td>
				<td class=box_title width=100>"._('Name')."</td>
				<td class=box_title width=100>"._('Alarm Message')."</td>	
				<td class=box_title width=100>"._('Alarm Numbers')."</td>
				<td class=box_title width=100>"._('User')."</td>	
				<td class=box_title width=75>"._('Action')."</td>
			</tr>	
			";	
		$i=0;
		$maxlen=50;
		while ($db_row = dba_fetch_array($db_result))
		{
			$alarm_id = $db_row['alarm_id'];
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$owner = uid2username($db_row['uid']);
			$action = "<a href=index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_edit&alarm_id=".$alarm_id.">$icon_edit</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS alarm?')." ("._('Name').": `".$db_row['alarm_name']."`)','index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_del&alarm_id=".$alarm_id."')\">$icon_delete</a>";
			
			$alarm_numbers = getAlarmNumbers_output($alarm_id);
			
			$content .= "
				<tr>
				<td class=$td_class>&nbsp;$i.</td>
				<td class=$td_class>".$db_row['alarm_name']."</td>
				<td class=$td_class>".$db_row['alarm_msg']."</td>
				<td class=$td_class>$alarm_numbers</td>	
				<td class=$td_class>$owner</td>	
				<td class=$td_class align=center>$action</td>
				</tr>";
		}

		
		$content .= "
			</table>
			";
				echo $content;
				echo "
				<p>
				<input type=button value=\""._('Add SMS alarm')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_add&command_id=$command_id')\" class=\"button\" />
			";
		break;
	case "sms_command_alarm_edit":
		if(isset($_GET['alarm_id'])){
			$alarm_id = $_GET['alarm_id'];	
			
			$db_query = "SELECT * FROM "._DB_PREF_."_featureCommand_Alarm WHERE alarm_id='$alarm_id'";
			$db_result = dba_query($db_query);
			$db_row = dba_fetch_array($db_result);
			
			$edit_alarm_name = $db_row['alarm_name'];
			$edit_alarm_msg = $db_row['alarm_msg'];
			
			$edit_alarm_min_value = $db_row['alarm_min_value'];
			$edit_alarm_max_value = $db_row['alarm_max_value'];
			
			if ($err = $_GET['err']) {
				$content = "<div class=error_string>$err</div>";
			}
			
			$max_length = $core_config['main']['max_sms_length'];
			$content .= "
				<h2>"._('Edit SMS alarm')."</h2>
				<p>
				<form name=\"form_alarm\" id=\"form_alarm\" action=index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_edit_yes&alarm_id=$alarm_id method=post>
				<p>"._('Alarm name').": <input type=text size=25 maxlength=100 name=\"add_alarm_name\" value=\"$edit_alarm_name\" />
				<p>"._('Min (Integer) Value').": <input type=text size=25 maxlength=100 name=\"add_alarm_min_value\" value=\"$edit_alarm_min_value\" /> ("._('included').")
				<p>"._('Max (Integer) Value').": <input type=text size=25 maxlength=100 name=\"add_alarm_max_value\" value=\"$edit_alarm_max_value\" /> ("._('included').")
				<p>"._('Alarm message').":	
					<br><textarea name=\"txt_MSG\" id=\"txt_MSG\" value=\"\" cols=\"40\" rows=\"5\" 
						onClick=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
						onkeypress=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
						onblur=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
						onKeyUp=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\"	
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_alarm', 'txt_MSG' );\" 
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_alarm');\">$edit_alarm_msg</textarea>
						
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" id=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_alarm.txt_MSG.focus();\" readonly>
					<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length'] ."\" name=\"hiddcount\" id=\"hiddcount\"> 
						<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length_unicode']."\" name=\"hiddcount_unicode\" id=\"hiddcount_unicode\"> 
					
				<p><input type=submit class=button value=\""._('Save')."\">
				</form>
				";
			
			echo $content;

			$content = "
				<h2>"._('Edit Alarm Numbers')."</h2>
				<p>
			";
			$db_query = "SELECT contact_number, id FROM "._DB_PREF_."_featureCommand_Alarm_contacts WHERE alarm_id=$alarm_id";
			$db_result = dba_query($db_query);
			$content .= "
				<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr>
					<td class=box_title width=25>*</td>
					<td class=box_title>"._('Alarm Number')."</td>
					<td class=box_title width=75>"._('Action')."</td>
				</tr>
				";
			$i=0;
			while ($db_row = dba_fetch_array($db_result))
			{
				$i++;
				$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$contact_number = $db_row['contact_number'];
				$id_number = $db_row['id'];
				$content .= "
					<tr>
						<td class=$td_class>&nbsp;$i.</td>
						<td class=$td_class>$contact_number</td>
						<td class=$td_class align=center>
							<a href=\"javascript:ConfirmURL('"._('Are you sure you want to delete alarm number ?')." ("._('Alarm Number').": ".$contact_number.")','index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_number_del&id_number=$id_number&alarm_id=$alarm_id');\">$icon_delete</a>
						</td>
					</tr>";	    
			}
			
			$content .= "
				<tr>
					<td class=box_title width=25>*</td>
					<td class=box_title>"._('Group Code')."</td>
					<td class=box_title width=75>"._('Action')."</td>
				</tr>
				";
			$db_query = "SELECT g.gpid, g.gp_name, g.gp_code FROM "._DB_PREF_."_toolsSimplephonebook_group as g, "._DB_PREF_."_featureCommand_Alarm_group_id as a  WHERE a.alarm_id=$alarm_id AND a.gpid = g.gpid" ;
			$db_result = dba_query($db_query);
			$i=0;
			while ($db_row = dba_fetch_array($db_result))
			{
				$i++;
				$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$group_name = $db_row['gp_name'];
				$group_code = $db_row['gp_code'];
				$group_id = $db_row['gpid'];
				
				$content .= "
					<tr>
						<td class=$td_class>&nbsp;$i.</td>
						<td class=$td_class>$group_code</td>
						<td class=$td_class align=center>
							<a href=\"javascript:ConfirmURL('"._('Are you sure you want to delete alarm group?')." ("._('Group Code').": ".$group_code.")','index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_group_del&group_id=$group_id&alarm_id=$alarm_id');\">$icon_delete</a>
						</td>
					</tr>";	    
			}
			
			
			$content .= "</table>";
			
			echo $content;
			
			$content = "
				<br /><br />
				<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr style=\"vertical-align:top;\">
					<td>
						<h2>"._('Add Alarm Number')."</h2>
						<p>
						<form name=\"form_alarm\" id=\"form_alarm\" action=index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_number_add_yes&alarm_id=$alarm_id method=post>
							<p>"._('Alarm Number:')." &nbsp;<input type=text size=13 maxlength=15 name=\"add_alarm_number\" />&nbsp;("._('International format').")
							<p><input type=submit class=button value=\""._('Add')."\">
						</form>
					</td>
					<td>
						<h2>"._('Add Alarm Group Numbers')."</h2>
						<p>
						<form name=\"form_alarm_group\" id=\"form_alarm_group\" action=index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_group_add_yes&alarm_id=$alarm_id method=post>
							<p>"._('Group Code').": &nbsp;<input type=text size=10 maxlength=10 name=\"add_group_code\" />&nbsp;
							<p><input type=submit class=button value=\""._('Add')."\">
						</form>
					</td>
				</tr>";
			
			echo $content;
		}

		break;
	case "sms_command_alarm_edit_yes":
	
		$alarm_name = $_POST['add_alarm_name'];
		if(isset($_GET['alarm_id'])){
			$alarm_id = $_GET['alarm_id'];

			$alarm_msg = $_POST['txt_MSG'];
			$alarm_min_value = $_POST['add_alarm_min_value'];
			$alarm_max_value = $_POST['add_alarm_max_value'];
			
			if ($alarm_name && $alarm_msg && validatevaluetype_int($alarm_min_value) && validatevaluetype_int($alarm_max_value))
			{
				//echo 'entrei';
				$val1 = intval($alarm_min_value);
				$val2 = intval($alarm_max_value);
				if($val2 >= $val1){
					
					$db_query = "UPDATE "._DB_PREF_."_featureCommand_Alarm SET c_timestamp='".mktime()."',alarm_name='$alarm_name',
							alarm_msg='$alarm_msg', alarm_min_value='$alarm_min_value', alarm_max_value='$alarm_max_value'
							WHERE alarm_id=$alarm_id";
					if (@dba_affected_rows($db_query))
					{
						$error_string = _('SMS Alarm has been saved')." ("._('Alarm Name').": `$alarm_name`)";
					}
					else
					{
						$error_string = _('Fail to save SMS Alarm')." ("._('Alarm Name').": `$alarm_name`)";
					}
	
				}else{
					$error_string = _('Max value must be equal or greater than Min value.');	
				}
				
			}
			else
			{
				$error_string = _('You must fill all fields, correctly');
			}
			
		}else{
			$error_string = _('Fail to update SMS Alarm')." ("._('Alarm name').": `$alarm_name`)";
		}	
		
		header ("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_edit&alarm_id=$alarm_id&err=".urlencode($error_string));
		break;
		
	case "sms_command_alarm_del":
		$alarm_id = $_GET['alarm_id'];
		//GET COMMAND ID FOR LATER TREATMENT
		$db_query = "SELECT command_id FROM "._DB_PREF_."_featureCommand_Alarm WHERE alarm_id=$alarm_id";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$command_id = $db_row['command_id'];
		//NOW WE CAN DELETE ALARM
		$db_query = "DELETE FROM "._DB_PREF_."_featureCommand_Alarm WHERE alarm_id='$alarm_id'";
		if (@dba_affected_rows($db_query))
		{
			//AND ALARM NUMBERS
			$db_query = "DELETE FROM "._DB_PREF_."_featureCommand_Alarm_contacts WHERE alarm_id='$alarm_id'";
			if (@dba_affected_rows($db_query))
			{
				$error_string = _('SMS Alarm has been deleted');
			}else{
				$error_string = _('SMS Alarm has been deleted, but action doesn\'t delete Alarm Numbers.');
			}
		}
		else
		{
			$error_string = _('Fail to delete Alarm');
		}
		
		//NOW WE CHECK IF COMMAND HAS NO ALARMS
		$db_query = "SELECT alarm_id FROM "._DB_PREF_."_featureCommand_Alarm WHERE command_id=$command_id";
		if (!($db_result = dba_num_rows($db_query)))
		{
			//UPDATE TABLE playsms_featureCommand
			$db_query = "UPDATE "._DB_PREF_."_featureCommand SET with_alarm=FALSE WHERE command_id=$command_id";
			if (@dba_affected_rows($db_query))
			{
				$error_string .= '<br>' . _(' # Command ID: ') . $command_id . _('got updated!');
			}
		}
		
		header ("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_list&command_id=$command_id&err=".urlencode($error_string));
		break;
	case "sms_command_alarm_number_del":
		$id_number = $_REQUEST['id_number'];
		$alarm_id  = $_REQUEST['alarm_id'];
		$db_query = "DELETE FROM "._DB_PREF_."_featureCommand_Alarm_contacts WHERE id='$id_number'";
		if (@dba_affected_rows($db_query))
		{
			$error_string = _('SMS Alarm Number has been deleted');
		}
		else
		{
			$error_string = _('Fail to delete Alarm Number');
		}
		
		header ("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_edit&alarm_id=$alarm_id&err=".urlencode($error_string));
		break;
	case "sms_command_alarm_number_add_yes":
		$alarm_number = $_POST['add_alarm_number'];
		if(isset($_GET['alarm_id'])){
			$alarm_id = $_GET['alarm_id'];	
			if($alarm_number){
				$db_query = "INSERT INTO "._DB_PREF_."_featureCommand_Alarm_contacts (alarm_id, contact_number) VALUES ($alarm_id,'$alarm_number')";
				if ($new_uid = @dba_insert_id($db_query))
				{
					$error_string = _('Alarm Number has been added')." ("._('Alarm Number')." `$alarm_number`)";
				}else{
					$error_string = _('Fail to add Alarm Number')."(1) ("._('Alarm Number').": `$alarm_number`)";
				}
			}else{
				$error_string = _('You must fill all fields, correctly');	
			}
		}else{
			$error_string = _('Fail to add Alarm Number')."(2) ("._('Alarm number').": `$alarm_number`)";
		}	
		
		header ("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_edit&alarm_id=$alarm_id&err=".urlencode($error_string));
		break;
	case "sms_command_alarm_add":
		$command_id = -1;
		if(isset($_GET['command_id']))
			$command_id = $_GET['command_id'];
		$max_length = $core_config['main']['max_sms_length'];
		
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		
		$content .= "
	    <h2>"._('Add SMS alarm')."</h2>
	    <p>
	    <form name=\"form_alarm\" id=\"form_alarm\" action=index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_add_yes&command_id=$command_id method=post>
	    <p>"._('Alarm name').": <input type=text size=25 maxlength=100 name=\"add_alarm_name\" />
	    <p>"._('Min (Integer) Value').": <input type=text size=25 maxlength=100 name=\"add_alarm_min_value\" /> ("._('included').")
	    <p>"._('Max (Integer) Value').": <input type=text size=25 maxlength=100 name=\"add_alarm_max_value\" /> ("._('included').")
		<p>"._('Alarm message').":	
			
			<br><textarea name=\"txt_MSG\" id=\"txt_MSG\" value=\"\" cols=\"40\" rows=\"5\" 
				onClick=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
				onkeypress=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
				onblur=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
				onKeyUp=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\"	
				onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_alarm', 'txt_MSG' );\" 
				onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_alarm');\"></textarea>
			<br>
			<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" id=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_alarm.txt_MSG.focus();\" readonly>
            <input type=\"hidden\" value=\"".$core_config['main']['max_sms_length']."\" name=\"hiddcount\" id=\"hiddcount\"> 
            <input type=\"hidden\" value=\"".$core_config['main']['max_sms_length_unicode']."\" name=\"hiddcount_unicode\" id=\"hiddcount_unicode\"> 
            
	    <p><input type=submit class=button value=\""._('Add')."\">
	    </form>
	";
		echo $content;	
		
		break;
	case "sms_command_alarm_add_yes":

		if(isset($_GET['command_id'])){
			$command_id = $_GET['command_id'];
				
			$alarm_name = $_POST['add_alarm_name'];
			$alarm_msg = $_POST['txt_MSG'];
			$alarm_min_value = $_POST['add_alarm_min_value'];
			$alarm_max_value = $_POST['add_alarm_max_value'];
			
			if ($alarm_name && $alarm_msg && validatevaluetype_int($alarm_min_value) && validatevaluetype_int($alarm_max_value))
			{
				$val1 = intval($alarm_min_value);
				$val2 = intval($alarm_max_value);
				if($val2 >= $val1){
					
					if (checkavailablealarmname($alarm_name))
					{
						$db_query = "INSERT INTO "._DB_PREF_."_featureCommand_Alarm (uid,alarm_name,alarm_msg, command_id, alarm_min_value, alarm_max_value) VALUES ('$uid','$alarm_name','$alarm_msg', '$command_id', $alarm_min_value, $alarm_max_value)";
						if ($new_uid = @dba_insert_id($db_query))
						{
							$error_string = _('SMS alarm has been added')." ("._('Alarm name')." `$alarm_name`)";
							
							//UPDATE TABLE playsms_featureCommand
							$db_query = "UPDATE "._DB_PREF_."_featureCommand SET with_alarm=TRUE WHERE command_id=$command_id";
							if (@dba_affected_rows($db_query))
							{
								$error_string .= '<br>' . _(' # Command ID: ') . $command_id . _('got updated!');
							}
						}
						else
						{
							$error_string = _('Fail to add SMS alarm')." ("._('Alarm name').": `$alarm_name`)";
						}
					}
					else
					{
						$error_string = _('SMS alarm name already exists.')." ("._('Alarm name').": `$alarm_name`)";
					}
							
				}else{
					$error_string = _('Max value must be equal or greater than Min value.');	
				}
				
			}
			else
			{
				$error_string = _('You must fill all fields, correctly');
			}
			
		}else{
			$error_string = _('Fail to add SMS alarm')." ("._('Alarm name').": `$alarm_name`)";
		}	
		
		header ("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_add&command_id=$command_id&err=".urlencode($error_string));
		break;
    case "sms_command_list":
        if ($err = $_SESSION['error_string']) {
            $content = "<div class=error_string>$err</div>";
        }
        $content .= "
	    <h2>" . _('Manage command') . "</h2>";
        $content .= "<p>
	    <input type=button value=\"" . _('Add SMS command') . "\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_command&op=sms_command_add')\" class=\"button\" />
	    <p>" . _('SMS command exec path') . " : <b>" . $plugin_config['feature']['sms_command']['bin'] . "/</b>
	";
        if (!isadmin()) {
            $query_user_only = "WHERE uid='$uid'";
        }
        $db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCommand $query_user_only ORDER BY command_keyword";
        $db_result = dba_query($db_query);
        $content .= "
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=5>*</td>
        <td class=box_title width=100>" . _('Keyword') . "</td>
        <td class=box_title>" . _('Exec') . "</td>
        <td class=box_title width=100>" . _('User') . "</td>
        <td class=box_title width=100>"._('With Answer?')."</td>
        <td class=box_title width=100>"._('With Alarm?')."</td>	
        <td class=box_title width=75>" . _('Action') . "</td>
    </tr>	
	";
        $i = 0;
        $maxlen = 50;
        while ($db_row = dba_fetch_array($db_result)) {
            $i++;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
            $owner = uid2username($db_row['uid']);
            $action = "<a href=index.php?app=menu&inc=feature_sms_command&op=sms_command_alarm_list&command_id=".$db_row['command_id'].">$icon_alarm</a>&nbsp;";
            $action .= "<a href=index.php?app=menu&inc=feature_sms_command&op=sms_command_edit&command_id=" . $db_row['command_id'] . ">$icon_edit</a>&nbsp;";
            $action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete SMS command ?') . " (" . _('keyword') . ": " . $db_row['command_keyword'] . ")','index.php?app=menu&inc=feature_sms_command&op=sms_command_del&command_id=" . $db_row['command_id'] . "')\">$icon_delete</a>";
            $command_exec = ( (strlen($db_row['command_exec']) > $maxlen) ? substr($db_row['command_exec'], 0, $maxlen) . "..." : $db_row['command_exec'] );
            
            $with_alarm = "False";
			if($db_row['with_alarm'])
				$with_alarm = "True";
				
			$with_answer = "False";	
			if($db_row['with_answer'])
				$with_answer = "True";
            
            $content .= "
    <tr>
	<td class=$td_class>&nbsp;$i.</td>
	<td class=$td_class>" . $db_row['command_keyword'] . "</td>
	<td class=$td_class>" . stripslashes($command_exec) . "</td>
	<td class=$td_class>$owner</td>	
	<td class=$td_class>".$with_answer."</td>
	<td class=$td_class>".$with_alarm."</td>	
	<td class=$td_class align=center>$action</td>
    </tr>";
        }

        $content .= "
    </table>
	";
        echo $content;
        echo "
	    <p>
	    <input type=button value=\"" . _('Add SMS command') . "\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_command&op=sms_command_add')\" class=\"button\" />
	";
        break;
    case "sms_command_edit":
        $command_id = $_REQUEST['command_id'];
        $db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCommand WHERE command_id='$command_id'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $edit_command_keyword = $db_row['command_keyword'];
        $edit_command_exec = stripslashes($db_row['command_exec']);
        $edit_command_exec = str_replace($plugin_config['feature']['sms_command']['bin'] . "/", '', $edit_command_exec);
        $edit_command_return_as_reply = ( $db_row['command_return_as_reply'] == '1' ? 'checked' : '' );
        if ($err = $_SESSION['error_string']) {
            $content = "<div class=error_string>$err</div>";
        }
        
        $max_length = $core_config['main']['max_sms_length'];
        
        $content .= "
	    <h2>" . _('Edit SMS command') . "</h2>
	    <p>
	    <form name=\"frm_edit_command\" id=\"frm_edit_command\" action=index.php?app=menu&inc=feature_sms_command&op=sms_command_edit_yes method=post>
	    <input type=hidden name=edit_command_id value=$command_id>
	    <input type=hidden name=edit_command_keyword value=$edit_command_keyword>
	    <p>" . _('SMS command keyword') . ": <b>$edit_command_keyword</b>
	    <p>" . _('Pass these parameter to command exec field') . ":
	    <p><b>{SMSDATETIME}</b> " . _('will be replaced by SMS incoming date/time') . "
	    <p><b>{SMSSENDER}</b> " . _('will be replaced by sender number') . "
	    <p><b>{COMMANDKEYWORD}</b> " . _('will be replaced by command keyword') . "
	    <p><b>{COMMANDPARAM}</b> " . _('will be replaced by command parameter passed to server from SMS') . "
	    <p><b>{COMMANDRAW}</b> " . _('will be replaced by SMS raw message') . "
	    <p>" . _('SMS command exec path') . ": <b>" . $plugin_config['feature']['sms_command']['bin'] . "</b>
	    <p>" . _('SMS command exec') . ": <input type=text size=60 name=edit_command_exec value=\"$edit_command_exec\">
        <p>"._('SMS command reply').":
        <table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
	    <td width=100></td>
	    <td>
	    <textarea name=\"txt_MSG\" id=\"txt_MSG\" value=\"\" cols=\"40\" rows=\"5\" 
				onClick=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
				onkeypress=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
				onblur=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
				onKeyUp=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\"	
				onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'frm_edit_command', 'txt_MSG');\" 
				onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'frm_edit_command');\">$edit_command_msg</textarea>
			<br><br>
			<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" id=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.frm_edit_command.txt_MSG.focus();\" readonly>
            <input type=\"hidden\" value=\"".$core_config['main']['max_sms_length']."\" name=\"hiddcount\" id=\"hiddcount\"> 
            <input type=\"hidden\" value=\"".$core_config['main']['max_sms_length_unicode']."\" name=\"hiddcount_unicode\" id=\"hiddcount_unicode\"> 
	    </td>
	    </tr>
	    </table>
        
        <p>" . _('Make return as reply') . " : <input type=checkbox name=edit_command_return_as_reply $edit_command_return_as_reply></p>
	    <p><input type=submit class=button value=\"" . _('Save') . "\">
	    </form>
	";
        echo $content;
        break;
    case "sms_command_edit_yes":
        $edit_command_return_as_reply = ( $_POST['edit_command_return_as_reply'] == 'on' ? '1' : '0' );
        $edit_command_id = $_POST['edit_command_id'];
        $edit_command_keyword = $_POST['edit_command_keyword'];
        $edit_command_exec = $_POST['edit_command_exec'];
        $edit_command_msg = $_POST['txt_MSG'];
        
        if ($edit_command_id && $edit_command_keyword && $edit_command_exec) {
            $edit_command_exec = str_replace("/", "", $edit_command_exec);
            $edit_command_exec = str_replace("|", "", $edit_command_exec);
            $edit_command_exec = str_replace("\\", "", $edit_command_exec);
            
            $db_query = "";
			if($edit_command_msg == ""){
				$db_query = "UPDATE "._DB_PREF_."_featureCommand SET 
						c_timestamp='".mktime()."', 
						command_exec='$edit_command_exec', 
						with_answer=FALSE, 
						command_msg='',
						command_return_as_reply='$edit_command_return_as_reply'  
						WHERE command_keyword='$edit_command_keyword' AND uid='$uid'";
			}else{
				$db_query = "UPDATE "._DB_PREF_."_featureCommand SET 
						c_timestamp='".mktime()."', 
						command_exec='$edit_command_exec', 
						with_answer=TRUE, 
						command_msg='$edit_command_msg',
						command_return_as_reply='$edit_command_return_as_reply'  
						WHERE command_keyword='$edit_command_keyword' AND uid='$uid'";
			}
            
            if (@dba_affected_rows($db_query)) {
                $_SESSION['error_string'] = _('SMS command has been saved') . " (" . _('keyword') . ": $edit_command_keyword)";
            } else {
                $_SESSION['error_string'] = _('Fail to save SMS command') . " (" . _('keyword') . ": $edit_command_keyword)";
            }
        } else {
            $_SESSION['error_string'] = _('You must fill all fields');
        }
        header("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_edit&command_id=$edit_command_id");
        exit();
        break;
    case "sms_command_del":
        $command_id = $_REQUEST['command_id'];
        $db_query = "SELECT command_keyword FROM " . _DB_PREF_ . "_featureCommand WHERE command_id='$command_id'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $keyword_name = $db_row['command_keyword'];
        if ($keyword_name) {
            $db_query = "DELETE FROM " . _DB_PREF_ . "_featureCommand WHERE command_keyword='$keyword_name'";
            if (@dba_affected_rows($db_query)) {
                $_SESSION['error_string'] = _('SMS command has been deleted') . " (" . _('keyword') . ": $keyword_name)";
            } else {
                $_SESSION['error_string'] = _('Fail to delete SMS command') . " (" . _('keyword') . ": $keyword_name)";
            }
        }
        header("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_list");
        exit();
        break;
    case "sms_command_add":
        if ($err = $_SESSION['error_string']) {
            $content = "<div class=error_string>$err</div>";
        }
        
        $max_length = $core_config['main']['max_sms_length'];
        
        $content .= "
	    <h2>" . _('Add SMS command') . "</h2>
	    <p>
	    <form name=\"frm_add_command\" id=\"frm_add_command\" action=index.php?app=menu&inc=feature_sms_command&op=sms_command_add_yes method=post>
	    <p>" . _('SMS command keyword') . ": <input type=text size=10 maxlength=10 name=add_command_keyword value=\"$add_command_keyword\">
	    <p>" . _('Pass these parameter to command exec field') . ":
	    <p><b>{SMSDATETIME}</b> " . _('will be replaced by SMS incoming date/time') . "
	    <p><b>{SMSSENDER}</b> " . _('will be replaced by sender number') . "
	    <p><b>{COMMANDKEYWORD}</b> " . _('will be replaced by command keyword') . "
	    <p><b>{COMMANDPARAM}</b> " . _('will be replaced by command parameter passed to server from SMS') . "
	    <p><b>{COMMANDRAW}</b> " . _('will be replaced by SMS raw message') . "
	    <p>" . _('SMS command exec path') . ": <b>" . $plugin_config['feature']['sms_command']['bin'] . "</b>
	    <p>" . _('SMS command exec') . ": <input type=text size=60 maxlength=200 name=add_command_exec value=\"$add_command_exec\">
	    
	    <p>"._('SMS command reply').":
	    <table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
	    <td width=100></td>
	    <td>
	    <textarea name=\"txt_MSG\" id=\"txt_MSG\" value=\"\" cols=\"40\" rows=\"5\" 
				onClick=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
				onkeypress=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
				onblur=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\" 
				onKeyUp=\"SmsSetCounter_Abstract('txt_MSG','txtcount','hiddcount','hiddcount_unicode');\"	
				onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'frm_add_command', 'txt_MSG');\" 
				onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'frm_add_command');\"></textarea>
			<br><br>
			<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" id=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.frm_add_command.txt_MSG.focus();\" readonly>
            <input type=\"hidden\" value=\"".$core_config['main']['max_sms_length'] ."\" name=\"hiddcount\" id=\"hiddcount\"> 
						<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length_unicode']."\" name=\"hiddcount_unicode\" id=\"hiddcount_unicode\"> 
	    </td>
	    </tr>
	    </table>
	    
	    <p>" . _('Make return as reply') . " : <input type=checkbox name=add_command_return_as_reply></p>
	    <p><input type=submit class=button value=\"" . _('Add') . "\">
	    </form>
	";
        echo $content;
        break;
    case "sms_command_add_yes":
        $add_command_return_as_reply = ( $_POST['add_command_return_as_reply'] == 'on' ? '1' : '0' );
        $add_command_keyword = strtoupper($_POST['add_command_keyword']);
        $add_command_exec = $_POST['add_command_exec'];
        $add_command_msg = $_POST['txt_MSG'];
        if ($add_command_keyword && $add_command_exec) {
            $add_command_exec = $add_command_exec;
            $add_command_exec = str_replace("/", "", $add_command_exec);
            $add_command_exec = str_replace("|", "", $add_command_exec);
            $add_command_exec = str_replace("\\", "", $add_command_exec);
            if (checkavailablekeyword($add_command_keyword)) {
                $db_query = "";
				if($add_command_msg != ""){
					$db_query = "INSERT INTO "._DB_PREF_."_featureCommand (uid,command_keyword,command_exec, with_answer,command_exec, command_return_as_reply) 
					VALUES ('$uid','$add_command_keyword','$add_command_exec', 'TRUE', '$add_command_return_as_reply')";
				}else{
					$db_query = "INSERT INTO "._DB_PREF_."_featureCommand (uid,command_keyword,command_exec, with_answer, command_msg, command_return_as_reply) 
					VALUES ('$uid','$add_command_keyword','$add_command_exec', 'FALSE', '$add_command_msg', '$add_command_return_as_reply')";
				}
				
                if ($new_uid = @dba_insert_id($db_query)) {
                    $_SESSION['error_string'] = _('SMS command has been added') . " (" . _('keyword') . " $add_command_keyword)";
                } else {
                    $_SESSION['error_string'] = _('Fail to add SMS command') . " (" . _('keyword') . ": $add_command_keyword)";
                }
            } else {
                $_SESSION['error_string'] = _('SMS command already exists, reserved or use by other feature') . " (" . _('keyword') . ": $add_command_keyword)";
            }
        } else {
            $_SESSION['error_string'] = _('You must fill all fields');
        }
        header("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_add");
        exit();
        break;
}
?>
