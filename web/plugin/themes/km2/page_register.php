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
				<td class="header"><!--br /-->
				<table border="0" cellpadding="2" cellspacing="2">
					<tr>
						<td colspan="2"><?php echo $error_content; ?></td>
					</tr>
					<form action="index.php" method="POST"><input type="hidden"
						name="app" value="page"> <input type="hidden" name="inc"
						value="register"> <input type="hidden" name="op"
						value="auth_register">
					<tr>
						<td width="90" align="right"><font color="white"><?php echo _('Name'); ?></td>
						<td>&nbsp;<input type="text" name="name" maxlength="100" size="20"></td>
					</tr>
					<tr>
						<td width="90" align="right"><font color="white"><?php echo _('Username'); ?></td>
						<td>&nbsp;<input type="text" name="username" maxlength="100"
							size="20"></td>
					</tr>
					<tr>
						<td width="90" align="right"><font color="white"><?php echo _('Mobile'); ?></td>
						<td>&nbsp;<input type="text" name="mobile" maxlength="100"
							size="20"></td>
					</tr>
					<tr>
						<td width="90" align="right"><font color="white"><?php echo _('Email'); ?></td>
						<td>&nbsp;<input type="text" name="email" maxlength="100"
							size="20"></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<input type="submit" class="button"
							value="<?php echo _('Register'); ?>"></td>
					</tr>
					</form>
				</table>
				<br />
				</TD>
			</tr>
		</table>
		</td>
	</tr>
</table>