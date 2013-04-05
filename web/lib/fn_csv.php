<?php
defined('_SECURE_') or die('Forbidden');

function csv_format($item) {
	if (is_array($item)) {
		$ret = '';
		for ($i=0;$i<count($item);$i++) {
			foreach ($item[$i] as $key => $val) {
				$val = str_replace('"', "'", $val);
				$ret .= '"'.$val.'",';
			}
			$ret = substr($ret, 0, -1);
			$ret .= "\n";
		}
	}
	return $ret;
}

?>