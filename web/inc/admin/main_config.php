<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

switch ($op)
{
	case "gateway_advanced_rules_add_yes":
		$gateway_name = $_POST['gateway_name'];
		$rule_digits = $_POST['rule_digits'];
		
		if($rule_digits && $gateway_name){
			//VERIFICAR SE DIGITOS JA NAO EXISTEM!
			$db_query = "SELECT * FROM "._DB_PREF_."_gateway_rules WHERE rules=$rule_digits";
			if (!($db_result = dba_num_rows($db_query)))
			{
				//ADD GATEWAY RULE
				$db_query = "INSERT INTO "._DB_PREF_."_gateway_rules (gateway, rules) VALUES ('$gateway_name', '$rule_digits')";
				if ($new_uid = @dba_insert_id($db_query))
				{
					$error_string = _('Gateway Rule has been added')." ("._('Rule Digits').": `$rule_digits`)";
					
					$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET cfg_sender_gateway_withrules=TRUE";
					$db_result = dba_query($db_query);
					
				}else{
					$error_string = _('Fail to add Gateway Rule')." ("._('Rule Digits').": `$rule_digits`)";
				}	
			}else{
				$error_string = _('Rule digits already exists.');
			}
		}else{
			$error_string = _('You must fill all fields, correctly');	
		}
		
		header ("Location: index.php?app=menu&inc=main_config&op=gateway_advanced_rules&err=".urlencode($error_string));
		break;
	case "gateway_advanced_rules_del":
		$rule_id = $_REQUEST['rule_id'];
		
		if($rule_id){
			$db_query = "DELETE FROM "._DB_PREF_."_gateway_rules WHERE id='$rule_id'";
			if (@dba_affected_rows($db_query)){
				$error_string = _('Gateway Rule has been deleted');
				
				//VERIFICAR SE NAO EXISTEM MAIS RULES
				$db_query = "SELECT * FROM "._DB_PREF_."_gateway_rules";
				if (!($db_result = dba_num_rows($db_query)))
				{
					$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET cfg_sender_gateway_withrules=FALSE";
					$db_result = dba_query($db_query);
				}
			}else{
				$error_string = _('Fail to delete Gateway Rule') . '(1)';	
			}
		}else{
			$error_string = _('Fail to delete Gateway Rule') . '(2)';
		}
		
		header ("Location: index.php?app=menu&inc=main_config&op=gateway_advanced_rules&err=".urlencode($error_string));
		break;
	case "gateway_advanced_rules":
		if ($err)
		{
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
	    <h2>"._('Advanced Rules')."</h2>
	    <p>";
	    $db_query = "SELECT * FROM "._DB_PREF_."_gateway_rules";
		$db_result = dba_query($db_query);
		$content .= "
				<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr>
					<td class=box_title width=25>*</td>
					<td class=box_title>"._('First Digits')."</td>
					<td class=box_title>"._('Gateway Name')."</td>
					<td class=box_title width=75>"._('Action')."</td>
				</tr>
				";
		$i=0;
		while ($db_row = dba_fetch_array($db_result))
		{
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$gateway_name = $db_row['gateway'];
			$id_rule = $db_row['id'];
			$rule_digits = $db_row['rules'];
			$content .= "
					<tr>
						<td class=$td_class>&nbsp;$i.</td>
						<td class=$td_class>$rule_digits</td>
						<td class=$td_class>$gateway_name</td>
						<td class=$td_class align=center>
							<a href=\"javascript:ConfirmURL('"._('Are you sure you want to delete gateway rule?')." ("._('Rule Digits').": ".$rule_digits.")','index.php?app=menu&inc=main_config&op=gateway_advanced_rules_del&rule_id=$id_rule');\">$icon_delete</a>
						</td>
					</tr>";	    
			}
			
		$content .= "</table>";
		
		echo $content;
		
		// get gateway options
		for ($i=0;$i<count($core_config['gatewaylist']);$i++) {
			$gateway = $core_config['gatewaylist'][$i];
			
			if($core_config['plugin'][$gateway]['ready'])
				$option_gateway .= "<option value=\"$gateway\">$gateway</option>";
			
			$selected = "";
			
		}
		
		$content = "
				<br /><br />
				<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr style=\"vertical-align:top;\">
					<td>
						<h2>"._('Add Gateway Rule')."</h2>
						<p>
						<form name=\"form_rules\" id=\"form_rules\" action=index.php?app=menu&inc=main_config&op=gateway_advanced_rules_add_yes method=post>
							<p>"._('Rule Digits').": &nbsp;
								<input type=text size=6 maxlength=6 name=\"rule_digits\" />&nbsp;
							<p>"._('Gateway').": &nbsp;
								<select name='gateway_name'>$option_gateway</select>
							<p><input type=submit class=button value=\""._('Add')."\">
						</form>
					</td>
				</tr>
				</table>";
		
		echo $content;
		$content = "<a href=\"index.php?app=menu&inc=main_config&op=main_config\">"._('Back to : Main configuration')."</a>";
		
		echo $content;
		
		break;
	case "main_config":
		if ($err = $_SESSION['error_string'])
		{
			$content = "<div class=error_string>$err</div>";
		}
		// enable register yes-no option
		if ($enable_register) { $selected1 = "selected"; } else { $selected2 = "selected"; };
		$option_enable_register = "<option value=\"1\" $selected1>"._('yes')."</option>";
		$option_enable_register .= "<option value=\"0\" $selected2>"._('no')."</option>";
		$selected1 = ""; $selected2 = "";
		// enable forgot yes-no option
		if ($enable_forgot) { $selected1 = "selected"; } else { $selected2 = "selected"; };
		$option_enable_forgot = "<option value=\"1\" $selected1>"._('yes')."</option>";
		$option_enable_forgot .= "<option value=\"0\" $selected2>"._('no')."</option>";
		$selected1 = ""; $selected2 = "";
		// get gateway options
		$db_query = "SELECT cfg_sender_gateway_withrules
			 FROM "._DB_PREF_."_tblConfig_main";	
			
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		
		if($db_row['cfg_sender_gateway_withrules'])
			$icon_defined_rules = $icon_warning_triangle;
		else
			$icon_defined_rules = "";
		
		// get sender gateway options
		for ($i=0;$i<count($core_config['gatewaylist']);$i++) {
			$gateway = $core_config['gatewaylist'][$i];
			
			if ($gateway == $gateway_sender_module) 
				$selected = "selected";
			
			//Verify if gateway is ready
			if($core_config['plugin'][$gateway]['ready'])
				$option_gateway_sender_module .= "<option value=\"$gateway\" $selected>$gateway</option>";
			
			$selected = "";
		}
		// get receiver gateway options
		for ($i=0;$i<count($core_config['gatewaylist']);$i++) {
			$gateway = $core_config['gatewaylist'][$i];
			
			if ($gateway == $gateway_receiver_module) 
				$selected = "selected";
			
			if($core_config['plugin'][$gateway]['ready'])
				$option_gateway_receiver_module .= "<option value=\"$gateway\" $selected>$gateway</option>";
			
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
		$lang_list = '';
		for ($i=0;$i<count($core_config['languagelist']);$i++) {
			$language = $core_config['languagelist'][$i];
			$c_language_title = $core_config['plugins']['language'][$language]['title'];
			if ($c_language_title) {
				$lang_list[$c_language_title] = $language;
			}
		}
		if (is_array($lang_list)) {
			foreach ($lang_list as $key => $val) {
				if ($val == $language_module) $selected = "selected";
				$option_language_module .= "<option value=\"".$val."\" $selected>".$key."</option>";
				$selected = "";
			}
		}

		$content .= "
	    <h2>"._('Main configuration')."</h2>
	    <p>
	    <form action='index.php?app=menu&inc=main_config&op=main_config_save' method='post'>
	<table width='100%' cellpadding='1' cellspacing='2' border='0'>
	    <tr>
		<td width='175'>"._('Website title')."</td><td width='5'>:</td><td><input type='text' size='50' name='edit_web_title' value=\"$web_title\"></td>
	    </tr>
	    <tr>
		<td>"._('Website email')."</td><td>:</td><td><input type='text' size='30' name='edit_email_service' value=\"$email_service\"></td>
	    </tr>
	    <tr>
		<td>"._('Forwarded email footer')."</td><td>:</td><td><input type='text' size='50' name='edit_email_footer' value=\"$email_footer\"></td>
	    </tr>
	    <tr>
		<td>"._('Default sender ID')."</td><td>:</td><td><input type='text' size='20' name='edit_gateway_number' value=\"$gateway_number\"></td>
	    </tr>
	    <tr>
		<td>"._('Default timezone')."</td><td>:</td><td><input type='text' size='5' maxlength='5' name='edit_gateway_timezone' value=\"$gateway_timezone\"> ("._('Eg: +0700 for Jakarta/Bangkok timezone').")</td>
	    </tr>
	    <tr>
		<td>"._('Default SMS rate')."</td><td>:</td><td><input type='text' size='20' name='edit_default_rate' value=\"$default_rate\"></td>
	    </tr>
	    <tr>
		<td>"._('Maximum SMS count')."</td><td>:</td><td><input type='text' size='2' maxlength='2' name='edit_sms_max_count' value=\"$sms_max_count\"></td>
	    </tr>
	    <tr>
		<td>"._('Default credit for user')."</td><td>:</td><td><input type='text' size='20' name='edit_default_credit' value=\"$default_credit\"></td>
	    </tr>
	    <tr>
		<td>"._('Enable public registration')."</td><td>:</td><td><select name='edit_enable_register'>$option_enable_register</select></td>
	    </tr>
	    <tr>
		<td>"._('Enable forgot password')."</td><td>:</td><td><select name='edit_enable_forgot'>$option_enable_forgot</select></td>
	    </tr>
	    <tr>
		<td>"._('Active receiver gateway module')."</td><td>:</td><td><select name='edit_gateway_receiver_module'>$option_gateway_receiver_module</select></td>
	    </tr>
	    <tr>
			<td>
				"._('Default Active sender gateway module')."
			</td>
			<td>:</td>
			<td>
				<select name='edit_gateway_sender_module'>$option_gateway_sender_module</select>
				&nbsp; (<a href=\"index.php?app=menu&inc=main_config&op=gateway_advanced_rules\">"._('Advanced Rules')."</a>) 
				&nbsp; $icon_defined_rules
			</td>
	    </tr>
	    <tr>
		<td>"._('Active themes')."</td><td>:</td><td><select name='edit_themes_module'>$option_themes_module</select></td>
	    </tr>
	    <tr>
		<td>Default language</td><td>:</td><td><select name='edit_language_module'>$option_language_module</select></td>
	    </tr>
	</table>	    
	    <p><input type='submit' class='button' value='"._('Save')."'>
	    </form>
	";
		echo $content;
		break;
	case "main_config_save":
		$edit_web_title = $_POST['edit_web_title'];
		$edit_email_service = $_POST['edit_email_service'];
		$edit_email_footer = $_POST['edit_email_footer'];
		$edit_gateway_number = $_POST['edit_gateway_number'];
		$edit_gateway_timezone = $_POST['edit_gateway_timezone'];
		$edit_default_rate = $_POST['edit_default_rate'];
		$edit_gateway_sender_module = $_POST['edit_gateway_sender_module'];
		$edit_gateway_receiver_module = $_POST['edit_gateway_receiver_module'];
		$edit_themes_module = $_POST['edit_themes_module'];
		$edit_language_module = $_POST['edit_language_module'];
		$edit_sms_max_count = ( $_POST['edit_sms_max_count'] > 1 ? $_POST['edit_sms_max_count'] : 1 );
		$edit_default_credit = $_POST['edit_default_credit'];
		$edit_enable_register = $_POST['edit_enable_register'];
		$edit_enable_forgot = $_POST['edit_enable_forgot'];
		$db_query = "
	    UPDATE "._DB_PREF_."_tblConfig_main 
	    SET c_timestamp='".mktime()."',
		cfg_web_title='$edit_web_title',
		cfg_email_service='$edit_email_service',
		cfg_email_footer='$edit_email_footer',
		cfg_gateway_number='$edit_gateway_number',
		cfg_default_rate='$edit_default_rate',
		cfg_datetime_timezone='$edit_gateway_timezone',
		cfg_sender_gateway_module='$edit_gateway_sender_module',
		cfg_receiver_gateway_module='$edit_gateway_receiver_module',
		cfg_themes_module='$edit_themes_module',
		cfg_language_module='$edit_language_module',
		cfg_sms_max_count='$edit_sms_max_count',
		cfg_default_credit='$edit_default_credit',
		cfg_enable_register='$edit_enable_register',
		cfg_enable_forgot='$edit_enable_forgot'
	";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Main configuration changes has been saved');
		header("Location: index.php?app=menu&inc=main_config&op=main_config");
		exit();
		break;
}

?>
