<?php
defined('_SECURE_') or die('Forbidden');

switch ($op) {
	case 'list':
		$conditions['board_id'] = $board_id;
		$list = dba_search(_DB_PREF_.'_featureBoard', '*', $conditions);
		$board_keyword = $list[0]['board_keyword'];
		$output_serialize = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_board&keyword=".urlencode($board_keyword)."&type=serialize";
		$output_json = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_board&keyword=".urlencode($board_keyword)."&type=json";
		$output_xml = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_board&keyword=".urlencode($board_keyword)."&type=xml";
		$output_rss091 = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_board&keyword=".urlencode($board_keyword)."&type=feed&format=rss0.91";
		$output_rss10 = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_board&keyword=".urlencode($board_keyword)."&type=feed&format=1.0";
		$output_rss20 = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_board&keyword=".urlencode($board_keyword)."&type=feed&format=2.0";
		$output_atom = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_board&keyword=".urlencode($board_keyword)."&type=feed&format=atom";
		$output_mbox = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_board&keyword=".urlencode($board_keyword)."&type=feed&format=mbox";
		$output_html = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_board&keyword=".urlencode($board_keyword)."&type=html";
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage board')."</h2>
			<h3>"._('View board')." : ".$board_keyword."</h3>
			<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr><td width=100>"._('PHP serialize output')."</td><td>:</td><td><a href=\"".$output_serialize."\" target=_blank>".$output_serialize."</a></td></tr>
				<tr><td>"._('JSON output')."</td><td>:</td><td><a href=\"".$output_json."\" target=_blank>".$output_json."</a></td></tr>
				<tr><td>"._('XML output')."</td><td>:</td><td><a href=\"".$output_xml."\" target=_blank>".$output_xml."</a></td></tr>
				<tr><td>"._('RSS 0.91 output')."</td><td>:</td><td><a href=\"".$output_rss091."\" target=_blank>".$output_rss091."</a></td></tr>
				<tr><td>"._('RSS 1.0 output')."</td><td>:</td><td><a href=\"".$output_rss10."\" target=_blank>".$output_rss10."</a></td></tr>
				<tr><td>"._('RSS 2.0 output')."</td><td>:</td><td><a href=\"".$output_rss20."\" target=_blank>".$output_rss20."</a></td></tr>
				<tr><td>"._('RSS ATOM output')."</td><td>:</td><td><a href=\"".$output_atom."\" target=_blank>".$output_atom."</a></td></tr>
				<tr><td>"._('MBOX output')."</td><td>:</td><td><a href=\"".$output_mbox."\" target=_blank>".$output_mbox."</a></td></tr>
				<tr><td>"._('HTML output')."</td><td>:</td><td><a href=\"".$output_html."\" target=_blank>".$output_html."</a></td></tr>
			</table>
			<p>"._b('index.php?app=menu&inc=feature_sms_board&op=sms_board_list');
		echo $content;
		break;
}

?>