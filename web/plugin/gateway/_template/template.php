<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

include $core_config['apps_path']['plug']."/gateway/template/config.php";

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
	    <h2>"._('Manage template')."</h2>
	    <p>
	    <form action=index.php?app=main&inc=gateway_template&op=manage_save method=post>
	    "._CSRF_FORM_."
	    <table class=playsms-table cellpadding=1 cellspacing=2 border=0>
		<tr>
		    <td class=label-sizer>"._('Gateway name')."</td><td>template</td>
		</tr>
		<tr>
		    <td>"._('Template installation path')."</td><td><input type=text maxlength=250 name=up_path value=\"".$template_param['path']."\"> ("._('No trailing slash')." \"/\")</td>
		</tr>
	    </table>
	    <p><input type=submit class=button value=\""._('Save')."\">
	    </form>";
		_p($content);
		break;
	case "manage_save":
		$up_path = $_POST['up_path'];
		$_SESSION['dialog']['info'][] = _('No changes have been made');
		if ($up_path)
		{
			$db_query = "
		UPDATE "._DB_PREF_."_gatewayTemplate_config
		SET c_timestamp='".mktime()."',cfg_path='$up_path'
	    ";
			if (@dba_affected_rows($db_query))
			{
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: "._u('index.php?app=main&inc=gateway_template&op=manage'));
		exit();
		break;
}
