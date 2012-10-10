<?php
defined('_SECURE_') or die('Forbidden');

if(!isadmin()){forcenoaccess();};

switch ($op)
{
	case "playsmslog_list":
		$log = playsmslog_view();
		$content = "
			<h2>"._('View log')."</h2>
			<p>"._('Log file').": ".$core_config['apps_path']['logs']."/playsms.log</p>
			<p>
			<textarea id=\"playsmslogView\" rows=\"36\" cols=\"98\">".$log."</textarea>
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
