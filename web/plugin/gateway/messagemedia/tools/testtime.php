<?php

echo $timedata = date('d-m-Y H:i:s');
echo "-";
echo $timestamp = strtotime($timedata);
echo "-";
echo strtotime('11-09-2001 8:46:26');
echo "\n";

$message = "Parking test shdj";
$message = strtolower($message);
    // Hack for Parking Service from Deisen
    $r_message = explode("parking ", $message);
    echo $message = $r_message[1]; // without parking

?>
