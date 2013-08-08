<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php include $apps_path['themes']."/".$themes_module."/header.php"; ?>

<table width='100%'>
	<tbody>
	<tr>
		<td align="center">
		<table style='border-radius: 3px; background-color: #2C3E50; width: 360px'>
			<tbody>
			<tr>
				<td>
				<form action="index.php" method=POST>
				<input type=hidden name=app value=page>
				<input type=hidden name=inc value=login>
				<input type=hidden name=op value=auth_forgot>
				<table width="100%">
					<tbody>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td align='center' colspan='2'>
							<a href=<?php echo $http_path['base']?>><?php echo $web_title?></a>
						</td>
					</tr>
					<tr>
						<td align='center' colspan='2'>
							<?php echo $error_content?>
						</td>
					</tr>
					<tr>
						<td width="100" align="right"><?php echo _('Username'); ?> &nbsp;</td>
						<td>&nbsp;<input type=text name=username maxlength=100 size=20></td>
					</tr>
					<tr>
						<td align="right"><?php echo _('Email'); ?> &nbsp;</td>
						<td>&nbsp;<input type=text name=email maxlength=100 size=20></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<input type=submit class=button value=<?php echo _('Recover password'); ?>></td>
					</tr>

					<?php if ($core_config['main']['cfg_enable_register']) { ?>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<?php echo "<a href='index.php?app=page&inc=register'>"._('Register an account')."</a>"; ?></td>
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
