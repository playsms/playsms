<?php
function phonebook_groupid2code($gpid) {
    global $core_config;
    if ($gpid) {
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
	    if ($gp_code = x_hook($core_config['toolslist'][$c],'phonebook_groupid2code',array($gpid))) {
		break;
	    }
	}
    }
    return $gp_code;
}

function phonebook_groupcode2id($uid,$gp_code) {
    global $core_config;
    if ($uid && $gp_code) {
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
	    if ($gpid = x_hook($core_config['toolslist'][$c],'phonebook_groupcode2id',array($uid,$gp_code))) {
		break;
	    }
	}
    }
    return $gpid;
}

function phonebook_number2name($p_num) {
    global $core_config;
    if ($p_num) {
	for ($c=0;$c<count($core_config['toolslist']);$c++) {
	    if ($p_desc = x_hook($core_config['toolslist'][$c],'phonebook_number2name',array($p_num))) {
		break;
	    }
	}
    }
    return $p_desc;
}

?>