<?php if(!(defined('_SECURE_'))){die('Intruder alert');}; ?>
<?php
if (!valid()) { auth_logout(); };

include $apps_path['themes']."/".$themes_module."/header.php";

$content = "
    <h2>"._('Welcome to playSMS')."</h2>
    <p>
    <input type=button value=\""._('About playSMS')."\" onClick=\"javascript:linkto('index.php?get=1')\" class=\"button\" />
    <input type=button value=\""._('Changelog')."\" onClick=\"javascript:linkto('index.php?get=2')\" class=\"button\" />
    <input type=button value=\""._('Installation Guide')."\" onClick=\"javascript:linkto('index.php?get=3')\" class=\"button\" />
    <input type=button value=\""._('F.A.Q')."\" onClick=\"javascript:linkto('index.php?get=4')\" class=\"button\" />
    <input type=button value=\""._('License')."\" onClick=\"javascript:linkto('index.php?get=5')\" class=\"button\" />
    <input type=button value=\""._('HTTP API')."\" onClick=\"javascript:linkto('index.php?get=6')\" class=\"button\" />
    <hr size=1>
";

$get = ( $_REQUEST['get'] ? $_REQUEST['get'] : 1 );
switch ($get)
{
	case 1: $read = "README"; break;
	case 2: $read = "CHANGELOG"; break;
	case 3: $read = "INSTALL"; break;
	case 4: $read = "FAQ"; break;
	case 5: $read = "LICENSE"; break;
	case 6: $read = "WEBSERVICES"; break;
}

$fn = $apps_path['base']."/docs/".$read;
$fd = @fopen($fn, "r");
$fc = @fread($fd, filesize($fn));
@fclose($fd);
$content .= "<pre>".htmlentities($fc)."</pre>";

echo $content;

include $apps_path['themes']."/".$themes_module."/footer.php";
?>