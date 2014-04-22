<?php

include "../src/Playsms/Tpl.php";

$all_fruits = 'Apple, Banana and Orange';

$tpl = new \Playsms\Tpl(array('echo' => 'print'));

$tpl->setConfig(array(
	'dir_cache' => '/tmp'
))->setVars(array(
	'title' => 'This is test 3',
	'content' => 'This is sample content',
))->setInjects(array(
	'all_fruits'
))->setTemplate('./templates/test4.html')->compile();

print_r($tpl->getConfig());

echo $tpl->getContent()."\n<br />\n";

echo $tpl->getResult()."\n<br />\n";

echo $tpl->getCompiled()."\n<br />\n";
