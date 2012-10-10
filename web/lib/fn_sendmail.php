<?php
defined('_SECURE_') or die('Forbidden');

function sendmail($mail_from,$mail_to,$mail_subject="",$mail_body="") {
	global $core_config;
	$ok = false;
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if (x_hook($core_config['toolslist'][$c],'sendmail',array($mail_from,$mail_to,$mail_subject,$mail_body))) {
			logger_print("sent from:".$mail_from." to:".$mail_to." subject:".$mail_subject, 2, "sendmail");
			$ok = true;
			break;
		}
	}
	return $ok;
}

?>