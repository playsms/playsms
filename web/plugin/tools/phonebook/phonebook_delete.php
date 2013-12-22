<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

$checkid = $_REQUEST['checkid'];
$itemid = $_REQUEST['itemid'];

$c_itemid = array();
foreach ($checkid as $key => $val) {
	if (strtoupper($val) == 'ON') {
		if ($c_itemid = $itemid[$key]) {
			if (dba_remove(_DB_PREF_.'_toolsPhonebook', array('uid' => $core_config['user']['uid'], 'id' => $c_itemid))) {
				dba_remove(_DB_PREF_.'_toolsPhonebook_group_contacts', array('pid' => $c_itemid));
				$found = TRUE;
			}
		}
	}

}

$search = themes_search_session();
$nav = themes_nav_session();

$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&search_category='.$search['category'].'&page='.$nav['page'].'&nav='.$nav['nav'];
if ($found) {
	$_SESSION['error_string'] = _('Selected contact has been deleted');
}
header("Location: ".$ref);
exit();
