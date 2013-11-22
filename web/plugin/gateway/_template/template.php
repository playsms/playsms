<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/template/config.php";

$gw = gateway_get();

if ($gw == $template_param['name']) {
	$status_active = "<span class=status_active />";
} else {
	$status_active = "<span class=status_inactive />";
}


switch ($op)
{
	case "manage":
		if ($err = $_SESSION['error_string'])
		{
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
	    <h2>"._('Manage template')."</h2>
	    <p>
	    <form action=index.php?app=menu&inc=gateway_template&op=manage_save method=post>
	    <table class=ps_table cellpadding=1 cellspacing=2 border=0>
		<tr>
		    <td class=label-sizer>"._('Gateway name')."</td><td>template $status_active</td>
		</tr>
		<tr>
		    <td>"._('Template installation path')."</td><td><input type=text size=30 maxlength=250 name=up_path value=\"".$template_param['path']."\"> ("._('No trailing slash')." \"/\")</td>
		</tr>	    
	    </table>	    
	    <p><input type=submit class=button value=\""._('Save')."\">
	    </form>";
		echo $content;
		break;
	case "manage_save":
		$up_path = $_POST['up_path'];
		$_SESSION['error_string'] = _('No changes has been made');
		if ($up_path)
		{
			$db_query = "
		UPDATE "._DB_PREF_."_gatewayTemplate_config 
		SET c_timestamp='".mktime()."',cfg_path='$up_path'
	    ";
			if (@dba_affected_rows($db_query))
			{
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=menu&inc=gateway_template&op=manage");
		exit();
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='template'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_template&op=manage");
		exit();
		break;
}

?>