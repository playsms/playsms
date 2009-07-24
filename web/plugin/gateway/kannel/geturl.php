<?
chdir ("../../../");
include "init.php";
include $apps_path['libs']."/function.php";
chdir ("plugin/gateway/kannel");

$remote_addr = $_SERVER["REMOTE_ADDR"];
if ($remote_addr != $kannel_param['bearerbox_host'])
{
    die();
}

$t = trim($_GET['t']); 	// sms_datetime
$q = trim($_GET['q']); 	// sms_sender
$a = trim($_GET['a']); 	// message

if ($t && $q && $a)
{
    // collected:
    // $sms_datetime, $sms_sender, $message
    setsmsincomingaction($t,$q,$a);
}
?>