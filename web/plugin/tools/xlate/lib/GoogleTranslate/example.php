
<?php

require_once('googleTranslate.class.php');

$gt = new GoogleTranslateWrapper();

/* Translate text from one language to another */
$test = 'hello';

/* language detection */
print_r($gt->detectLanguage($test));

/* Translate */
echo $gt->translate($test, "fr", "en");

/* Was translation successful */
echo $gt->isSuccess();



?>