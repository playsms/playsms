<html>
<head>
<title><?=$web_title?></title>
<meta name="author" content="http://playsms.sourceforge.net">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<script type="text/javascript" src="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/selectbox.js"></script>
<script type="text/javascript" src="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/common.js"></script>
<script type="text/javascript" src="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/dtree.js"></script>
<script type="text/javascript" src="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/sorttable.js"></script>
<script type="text/javascript" src="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/dropdown.js"></script>

<link rel="stylesheet" type="text/css" href="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/common.css">
<link rel="stylesheet" type="text/css" href="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/dtree.css">
<link rel="stylesheet" type="text/css" href="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/rfnet.css">
<link rel="stylesheet" type="text/css" href="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/dropdown.css">

<table cellpadding=8 cellspacing=2 border=0 width=100%>
<tr>
    
    <!-- left menu -->
    <?php if (valid()) { ?>
    <td style="vertical-align: top">
	<p><b><?=_('Logged in')?>: <?=$username?></b> &nbsp; &nbsp; <b><?=_('Status')?>: <?=$userstatus?></b></p>
	<?=themes_work2_get_menu_dropdown()?>
    </td>
    <?php } ?>
</tr>
    <!-- content -->
<tr>
    <td style="vertical-align: top; width: 100%;">

