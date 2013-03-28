<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php include $apps_path['themes']."/".$themes_module."/header.php"; ?>
<?php echo "<div align='center'>"; ?>
<?php echo $error_content; ?>
<?php echo "<p><a href='".$http_path['base']."'>"._('Home')."</a></p>"; ?>
<?php echo "</div>"; ?>
<?php include $apps_path['themes']."/".$themes_module."/footer.php"; ?>
