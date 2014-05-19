<?php

$DAEMON_PROCESS = TRUE;

chdir('../../../../');

include 'init.php';
include _APPS_PATH_LIBS_ . '/function.php';

$fn = 'plugin/core/country/tools/countries.xml';
$fd = fopen($fn, 'r');
$xml = fread($fd, filesize($fn));
fclose($fd);

$countries = core_xml_to_array($xml);

$country = array();

foreach ($countries['country'] as $item) {
	$c = array_values($item);
	$country[] = array(
		'code' => $c[0]['code'],
		'phoneCode' => $c[0]['phoneCode'],
		'name' => addslashes($c[0]['name']),
	);	
}

$csv = '"code","phoneCode","name"'.PHP_EOL;
foreach ($country as $item) {
	$csv.= '"'.$item['code'].'","'.$item['phoneCode'].'","'.stripslashes($item['name']).'"'.PHP_EOL;
}

$fn = 'plugin/core/country/tools/countries.csv';
$fd = fopen($fn, 'w+');
fputs($fd, $csv);
fclose($fd);

$db_query = 'TRUNCATE '._DB_PREF_.'_tblCountry';
$db_result = dba_query($db_query);

$sql = $db_query.';'.PHP_EOL;
foreach ($country as $item) {
	$db_query = "INSERT INTO "._DB_PREF_."_tblCountry (country_code, country_name, country_prefix) VALUES ('".$item['code']."','".$item['name']."','".$item['phoneCode']."')";
	$db_result = dba_query($db_query);
	$sql.= $db_query.';'.PHP_EOL;
}

$fn = 'plugin/core/country/tools/countries.sql';
$fd = fopen($fn, 'w+');
fputs($fd, $sql);
fclose($fd);
