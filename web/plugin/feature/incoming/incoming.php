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
	case "incoming":
		
		// form pre rules
		
		$pre_rules = incoming_pre_rules_get();
		
		// scan message for @username
		$select_match_username = _yesno('incoming_match_username', $pre_rules['match_username'], '', '', '', 'playsms-incoming-match-username', 'form-control');
		
		// scan message for #groupcode
		$select_match_groupcode = _yesno('incoming_match_groupcode', $pre_rules['match_groupcode'], '', '', '', 'playsms-incoming-match-groupcode', 'form-control');
		
		$form_pre_rules = array(
			array(
				'id' => 'playsms-incoming-match-username',
				'label' => _('Scan incoming SMS for @username'),
				'input' => $select_match_username,
				'help' => _('Copy the message to user inbox when incoming SMS contains @username') 
			),
			array(
				'id' => 'playsms-incoming-match-groupcode',
				'label' => _('Scan incoming SMS for #groupcode'),
				'input' => $select_match_groupcode,
				'help' => _('Send SMS to groups with found group codes in the incoming SMS') 
			) 
		);
		
		// form post rules
		
		$post_rules = incoming_post_rules_get();
		
		// sandbox match receiver number and sender ID
		$select_match_sender_id = _yesno('sandbox_match_sender_id', $post_rules['match_sender_id'], '', '', '', 'playsms-sandbox-match-sender-id', 'form-control');
		
		// sandbox prefix
		unset($params);
		$params = array(
			'size' => '100%',
			'maxlength' => 30,
			'placeholder' => _('Insert keyword') 
		);
		$input_prefix = _input('text', 'sandbox_prefix', $post_rules['insert_prefix'], $params, 'playsms-sandbox-prefix', 'form-control');
		
		// sandbox forward to users
		unset($params);
		$params = array(
			'width' => '100%',
			'placeholder' => _('Select users') 
		);
		$select_users = themes_select_users_multi('uids', $post_rules['forward_to'], $params, 'playsms-route-to-users');
		
		$form_post_rules = array(
			array(
				'id' => 'playsms-sandbox-match-sender-id',
				'label' => _('Route all sandbox SMS with matched sender ID'),
				'input' => $select_match_sender_id,
				'help' => _('Route to user inbox if receiver number matched with user sender ID') 
			),
			/**
			 * array(
			 * 'id' => 'playsms-sandbox-prefix',
			 * 'label' => _('Route all sandbox SMS to keyword'),
			 * 'input' => $input_prefix,
			 * 'help' => _('A valid keyword will be inserted and prefixed to the message')
			 * ),
			 */
			array(
				'id' => 'playsms-route-to-users',
				'label' => _('Route all sandbox SMS to users'),
				'input' => $select_users,
				'help' => '' 
			) 
		);
		
		// form settings
		
		$settings = incoming_settings_get();
		
		// settings to leave copy on sandbox
		$settings_leave_copy_sandbox = _yesno('settings_leave_copy_sandbox', $settings['leave_copy_sandbox'], '', '', '', 'settings_leave_copy_sandbox', 'form-control');
		
		// settings to match with all approved sender ID
		$settings_match_all_sender_id = _yesno('settings_match_all_sender_id', $settings['match_all_sender_id'], '', '', '', 'settings_match_all_sender_id', 'form-control');
		
		$form_settings = array(
			array(
				'id' => 'playsms-settings-leave-copy',
				'label' => _('Leave a copy in sandbox SMS page'),
				'input' => $settings_leave_copy_sandbox,
				'help' => _('Leaving a copy in sandbox SMS page may be useful for audit or reviews') 
			),
			array(
				'id' => 'playsms-settings-match-all',
				'label' => _('Match with all approved sender ID'),
				'input' => $settings_match_all_sender_id,
				'help' => _('Receiver number can be matched with default sender ID or with all approved sender ID') 
			) 
		);
		
		$tpl = array(
			'name' => 'incoming',
			'vars' => array(
				'DIALOG_DISPLAY' => _dialog(),
				'PAGE_TITLE' => _('Route incoming SMS'),
				'ACTION_URL' => _u('index.php?app=main&inc=feature_incoming&op=incoming_save'),
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'HINT_PRE_RULES' => _hint(_('Rules applied before incoming SMS processed')),
				'HINT_POST_RULES' => _hint(_('Rules applied after incoming SMS processed')),
				'Pre rules' => _('Pre rules'),
				'Post rules' => _('Post rules'),
				'Settings' => _('Settings'),
				'Save' => _('Save') 
			),
			'loops' => array(
				'form_pre_rules' => $form_pre_rules,
				'form_post_rules' => $form_post_rules,
				'form_settings' => $form_settings 
			),
			'injects' => array(
				'core_config' 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	
	case "incoming_save":
		
		// form pre rules
		
		// scan message for @username
		$pre_rules['match_username'] = (int) $_REQUEST['incoming_match_username'];
		$items['incoming_match_username'] = $pre_rules['match_username'];
		
		// scan message for #groupcode
		$pre_rules['match_groupcode'] = (int) $_REQUEST['incoming_match_groupcode'];
		$items['incoming_match_groupcode'] = $pre_rules['match_groupcode'];
		
		// form post rules
		
		// sandbox match receiver number and sender ID
		$post_rules['match_sender_id'] = (int) $_REQUEST['sandbox_match_sender_id'];
		$items['sandbox_match_sender_id'] = $post_rules['match_sender_id'];
		
		// sandbox prefix
		$post_rules['insert_prefix'] = trim(strtoupper(core_sanitize_alphanumeric($_REQUEST['sandbox_prefix'])));
		if ($post_rules['insert_prefix'] && checkavailablekeyword($post_rules['insert_prefix'])) {
			$_SESSION['dialog']['info'][] = _('Fail to insert keyword') . ' (' . _('keyword') . ': ' . $post_rules['insert_prefix'] . ')';
			$post_rules['insert_prefix'] = '';
		}
		$items['sandbox_prefix'] = $post_rules['insert_prefix'];
		
		// sandbox forward to users
		$post_rules['forward_to'] = serialize(array_unique($_REQUEST['uids']));
		$items['sandbox_forward_to'] = $post_rules['forward_to'];
		
		// form settings
		
		// settings to leave copy on sandbox
		$items['settings_leave_copy_sandbox'] = (int) $_REQUEST['settings_leave_copy_sandbox'];
		
		// settings to match with all approved sender ID
		$items['settings_match_all_sender_id'] = (int) $_REQUEST['settings_match_all_sender_id'];
		
		// save to registry
		if (count($items)) {
			registry_update(1, 'feature', 'incoming', $items);
			$_SESSION['dialog']['info'][] = _('Incoming SMS route changes has been saved');
		} else {
			$_SESSION['dialog']['info'][] = _('No route has been saved');
		}
		
		header("Location: " . _u('index.php?app=main&inc=feature_incoming&op=incoming'));
		exit();
		break;
}
