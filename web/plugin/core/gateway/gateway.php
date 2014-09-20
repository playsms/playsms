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
	case 'add_virtual' :
		$c_gateway = $_REQUEST['gateway'];
		
		$dv = ($plugin_config[$c_gateway]['_dynamic_variables_'] ? $plugin_config[$c_gateway]['_dynamic_variables_'] : array());
		foreach ($dv as $key => $val ) {
			$dynamic_variables[] = array(
				'key' => $key,
				'title' => $val 
			);
		}
		
		$tpl = array(
			'name' => 'gateway_add_virtual',
			'vars' => array(
				'FORM_TITLE' => _('Add virtual gateway'),
				'ACTION_URL' => 'index.php?app=main&inc=core_gateway&op=add_virtual_save',
				'GATEWAY' => $c_gateway,
				'BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'Gateway' => _('Gateway'),
				'Virtual gateway name' => _mandatory(_('Virtual gateway name')),
				'Save' => _('Save') 
			),
			'loops' => array(
				'dynamic_variables' => $dynamic_variables 
			) 
		);
		$content = tpl_apply($tpl);
		break;
	
	case 'add_virtual_save' :
		$c_gateway = gateway_valid_name($_REQUEST['gateway']);
		$c_name = core_sanitize_alphanumeric($_REQUEST['name']);
		if (!$c_name) {
			$c_name = mktime();
		}
		
		$vgw = gateway_get_virtualbyname($c_name);
		
		if ($vgw['name']) {
			$_SESSION['error_string'] = _('Virtual gateway already exists');
		} else {
			
			if ($c_name && $c_gateway) {
				$dv = ($plugin_config[$c_gateway]['_dynamic_variables_'] ? $plugin_config[$c_gateway]['_dynamic_variables_'] : array());
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
					$_SESSION['error_string'] = _('New virtual gateway has been added');
				} else {
					$_SESSION['error_string'] = _('Fail to add new virtual gateway');
				}
			} else {
				$_SESSION['error_string'] = _('Unknown error');
				header('Location: ' . _u('index.php?app=main&inc=core_gateway&op=gateway_list'));
				exit();
			}
		}
		
		header('Location: ' . _u('index.php?app=main&inc=core_gateway&op=add_virtual&gateway=' . $c_gateway));
		exit();
		break;
	
	case 'edit_virtual' :
		$c_id = $_REQUEST['id'];
		
		$vgw = gateway_get_virtual($c_id);
		
		$c_name = $vgw['name'];
		$c_gateway = gateway_valid_name($vgw['gateway']);
		$c_data = json_decode($vgw['data']);
		
		$dv = ($plugin_config[$c_gateway]['_dynamic_variables_'] ? $plugin_config[$c_gateway]['_dynamic_variables_'] : array());
		foreach ($dv as $key => $val ) {
			$dynamic_variables[] = array(
				'key' => $key,
				'title' => $val,
				'value' => $c_data->$key 
			);
		}
		
		$tpl = array(
			'name' => 'gateway_edit_virtual',
			'vars' => array(
				'FORM_TITLE' => _('Edit virtual gateway'),
				'ACTION_URL' => 'index.php?app=main&inc=core_gateway&op=edit_virtual_save',
				'ID' => $c_id,
				'NAME' => $c_name,
				'GATEWAY' => $c_gateway,
				'BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'Gateway' => _('Gateway'),
				'Virtual gateway name' => _('Virtual gateway name'),
				'Save' => _('Save') 
			),
			'loops' => array(
				'dynamic_variables' => $dynamic_variables 
			) 
		);
		$content = tpl_apply($tpl);
		break;
	
	case 'edit_virtual_save' :
		$c_id = (int) $_REQUEST['id'];
		$vgw = gateway_get_virtual($c_id);
		
		$c_gateway = gateway_valid_name($_REQUEST['gateway']);
		
		if ($c_id && $c_gateway && ($c_gateway == $vgw['gateway'])) {
			$dv = ($plugin_config[$c_gateway]['_dynamic_variables_'] ? $plugin_config[$c_gateway]['_dynamic_variables_'] : array());
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
				$_SESSION['error_string'] = _('Virtual gateway has been edited');
			} else {
				$_SESSION['error_string'] = _('Fail to edit virtual gateway');
			}
		} else {
			$_SESSION['error_string'] = _('Unknown error');
			header('Location: ' . _u('index.php?app=main&inc=core_gateway&op=gateway_list'));
			exit();
		}
		
		header('Location: ' . _u('index.php?app=main&inc=core_gateway&op=edit_virtual&id=' . $c_id));
		exit();
		break;
	
	case 'del_virtual' :
		if ($c_id = $_REQUEST['id']) {
			$db_table = _DB_PREF_ . '_tblGateway';
			$condition = array(
				'id' => $c_id 
			);
			if (dba_remove($db_table, $condition)) {
				$_SESSION['error_string'] = _('Virtual gateway has been removed');
			} else {
				$_SESSION['error_string'] = _('Fail to remove virtual gateway');
			}
		} else {
			$_SESSION['error_string'] = _('Unknown error');
		}
		header('Location: ' . _u('index.php?app=main&inc=core_gateway&op=gateway_list'));
		exit();
		break;
	
	default :
		$content = "
			<h3>" . _('List of gateways and virtual gateways') . "</h3>
			<ul class='nav nav-tabs nav-justified' id='playsms-tab'>
				<li class=active><a href='#tabs-gateway' data-toggle=tab>" . _('Gateways') . "</a></li>
				<li><a href='#tabs-virtual' data-toggle=tab>" . _('Virtual gateways') . "</a></li>
			</ul>
			<div class=tab-content>
				<div id='tabs-gateway' class='tab-pane fade in active'>
					" . _gateway_display() . "
				</div>
				<div id='tabs-virtual' class='tab-pane fade'>
					" . _gateway_display_virtual() . "
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
$final_content = _err_display() . "
	<h2>" . _('Manage gateway') . "</h2>
	" . $content;

_p($final_content);
