<?php if(!(defined('_SECURE_'))){die('Intruder alert');}; ?>
<?php
// insert to left menu array
if (isadmin()) {
	$arr_menu['Feature'][] = array("index.php?app=menu&inc=feature_inboxgroup&op=list", "Group inbox");
}
?>