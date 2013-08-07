<html>
<head>
<title><?php echo $web_title; ?></title>
<meta name="author" content="http://playsms.org">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/common/jscss/common.js"></script>
<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/common/jscss/selectbox.js"></script>
<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/common/jscss/sorttable.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/main.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/menu.css" media="screen" />

<div class="main">
<table class="main">
	<tbody>
	<tr>

		<!-- left menu -->
		<?php if (valid()) { ?>
		<td style="vertical-align: top; width: 274px;">
			<table style="vertical-align: top; width: 274px; height: 100%;">
				<tbody>
				<tr>
					<td style="background-color: #2C3E50; vertical-align: top; padding: 10px; height: 100px;">
						<a href="<?php echo $http_path['base']?>"><img style="float: left; padding-right: 10px;" src="<?php echo $core_config['user']['opt']['gravatar']; ?>"></a>
						<div><a href="<?php echo $http_path['base']?>"><?php echo $name; ?></a></div>
						<div><a href="<?php echo $http_path['base']?>"><?php echo $username; ?></a></div>
						<div style='padding-top: 1em;'><a href="index.php?app=page&op=auth_logout"><?php echo _('Logout'); ?></a></div>
					</td>
				</tr>
				<tr>
					<td style="background-color: #34495E; vertical-align: top; padding: 0px;">
						<?php echo themes_get_menu_tree(); ?>
					</td>
				</tr>
				</tbody>
			</table>
		</td>
		<?php } ?>

		<!-- content -->
		<td style="vertical-align: top; width: 750px;">
			<table style="vertical-align: top; width: 100%; height: 100%;">
				<tbody>
				<tr>
					<td style="background-color: #7F8C8D; vertical-align: top; padding: 10px;">
