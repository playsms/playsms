<html>
<head>
<title><?php echo $web_title; ?></title>
<meta name="author" content="http://antonraharja.com">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $themes_default_charset; ?>">
</head>
<body>

<!-- load common js and css -->
<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/common/jscss/common.js"></script>
<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/common/jscss/selectbox.js"></script>
<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/common/jscss/sorttable.js"></script>
<script type="text/javascript" src="<?php echo $http_path['themes']; ?>/common/jscss/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $http_path['themes']; ?>/common/jscss/common.css" />

<!-- load specific themes default js and css -->
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
						<div style='float: left'>
							<div style='padding: 0; width: 170px;'><a href="index.php?app=menu&inc=user_pref&op=user_pref"><?php echo $name; ?></a></div>
							<div style='padding: 0; width: 170px;'><a href="index.php?app=menu&inc=user_pref&op=user_pref"><?php echo $username; ?></a></div>
							<div style='padding: 22px 0 0 0; width: 170px;'><a href="index.php?app=page&op=auth_logout"><?php echo _('Logout'); ?></a></div>
						</div>
						<style type='text/css'>
							#profile_pic {
								background-image:url('<?php echo $core_config['user']['opt']['gravatar']; ?>');
								background-color: #2980B9;
								background-repeat: no-repeat;
								margin-left: 174px;
								width: 80px;
								height: 80px;
								cursor: pointer;
							}
						</style>
						<div id=profile_pic onClick="window.location.href='<?php echo $http_path['base']?>'"/>
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
