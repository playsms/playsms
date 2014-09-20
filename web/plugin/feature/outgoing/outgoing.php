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
	case "outgoing_list" :
		unset($tpl);
		$tpl = array(
			'name' => 'outgoing_list',
			'vars' => array(
				'ERROR' => $error_content,
				'Route outgoing SMS' => _('Route outgoing SMS'),
				'Add route' => _button('index.php?app=main&inc=feature_outgoing&op=outgoing_add', _('Add route')),
				'Destination' => _('Destination'),
				'Prefix' => _('Prefix'),
				'Gateway' => _('Gateway'),
				'Action' => _('Action'),
				'option' 
			) 
		);
		
		$data = outgoing_getdata();
		foreach ($data as $row ) {
			$c_rid = $row['id'];
			$c_action = "<a href='" . _u('index.php?app=main&inc=feature_outgoing&op=outgoing_edit&rid=' . $c_rid) . "'>" . $icon_config['edit'] . "</a> ";
			$c_action .= "<a href='" . _u('index.php?app=main&inc=feature_outgoing&op=outgoing_del&rid=' . $c_rid) . "'>" . $icon_config['delete'] . "</a> ";
			$tpl['loops']['data'][] = array(
				'tr_class' => $tr_class,
				'dst' => $row['dst'],
				'prefix' => $row['prefix'],
				'gateway' => ( $row['gateway'] ? $row['gateway'] : _('blocked') ),
				'action' => $c_action 
			);
		}
		
		$content = tpl_apply($tpl);
		_p($content);
		break;
	case "outgoing_del" :
		$rid = $_REQUEST['rid'];
		$dst = outgoing_getdst($rid);
		$prefix = outgoing_getprefix($rid);
		$_SESSION['error_string'] = _('Fail to delete route') . " (" . _('destination') . ": $dst, " . _('prefix') . ": $prefix)";
		$db_query = "DELETE FROM " . _DB_PREF_ . "_featureOutgoing WHERE id='$rid'";
		if (@dba_affected_rows($db_query)) {
			$_SESSION['error_string'] = _('gateway has been deleted') . " (" . _('destination') . ": $dst, " . _('prefix') . ": $prefix)";
		}
		header("Location: " . _u('index.php?app=main&inc=feature_outgoing&op=outgoing_list'));
		exit();
		break;
	case "outgoing_edit" :
		$rid = $_REQUEST['rid'];
		$dst = outgoing_getdst($rid);
		$prefix = outgoing_getprefix($rid);
		$gateway = outgoing_getgateway($rid);
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$select_gateway = "<select name=up_gateway>";
		unset($vgw_list);
		$list = gateway_getall_virtual();
		foreach ($list as $vgw) {
			$vgw_list[] = $vgw['name'];
		}
		foreach ($vgw_list as $vgw) {
			$selected = $vgw == $gateway ? "selected" : "";
			$select_gateway .= "<option " . $selected . ">" . $vgw . "</option>";
		}
		$select_gateway .= "</select>";
		$content .= "
			<h2>" . _('Route SMS outgoing') . "</h2>
			<h3>" . _('Edit route') . "</h3>
			<form action='index.php?app=main&inc=feature_outgoing&op=outgoing_edit_save' method='post'>
			" . _CSRF_FORM_ . "
			<input type='hidden' name='rid' value=\"$rid\">
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _mandatory('Destination') . "</td><td><input type='text' maxlength='30' name='up_dst' value=\"$dst\" required></td>
			</tr>
			<tr>
				<td>" . _mandatory('Prefix') . "</td><td><input type='text' maxlength=10 name='up_prefix' value=\"$prefix\" required></td>
			</tr>
			<tr>
				<td>" . _('Gateway') . "</td><td>" . $select_gateway . "</td>
			</tr>
			</table>
			<p><input type='submit' class='button' value='" . _('Save') . "'></p>
			</form>
			" . _back('index.php?app=main&inc=feature_outgoing&op=outgoing_list');
		_p($content);
		break;
	case "outgoing_edit_save" :
		$rid = $_POST['rid'];
		$up_dst = $_POST['up_dst'];
		$up_prefix = $_POST['up_prefix'];
		$up_prefix = core_sanitize_numeric($up_prefix);
		$up_prefix = substr($up_prefix, 0, 8);
		$up_gateway = ( $_POST['up_gateway'] ? $_POST['up_gateway'] : '_gateway_none_' );
		$_SESSION['error_string'] = _('No changes made!');
		if ($rid && $up_dst && $up_prefix) {
			$db_query = "UPDATE " . _DB_PREF_ . "_featureOutgoing SET c_timestamp='" . mktime() . "',dst='$up_dst',prefix='$up_prefix',gateway='$up_gateway' WHERE id='$rid'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Route has been saved') . " (" . _('destination') . ": $up_dst, " . _('prefix') . ": $up_prefix)";
			} else {
				$_SESSION['error_string'] = _('Fail to save route') . " (" . _('destination') . ": $up_dst, " . _('prefix') . ": $up_prefix)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all mandatory fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_outgoing&op=outgoing_edit&rid=' . $rid));
		exit();
		break;
	case "outgoing_add" :
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$select_gateway = "<select name=add_gateway>";
		unset($vgw_list);
		$list = gateway_getall_virtual();
		foreach ($list as $vgw) {
			$vgw_list[] = $vgw['name'];
		}
		foreach ($vgw_list as $vgw) {
			$select_gateway .= "<option>" . $vgw . "</option>";
		}
		$select_gateway .= "</select>";
		$content .= "
			<h2>" . _('Manage SMS gateway') . "</h2>
			<h3>" . _('Add gateway') . "</h3>
			<form action='index.php?app=main&inc=feature_outgoing&op=outgoing_add_yes' method='post'>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _mandatory('Destination') . "</td><td><input type='text' maxlength='30' name='add_dst' value=\"$add_dst\" required></td>
			</tr>
			<tr>
				<td>" . _mandatory('Prefix') . "</td><td><input type='text' maxlength=10 name='add_prefix' value=\"$add_prefix\" required></td>
			</tr>
			<tr>
				<td>" . _('Gateway') . "</td><td>" . $select_gateway . "</td>
			</tr>
			</table>
			<input type='submit' class='button' value='" . _('Save') . "'>
			</form>
			" . _back('index.php?app=main&inc=feature_outgoing&op=outgoing_list');
		_p($content);
		break;
	case "outgoing_add_yes" :
		$add_dst = $_POST['add_dst'];
		$add_prefix = $_POST['add_prefix'];
		$add_prefix = core_sanitize_numeric($add_prefix);
		$add_prefix = substr($add_prefix, 0, 8);
		$add_gateway = ( $_POST['add_gateway'] ? $_POST['up_gateway'] : '_gateway_none_' );
		if ($add_dst && $add_prefix) {
			$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureOutgoing WHERE prefix='$add_prefix'";
			$db_result = dba_query($db_query);
			if ($db_row = dba_fetch_array($db_result)) {
				$_SESSION['error_string'] = _('Route is already exists') . " (" . _('destination') . ": " . $db_row['dst'] . ", " . _('prefix') . ": " . $db_row['prefix'] . ")";
			} else {
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_featureOutgoing (dst,prefix,gateway)
					VALUES ('$add_dst','$add_prefix','$add_gateway')";
				if ($new_uid = @dba_insert_id($db_query)) {
					$_SESSION['error_string'] = _('Route has been added') . " (" . _('destination') . ": $add_dst, " . _('prefix') . ": $add_prefix)";
				}
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_outgoing&op=outgoing_add'));
		exit();
		break;
}
