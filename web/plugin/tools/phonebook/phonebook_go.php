<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

$checkid = $_REQUEST['checkid'];
$itemid = $_REQUEST['itemid'];

$items = array();
foreach ($checkid as $key => $val) {
	if (strtoupper($val) == 'ON') {
		if ($itemid[$key]) {
			$items[] = $itemid[$key];
		}
	}

}

switch (_OP_) {
	case 'delete':
		foreach ($items as $item) {
			if (dba_remove(_DB_PREF_.'_toolsPhonebook', array('uid' => $core_config['user']['uid'], 'id' => $item))) {
				dba_remove(_DB_PREF_.'_toolsPhonebook_group_contacts', array('pid' => $item));
				$found = TRUE;
			}
		}
		break;
}

$search = themes_search_session();
$nav = themes_nav_session();

$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&search_category='.$search['category'].'&page='.$nav['page'].'&nav='.$nav['nav'];
if ($found) {
	$_SESSION['error_string'] = _('Selected contact has been deleted');
}
header("Location: ".$ref);
exit();
