<?php include $apps_path['themes']."/".$themes_module."/header.php"; ?>

<TABLE WIDTH="100%" height="100%" BORDER=0 CELLPADDING=0 CELLSPACING=0>
<TR>
    <td align="center" valign="middle" bgcolor="#fcfcfc">
    <TABLE WIDTH=370 BORDER=0 CELLPADDING=0 CELLSPACING=0>
    <TR>
        <TD COLSPAN=3><a href="<?=$http_path['base']?>"><IMG SRC="<?=$http_path['themes']?>/<?=$themes_module?>/images/login_00.gif" WIDTH=370 border=0></a></TD>
    </TR>
    <TR>
        <TD WIDTH=7 background="<?=$http_path['themes']?>/<?=$themes_module?>/images/login_02.gif"><IMG SRC="<?=$http_path['themes']?>/<?=$themes_module?>/images/login_02.gif" WIDTH=7 HEIGHT=16></TD>
        <TD WIDTH=348 bgcolor="#FDFCFC">
            <br />
            <?=$error_content?>
            <table width="100%" border="0" cellpadding="2" cellspacing="2">
            <form action=menu.php method=POST>
            <input type=hidden name=inc value=login>
            <tr>
        <td width="140" align="right"><?=_('Username')?> &nbsp; </td>
        <td>&nbsp;<input type=text name=username maxlength=100 size=20></td>
    </tr>
    <tr>
    <td align="right"><?=_('Password')?> &nbsp; </td>
    <td>&nbsp;<input type=password name=password maxlength=100 size=20></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;<input type=submit class=button value=Login></td>
</tr>
</form>          
</table>
<br />
</TD>
<TD WIDTH=15 background="<?=$http_path['themes']?>/<?=$themes_module?>/images/login_04.gif"><IMG SRC="<?=$http_path['themes']?>/<?=$themes_module?>/images/login_04.gif" WIDTH=15 HEIGHT=16></TD>
</TR>
<TR>
    <TD COLSPAN=3><a href="http://playsms.org"><IMG SRC="<?=$http_path['themes']?>/<?=$themes_module?>/images/powered_by_playsms.gif" WIDTH=370 border=0></a></TD>
</TR>
</TABLE>
</td>
</TR>
</TABLE>

<?php include $apps_path['themes']."/".$themes_module."/footer.php"; ?>
