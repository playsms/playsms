<?php
    include "init.php";
    include $apps_path['libs']."/function.php";

    $error_content = "";
    if ($err)
    {
        $error_content .= "<p align=center><font color=red>$err</font></p>";
    }

    include $apps_path['themes']."/".$themes_module."/header.php";

    if (valid()) 
    {
        include $apps_path['themes']."/".$themes_module."/default.php";
    }
    else
    {
        include $apps_path['themes']."/".$themes_module."/loginpage.php";
    }

    include $apps_path['themes']."/".$themes_module."/footer.php";
?>