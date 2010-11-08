<?php include $apps_path['themes']."/".$themes_module."/header.php"; ?>
<?php
if ($errid) {
    $err = logger_get_error_string($errid);
}
if ($err) {
    $error_content = "<div class=error_string>$err</div>";
}
echo $error_content;
?>
<?php include $apps_path['themes']."/".$themes_module."/footer.php"; ?>
