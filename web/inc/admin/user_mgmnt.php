<?php

if(!(defined('_SECURE_'))){die('Intruder alert');};
if(!isadmin()){forcenoaccess();};

switch ($op) {
	case "user_list":
		if ($err) {
			$content = "<p><font color='red'>$err</font><p>";
		}
		$content .= "
			<h2>" . _('Manage user') . "</h2>
			<p>
			<input type='button' value='" . _('Add user') . "' onClick=\"javascript:linkto('index.php?app=menu&inc=user_mgmnt&op=user_add')\" class=\"button\" />
			<p>" . _('Status') . ": <b>" . _('Administrator') . "</b><br>
			<table cellpadding='1' cellspacing='2' border='0' width='100%'>
			<tr>
				<td class='box_title' width='25'>*</td>
				<td class='box_title' width='100'>" . _('Username') . "</td>
				<td class='box_title' width='125'>" . _('Name') . "</td>	
				<td class='box_title' width='150'>" . _('Email') . "</td>
				<td class='box_title' width='150'>" . _('Mobile') . "</td>
				<td class='box_title' width='75'>" . _('Credit') . "</td>
				<td class='box_title' width='75'>" . _('Action') . "</td>
			</tr>";
		$i = 0;
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE status='2' ORDER BY username";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$action = "<a href=index.php?app=menu&inc=user_mgmnt&op=user_edit&uname=" . $db_row['username'] . ">$icon_edit</a>";
			$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete user ?') . " (" . _('username') . ": " . $db_row['username'] . ")','index.php?app=menu&inc=user_mgmnt&op=user_del&uname=" . $db_row['username'] . "')\">$icon_delete</a>";
			$content .= "
				<tr>
					<td class='$td_class'>&nbsp;$i.</td>
					<td class='$td_class'>" . $db_row['username'] . "</td>
					<td class='$td_class'>" . $db_row['name'] . "</td>
					<td class='$td_class'>" . $db_row['email'] . "</td>	
					<td class='$td_class'>" . $db_row['mobile'] . "</td>	
					<td class='$td_class'>" . rate_getusercredit($db_row['username']) . "</td>	
					<td class='$td_class' align='center'>$action</td>
				</tr>";
		}
		$content .= "</table>";
		$content .= "<p>" . _('Status') . ": <b>" . _('Normal user') . "</b><br>
			<table cellpadding='1' cellspacing='2' border='0' width='100%'>
			<tr>
				<td class='box_title' width='25'>*</td>
				<td class='box_title' width='100'>" . _('Username') . "</td>
				<td class='box_title' width='125'>" . _('Name') . "</td>	
				<td class='box_title' width='150'>" . _('Email') . "</td>
				<td class='box_title' width='150'>" . _('Mobile') . "</td>
				<td class='box_title' width='75'>" . _('Credit') . "</td>
				<td class='box_title' width='75'>" . _('Action') . "</td>
			</tr>";
		$i = 0;
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE status='3' ORDER BY username";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$action = "<a href=index.php?app=menu&inc=user_mgmnt&op=user_edit&uname=" . $db_row['username'] . ">$icon_edit</a>";
			$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete user') . " " . $db_row['username'] . " ?','index.php?app=menu&inc=user_mgmnt&op=user_del&uname=" . $db_row['username'] . "')\">$icon_delete</a>";
			$content .= "
				<tr>
					<td class='$td_class'>&nbsp;$i.</td>
					<td class='$td_class'>" . $db_row['username'] . "</td>
					<td class='$td_class'>" . $db_row['name'] . "</td>
					<td class='$td_class'>" . $db_row['email'] . "</td>	
					<td class='$td_class'>" . $db_row['mobile'] . "</td>	
					<td class='$td_class'>" . rate_getusercredit($db_row['username']) . "</td>	
					<td class='$td_class' align='center'>$action</td>
				</tr>";
		}
		$content .= "</table>";
		echo $content;
		echo "<p><input type=button value='" . _('Add user') . "' onClick=\"javascript:linkto('index.php?app=menu&inc=user_mgmnt&op=user_add')\" class=\"button\" /></p>";
		break;
	case "user_del":
		$uname = $_REQUEST['uname'];
		$del_uid = username2uid($uname);
		$error_string = _('Fail to delete user') . " $uname!";
		if (($del_uid > 1) && ($del_uid != $uid)) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_tblUser WHERE uid='$del_uid'";
			if (@dba_affected_rows($db_query)) {
				$error_string = _('User has been deleted') . " (" . _('username') . ": $uname)";
			}
		}
		if (($del_uid == 1) || ($uname == "admin")) {
			$error_string = _('User is immune to deletion') . " (" . _('username') . " $uname)";
		} else if ($del_uid == $uid) {
			$error_string = _('Currently logged in user is immune to deletion');
		}
		header("Location: index.php?app=menu&inc=user_mgmnt&op=user_list&err=" . urlencode($error_string));
		break;
	case "user_edit":
		$uname = $_REQUEST['uname'];
		$uid = username2uid($uname);
		$c_user = user_getdatabyuid($uid);
		$mobile = $c_user['mobile'];
		$email = $c_user['email'];
		$name = $c_user['name'];
		$status = $c_user['status'];
		$sender = $c_user['sender'];
		$footer = $c_user['footer'];
		$timezone = $c_user['datetime_timezone'];
		$language_module = $c_user['language_module'];
		// get language options
		for ($i = 0; $i < count($core_config['languagelist']); $i++) {
			$language = $core_config['languagelist'][$i];
			if ($language == $language_module)
				$selected = "selected";
			$option_language_module .= "<option value=\"$language\" $selected>$language</option>";
			$selected = "";
		}
		$credit = rate_getusercredit($uname);
		if ($err) {
			$content = "<p><font color='red'>$err</font><p>";
		}
		if ($status == 2) {
			$selected_2 = "selected";
		}
		if ($status == 3) {
			$selected_3 = "selected";
		}
		$option_status = "
			<option value='2' $selected_2>" . _('Administrator') . "</option>
			<option value='3' $selected_3>" . _('Normal user') . "</option>";
		$content .= "
			<h2>" . _('Preferences') . ": $uname</h2>
			<p>
			<form action='index.php?app=menu&inc=user_mgmnt&op=user_edit_save' method='post'>
			<input type='hidden' name='uname' value=\"$uname\">
			<table width='100%' cellpadding='1' cellspacing='2' border='0'>
			<tr>
				<td width='175'>" . _('Username') . " $nd</td><td width='5'>:</td><td><b>$uname</b></td>
			</tr>
			<tr>
				<td>" . _('Full name') . " $nd</td><td>:</td><td><input type='text' size='30' maxlength='30' name='up_name' value=\"$name\"></td>
			</tr>	    	    
			<tr>
				<td>" . _('Email') . " $nd</td><td>:</td><td><input type='text' size='30' maxlength='30' name='up_email' value=\"$email\"></td>
			</tr>
			<tr>
				<td>" . _('Mobile') . "</td><td>:</td><td><input type='text' size='16' maxlength='16' name='up_mobile' value=\"$mobile\"> (" . _('Max. 16 numeric or 11 alphanumeric characters') . ")</td>
			</tr>
			<tr>
				<td>" . _('SMS sender ID') . "</td><td>:</td><td><input type='text' size='16' maxlength='16' name='up_sender' value=\"$sender\"> (" . _('Max. 16 numeric or 11 alphanumeric characters') . ")</td>
			</tr>
			<tr>
				<td>" . _('SMS footer') . "</td><td>:</td><td><input type='text' size='30' maxlength='30' name='up_footer' value=\"$footer\"> (" . _('Max. 30 alphanumeric characters') . ")</td>
			</tr>	    
			<tr>
				<td>" . _('Timezone') . "</td><td>:</td><td><input type='text' size='5' maxlength='5' name='up_timezone' value=\"$timezone\"> (" . _('Eg: +0700 for Jakarta/Bangkok timezone') . ")</td>
			</tr>
			<tr>
				<td>" . _('Password') . "</td><td>:</td><td><input type='password' size='30' maxlength='30' name='up_password'> (" . _('Fill to change password for username') . " $uname)</td>
			</tr>	    
			<tr>
				<td>" . _('Credit') . "</td><td>:</td><td><input type='text' size='16' maxlength='30' name='up_credit' value=\"$credit\"></td>
			</tr>	    
			<tr>
				<td>" . _('User level') . "</td><td>:</td><td><select name='up_status'>$option_status</select></td>
			</tr>
			<tr>
				<td>" . _('Active language') . "</td><td>:</td><td><select name='up_language_module'>$option_language_module</select></td>
			</tr>
			</table>	    
			<p><input type='submit' class='button' value='" . _('Save') . "'>
			</form>";
		echo $content;
		break;
	case "user_edit_save":
		$uname = $_POST['uname'];
		$up_name = $_POST['up_name'];
		$up_email = $_POST['up_email'];
		$up_mobile = $_POST['up_mobile'];
		$up_sender = $_POST['up_sender'];
		$up_footer = $_POST['up_footer'];
		$up_password = $_POST['up_password'];
		$up_status = $_POST['up_status'];
		$up_credit = $_POST['up_credit'];
		$up_timezone = ( $_POST['up_timezone'] ? $_POST['up_timezone'] : $gateway_timezone );
		$up_language = $_POST['up_language_module'];
		$error_string = _('No changes made');
		if ($up_name && $up_email) {
			$db_query = "SELECT username FROM " . _DB_PREF_ . "_tblUser WHERE email='$up_email' AND NOT username='$uname'";
			$db_result = dba_query($db_query);
			if ($db_row = dba_fetch_array($db_result)) {
				$error_string = _('Email is already in use by other username') . " (" . _('email') . ": $up_email, " . _('username') . ": " . $db_row['username'] . ") ";
			} else {
				if ($up_password) {
					$chg_pwd = ",password='$up_password'";
				}
				$db_query = "UPDATE " . _DB_PREF_ . "_tblUser SET c_timestamp='" . mktime() . "',name='$up_name',email='$up_email',mobile='$up_mobile',sender='$up_sender',footer='$up_footer',datetime_timezone='$up_timezone',language_module='$up_language',status='$up_status'" . $chg_pwd . " WHERE username='$uname'";
				if (@dba_affected_rows($db_query)) {
					$c_uid = username2uid($uname);
					rate_setusercredit($c_uid, $up_credit);
					$error_string = _('Preferences has been saved') . " (" . _('username') . ": $uname)";
				} else {
					$error_string = _('Fail to save preferences') . " (" . _('username') . ": $uname)";
				}
			}
		} else {
			$error_string = _('You must fill all field');
		}
		header("Location: index.php?app=menu&inc=user_mgmnt&op=user_edit&uname=$uname&err=" . urlencode($error_string));
		break;
	case "user_add":
		if ($err) {
			$content = "<p><font color='red'>$err</font><p>";
		}
		$add_timezone = ( $add_timezone ? $add_timezone : $gateway_timezone );
		// get language options
		for ($i = 0; $i < count($core_config['languagelist']); $i++) {
			$language = $core_config['languagelist'][$i];
			if ($language == $language_module)
				$selected = "selected";
			$option_language_module .= "<option value=\"$language\" $selected>$language</option>";
			$selected = "";
		}
		$option_status = "
			<option value='2'>" . _('Administrator') . "</option>
			<option value='3' selected>" . _('Normal User') . "</option>";
		$content .= "
		<h2>" . _('Add user') . "</h2>
		<p>
		<form action='index.php?app=menu&inc=user_mgmnt&op=user_add_yes' method='post'>
		<table width='100%' cellpadding='1' cellspacing='2' border='0'>
		<tr>
			<td width='175'>" . _('Username') . " $nd</td><td width='5'>:</td><td><input type='text' size='30' maxlength='30' name='add_username' value=\"$add_username\"></td>
		</tr>
		<tr>
			<td>" . _('Full name') . " $nd</td><td>:</td><td><input type='text' size='30' maxlength='30' name='add_name' value=\"$add_name\"></td>
		</tr>
		<tr>
			<td>" . _('Email') . " $nd</td><td>:</td><td><input type='text' size='30' maxlength='30' name='add_email' value=\"$add_email\"></td>
		</tr>
		<tr>
			<td>" . _('Mobile') . "</td><td>:</td><td><input type='text' size='16' maxlength='16' name='add_mobile' value=\"$add_mobile\"> (" . _('Max. 16 numeric or 11 alphanumeric characters') . ")</td>
		</tr>
		<tr>
			<td>" . _('SMS sender ID') . "</td><td>:</td><td><input type='text' size='16' maxlength='16' name='add_sender' value=\"$add_sender\"> (" . _('Max. 16 numeric or 11 alphanumeric characters') . ")</td>
		</tr>
		<tr>
			<td>" . _('SMS footer') . "</td><td>:</td><td><input type='text' size='30' maxlength='30' name='add_footer' value=\"$add_footer\"> (" . _('Max. 30 alphanumeric characters') . ")</td>
		</tr>	    	    	    
		<tr>
			<td>" . _('Timezone') . "</td><td>:</td><td><input type='text' size='5' maxlength='5' name='add_timezone' value=\"$add_timezone\"> (" . _('Eg: +0700 for Jakarta/Bangkok timezone') . ")</td>
		</tr>
		<tr>
			<td>" . _('Password') . " $nd</td><td>:</td><td><input type='password' size='30' maxlength='30' name='add_password' value=\"$add_password\"></td>
		</tr>
		<tr>
			<td>" . _('Credit') . "</td><td>:</td><td><input type='text' size='16' maxlength='30' name='add_credit' value=\"" . $core_config['main']['cfg_default_credit'] . "\"></td>
		</tr>
		<tr>
			<td>" . _('User level') . "</td><td>:</td><td><select name='add_status'>$option_status</select></td>
		</tr>
		<tr>
			<td>" . _('Active language') . "</td><td>:</td><td><select name='add_language_module'>$option_language_module</select></td>
		</tr>

		</table>	    
		<p><input type='submit' class='button' value='" . _('Add') . "'>
		</form>";
		echo $content;
		break;
	case "user_add_yes":
		$add_email = $_POST['add_email'];
		$add_username = $_POST['add_username'];
		$add_name = $_POST['add_name'];
		$add_mobile = $_POST['add_mobile'];
		$add_sender = $_POST['add_sender'];
		$add_footer = $_POST['add_footer'];
		$add_password = $_POST['add_password'];
		$add_credit = $_POST['add_credit'];
		$add_status = $_POST['add_status'];
		$add_timezone = $_POST['add_timezone'];
		$add_language_module = $_POST['add_language_module'];
		if (ereg("^(.+)(.+)\\.(.+)$", $add_email, $arr) && $add_email && $add_username && $add_name && $add_password) {
			$db_query = "SELECT * FROM " . _DB_PREF_ . "_tblUser WHERE username='$add_username'";
			$db_result = dba_query($db_query);
			if ($db_row = dba_fetch_array($db_result)) {
				$error_string = _('User is already exists') . " (" . _('username') . ": " . $db_row['username'] . ")";
			} else {
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_tblUser (status,username,password,name,mobile,email,sender,footer,credit,datetime_timezone,language_module)
					VALUES ('$add_status','$add_username','$add_password','$add_name','$add_mobile','$add_email','$add_sender','$add_footer','$add_credit','$add_timezone','$add_language_module')";
				if ($new_uid = @dba_insert_id($db_query)) {
					rate_setusercredit($new_uid, $add_credit);
					$error_string = _('User has been added') . " (" . _('username') . ": $add_username)";
				}
			}
		} else {
			$error_string = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=user_mgmnt&op=user_add&err=" . urlencode($error_string));
		break;
}
?>