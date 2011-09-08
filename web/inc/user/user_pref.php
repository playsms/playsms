<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};
if(!valid()){forcenoaccess();};

switch ($op)
{
	case "user_pref":
		if ($err)
		{
			$content = "<div class=error_string>$err</div>";
		}
		$db_query = "SELECT * FROM "._DB_PREF_."_tblUser WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		$daily = 0;
		if ($db_row = dba_fetch_array($db_result))
		{
			$daily = $db_row['dailysms'];
			$gender = $db_row['gender'];
			$address = $db_row['address'];
			$city = $db_row['city'];
			$state = $db_row['state'];
			$country = $db_row['country'];
			$marital = $db_row['marital'];
			$education = $db_row['education'];
			$zipcode = $db_row['zipcode'];
			$sender = $db_row['sender'];
			$timezone = $db_row['datetime_timezone'];
			$fwd_to_inbox = $db_row['fwd_to_inbox'];
			$fwd_to_email = $db_row['fwd_to_email'];
			$fwd_to_mobile = $db_row['fwd_to_mobile'];
			$credit = rate_getusercredit($username);
		}
		// select fwd_to_inbox
		if ($fwd_to_inbox) {
			$selected_1 = 'selected'; $selected_0 = '';
		} else {
			$selected_1 = ''; $selected_0 = 'selected';
		}
		$option_fwd_to_inbox = "<option value='1' ".$selected_1.">"._('yes')."</option>";
		$option_fwd_to_inbox .= "<option value='0' ".$selected_0.">"._('no')."</option>";

		// select fwd_to_email
		if ($fwd_to_email) {
			$selected_1 = 'selected'; $selected_0 = '';
		} else {
			$selected_1 = ''; $selected_0 = 'selected';
		}
		$option_fwd_to_email = "<option value='1' ".$selected_1.">"._('yes')."</option>";
		$option_fwd_to_email .= "<option value='0' ".$selected_0.">"._('no')."</option>";

		// select fwd_to_mobile
		if ($fwd_to_mobile) {
			$selected_1 = 'selected'; $selected_0 = '';
		} else {
			$selected_1 = ''; $selected_0 = 'selected';
		}
		$option_fwd_to_mobile = "<option value='1' ".$selected_1.">"._('yes')."</option>";
		$option_fwd_to_mobile .= "<option value='0' ".$selected_0.">"._('no')."</option>";

		// get country option
		$db_query = "SELECT * FROM "._DB_PREF_."_tblUser_country ORDER BY country_name";
		$db_result = dba_query($db_query);
		$option_country = "<option value=\"0\">--"._('Please select')."--</option>\n";
		while ($db_row = dba_fetch_array($db_result))
		{
			$country_id = $db_row['country_id'];
			$country_name = $db_row['country_name'];
			$selected = "";
			if ($country_id == $country)
			{
				$selected = "selected";
			}
			$option_country .= "<option value=\"$country_id\" $selected>$country_name</option>\n";
		}
		$content .= "
	    <h2>"._('Preferences')."</h2>
	    <p>
	    <form action=index.php?app=menu&inc=user_pref&op=user_pref_save method=post enctype=\"multipart/form-data\">
	    <table width=100% cellpadding=1 cellspacing=1 border=0>
	    <tr><td colspan=3><h2>"._('Login information')."</h2><hr></td></tr>
	    <tr><td width=200>"._('Username')."</td><td>:</td><td><b>$username</b></td></tr>
	    <tr><td width=200>"._('Email')." $nd</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_email value=\"$email\"></td></tr>
	    <tr><td width=200>"._('Password')."</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password></td></tr>
	    <tr><td width=200>"._('Re-Type Password')."</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password_conf></td></tr>
	    <tr><td colspan=3>&nbsp;</td></tr>
	    <tr><td colspan=3><h2>"._('Personal information')."</h2><hr></td></tr>
	    <tr><td width=200>"._('Name')." $nd</td><td>:</td><td><input type=text size=40 maxlength=100 name=up_name value=\"$name\"></td></tr>
	    <tr><td width=200>"._('Address')." $nd</td><td>:</td><td><input type=text size=40 maxlength=250 name=up_address value=\"$address\"></td></tr>
	    <tr><td width=200>"._('City')."</td><td>:</td><td><input type=text size=40 maxlength=100 name=up_city value=\"$city\"></td></tr>
	    <tr><td width=200>"._('State or Province')."</td><td>:</td><td><input type=text size=40 maxlength=100 name=up_state value=\"$state\"></td></tr>
	    <tr><td width=200>"._('Country')." $nd</td><td>:</td><td><select name=up_country>$option_country</select></td></tr>
	    <tr><td width=200>"._('Zipcode')."</td><td>:</td><td><input type=text size=10 maxlength=10 name=up_zipcode value=\"$zipcode\"></td></tr>
	    <tr><td colspan=3>&nbsp;</td></tr>
	    <tr><td colspan=3><h2>"._('Application information')."</h2><hr></td></tr>
	    <tr><td width=200>"._('Timezone')."</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_timezone value=\"$timezone\"> ("._('Eg: +0700 for Jakarta/Bangkok timezone').")</td></tr>
	    <tr><td width=200>"._('Mobile')." ("._('sender number').")</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_mobile value=\"$mobile\"> ("._('Max. 16 numeric or 11 alphanumeric characters').")</td></tr>
	    <tr><td width=200>"._('SMS Sender ID')." ("._('SMS footer').")</td><td>:</td><td><input type=text size=35 maxlength=30 name=up_sender value=\"$sender\"> ("._('Max. 30 alphanumeric characters').")</td></tr>
	    <tr><td width=200>"._('Credit')."</td><td>:</td><td><b>$credit</b></td></tr>
	    <tr><td width=200>"._('Forward PV to inbox')."</td><td>:</td><td><select name='up_fwd_to_inbox'>".$option_fwd_to_inbox."</select></td></tr>
	    <tr><td width=200>"._('Forward PV to email')."</td><td>:</td><td><select name='up_fwd_to_email'>".$option_fwd_to_email."</select></td></tr>
	    <tr><td width=200>"._('Forward PV to mobile')."</td><td>:</td><td><select name='up_fwd_to_mobile'>".$option_fwd_to_mobile."</select></td></tr>
	    <tr><td colspan=3>&nbsp;</td></tr>
	    <tr><td colspan=3><hr></td></tr>
	    <tr><td width=200><input type=submit class=button value='"._('Save')."'></td></tr>
	    </table>
	    </form>
	";
		echo $content;
		break;
	case "user_pref_save":
		$up_name = $_POST['up_name'];
		$up_email = $_POST['up_email'];
		$up_gender = $_POST['up_gender'];
		$up_address = $_POST['up_address'];
		$up_city = $_POST['up_city'];
		$up_state = $_POST['up_state'];
		$up_country = $_POST['up_country'];
		$up_mobile = $_POST['up_mobile'];
		$up_sender = $_POST['up_sender'];
		$up_daily = intval(trim($_POST['up_daily']));
		$up_password = $_POST['up_password'];
		$up_password_conf = $_POST['up_password_conf'];
		$up_marital = $_POST['up_marital'];
		$up_education = $_POST['up_education'];
		$up_zipcode = $_POST['up_zipcode'];
		$up_trn = $_POST['up_trn'];
		$up_timezone = $_POST['up_timezone'];
		$up_fwd_to_inbox = $_POST['up_fwd_to_inbox'];
		$up_fwd_to_email = $_POST['up_fwd_to_email'];
		$up_fwd_to_mobile = $_POST['up_fwd_to_mobile'];
		$error_string = _('No changes made');
		if ($up_name && $up_email && $up_address && $up_country)
		{
			$up_uname = $username;
			$db_query = "SELECT username FROM "._DB_PREF_."_tblUser WHERE email='$up_email' AND NOT username='$up_uname'";
			$db_result = dba_query($db_query);
			if ($db_row = dba_fetch_array($db_result))
			{
				$error_string = _('Email is already in use by other username')." ("._('email').": `$email`)";
			}
			else
			{
				$chg_pwd = "";
				if ($up_password && $up_password_conf && ($up_password == $up_password_conf))
				{
					$chg_pwd = ",password='$up_password'";
				}
				$db_query = "
		    UPDATE "._DB_PREF_."_tblUser 
		    SET c_timestamp='".mktime()."',
			name='$up_name',email='$up_email',mobile='$up_mobile',sender='$up_sender'$chg_pwd,
			gender='$up_gender',address='$up_address',city='$up_city',state='$up_state',country='$up_country',
			marital='$up_marital',education='$up_education',zipcode='$up_zipcode',junktimestamp='".mktime()."',
			datetime_timezone='$up_timezone',fwd_to_inbox='$up_fwd_to_inbox',fwd_to_email='$up_fwd_to_email',
			fwd_to_mobile='$up_fwd_to_mobile'
		    WHERE uid='$uid'";
				if (@dba_affected_rows($db_query))
				{
					if ($up_password && $up_password_conf && ($up_password == $up_password_conf))
					{
						$error_string = _('Preferences has been saved and password updated');
					}
					else
					{
						$error_string = _('Preferences has been saved');
					}
				}
				else
				{
					$error_string = _('Fail to save preferences');
				}
			}
		}
		else
		{
			$error_string = _('You must fill all field');
		}
		header ("Location: index.php?app=menu&inc=user_pref&op=user_pref&err=".urlencode($error_string));
		break;
}

?>