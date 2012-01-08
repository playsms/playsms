<?php
// you can replace $web_title with something like
// $theme_play_head1 = "My Awesome Corp.";
$theme_play_head1 = $web_title;

// must be replaced, it exposes this file location
// try something like
// $theme_play_head2 = "Pls dont bomb my modem, peace...";
// or just disable it
// $theme_play_head2 = "";
$theme_play_head2 = "Change ".__FILE__." to customize";

$copychar = "&copy;";
$copyyear = date("Y");

// copyright on footer
//$theme_play_foot1 = "$copychar $copyyear My Awesome Corp.";
$theme_play_foot1 = "$copychar $copyyear $web_title";
?> 
