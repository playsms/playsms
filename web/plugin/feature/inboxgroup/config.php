<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php
// insert to left menu array
if (isadmin()) {
	$menutab_feature = $core_config['menutab']['feature'];
	$menu_config[$menutab_feature][] = array("index.php?app=menu&inc=feature_inboxgroup&op=list", _('Group inbox'));
}
?>