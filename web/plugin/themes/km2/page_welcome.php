<?php if(!(defined('_SECURE_'))){die('Intruder alert');}; ?>
<?php
if (!valid()) { auth_logout(); };

include $apps_path['themes']."/".$themes_module."/header.php";

$content = "
    <h2>"._('Welcome to PlaySMS')."</h2>
    <p>";

$content .= "
    <!--input type=button value=\"Vos statistiques\" onClick=\"javascript:linkto('index.php?app=menu&inc=user_home&op=user_stat')\" class=\"button\" /-->
";

if($status == 2) {
	$content .= "
    <input type=button value='"._('About playSMS')."' onClick=\"javascript:linkto('index.php?get=1')\" class='button' />
    <input type=button value='"._('Changelog')."' onClick=\"javascript:linkto('index.php?get=2')\" class='button' />
    <input type=button value='"._('Installation Guide')."' onClick=\"javascript:linkto('index.php?get=3')\" class='button' />
    <input type=button value='"._('F.A.Q')."' onClick=\"javascript:linkto('index.php?get=4')\" class='button' />
    <input type=button value='"._('License')."' onClick=\"javascript:linkto('index.php?get=5')\" class='button' />
";
}

$content .= "
    <!-- input type=button value='"._('FAQ Users')."' onClick=\"javascript:linkto('index.php?get=6')\" class='button' /-->";

$content .= "<hr size='1'>";


if($status == 2) {
	$get = ( $_GET['get'] ? $_GET['get'] : 1 );
	switch ($get)
	{
		case 1: $read = "README"; break;
		case 2: $read = "CHANGELOG"; break;
		case 3: $read = "INSTALL"; break;
		case 4: $read = "FAQ"; break;
		case 5: $read = "LICENSE"; break;
		case 6: $read = "FAQUSERS"; break;
	}
}
else {
	$get = ( $_GET['get'] ? $_GET['get'] : 1 );
	switch ($get)
	{
		case 6: $read = "FAQUSERS"; break;
	}
}

$fn = $apps_path['base']."/docs/".$read;
$fd = @fopen($fn, "r");
$fc = @fread($fd, filesize($fn));
@fclose($fd);

//$content .= "<pre>".htmlentities($fc)."</pre>";
$content .= "<pre>".$fc."</pre>";

echo $content;

include $apps_path['themes']."/".$themes_module."/footer.php";

?>
