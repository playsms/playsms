<?php

/**
 * Copy this file to playSMS web path and run from shell
 */

include 'init.php';
include $core_config['apps_path']['libs'] . '/function.php';

ob_end_clean();

$sender_username = 'user3';
$from = '1234';
$to = [];
$messages = [];

// send to 10k number
for ($i = 0; $i < 10000; $i++) {
	$to[] = 15551870001 + $i;
}

// send 10 types of SMS
for ($i = 0; $i < 100; $i++) {
	$messages[] = 'Status G/' . ($i + 1) . ' V/' . str_shuffle(uniqid()) . ' HAVE FUN!';
}

$total_duration = 0;
$start = time();

$i = 0;
foreach ( $messages as $message ) {
	$i++;
	echo '#' . $i . '. c:' . count($to) . ' m:' . $message . PHP_EOL;
	$time_start = time();
	echo "  Start: " . date('Y-m-d H:i:s', $time_start) . PHP_EOL;
	list($status, $sms_to, $smslog_id, $queue, $counts, $error_strings) = sendsms($sender_username, $to, $message, 'text', 0, '', true, '', $from, '');
	$time_stop = time();
	echo "  Stop: " . date('Y-m-d H:i:s', $time_stop) . PHP_EOL;
	$duration = $time_stop - $time_start;
	echo "  Duration: " . date('i:s', $duration) . PHP_EOL;
	$total_duration += $duration;
}

$stop = time();

echo PHP_EOL;
echo "First start: " . date('H:i:s', $start) . PHP_EOL;
echo "Last stop: " . date('H:i:s', $stop) . PHP_EOL;
echo "Total SMS: " . count($to) * count($messages) . PHP_EOL;

if ($total_duration > 3600) {
	$total_duration_formatted = date('H:i:s', $total_duration);
} else {
	$total_duration_formatted = date('i:s', $total_duration);
}
echo "Total duration: " . $total_duration_formatted . PHP_EOL;
