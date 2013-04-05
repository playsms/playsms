<?php
defined('_SECURE_') or die('Forbidden');

function download($content, $fn='', $content_type='') {
	$fn = ( $fn ? $fn : 'download.txt' );
	$content_type = ( $content_type ? $content_type : 'text/plain' );
	ob_end_clean();
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Type: '.$content_type);
	header('Content-Disposition: attachment; filename='.$fn);
	echo $content;
	die();
}

?>