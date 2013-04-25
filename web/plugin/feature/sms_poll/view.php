<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case 'list':
		$poll_id = $_REQUEST['poll_id'];
		$conditions['poll_id'] = $poll_id;
		if (! isadmin()) {
			$uid = $core_config['user']['uid'];
			$conditions['uid'] = $uid;
		}
		$list = dba_search(_DB_PREF_.'_featurePoll', '*', $conditions);
		$poll_keyword = $list[0]['poll_keyword'];
		$output_serialize = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_poll&keyword=".urlencode($poll_keyword)."&type=serialize";
		$output_json = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_poll&keyword=".urlencode($poll_keyword)."&type=json";
		$output_xml = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_poll&keyword=".urlencode($poll_keyword)."&type=xml";
		$output_html = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_poll&keyword=".urlencode($poll_keyword)."&type=html";
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage poll')."</h2>
			<h3>"._('View poll')." : ".$poll_keyword."</h3>
			<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr><td width=100>"._('PHP serialize output')."</td><td>:</td><td><a href=\"".$output_serialize."\" target=_blank>".$output_serialize."</a></td></tr>
				<tr><td>"._('JSON output')."</td><td>:</td><td><a href=\"".$output_json."\" target=_blank>".$output_json."</a></td></tr>
				<tr><td>"._('XML output')."</td><td>:</td><td><a href=\"".$output_xml."\" target=_blank>".$output_xml."</a></td></tr>
				<tr><td>"._('HTML output')."</td><td>:</td><td><a href=\"".$output_html."\" target=_blank>".$output_html."</a></td></tr>
			</table>
			<p>"._b('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_list');
		echo $content;
		break;
}

?>