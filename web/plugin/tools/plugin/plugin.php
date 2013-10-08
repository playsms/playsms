<?php
$content = "<link href='".$http_path['plug']."/tools/plugin/css/style.css' rel='stylesheet'>";
$content .= "<script src='".$http_path['plug']."/tools/plugin/js/jquery.js' type='text/javascript'></script>
	<script src='".$http_path['plug']."/tools/plugin/js/jquery.hashchange.js' type='text/javascript'></script>
	<script src='".$http_path['plug']."/tools/plugin/js/jquery.easytabs.js' type='text/javascript'></script>
	<script type='text/javascript'>
	    $(document).ready( function() {
	      $('#tab-container').easytabs();
	    });
	  </script>
";

$content .= "
<div id='tab-container' class='tab-container'>
  <ul class='etabs'>
    <li class='tab'><a href='#tabs-feature'>"._('Features')."</a></li>
    <li class='tab'><a href='#tabs-gateway'>"._('Gateways')."</a></li>
    <li class='tab'><a href='#tabs-theme'>"._('Themes')."</a></li>
    <li class='tab'><a href='#tabs-tool'>"._('Tools')."</a></li>
    <li class='tab'><a href='#tabs-lang'>"._('Languages')."</a></li>
  </ul>

  <div id='tabs-feature'>";
    $content .= plugin_table('feature');
    $content .= "<br />";
  $content .= "</div>

  <div id='tabs-gateway'>";
    $content .= plugin_table('gateway');
    $content .= "<br />";
  $content .= "</div>

  <div id='tabs-theme'>";
    $content .= plugin_table('themes');
    $content .= "<br />";
  $content .= "</div>

  <div id='tabs-tool'>";
    $content .= plugin_table('tools');
    $content .= "<br />";
  $content .= "</div>

  <div id='tabs-lang'>";
    $content .= plugin_table('language');
    $content .= "<br />";
  $content .= "</div>

</div>";

echo $content;
?>
