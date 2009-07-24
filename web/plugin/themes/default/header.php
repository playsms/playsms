<html>
<head>
<title><?=$web_title?></title>
<meta name="author" content="http://playsms.sourceforge.net">

</head>
<body>

<script type="text/javascript" src="<?=$http_path[themes]?>/<?=$themes_module?>/jscss/selectbox.js"></script>
<script type="text/javascript" src="<?=$http_path[themes]?>/<?=$themes_module?>/jscss/common.js"></script>
<script type="text/javascript" src="<?=$http_path[themes]?>/<?=$themes_module?>/jscss/dtree.js"></script>
<script type="text/javascript" src="<?=$http_path[themes]?>/<?=$themes_module?>/jscss/sorttable.js"></script>
<script type="text/javascript" src="<?=$http_path[themes]?>/<?=$themes_module?>/jscss/addfield.js"></script>

<link rel="stylesheet" type="text/css" href="<?=$http_path[themes]?>/<?=$themes_module?>/jscss/common.css">
<link rel="stylesheet" type="text/css" href="<?=$http_path[themes]?>/<?=$themes_module?>/jscss/dtree.css">
<link rel="stylesheet" type="text/css" href="<?=$http_path[themes]?>/<?=$themes_module?>/jscss/rfnet.css">

<table cellpadding=8 cellspacing=2 border=0 width=100%>
<tr>
    
    <!-- left menu -->
    <? if (valid()) { ?>
    <td style="vertical-align: top">
	<table style="width:200px">
	<tr>
	    <td style="border:#B4B3B3 1px solid; background-color:#F8F8F8; vertical-align:top; padding:10px;">
		<p><b>Logged in: <?=$username?></b></p>
		<p><b>Status: <?=$userstatus?></b></p>
		<?=themes_get_menu_tree()?>
	    </td>
	</tr>
	</table>
    </td>
    <? } ?>

    <!-- content -->
    <td style="vertical-align: top; width: 100%;">

