<?php

function rateless_hook_rate_cansend($username, $sms_len, $unicode, $sms_to) {
	return true;
}

function rateless_hook_rate_getusercredit($username) {
	return 0;
}

function rateless_hook_rate_getcharges($uid, $sms_len, $unicode, $sms_to) {
	# nicked from https://sakari.io/sms-length-calculator
	static $lengths = [
		'ascii' => [
			160,
			306,
			459,
			612,
			765,
			918,
			1071,
			1224,
			1377,
			1530,
			1683,
			1836,
			1989,
			2142,
			2295,
			2448,
			2601,
			2754,
			2907,
			3060,
			3213,
			3366,
			3519,
			3672,
			3825,
			3978,
			4131,
			4284,
			4437,
			4590,
		],
		'unicode' => [
			70,
			134,
			201,
			268,
			335,
			402,
			469,
			536,
			603,
			670,
			737,
			804,
			871,
			938,
			1005,
			1072,
			1139,
			1206,
			1273,
			1340,
			1407,
			1474,
			1541,
			1608,
			1675,
			1742,
			1809,
			1876,
			1943,
			2010,
		],
	];
	foreach ($lengths[$unicode ? 'unicode' : 'ascii'] as $i => $length) {
		$count = $i + 1;
		if ($sms_len <= $length)
			break;
	}
	return [$count, 0, 0];
}
