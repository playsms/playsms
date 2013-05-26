<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

switch ($op) {
	case "main_config":
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
		for ($i=0;$i<count($core_config['gatewaylist']);$i++) {
			$gateway = $core_config['gatewaylist'][$i];
			$gw = gateway_get();
			if ($gateway == $gw) $selected = "selected";
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

		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
		<h2>"._('Main configuration')."</h2>
		<p>
		<form action='index.php?app=menu&inc=main_config&op=main_config_save' method='post'>
		<table width='100%' cellpadding='1' cellspacing='2' border='0'>
		<tbody>
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
			<td>"._('Active gateway module')."</td><td>:</td><td><select name='edit_gateway_module'>$option_gateway_module</select></td>
		</tr>
		<tr>
			<td>"._('Active themes')."</td><td>:</td><td><select name='edit_themes_module'>$option_themes_module</select></td>
		</tr>
		<tr>
			<td>Default language</td><td>:</td><td><select name='edit_language_module'>$option_language_module</select></td>
		</tr>
		</tbody>
		</table>
		<p><input type='submit' class='button' value='"._('Save')."'>
		</form>";
		echo $content;
		break;
	case "main_config_save":
		$edit_web_title = $_POST['edit_web_title'];
		$edit_email_service = $_POST['edit_email_service'];
		$edit_email_footer = $_POST['edit_email_footer'];
		$edit_gateway_number = core_sanitize_sender($_POST['edit_gateway_number']);
		$edit_gateway_timezone = $_POST['edit_gateway_timezone'];
		$edit_default_rate = $_POST['edit_default_rate'];
		$edit_gateway_module = $_POST['edit_gateway_module'];
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
				cfg_gateway_module='$edit_gateway_module',
				cfg_themes_module='$edit_themes_module',
				cfg_language_module='$edit_language_module',
				cfg_sms_max_count='$edit_sms_max_count',
				cfg_default_credit='$edit_default_credit',
				cfg_enable_register='$edit_enable_register',
				cfg_enable_forgot='$edit_enable_forgot'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Main configuration changes has been saved');
		header("Location: index.php?app=menu&inc=main_config&op=main_config");
		exit();
		break;
}

?>