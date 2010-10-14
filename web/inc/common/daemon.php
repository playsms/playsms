<?php

playsmsd();
getsmsinbox();
getsmsstatus();
execcommoncustomcmd();

if ($_REQUEST['op']=='daemon') {
    echo "<p><font color=green>"._('playSMS server successfully refreshed')."</font></p>";
    die();
}

$url = $_REQUEST['url'];
if (isset($url)) {
    $url = base64_decode($url);
    header ("Location: $url");
}
?>