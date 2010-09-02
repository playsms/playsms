<?php
if(!isadmin()){forcenoaccess();};

switch ($op)
{
    case "main_config":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>Main configuration</h2>
	    <p>
	    <form action=menu.php?inc=main_config&op=main_config_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=125>Website title</td><td width=5>:</td><td><input type=text size=50 name=edit_web_title value=\"$web_title\"></td>
	    </tr>
	    <tr>
		<td>Website email</td><td>:</td><td><input type=text size=30 name=edit_email_service value=\"$email_service\"></td>
	    </tr>
	    <tr>
		<td>Forwarded email footer</td><td>:</td><td><input type=text size=50 name=edit_email_footer value=\"$email_footer\"></td>
	    </tr>
	    <tr>
		<td>Gateway number</td><td>:</td><td><input type=text size=20 name=edit_gateway_number value=\"$gateway_number\"></td>
	    </tr>
	    <tr>
		<td>Default SMS rate</td><td>:</td><td><input type=text size=20 name=edit_default_rate value=\"$default_rate\"></td>
	    </tr>
	    <tr>
		<td>Activated gateway module</td><td>:</td><td>$gateway_module</td>
	    </tr>
	</table>	    
	    <p><input type=submit class=button value=Save>
	    </form>
	";
	echo $content;
	break;
    case "main_config_save":
	$edit_web_title = $_POST['edit_web_title'];
	$edit_email_service = $_POST['edit_email_service'];
	$edit_email_footer = $_POST['edit_email_footer'];
	$edit_gateway_number = $_POST['edit_gateway_number'];
	$edit_default_rate = $_POST['edit_default_rate'];
	$db_query = "
	    UPDATE "._DB_PREF_."_tblConfig_main 
	    SET c_timestamp='".mktime()."',
		cfg_web_title='$edit_web_title',
		cfg_email_service='$edit_email_service',
		cfg_email_footer='$edit_email_footer',
		cfg_gateway_number='$edit_gateway_number',
		cfg_default_rate='$edit_default_rate'
	";
	$db_result = dba_query($db_query);
	$error_string = "Main configuration has been saved";
	header ("Location: menu.php?inc=main_config&op=main_config&err=".urlencode($error_string));
	break;
}

?>