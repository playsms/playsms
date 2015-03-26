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
defined('_SECURE_') or die('Forbidden');

if (!auth_isadmin()) {
	auth_block();
}

switch (_OP_) {
	case 'add_smsc' :
		$c_gateway = $_REQUEST['gateway'];
		
		$dv = ($plugin_config[$c_gateway]['_smsc_config_'] ? $plugin_config[$c_gateway]['_smsc_config_'] : array());
		foreach ($dv as $key => $val ) {
			$dynamic_variables[] = array(
				'key' => $key,
				'title' => $val 
			);
		}
		
		$tpl = array(
			'name' => 'gateway_add_smsc',
			'vars' => array(
				'FORM_TITLE' => _('Add SMSC'),
				'ACTION_URL' => 'index.php?app=main&inc=core_gateway&op=add_smsc_save',
				'GATEWAY' => $c_gateway,
				'BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'Gateway' => _('Gateway'),
				'SMSC name' => _mandatory(_('SMSC name')),
				'Save' => _('Save') 
			),
			'loops' => array(
				'dynamic_variables' => $dynamic_variables 
			) 
		);
		$content = tpl_apply($tpl);
		break;
	
	case 'add_smsc_save' :
		$c_gateway = gateway_valid_name($_REQUEST['gateway']);
		
		// do not add dev and blocked
		$continue = FALSE;
		if (!(($c_gateway == 'dev') || ($c_gateway == 'blocked'))) {
			$continue = TRUE;
		}
		
		$c_name = core_sanitize_alphanumeric(strtolower($_REQUEST['name']));
		if (!$c_name) {
			$c_name = mktime();
		}
		
		$smsc = gateway_get_smscbyname($c_name);

		if ($smsc['name']) {
			$_SESSION['dialog']['info'][] = _('SMSC already exists');
		} else {
			
			if ($c_name && $c_gateway) {
				$dv = ($plugin_config[$c_gateway]['_smsc_config_'] ? $plugin_config[$c_gateway]['_smsc_config_'] : array());
				$dynamic_variables = array();
				foreach ($dv as $key => $val ) {
					$dynamic_variables[$key] = $_REQUEST[$key];
				}
				$items = array(
					'created' => core_get_datetime(),
					'name' => $c_name,
					'gateway' => $c_gateway,
					'data' => json_encode($dynamic_variables) 
				);
				$db_table = _DB_PREF_ . '_tblGateway';
				if ($new_id = dba_add($db_table, $items)) {
					$_SESSION['dialog']['info'][] = _('New SMSC has been added');
				} else {
					$_SESSION['dialog']['info'][] = _('Fail to add new SMSC');
				}
			} else {
				$_SESSION['dialog']['info'][] = _('Unknown error');
				header('Location: ' . _u('index.php?app=main&inc=core_gateway&op=gateway_list'));
				exit();
			}
		}
		
		header('Location: ' . _u('index.php?app=main&inc=core_gateway&op=add_smsc&gateway=' . $c_gateway));
		exit();
		break;
	
	case 'edit_smsc' :
		$c_id = $_REQUEST['id'];
		
		$smsc = gateway_get_smscbyid($c_id);
		
		$c_name = $smsc['name'];
		$c_gateway = gateway_valid_name($smsc['gateway']);
		$c_data = json_decode($smsc['data']);
		
		$dv = ($plugin_config[$c_gateway]['_smsc_config_'] ? $plugin_config[$c_gateway]['_smsc_config_'] : array());
		foreach ($dv as $key => $val ) {
			$dynamic_variables[] = array(
				'key' => $key,
				'title' => $val,
				'value' => $c_data->$key 
			);
		}
		
		$tpl = array(
			'name' => 'gateway_edit_smsc',
			'vars' => array(
				'FORM_TITLE' => _('Edit SMSC'),
				'ACTION_URL' => 'index.php?app=main&inc=core_gateway&op=edit_smsc_save',
				'ID' => $c_id,
				'NAME' => $c_name,
				'GATEWAY' => $c_gateway,
				'BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'Gateway' => _('Gateway'),
				'SMSC name' => _('SMSC name'),
				'Save' => _('Save') 
			),
			'loops' => array(
				'dynamic_variables' => $dynamic_variables 
			) 
		);
		$content = tpl_apply($tpl);
		break;
	
	case 'edit_smsc_save' :
		$c_id = (int) $_REQUEST['id'];
		$smsc = gateway_get_smscbyid($c_id);
		
		// do not edit dev and blocked
		$continue = FALSE;
		if (!(($smsc['gateway'] == 'dev') || ($smsc['gateway'] == 'blocked'))) {
			$continue = TRUE;
		}
		
		$c_gateway = gateway_valid_name($_REQUEST['gateway']);
		
		if ($continue && $c_id && $c_gateway && ($c_gateway == $smsc['gateway'])) {
			$dv = ($plugin_config[$c_gateway]['_smsc_config_'] ? $plugin_config[$c_gateway]['_smsc_config_'] : array());
			$dynamic_variables = array();
			foreach ($dv as $key => $val ) {
				$dynamic_variables[$key] = $_REQUEST[$key];
			}
			$items = array(
				'last_update' => core_get_datetime(),
				'data' => json_encode($dynamic_variables) 
			);
			$condition = array(
				'id' => $c_id 
			);
			$db_table = _DB_PREF_ . '_tblGateway';
			if ($new_id = dba_update($db_table, $items, $condition)) {
				$_SESSION['dialog']['info'][] = _('SMSC has been edited');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to edit SMSC');
			}
		} else {
			$_SESSION['dialog']['info'][] = _('Unknown error');
			header('Location: ' . _u('index.php?app=main&inc=core_gateway&op=gateway_list'));
			exit();
		}
		
		header('Location: ' . _u('index.php?app=main&inc=core_gateway&op=edit_smsc&id=' . $c_id));
		exit();
		break;
	
	case 'del_smsc' :
		if ($c_id = $_REQUEST['id']) {
			$db_table = _DB_PREF_ . '_tblGateway';
			$condition = array(
				'id' => $c_id 
			);
			if (dba_remove($db_table, $condition)) {
				$_SESSION['dialog']['info'][] = _('SMSC has been removed');
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to remove SMSC');
			}
		} else {
			$_SESSION['dialog']['info'][] = _('Unknown error');
		}
		header('Location: ' . _u('index.php?app=main&inc=core_gateway&op=gateway_list'));
		exit();
		break;
	
	default :
		$content = "
			<h3>" . _('List of gateways and SMSCs') . "</h3>
			<ul class='nav nav-tabs nav-justified' id='playsms-tab'>
				<li class=active><a href='#tabs-gateway' data-toggle=tab>" . _('Gateways') . "</a></li>
				<li><a href='#tabs-virtual' data-toggle=tab>" . _('SMSCs') . "</a></li>
			</ul>
			<div class=tab-content>
				<div id='tabs-gateway' class='tab-pane fade in active'>
					" . _gateway_display() . "
				</div>
				<div id='tabs-virtual' class='tab-pane fade'>
					" . _gateway_display_smsc() . "
				</div>
			</div>
			<script type=\"text/javascript\" src=\"" . $core_config['http_path']['plug'] . "/themes/common/jscss/jquery.cookie.js\"></script>
			<script type=\"text/javascript\">
				$(document).ready(function() {
					$('a[data-toggle=\"tab\"]').on('shown.bs.tab', function(e){
						//save the latest tab using a cookie:
						$.cookie('gateway_last_tab', $(e.target).attr('href'));
					});
					
					//activate latest tab, if it exists:
					var lastTab = $.cookie('gateway_last_tab');
					if (lastTab) {
						$('ul.nav-tabs').children().removeClass('active');
						$('a[href='+ lastTab +']').parents('li:first').addClass('active');
						$('div.tab-content').children().removeClass('in active');
						$(lastTab).addClass('in active');
					}
				});
			</script>
		";
}
$final_content = _dialog() . "
	<h2>" . _('Manage gateway and SMSC') . "</h2>
	" . $content;

_p($final_content);
