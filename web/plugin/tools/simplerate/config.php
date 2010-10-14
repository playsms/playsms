<?php
if (isadmin()) {
    $arr_menu['Tools'][] = array("index.php?app=menu&inc=tools_simplerate&op=simplerate_list", _('Manage SMS rate'));
}
?>