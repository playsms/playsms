<html>
<head>
<title><?=$web_title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<script type="text/javascript" src="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/selectbox.js"></script>
<script type="text/javascript" src="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/common.js"></script>
<script type="text/javascript" src="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/dtree.js"></script>
<script type="text/javascript" src="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/sorttable.js"></script>
<!--script type="text/javascript" src="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/addfield.js"></script-->

<!--link rel="stylesheet" type="text/css" href="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/common.css"-->
<link rel="stylesheet" type="text/css" href="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/dtree.css">
<!--link rel="stylesheet" type="text/css" href="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/rfnet.css"-->
<link rel="stylesheet" type="text/css" href="<?=$http_path['themes']?>/<?=$themes_module?>/jscss/style.css">

</head>
<body>
<div id="wrap">

  <div id="top">
    <img alt="top" src="<?=$http_path['themes']?>/<?=$themes_module?>/images/top.gif">
  </div>

  <div id="header">

    <div class="headerleft">
      <a href="<?=$http_path['base']?>">
        <!--img src="<?=$http_path['themes']?>/<?=$themes_module?>/images/your-logo.png" alt="Your logo" /-->
      </a>
    </div>

</div>


<div id="navbar">

	<ul id="nav">
		<li><a href="<?=$http_path['base']?>"><?=_('Home')?></a></li>
		<li class="page_item page-item-22"><a href="./index.php?app=menu&inc=user_pref&op=user_pref" title="<?=_('Preferences')?>"><?=_('Preferences')?></a></li>
		<li class="page_item page-item-22"><a href="./contact.php" title="<?=_('Contact')?>"><?=_('Contact')?></a></li>
		<li class="page_item page-item-22"><a href="./index.php?app=menu&inc=logout" title="<?=_('Logout')?>"><?=_('Logout')?></a></li>
    
</div>

<div style="clear:both;"></div>

<div id="content">
<div id="homepage">

<table class="main" cellpadding="8" cellspacing="2" border="0" width="100%">
<tr>
    
    <!-- left menu -->
    <?php if (valid()) { ?>
    <td style="vertical-align: top">
	<table style="width:200px">
	<tr>
	    <td style="border:#B4B3B3 1px solid; background-color:#F8F8F8; vertical-align:top; padding:10px;">
		<p><b>Login: <?=$username ?></b></p>
		<p><b>Status: <?=$userstatus ?></b></p>
		<?=themes_get_menu_tree()?>
	    </td>
	</tr>
	</table>
    </td>
    <?php } ?>

    <!-- content -->
    <td style="vertical-align: top; width: 100%;">

