<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$get = ( $_REQUEST['get'] ? $_REQUEST['get'] : 1 );
switch ($get) {
	case 1: $read = "README"; break;
	case 2: $read = "CHANGELOG"; break;
	case 3: $read = "INSTALL"; break;
	case 4: $read = "FAQ"; break;
	case 5: $read = "LICENSE"; break;
	case 6: $read = "WEBSERVICES"; break;
}

${'youarehere_'.$get} = 'class=youarehere';

$content = "
	<h2>"._('Welcome to playSMS')."</h2>
	<input type=button $youarehere_1 value=\""._('About playSMS')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=page_welcome&get=1')\" class=\"button\" />
	<input type=button $youarehere_2 value=\""._('Changelog')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=page_welcome&get=2')\" class=\"button\" />
	<input type=button $youarehere_4 value=\""._('F.A.Q')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=page_welcome&get=4')\" class=\"button\" />
	<input type=button $youarehere_5 value=\""._('License')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=page_welcome&get=5')\" class=\"button\" />
	<input type=button $youarehere_6 value=\""._('Webservices')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=page_welcome&get=6')\" class=\"button\" />
	<p>";

$fn = $apps_path['base']."/docs/".$read;
if (file_exists($fn)) {
	$fd = @fopen($fn, "r");
	$fc = @fread($fd, filesize($fn));
	@fclose($fd);
	$fc = str_replace('{VERSION}', $core_config['version'], $fc);
	$fi = pathinfo($fn);
	if ($fi['extension'] == 'md') {
		$content .= Parsedown::instance()->parse($fc);
	} else if ($fi['extension'] == 'html') {
		$content .= $fc;
	} else {
		$content .= '<pre>'.htmlentities($fc).'</pre>';
	}
}

echo $content;

?>