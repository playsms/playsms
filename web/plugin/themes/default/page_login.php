<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php include $apps_path['themes']."/".$themes_module."/header.php"; ?>

<table width="100%" height="100%" border=0 cellpadding=0 cellspacing=0>
	<tbody>
	<tr>
		<td align="center" valign="middle">
		<table width=400 border=0 cellpadding=0 cellspacing=0 bgcolor=#2C3E50>
			<tbody>
			<tr>
				<td>
				<form action="index.php" method=POST>
				<input type=hidden name=app value=page>
				<input type=hidden name=inc value=login>
				<input type=hidden name=op value=auth_login>
				<table width="100%" border="0" cellpadding="2" cellspacing="2">
					<tbody>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td align='center' valign='middle' colspan='2'>
							<a href=<?php echo $http_path['base']?>><?php echo $web_title?></a>
						</td>
					</tr>
					<tr>
						<td align='center' valign='middle' colspan='2'>
							<?php echo $error_content?>
						</td>
					</tr>
					<tr>
						<td width="140" align="right"><?php echo _('Username'); ?> &nbsp;</td>
						<td>&nbsp;<input type=text name=username maxlength=100 size=20></td>
					</tr>
					<tr>
						<td align="right"><?php echo _('Password'); ?> &nbsp;</td>
						<td>&nbsp;<input type=password name=password maxlength=100 size=20></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<input type=submit class=button value=<?php echo _('Login'); ?>></td>
					</tr>

					<?php if ($core_config['main']['cfg_enable_register']) { ?>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<?php echo "<a href='index.php?app=page&inc=register'>"._('Register an account')."</a>"; ?></td>
					</tr>
					<?php } ?>

					<?php if ($core_config['main']['cfg_enable_forgot']) { ?>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<?php echo "<a href='index.php?app=page&inc=forgot'>"._('Forgot password')."</a>"; ?></td>
					</tr>
					<?php } ?>

					</tbody>
				</table>
				</form>
				</td>
			</tr>
			</tbody>
		</table>
		</td>
	</tr>
	</tbody>
</table>

<?php include $apps_path['themes']."/".$themes_module."/footer.php"; ?>
