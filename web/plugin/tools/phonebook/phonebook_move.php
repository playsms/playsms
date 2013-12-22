<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

echo "move";

echo "<pre>";
print_r($_REQUEST);
echo "</pre>";

$checkid = $_REQUEST['checkid'];
$itemid = $_REQUEST['itemid'];

foreach ($checkid as $key => $val) {
	$c_itemid = $itemid[$key];
}