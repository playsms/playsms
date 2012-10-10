<?php defined('_SECURE_') or die('Forbidden'); ?>
<html>
<head>
<title><?php echo $web_title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<!--link rel="stylesheet" type="text/css" href="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/test/common.css"-->
<link rel="stylesheet" type="text/css"
	href="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/style.css">

</head>
<body>
<div id="wraplogin">
<table width="100%" height="100%" border="0" cellpadding="0"
	cellspacing="0">
	<tr>
		<td align="center" valign="center" bgcolor="#666666">
		<table width="960px" height="125px" border="0" cellpadding="0"
			cellspacing="0">
			<tr>
				<!--td background="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/images/header-4.png"-->
				<td class="header">
				<table border="0" cellpadding="2" cellspacing="2">
					<tr>
						<td width="90">&nbsp;</td>
						<td>&nbsp;<?php echo $error_content; ?></td>
					</tr>
					<form action="index.php" method="POST"><input type="hidden"
						name="app" value="page"> <input type="hidden" name="inc"
						value="login"> <input type="hidden" name="op" value="auth_login">
					<tr>
						<td width="90" align="right"><font color="white"><?php echo _('Username'); ?></td>
						<td>&nbsp;<input type="text" name="username" maxlength="100"
							size="20"></td>
					</tr>
					<tr>
						<td align="right"><font color="white"><?php echo _('Password'); ?></font></td>
						<td>&nbsp;<input type=password name=password maxlength=100 size=20></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<input type="submit" class="button"
							value="<?php echo _('Login'); ?>"></td>
					</tr>
					</form>

					<tr>
						<td>&nbsp;</td>
						<td>&nbsp; <?php if ($core_config['main']['cfg_enable_register']) { ?>
						<?php echo "<a href='index.php?app=page&inc=register'>"._('Register an account')."</a>"; ?>&nbsp;&nbsp;
						<?php } ?> <?php if ($core_config['main']['cfg_enable_forgot']) { ?>
						<?php echo "<a href='index.php?app=page&inc=forgot'>"._('Forgot password')."</a>"; ?>
						<?php } ?></td>
					</tr>

				</table>
				<br />
				</TD>
			</tr>
		</table>
		</td>
	</tr>
</table>