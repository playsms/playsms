<?php

if (! $called_from_hook_call) {
	chdir("../../../");
	include "init.php";
	include $apps_path['libs']."/function.php";
	chdir("plugin/feature/sms_command/");
	$requests = $_REQUEST;
}

//Verificar se existem comandos com alarmes
$db_query = "SELECT * FROM "._DB_PREF_."_featureCommand WHERE with_alarm=TRUE";	
$db_result = dba_query($db_query);
if ($db_result)
{	
	while ($db_row = dba_fetch_array($db_result))
	{
		$command_keyword = $db_row['command_keyword'];
		$time_now = $datetime_now;
		$ret = sms_command_handle($datetime_now,'callback',$command_keyword,'');
	}
}

?>
