<?php
defined ( '_SECURE_' ) or die ( 'Forbidden' );

if (!isadmin()) { forcenoaccess (); };

$name = $_REQUEST['name'];

switch ($op) {
	case 'toggle_status':
		if (gatewaymanager_set_active($name)) {
			$error_string = '<div class=error_string>'._('You have enabled gateway plugin').' '.$name.'</div>';
		}
		break;
}

$content = $error_string;
$content .= "<h2>" . _ ( 'Manage gateway' ) . "</h2>";
$content .= gatewaymanager_display ();
echo $content;

?>