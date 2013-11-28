<?php
// You can replace $web_title with a fixed text like this:
// $theme_play_head1 = "My Awesome Corp."; 
// Otherwise it will pick the value from configuration
$theme_play_head1 = $web_title;

// This string must be replaced, it exposes this file location
// try something like
// $theme_play_head2 = "Pls dont bomb my modem, peace...";
// or just disable it
// $theme_play_head2 = "";
$theme_play_head2 = "Change ".__FILE__." to customize";

// If you have a custom logo, you can enable it here, just change
// the value of $theme_image to the logo filename the theme
// images folder (i.e. default_logo.png)
$theme_image = "";

$copychar = "&copy;";
$copyyear = date("Y");

// copyright on footer
//$theme_play_foot1 = "$copychar $copyyear My Awesome Corp.";
$theme_play_foot1 = "$copychar $copyyear $web_title";

// themes 'play' action icons
$core_config['plugin']['play']['icon']['edit'] 		= "<img class=icon id=icon_edit src=\"".$http_path['themes']."/play/images/action_edit.png\" alt=\""._('Edit')."\" title=\""._('Edit')."\">";
$core_config['plugin']['play']['icon']['delete'] 	= "<img class=icon id=icon_delete src=\"".$http_path['themes']."/play/images/action_delete.png\" alt=\""._('Delete')."\" title=\""._('Delete')."\">";
$core_config['plugin']['play']['icon']['view'] 		= "<img class=icon id=icon_view src=\"".$http_path['themes']."/play/images/action_view.png\" alt=\""._('View')."\" title=\""._('View')."\">";
$core_config['plugin']['play']['icon']['manage'] 	= "<img class=icon id=icon_manage src=\"".$http_path['themes']."/play/images/action_manage.png\" alt=\""._('Manage')."\" title=\""._('Manage')."\">";
$core_config['plugin']['play']['icon']['reply'] 	= "<img class=icon id=icon_reply src=\"".$http_path['themes']."/play/images/action_reply.png\" alt=\""._('Reply')."\" title=\""._('Reply')."\">";
$core_config['plugin']['play']['icon']['forward'] 	= "<img class=icon id=icon_forward src=\"".$http_path['themes']."/play/images/action_forward.png\" alt=\""._('Forward')."\" title=\""._('Forward')."\">";
$core_config['plugin']['play']['icon']['resend'] 	= "<img class=icon id=icon_resend src=\"".$http_path['themes']."/play/images/action_resend.png\" alt=\""._('Resend')."\" title=\""._('Resend')."\">";
$core_config['plugin']['play']['icon']['user_pref'] 	= "<img class=icon id=icon_user_pref src=\"".$http_path['themes']."/play/images/action_user_pref.png\" alt=\""._('User preference')."\" title=\""._('User preference')."\">";
$core_config['plugin']['play']['icon']['user_config'] 	= "<img class=icon id=icon_user_config src=\"".$http_path['themes']."/play/images/action_user_config.png\" alt=\""._('User configuration')."\" title=\""._('User configuration')."\">";
$core_config['plugin']['play']['icon']['user_delete'] 	= "<img class=icon id=icon_user_delete src=\"".$http_path['themes']."/play/images/action_user_delete.png\" alt=\""._('Delete user')."\" title=\""._('Delete user')."\">";

?>