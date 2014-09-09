<?php
defined('_SECURE_') or die('Forbidden');
if (!auth_isvalid()) {
	auth_block();
}

$checkid = $_REQUEST['checkid'];
$itemid = $_REQUEST['itemid'];

$items = array();
foreach ($checkid as $key => $val ) {
	if (strtoupper($val) == 'ON') {
		if ($itemid[$key]) {
			$items[] = $itemid[$key];
		}
	}
}

switch (_OP_) {
	case 'delete' :
		foreach ($items as $item ) {
			if (dba_remove(_DB_PREF_ . '_featurePhonebook', array(
				'uid' => $user_config['uid'],
				'id' => $item 
			))) {
				dba_remove(_DB_PREF_ . '_featurePhonebook_group_contacts', array(
					'pid' => $item 
				));
				$_SESSION['error_string'] = _('Selected contact has been deleted');
			}
		}
		break;
}

$gpid = 0;
$ops = explode('_', _OP_);
if (($ops[0] == 'move') && $ops[1]) {
	$gpid = $ops[1];
}

if ($gpid && (dba_valid(_DB_PREF_ . '_featurePhonebook_group', 'id', $gpid))) {
	foreach ($items as $item ) {
		if (dba_remove(_DB_PREF_ . '_featurePhonebook_group_contacts', array(
			'pid' => $item 
		))) {
			$data = array(
				'pid' => $item,
				'gpid' => $gpid 
			);
			if (dba_add(_DB_PREF_ . '_featurePhonebook_group_contacts', $data)) {
				$_SESSION['error_string'] = _('Selected contact moved to new group');
			}
		}
	}
}

$search = themes_search_session();
$nav = themes_nav_session();

$ref = $search['url'] . '&search_keyword=' . $search['keyword'] . '&search_category=' . $search['category'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
header("Location: " . _u($ref));
exit();
