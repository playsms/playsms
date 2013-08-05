<html>
<head>
<title><?php echo $web_title; ?></title>
<meta name="author" content="http://playsms.org">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/common.js"></script>
<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/dtree.js"></script>
<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/selectbox.js"></script>
<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/sorttable.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/common.css">
<link rel="stylesheet" type="text/css" href="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/dtree.css">

<div class="main">
<table class="main">
	<tbody>
	<tr>

		<!-- left menu -->
		<?php if (valid()) { ?>
		<td style="vertical-align: top; width: 20%;">
			<table style="vertical-align: top; width: 100%; height: 100%;">
				<tbody>
				<tr>
					<td style="background-color: #2C3E50; vertical-align: top; padding: 10px; height: 100px;">
						<img style="float: left; padding-right: 10px;" src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($core_config['user']['email']))); ?>">
						<p><?php echo $name; ?></p>
						<p><?php echo $username; ?></p>
					</td>
				</tr>
				<tr>
					<td style="background-color: #34495E; vertical-align: top; padding: 10px;">
						<?php echo themes_get_menu_tree(); ?>
					</td>
				</tr>
				</tbody>
			</table>
		</td>
		<?php } ?>

		<!-- content -->
		<td style="vertical-align: top; width: 80%;">
			<table style="vertical-align: top; width: 100%; height: 100%">
				<tbody>
				<tr>
					<td style="background-color: #7F8C8D; vertical-align: top; padding: 10px;">
