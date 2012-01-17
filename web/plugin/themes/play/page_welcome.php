<?php if(!(defined('_SECURE_'))){die('Intruder alert');}; ?>
<?php
if (!valid()) { auth_logout(); };

include $apps_path['themes']."/".$themes_module."/header.php";

$content = "\n".
'  <h3>'._('Welcome to playSMS').'</h3>' .
'  <a class="info btn" href="index.php?get=1">'._('About playSMS').'</a>' .
'  <a class="info btn" href="index.php?get=2">'._('Changelog').'</a>' .
'  <a class="info btn" href="index.php?get=3">'._('Installation Guide').'</a>' .
'  <a class="info btn" href="index.php?get=4">'._('F.A.Q').'</a>' .
'  <a class="info btn" href="index.php?get=5">'._('License').'</a>' .
'  <hr size=1>';


$get = ( $_REQUEST['get'] ? $_REQUEST['get'] : 1 );
switch ($get)
{
	case 1: $read = "README"; break;
	case 2: $read = "CHANGELOG"; break;
	case 3: $read = "INSTALL"; break;
	case 4: $read = "FAQ"; break;
	case 5: $read = "LICENSE"; break;
}

$fn = $apps_path['base']."/docs/".$read;
$fd = @fopen($fn, "r");
$fc = @fread($fd, filesize($fn));
@fclose($fd);
$content .= "<pre>".htmlentities($fc)."</pre>";

echo $content;

include $apps_path['themes']."/".$themes_module."/footer.php";
?>
