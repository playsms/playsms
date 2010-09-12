<?php
if(!isadmin()){forcenoaccess();};

switch ($op)
{
    case "main_config":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	// get gateway options
	for ($i=0;$i<count($core_config['gatewaylist']);$i++) {
	    $gateway = $core_config['gatewaylist'][$i];
	    if ($gateway == $gateway_module) $selected = "selected";
	    $option_gateway_module .= "<option value=\"$gateway\" $selected>$gateway</option>";
	    $selected = "";
	}
	// get themes options
	for ($i=0;$i<count($core_config['themeslist']);$i++) {
	    $themes = $core_config['themeslist'][$i];
	    if ($themes == $themes_module) $selected = "selected";
	    $option_themes_module .= "<option value=\"$themes\" $selected>$themes</option>";
	    $selected = "";
	}
	// get language options
	for ($i=0;$i<count($core_config['languagelist']);$i++) {
	    $language = $core_config['languagelist'][$i];
	    if ($language == $language_module) $selected = "selected";
	    $option_language_module .= "<option value=\"$language\" $selected>$language</option>";
	    $selected = "";
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
		<td>Active gateway module</td><td>:</td><td><select name=edit_gateway_module>$option_gateway_module</select></td>
	    </tr>
	    <tr>
		<td>Active themes</td><td>:</td><td><select name=edit_themes_module>$option_themes_module</select></td>
	    </tr>
	    <tr>
		<td>Active language</td><td>:</td><td><select name=edit_language_module>$option_language_module</select></td>
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
	$edit_gateway_module = $_POST['edit_gateway_module'];
	$edit_themes_module = $_POST['edit_themes_module'];
	$db_query = "
	    UPDATE "._DB_PREF_."_tblConfig_main 
	    SET c_timestamp='".mktime()."',
		cfg_web_title='$edit_web_title',
		cfg_email_service='$edit_email_service',
		cfg_email_footer='$edit_email_footer',
		cfg_gateway_number='$edit_gateway_number',
		cfg_default_rate='$edit_default_rate',
		cfg_gateway_module='$edit_gateway_module',
		cfg_themes_module='$edit_themes_module'
	";
	$db_result = dba_query($db_query);
	$error_string = "Main configuration has been saved";
	header ("Location: menu.php?inc=main_config&op=main_config&err=".urlencode($error_string));
	break;
}

?>