<?php
defined('_SECURE_') or die('Forbidden');

/**
 * Send email service.
 * 
 * Parameters are as follows:
 *	$data = array(
 * 		'mail_from_name'		=> '', 
 *		'mail_from'		=> '',
 *		'mail_reply_name'	=> '',
 *		'mail_reply'		=> '',
 *		'mail_error_name'	=> '',
 *		'mail_error'		=> '',
 *		'mail_to_name'		=> '',
 *		'mail_to'		=> '',
 *		'mail_cc_name'		=> '',
 *		'mail_cc'		=> '',
 *		'mail_bcc_name'		=> '',
 *		'mail_bcc'		=> '',
 *		'mail_subject'		=> '',
 *		'mail_body'		=> '',
 *		'mail_charset'		=> '',
 *		'smtp_relm'		=> '',
 *		'smtp_user'		=> '',
 *		'smtp_pass'		=> '',
 *		'smtp_host'		=> '',
 *		'smtp_port'		=> '',
 *		'smtp_debug'		=> '',
 *		'smtp_direct'		=> '',
 *		'attachment_data'	=> '',
 *		'attachment_name'	=> '',
 *		'attachment_type'	=> ''
 *	);
 * 
 * @param array
 * @return boolean
 */
function sendmail($data=array()) {
	global $core_config;
	$ok = false;
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
		if (x_hook($core_config['toolslist'][$c],'sendmail',array($data))) {
			logger_print("sent from:".$data['mail_from']." to:".$data['mail_to']." subject:".$data['mail_subject'], 2, "sendmail");
			$ok = true;
			break;
		}
	}
	return $ok;
}
