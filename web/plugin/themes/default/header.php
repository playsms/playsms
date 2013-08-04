<html>
<head>
<title><?php echo $web_title; ?></title>
<meta name="author" content="http://playsms.org">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<script type="text/javascript"
	src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/selectbox.js"></script>
<script type="text/javascript"
	src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/common.js"></script>
<script type="text/javascript"
	src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/dtree.js"></script>
<script type="text/javascript"
	src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/sorttable.js"></script>

<link rel="stylesheet" type="text/css"
	href="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/common.css">
<link rel="stylesheet" type="text/css"
	href="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/dtree.css">
<link rel="stylesheet" type="text/css"
	href="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/rfnet.css">

<div class="main">
<table cellpadding="8" cellspacing="2" border="0" width="100%">
	<tbody>
	<tr>

		<!-- left menu -->
	<?php if (valid()) { ?>
		<td style="vertical-align: top">
			<table style="width: 240px">
				<tbody>
				<tr>
					<td style="border: #E8E8E8 0px solid; background-color: #F5F5F5; vertical-align: top; padding: 10px;">
						<p><?php echo $name; ?>&nbsp;(<?php echo $username; ?>)</p>
						<p> <?php echo $userstatus; ?></p>
					</td>
				</tr>
				<tr>
					<td style="border: #E8E8E8 0px solid; background-color: #F5F5F5; vertical-align: top; padding: 10px;">
						<?php echo themes_get_menu_tree(); ?>
					</td>
				</tr>
				</tbody>
			</table>
		</td>
		<?php } ?>

		<!-- content -->
		<td style="vertical-align: top; width: 100%;">