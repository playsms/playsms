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
?> 
