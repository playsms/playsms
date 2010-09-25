<?php
if(!valid()){forcenoaccess();};

switch ($op)
{
    case "user_pref":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
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
	    $credit = rate_getusercredit($username);
	}
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
	    <form action=menu.php?inc=user_pref&op=user_pref_save method=post enctype=\"multipart/form-data\">
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
	    <tr><td width=200>"._('Mobile')." ("._('sender number').")</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_mobile value=\"$mobile\"> ("._('Max. 16 numeric or 11 alphanumeric characters').")</td></tr>
	    <tr><td width=200>"._('SMS Sender ID')." ("._('SMS footer').")</td><td>:</td><td><input type=text size=35 maxlength=30 name=up_sender value=\"$sender\"> ("._('Max. 30 alphanumeric characters').")</td></tr>
	    <tr><td width=200>"._('Credit')."</td><td>:</td><td><b>$credit</b></td></tr>
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
	$error_string = _('No changes made');
	if ($up_name && $up_mobile && $up_email && $up_address && $up_country)
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
			marital='$up_marital',education='$up_education',zipcode='$up_zipcode',junktimestamp='".mktime()."'
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
	header ("Location: menu.php?inc=user_pref&op=user_pref&err=".urlencode($error_string));
	break;
}

?>