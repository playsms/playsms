<?php

include "../src/Playsms/Tpl.php";

$data = 'THE DATA HERE';

$loops = array(
    	'lines' => array(
		array('color' => 'Red',   'hexcode' => '#FF0000'),
        	array('color' => 'Green', 'hexcode' => '#00FF00'),
        	array('color' => 'Blue',  'hexcode' => '#0000FF'),
    	),
);

$tpl = new \Playsms\Tpl;

$tpl->setTemplate('./templates/test6.html');

$tpl->setVars(array(
    'title' => 'THE TITLE HERE',
    'content' => 'THE CONTENT HERE',
    ))
    ->setLoops($loops)
    ->setInjects(array('data'));

$tpl->compile();

echo $tpl->getCompiled();
