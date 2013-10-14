<?php

if (isadmin()) {
	$menutab = $core_config ['menutab'] ['administration'];
	$menu_config [$menutab] [] = array(
	    "index.php?app=menu&inc=tools_report&op=report_admin",
	    _('All reports')
	);
}

$menutab = $core_config ['menutab'] ['my_account'];
$menu_config [$menutab] [] = array(
    "index.php?app=menu&inc=tools_report&op=report_user",
    _('My report')
);

?>