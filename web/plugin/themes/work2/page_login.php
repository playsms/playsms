<?php defined('_SECURE_') or die('Forbidden'); ?>
<?php include $apps_path['themes']."/".$themes_module."/header.php"; ?>

<TABLE WIDTH="100%" height="100%" BORDER=0 CELLPADDING=0 CELLSPACING=0>
	<TR>
		<td align="center" valign="middle" bgcolor="#fcfcfc">
		<TABLE WIDTH=370 BORDER=0 CELLPADDING=0 CELLSPACING=0>
			<TR>
				<TD COLSPAN=3><a href="<?php echo $http_path['base']; ?>"><IMG
					SRC="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/images/login_00.gif"
					WIDTH=370 border=0></a></TD>
			</TR>
			<TR>
				<TD WIDTH=7
					background="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/images/login_02.gif"><IMG
					SRC="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/images/login_02.gif"
					WIDTH=7 HEIGHT=16></TD>
				<TD WIDTH=348 bgcolor="#FDFCFC"><br />
				<div align=center><?php echo $error_content?></div>
				<table width="100%" border="0" cellpadding="2" cellspacing="2">
					<form action="index.php" method=POST><input type=hidden name=app
						value=page> <input type=hidden name=inc value=login> <input
						type=hidden name=op value=auth_login>
					<tr>
						<td width="140" align="right"><?php echo _('Username'); ?> &nbsp;
						</td>
						<td>&nbsp;<input type=text name=username maxlength=100 size=20></td>
					</tr>
					<tr>
						<td align="right"><?php echo _('Password'); ?> &nbsp;</td>
						<td>&nbsp;<input type=password name=password maxlength=100 size=20></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<input type=submit class=button
							value="<?php echo _('Login'); ?>"></td>
					</tr>
					</form>

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

				</table>

				<br />

				</TD>
				<TD WIDTH=15
					background="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/images/login_04.gif"><IMG
					SRC="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/images/login_04.gif"
					WIDTH=15 HEIGHT=16></TD>
			</TR>
			<TR>
				<TD COLSPAN=3><a href="http://playsms.org"><IMG
					SRC="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/images/powered_by_playsms.gif"
					WIDTH=370 border=0></a></TD>
			</TR>
		</TABLE>
		</td>
	</TR>
</TABLE>

					<?php include $apps_path['themes']."/".$themes_module."/footer.php"; ?>
