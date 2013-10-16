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
				<input type=hidden name=op value=auth_login>
				<table width="100%">
					<tbody>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td align='center' colspan='2'>
							<a href={HTTP_PATH_BASE}>{WEB_TITLE}</a>
						</td>
					</tr>
					<tr>
						<td align='center' colspan='2'>
							{ERROR}
						</td>
					</tr>
					<tr>
						<td width="100" align="right">{Username} &nbsp;</td>
						<td>&nbsp;<input type=text name=username maxlength=100 size=20></td>
					</tr>
					<tr>
						<td align="right">{Password} &nbsp;</td>
						<td>&nbsp;<input type=password name=password maxlength=100 size=20></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<input type=submit class=button value='{Login}'></td>
					</tr>

					<if.enable_register>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<a href='index.php?app=page&inc=register'>{Register an account}</a></td>
					</tr>
					</if.enable_register>

					<if.enable_forgot>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<a href='index.php?app=page&inc=forgot'>{Forgot password}</a></td>
					</tr>
					</if.enable_forgot>

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
