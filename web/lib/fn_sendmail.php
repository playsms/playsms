<?php
function sendmail($mail_from,$mail_to,$mail_subject="",$mail_body="") {
    global $core_config;
    $ok = false;
    for ($c=0;$c<count($core_config['toolslist']);$c++) {
	if (x_hook($core_config['toolslist'][$c],'sendmail',array($mail_from,$mail_to,$mail_subject,$mail_body))) {
	    $ok = true;
	    break;
	}
    }
    return $ok;
}

?>