<?php
if(!isadmin()){forcenoaccess();};

switch ($op)
{
    case "user_list":
	$db_query = "SELECT * FROM "._DB_PREF_."_tblUser WHERE status='2' ORDER BY username";
	$db_result = dba_query($db_query);
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Manage user</h2>
	    <p>
	    <input type=button value=\"Add user\" onClick=\"javascript:linkto('menu.php?inc=user_mgmnt&op=user_add')\" class=\"button\" />
	    <p>Status: <b>Administrator</b><br>
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=25>*</td>
        <td class=box_title width=150>Username</td>
        <td class=box_title>Name</td>	
        <td class=box_title width=200>Email</td>
        <td class=box_title width=200>Mobile</td>
        <td class=box_title width=100>Credit</td>
        <td class=box_title width=75>Action</td>
    </tr>		    
	";
	$i=0;
	while ($db_row = dba_fetch_array($db_result))
	{
	    $i++;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
	    $action = "<a href=menu.php?inc=user_mgmnt&op=user_edit&uname=".$db_row['username'].">$icon_edit</a>";
	    $action .= "<a href=\"javascript: ConfirmURL('Are you sure you want to delete user `".$db_row['username']."` ?','menu.php?inc=user_mgmnt&op=user_del&uname=".$db_row['username']."')\">$icon_delete</a>";
	    $content .= "
    <tr>
	<td class=$td_class>&nbsp;$i.</td>
	<td class=$td_class>".$db_row['username']."</td>
	<td class=$td_class>".$db_row['name']."</td>
	<td class=$td_class>".$db_row['email']."</td>	
	<td class=$td_class>".$db_row['mobile']."</td>	
	<td class=$td_class>".$db_row['credit']."</td>	
	<td class=$td_class align=center>$action</td>
    </tr>
    ";
	}
	$content .= "</table>";
	$db_query = "SELECT * FROM "._DB_PREF_."_tblUser WHERE status='3' ORDER BY username";
	$db_result = dba_query($db_query);
	$content .= "<p>Status: <b>Normal User</b><br>
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=25>*</td>
        <td class=box_title width=150>Username</td>
        <td class=box_title>Name</td>	
        <td class=box_title width=200>Email</td>
        <td class=box_title width=200>Mobile</td>
        <td class=box_title width=100>Credit</td>
        <td class=box_title width=75>Action</td>
    </tr>		    
	";
	$i=0;	
	while ($db_row = dba_fetch_array($db_result))
	{
	    $i++;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
	    $action = "<a href=menu.php?inc=user_mgmnt&op=user_edit&uname=".$db_row['username'].">$icon_edit</a>";
	    $action .= "<a href=\"javascript: ConfirmURL('Are you sure you want to delete user `".$db_row['username']."` ?','menu.php?inc=user_mgmnt&op=user_del&uname=".$db_row['username']."')\">$icon_delete</a>";
	    $content .= "
    <tr>
	<td class=$td_class>&nbsp;$i.</td>
	<td class=$td_class>".$db_row['username']."</td>
	<td class=$td_class>".$db_row['name']."</td>
	<td class=$td_class>".$db_row['email']."</td>	
	<td class=$td_class>".$db_row['mobile']."</td>	
	<td class=$td_class>".$db_row['credit']."</td>	
	<td class=$td_class align=center>$action</td>
    </tr>
    ";
	}
	$content .= "</table>";
	echo $content;
	echo "
	    <p>
	    <input type=button value=\"Add user\" onClick=\"javascript:linkto('menu.php?inc=user_mgmnt&op=user_add')\" class=\"button\" />
	";
	break;
    case "user_del":
	$uname = $_REQUEST['uname'];
	$del_uid = username2uid($uname);
	$error_string = "Fail to delete user `$uname`!";
	if (($del_uid > 1) && ($del_uid != $uid))
	{
	    $db_query = "DELETE FROM "._DB_PREF_."_tblUser WHERE uid='$del_uid'";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = "User `$uname` has been deleted!";
	    }
	}
	if (($del_uid == 1) || ($uname == "admin"))
	{
	    $error_string = "User `$uname` is immune to deletion!";
	}
	else if ($del_uid == $uid)
	{
	    $error_string = "Current logged in user is immune to deletion!";
	}
	header ("Location: menu.php?inc=user_mgmnt&op=user_list&err=".urlencode($error_string));
	break;
    case "user_edit":
	$uname = $_REQUEST['uname'];
	$uid = username2uid($uname);
	$mobile = username2mobile($uname);
	$email = username2email($uname);
	$name = username2name($uname);
	$status = username2status($uname);
	$sender = username2sender($uname);
	$credit = username2credit($uname);
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	// if ($status == 1) { $selected_1 = "selected"; }
	if ($status == 2) { $selected_2 = "selected"; }
	if ($status == 3) { $selected_3 = "selected"; }
	$option_status = "
	    <option value=2 $selected_2>Administrator</option>
	    <!--
	    <option value=1 $selected_1>Advertiser</option>
	    -->
	    <option value=3 $selected_3>Normal User</option>
	";
	$content .= "
	    <h2>Preferences: $uname</h2>
	    <p>
	    <form action=menu.php?inc=user_mgmnt&op=user_edit_save method=post>
	    <input type=hidden name=uname value=\"$uname\">
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=175>Username</td><td width=5>:</td><td><b>$uname</b></td>
	    </tr>
	    <tr>
		<td>Email</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_email value=\"$email\"></td>
	    </tr>
	    <tr>
		<td>Full name</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_name value=\"$name\"></td>
	    </tr>	    	    
	    <tr>
		<td>Mobile</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_mobile value=\"$mobile\"> (Max. 16 numeric or 11 alphanumeric char.)</td>
	    </tr>
	    <tr>
		<td>SMS Sender ID</td><td>:</td><td><input type=text size=35 maxlength=30 name=up_sender value=\"$sender\"> (Max. 30 Alphanumeric char.)</td>
	    </tr>	    
	    <tr>
		<td>Password</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_password> (Fill to change password for username `$uname`)</td>
	    </tr>	    
	    <tr>
		<td>Credit</td><td>:</td><td><input type=text size=16 maxlength=30 name=up_credit value=\"$credit\"></td>
	    </tr>	    
	    <tr>
		<td>User level</td><td>:</td><td><select name=up_status>$option_status</select></td>
	    </tr>
	</table>	    
	    <p><input type=submit class=button value=save>
	    </form>
	";
	echo $content;
	break;
    case "user_edit_save":
	$uname = $_POST['uname'];
	$up_name = $_POST['up_name'];
	$up_email = $_POST['up_email'];
	$up_mobile = $_POST['up_mobile'];
	$up_sender = $_POST['up_sender'];
	$up_password = $_POST['up_password'];
	$up_status = $_POST['up_status'];
	$up_credit = $_POST['up_credit'];
