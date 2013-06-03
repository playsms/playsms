<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php

$phonebook_row_limit = 20000;

$phonebook_icon_export = "<img src=\"".$http_path['themes']."/".$themes_module."/images/export.gif\" alt=\""._('Export')."\" title=\""._('Export')."\" border=0>";
$phonebook_icon_import = "<img src=\"".$http_path['themes']."/".$themes_module."/images/import.gif\" alt=\""._('Import')."\" title=\""._('Import')."\" border=0>";
$phonebook_icon_publish = "<img src=\"".$http_path['themes']."/".$themes_module."/images/publish.gif\" alt=\""._('Publish')."\" title=\""._('Publish')."\" border=0>";
$phonebook_icon_unpublish = "<img src=\"".$http_path['themes']."/".$themes_module."/images/unpublish.gif\" alt=\""._('Unpublish')."\" title=\""._('Unpublish')."\" border=0>";

$menutab_tools = $core_config['menutab']['tools'];
$menu_config[$menutab_tools][] = array("index.php?app=menu&inc=tools_phonebook&op=phonebook_list", _('Phonebook'));

?>