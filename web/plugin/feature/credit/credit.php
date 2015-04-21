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

if (!auth_isuser()) {
	if (!auth_isadmin()) {
		auth_block();
	}
}

switch (_OP_) {
	case "credit_list":
		$db_table = $plugin_config['credit']['db_table'];
		$search_category = array(
			_('Username') => 'username',
			_('Transaction datetime') => 'create_datetime' 
		);
		$base_url = 'index.php?app=main&inc=feature_credit&op=credit_list';
		$search = themes_search($search_category, $base_url);
		$conditions = array(
			'flag_deleted' => 0 
		);
		
		// only if users
		if ($user_config['status'] == 3) {
			$conditions['parent_uid'] = $user_config['uid'];
			$conditions['status'] = 4;
		}
		
		$keywords = $search['dba_keywords'];
		$count = dba_count($db_table, $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = array(
			'ORDER BY' => 'id DESC',
			'LIMIT' => $nav['limit'],
			'OFFSET' => $nav['offset'] 
		);
		$list = dba_search($db_table, '*', $conditions, $keywords, $extras);
		
		$content = "
			<h2>" . _('Manage credit') . "</h2>
			<h3>" . _('List of transactions') . "</h3>
			<p>" . $search['form'] . "</p>
			<form id=fm_feature_credit name=fm_feature_credit action=\"" . _u('index.php?app=main&inc=feature_credit&op=actions') . "\" method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=actions_box>
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=feature_credit&op=credit_add') . "\">" . $icon_config['add'] . "</a>
					<a href=\"" . _u('index.php?app=main&inc=feature_credit&op=credit_reduce') . "\">" . $icon_config['reduce'] . "</a>
					<a href=\"" . _u('index.php?app=main&inc=feature_credit&op=actions&go=export') . "\">" . $icon_config['export'] . "</a>
				</div>
				<div class=pull-right>
					<a href='#' onClick=\"return SubmitConfirm('" . _('Are you sure you want to delete these transactions ?') . "', 'fm_feature_credit');\">" . $icon_config['delete'] . "</a>
				</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=25%>" . _('User') . "</th>
				<th width=30%>" . _('Transaction datetime') . "</th>
				<th width=20%>" . _('Amount') . "</th>
				<th width=20%>" . _('Balance') . " " . _hint(_('Balance recorded on transaction')) . "</th>
				<th width=5% class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_feature_credit)></th>
			</tr>
			</thead>
			<tbody>";
		
		$j = 0;
		foreach ($list as $row) {
			$row = core_display_data($row);
			$content .= "
				<tr>
					<td>" . $row['username'] . "</td>
					<td>" . core_display_datetime($row['create_datetime']) . "</td>
					<td>" . $row['amount'] . "</td>
					<td>" . $row['balance'] . "</td>
					<td>
						<input type=hidden name=itemid" . $j . " value=\"" . $row['id'] . "\">
						<input type=checkbox name=checkid" . $j . ">
					</td>
				</tr>";
			$j++;
		}
		
		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			</form>";
		
		if ($err = TRUE) {
			_p(_dialog());
		}
		_p($content);
		break;
	
	case "credit_add":
		
		$select_user = credit_html_select_user();
		
		$content = _dialog() . "
			<script language=\"javascript\" type=\"text/javascript\">
				$(document).ready(function() {
					$(\"#playsms-credit-select-user\").select2({
						placeholder: \"" . _('Select users') . "\",
						width: \"resolve\",
						separator: [','],
						tokenSeparators: [','],
					});
				});
			</script>
			<h2>" . _('Manage credit') . "</h2>
			<h3>" . _('Add credit') . "</h3>
			<form id=fm_feature_credit_add name=id_feature_credit_add action=\"" . _u('index.php?app=main&inc=feature_credit&op=actions&go=add') . "\" method=POST>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
				<tbody>
					<tr>
						<td class=label-sizer>" . _('Add credit to users') . "</td>
						<td>" . $select_user . "</td>
					</tr>
					<tr>
						<td>" . _('Amount') . "</td>
						<td><input type='text' maxlength=14 name=amount value=\"0.0\"></td>
					</tr>
				</tbody>
			</table>
			<p><input type='submit' class='button' value='" . _('Add credit') . "'>
			</form>
			" . _back('index.php?app=main&inc=feature_credit&op=credit_list');
		
		_p($content);
		break;
	
	case "credit_reduce":
		
		$select_user = credit_html_select_user();
		
		$content = _dialog() . "
			<script language=\"javascript\" type=\"text/javascript\">
				$(document).ready(function() {
					$(\"#playsms-credit-select-user\").select2({
						placeholder: \"" . _('Select users') . "\",
						width: \"resolve\",
						separator: [','],
						tokenSeparators: [',']
					});
				});
			</script>
			<h2>" . _('Manage credit') . "</h2>
			<h3>" . _('Reduce credit') . "</h3>
			<form id=fm_feature_credit_reduce name=id_feature_credit_reduce action=\"" . _u('index.php?app=main&inc=feature_credit&op=actions&go=reduce') . "\" method=POST>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
				<tbody>
					<tr>
						<td class=label-sizer>" . _('Reduce credit from users') . "</td>
						<td>" . $select_user . "</td>
					</tr>
					<tr>
						<td>" . _('Amount') . "</td>
						<td><input type='text' maxlength=14 name=amount value=\"0.0\"></td>
					</tr>
				</tbody>
			</table>
			<p><input type='submit' class='button' value='" . _('Reduce credit') . "'>
			</form>
			" . _back('index.php?app=main&inc=feature_credit&op=credit_list');
		
		_p($content);
		break;
	
	case "actions":
		$db_table = $plugin_config['credit']['db_table'];
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$conditions = array(
					'flag_deleted' => 0 
				);
				
				// only if users
				if ($user_config['status'] == 3) {
					$conditions['parent_uid'] = $user_config['uid'];
					$conditions['status'] = 4;
				}
				
				$list = dba_search($db_table, '*', $conditions, $search['dba_keywords']);
				$data[0] = array(
					_('User'),
					_('Transaction datetime'),
					_('Amount'),
					_('Balance') 
				);
				for ($i = 0; $i < count($list); $i++) {
					$j = $i + 1;
					$data[$j] = array(
						$list[$i]['username'],
						core_display_datetime($list[$i]['create_datetime']),
						$list[$i]['amount'],
						$list[$i]['balance'] 
					);
				}
				$content = core_csv_format($data);
				$fn = 'credit-' . $core_config['datetime']['now_stamp'] . '.csv';
				core_download($content, $fn, 'text/csv');
				break;
			
			case 'delete':
				for ($i = 0; $i < $nav['limit']; $i++) {
					$checkid = $_POST['checkid' . $i];
					$itemid = $_POST['itemid' . $i];
					if (($checkid == "on") && $itemid) {
						$up = array(
							'c_timestamp' => mktime(),
							'delete_datetime' => core_get_datetime(),
							'flag_deleted' => '1' 
						);
						
						// only if users
						if ($user_config['status'] == 3) {
							$up['parent_uid'] = $user_config['uid'];
							$up['status'] = 4;
						}
						
						dba_update($db_table, $up, array(
							'id' => $itemid 
						));
					}
				}
				$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				$_SESSION['dialog']['info'][] = _('Selected transactions has been deleted');
				header("Location: " . _u($ref));
				exit();
				break;
			
			case "add":
				$continue = FALSE;
				
				$uids = $_POST['uids'];
				
				if (is_array($uids)) {
					foreach ($uids as $uid) {
						if ($user_config['status'] == 3) {
							$parent_uid = user_getparentbyuid($uid);
							if ($parent_uid == $user_config['uid']) {
								$continue = TRUE;
							}
						}
						
						if (auth_isadmin()) {
							$continue = TRUE;
						}
						
						$amount = abs($_POST['amount']);
						if ($continue && ($amount > 0) && ($username = user_uid2username($uid))) {
							if (credit_add($uid, $amount)) {
								$current_balance = credit_getbalance($uid);
								$_SESSION['dialog']['info'][] .= _('Credit has been added') . ' (' . _('user') . ':' . $username . ' ' . _('amount') . ':' . $amount . ' ' . _('balance') . ':' . $current_balance . ')';
							} else {
								$_SESSION['dialog']['info'][] .= _('Fail to add credit') . ' (' . _('user') . ':' . $username . ' ' . _('amount') . ':' . $amount . ')';
							}
						} else {
							$_SESSION['dialog']['info'][] .= _('Wrong amount or user does not exist') . ' (' . _('User ID') . ':' . $uid . ')';
						}
					}
				}
				
				header("Location: " . _u('index.php?app=main&inc=feature_credit&op=credit_add'));
				exit();
				break;
			
			case "reduce":
				$continue = FALSE;
				
				$uids = $_POST['uids'];
				
				if (is_array($uids)) {
					foreach ($uids as $uid) {
						if ($user_config['status'] == 3) {
							$parent_uid = user_getparentbyuid($uid);
							if ($parent_uid == $user_config['uid']) {
								$continue = TRUE;
							}
						}
						
						if (auth_isadmin()) {
							$continue = TRUE;
						}
						
						$amount = -1 * abs($_POST['amount']);
						if ($continue && ($amount < 0) && ($username = user_uid2username($uid))) {
							if (credit_add($uid, $amount)) {
								$current_balance = credit_getbalance($uid);
								$_SESSION['dialog']['info'][] .= _('Credit has been reduced') . ' (' . _('user') . ':' . $username . ' ' . _('amount') . ':' . $amount . ' ' . _('balance') . ':' . $current_balance . ')';
							} else {
								$_SESSION['dialog']['info'][] .= _('Fail to reduce credit') . ' (' . _('user') . ':' . $username . ' ' . _('amount') . ':' . $amount . ')';
							}
						} else {
							$_SESSION['dialog']['info'][] .= _('Wrong amount or user does not exist') . ' (' . _('User ID') . ':' . $uid . ')';
						}
					}
				}
				
				header("Location: " . _u('index.php?app=main&inc=feature_credit&op=credit_reduce'));
				exit();
				break;
		}
		break;
}
