<?php
if (isadmin()) {
    $arr_menu['Tools'][] = array("menu.php?inc=tools_simplerate&op=simplerate_list", _('SMS rate'));
}
?>