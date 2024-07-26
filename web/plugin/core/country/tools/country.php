<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */

// check CLI
if (!(defined('STDIN') || in_array(PHP_SAPI, ['cli', 'cli-server', 'phpdbg']))) {
	echo "This script must be called from cli\n";
	exit();
}

$DAEMON_PROCESS = TRUE;

chdir('../../../../');

include 'init.php';
include _APPS_PATH_LIBS_ . '/function.php';

$fn = 'plugin/core/country/tools/countries.xml';
$fd = fopen($fn, 'r');
$xml = fread($fd, filesize($fn));
fclose($fd);

$countries = core_xml_to_array($xml);
$countries = $countries['string-array']['item'];

$country = [];
foreach ( $countries as $item ) {
	$phoneCode = explode(',', $item['countryCode']);
	$phoneCode = $phoneCode[0];

	$country[] = [
		'code' => strtoupper($item['iso2']),
		'phoneCode' => preg_replace('/\D/', '', $phoneCode),
		'name' => preg_replace('/A-Za-z\+/', '', $item['country']),
	];
}
$countries = $country;

$csv = '"code","phoneCode","name"' . PHP_EOL;
foreach ( $countries as $country ) {
	$csv .= '"' . $country['code'] . '","' . $country['phoneCode'] . '","' . $country['name'] . '"' . PHP_EOL;
}

$fn = 'plugin/core/country/tools/countries.csv';
$fd = fopen($fn, 'w+');
fputs($fd, $csv);
fclose($fd);

$db_query = 'TRUNCATE ' . _DB_PREF_ . '_tblCountry';
$db_result = dba_query($db_query);

foreach ( $countries as $country ) {
	$db_query = "INSERT INTO " . _DB_PREF_ . "_tblCountry (country_code, country_name, country_prefix) VALUES (?,?,?)";
	$db_result = dba_query($db_query, [
		$country['code'],
		$country['name'],
		$country['phoneCode'],
	]);
}
