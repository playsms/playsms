<?php
defined('_SECURE_') or die('Forbidden');

if(!isadmin()){forcenoaccess();};

switch ($op)
{
	case "playsmslog_list":
		$log = playsmslog_view();
		$content = "
			<h2>"._('View log')."</h2>
			<h3>" . $core_config['apps_path']['logs'] . "/playsms.log</h3>
			<div style=\"height: 70%\">
				<textarea id=\"playsmslogView\" style=\"width: 100%; height: 100%; border: 1px solid #ECF0F1; padding: 5px; font-size: 10pt;\" wrap=off>".$log."</textarea>
			</div>
			</p>
			<script language='javascript' type='text/javascript'>
				<!--//
				var textarea = document.getElementById('playsmslogView');
				textarea.scrollTop = textarea.scrollHeight;
				//-->
			</script>
		";
		echo $content;
		break;
}

?>
