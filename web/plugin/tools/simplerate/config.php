<?php
if (isadmin()) {
    $arr_menu['Administration'][] = array("menu.php?inc=tools_simplerate&op=simplerate_list", _('Manage SMS rate'));
}
?>