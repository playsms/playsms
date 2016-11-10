<?php
defined ( '_SECURE_' ) or die ( 'Forbidden' );
if (! auth_isadmin ()) {
	auth_block ();
};

$callback_url = $_SERVER['HTTP_HOST'] . dirname ( $_SERVER['PHP_SELF'] ) . "/plugin/gateway/orange/callback.php";
$callback_url = str_replace ( "//", "/", $callback_url );
$callback_url = "http://" . $callback_url;

switch (_OP_) {
	case "manage" :
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>" . _ ( 'Manage Orange' ) . "</h2>
			<form action=index.php?app=main&inc=gateway_orange&op=manage_save method=post>
			"._CSRF_FORM_."
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _ ( 'Gateway name' ) . "</td><td>Orange</td>
			</tr>
			<tr>
				<td>" . _ ( 'Country Code' ) . "</td><td><input type=text maxlength=5 name=up_country_code value=\"" . $plugin_config['orange']['country_code'] . "\"> " . _hint ( _ ( 'Alphabetic char Eg: SEN for SENEGAL' ) ) . "</td>
			</tr>
			<tr>
				<td>" . _ ( 'Client Id' ) . "</td><td><input type=text maxlength=102 name=up_client_id value=\"". $plugin_config['orange']['client_id'] ."\">". _hint ( _ ('Fill to change the Client Id' ) ). "</td>
			</tr>
			<tr>
				<td>" . _ ( 'Client Secret' ) . "</td><td><input type=up_client_secret maxlength=255 name=up_client_secret value=\"". $plugin_config['orange']['client_secret'] ."\"> " . _hint ( _ ( 'Fill to change the Client Secret' ) ) . "</td>
			</tr>
			<tr>
				<td>" . _ ( 'Sender Address' ) . "</td><td><input type=text maxlength=250 name=up_sender_address value=\"" . $plugin_config['orange']['sender_address'] . "\"> " . _hint ( _ ( 'Sender Address Eg +221xxxxxxxxx for Senegal' ) ) . "</td>
			</tr>
			<tr>
				<td>" . _ ( 'Sender Name' ) . "</td><td><input type=text maxlength=16 name=up_sender_name value=\"" . $plugin_config['orange']['sender_name'] . "\"> " . _hint ( _ ( 'Contact developer.orange.com support and request for one or leave this field empty to disable' ) ) . "</td>
			</tr>
			<tr>
				<td>" . _ ( 'Orange API URL' ) . "</td><td><input type=text maxlength=250 name=up_send_url value=\"" . $plugin_config['orange']['send_url'] . "\"> " . _hint ( _ ( 'No trailing slash' ) . " \"/\"" ) . "</td>
			</tr>
			<tr>
				<td>" . _ ( 'Module Timezone' ) . "</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"" . $plugin_config['orange']['datetime_timezone'] . "\"> " . _hint ( _ ( 'Eg: +0000 for Dakar/Senegal timezone' ) ) . "</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\"" . _ ( 'Save' ) . "\">
			</form>
			<br />
			" . _ ( 'Notes' ) . ":<br />
			- " . _ ( 'Orange is a bulk SMS provider' ) . ", <a href=\"https://developer.orange.com/signup/\" target=\"_blank\">" . _ ( 'Link' ) . "</a><br />";
		$content .= _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;

	case "manage_save" :

		$up_country_code = $_POST['up_country_code'];
		$up_client_id = $_POST['up_client_id'];
		$up_client_secret = $_POST['up_client_secret'];
		$up_sender_address = $_POST['up_sender_address'];
		$up_sender_name = $_POST['up_sender_name'];

		$up_global_timezone = $_POST['up_global_timezone'];
		$up_send_url = $_POST['up_send_url'];
		$up_incoming_path = $_POST['up_incoming_path'];

		$db_query = "
			UPDATE " . _DB_PREF_ . "_gatewayOrange_config
			SET c_timestamp='" . mktime() . "',				
			cfg_client_id='$up_client_id',
			cfg_client_secret='$up_client_secret',
			cfg_sender_address='$up_sender_address',
			cfg_sender_name='$up_sender_name',
			cfg_country_code='$up_country_code',
			cfg_token='',
			cfg_token_updated_at=0,
			cfg_token_expirate_at=0,
			cfg_datetime_timezone='$up_global_timezone',
			cfg_send_url='$up_send_url',
			cfg_incoming_path='$up_incoming_path'";
		if (@dba_affected_rows ( $db_query )) {
			$_SESSION['dialog']['info'][] = _ ( 'Gateway module configurations has been saved' );
		}else{
			$_SESSION['dialog']['info'][] = _ ( 'Error: configurations not saved' );
		}
		
		header ( "Location: index.php?app=main&inc=gateway_orange&op=manage" );
		exit ();
		break;
}
