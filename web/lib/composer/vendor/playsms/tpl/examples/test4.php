<?php

include "../src/Playsms/Tpl.php";

$all_fruits = 'Apple, Banana and Orange';

$tpl = new Playsms\Tpl;

$tpl->setVars(array(
	'title' => 'This is test 3',
	'content' => 'This is sample content',
))->setInjects(array(
	'all_fruits'
))->setTemplate('./templates/test4.html')->compile();

echo $tpl->getCompiled();
