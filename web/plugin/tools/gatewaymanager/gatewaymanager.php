<?php
defined ( '_SECURE_' ) or die ( 'Forbidden' );

if (!isadmin()) { forcenoaccess (); };

$content .= "<h2>" . _ ( 'Manage gateway' ) . "</h2>";
$content .= gatewaymanager_display ();
echo $content;

?>