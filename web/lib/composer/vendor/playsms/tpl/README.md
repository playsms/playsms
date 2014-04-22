README
======

Dead simple PHP template engine

Information      | Description
---------------- | ----------------
Author           | Anton Raharja
Version          | 1.0.3
Release date     | 140422


Install
-------

Using composer by providing or editing `composer.json`.

Minimum `composer.json`:

```
{
	"require": {
		"playsms/tpl": "1.*"
	}
}
```

More information about composer can be found at its website https://getcomposer.org

This package can also be installed without composer. You can simply include the `src/Playsms/Tpl.php`.


Usage example
-------------

An example template file `the_page.html`:

```
<div>
	<p>This is the title: {{ title }}</p>
	<p>This is the content: {{ content }}</p>
	<p>And this is the data: {{ $data }}</p>
	<loop.lines>
	<p style='background-color: {{ lines.hexcode }}'>Color: {{ lines.color }}</p>
	</loop.lines>
</div>
```

Example PHP file `show_page.php` using the template file `the_page.html`:

```
<?php

require 'vendor/autoload.php';

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
```

After `compile()` you can get compiled content using `getCompiled()`:

```
<div>                                                                                                                                                        
    <p>This is the title: THE TITLE HERE</p>                                                                                                                 
    <p>This is the content: THE CONTENT HERE</p>                                                                                                             
    <p>And this is the data: THE DATA HERE</p>                                                                                                               
                                                                                                                                                             
    <p style='background-color: #FF0000'>Color: Red</p>                                                                                                      
                                                                                                                                                             
    <p style='background-color: #00FF00'>Color: Green</p>                                                                                                    
                                                                                                                                                             
    <p style='background-color: #0000FF'>Color: Blue</p>                                                                                                     
                                                                                                                                                             
</div>
```

For more examples please see **examples** folder.

Other documents can be found in **docs** folder.