//	$status = username2status($uname);
	$error_string = "No changes made!";
	if ($up_name && $up_mobile && $up_email)
	{
	    $db_query = "SELECT email FROM "._DB_PREF_."_tblUser WHERE email='$up_email' AND NOT username='$uname'";
	    $db_result = dba_num_rows($db_query);
	    if ($db_result > 0)
	    {
		$error_string = "Email `$email` already in use by other username";
	    }
	    else
	    {
		if ($up_password)
		{
		    $chg_pwd = ",password='$up_password'";
		}
		$db_query = "UPDATE "._DB_PREF_."_tblUser SET c_timestamp='".mktime()."',name='$up_name',email='$up_email',mobile='$up_mobile',sender='$up_sender',status='$up_status'".$chg_pwd.",credit='$up_credit' WHERE username='$uname'";
		if (@dba_affected_rows($db_query))
		{
		    $error_string = "Preferences for user `$uname` has been saved";
		}
		else
		{
		    $error_string = "Fail to save preferences for `$uname`";
		}
	    }
	}
	else
	{
	    $error_string = "Empty field is not allowed";
	}
	header ("Location: menu.php?inc=user_mgmnt&op=user_edit&uname=$uname&err=".urlencode($error_string));
	break;
    case "user_add":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$option_status = "
	    <option value=2>Administrator</option>
	    <!--
	    <option value=1>Advertiser</option>
	    -->
	    <option value=3 selected>Normal User</option>
	";
	$content .= "
	    <h2>Add user</h2>
	    <p>
	    <form action=menu.php?inc=user_mgmnt&op=user_add_yes method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=175>Username</td><td width=5>:</td><td><input type=text size=30 maxlength=30 name=add_username value=\"$add_username\"></td>
	    </tr>
	    <tr>
		<td>Email</td><td>:</td><td><input type=text size=30 maxlength=30 name=add_email value=\"$add_email\"></td>
	    </tr>
	    <tr>
		<td>Full name</td><td>:</td><td><input type=text size=30 maxlength=30 name=add_name value=\"$add_name\"></td>
	    </tr>
	    <tr>
		<td>Mobile</td><td>:</td><td><input type=text size=16 maxlength=16 name=add_mobile value=\"$add_mobile\"> (Max. 16 numeric char.)</td>
	    </tr>
	    <tr>
		<td>SMS Sender ID</td><td>:</td><td><input type=text size=35 maxlength=30 name=add_sender value=\"$add_sender\"> (Max. 30 Alphanumeric char.)</td>
	    </tr>	    	    	    
	    <tr>
		<td>Password</td><td>:</td><td><input type=text size=30 maxlength=30 name=add_password value=\"$add_password\"></td>
	    </tr>
	    <tr>
		<td>Credit</td><td>:</td><td><input type=text size=16 maxlength=30 name=add_credit value=\"$add_credit\"></td>
	    </tr>
	    <tr>
		<td>User level</td><td>:</td><td><select name=add_status>$option_status</select></td>
	    </tr>
	</table>	    
	    <p><input type=submit class=button value=Add>
	    </form>
	";
	echo $content;
	break;
    case "user_add_yes":
	$add_email = $_POST['add_email'];
	$add_username = $_POST['add_username'];
	$add_name = $_POST['add_name'];
	$add_mobile = $_POST['add_mobile'];
	$add_sender = $_POST['add_sender'];
	$add_password = $_POST['add_password'];
	$add_credit = $_POST['add_credit'];
	$add_status = $_POST['add_status'];
	if (ereg("^(.+)(.+)\\.(.+)$",$add_email,$arr) && $add_email && $add_username && $add_name && $add_password)
	{
	    $db_query = "SELECT * FROM "._DB_PREF_."_tblUser WHERE username='$add_username'";
	    $db_result = dba_query($db_query);
	    if ($db_row = dba_fetch_array($db_result))
	    {
		$error_string = "User with username `".$db_row['username']."` already exists!";
	    }
	    else
	    {
		$db_query = "
		    INSERT INTO "._DB_PREF_."_tblUser (status,username,password,name,mobile,email,sender,credit)
		    VALUES ('$add_status','$add_username','$add_password','$add_name','$add_mobile','$add_email','$add_sender','$add_credit')
		";
		if ($new_uid = @dba_insert_id($db_query))
		{
		    $error_string = "User with username `$add_username` has been added";
		}
	    }
	}
	else
	{
	    $error_string = "You must fill all fields!";
	}
	header ("Location: menu.php?inc=user_mgmnt&op=user_add&err=".urlencode($error_string));
	break;
}

?>