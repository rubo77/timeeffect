<!-- admin/pdflayout/list.ihtml - START -->
<?php
	include($GLOBALS['_PJ_root'] . '/templates/admin/pdflayout/options/list.ihtml.php');
?>
<TABLE	WIDTH="100%"
		BORDER="<?php print($_PJ_inner_frame_border); ?>"
		CELLPADDING="<?php print($_PJ_inner_frame_cellpadding); ?>"
		CELLSPACING="<?php print($_PJ_inner_frame_cellspacing ); ?>">
	<TR>
		<TD CLASS="content">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
			<TR>
				<TD COLSPAN="3"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
					<TR>
						<TD>&nbsp;</TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="90%">
					<TR>
<form action="<?php if(isset($PHP_SELF)) echo $PHP_SELF; ?>" method="POST">
<input type="hidden" name="altered" value="1">
<?php
foreach($GLOBALS as $gname => $gvalue) {
	if(!strstr($gname, '_script') &&  $gname != '_PJ_pdf_font_face' && substr($gname, 0, 8) == '_PJ_pdf_') {
?>
					<tr>
<?php
		$sg_name = substr($gname, 4);
		$show_name = $sg_name;
		if($GLOBALS['_PJ_strings'][$sg_name]) {
			$show_name = $GLOBALS['_PJ_strings'][$sg_name];
		}
		if(is_array($gvalue)) {
?>
						<td align="right" width="50%" valign="top"><?php if(isset($show_name)) echo $show_name; ?>:&nbsp;</td><td>
<?php
			foreach($gvalue as $ggname => $ggvalue) {
?>
						<input name="<?php if(isset($sg_name)) echo $sg_name; ?>[<?php if(isset($ggname)) echo $ggname; ?>]" value="<?php if(isset($ggvalue)) echo $ggvalue; ?>" size="3"> <?= $GLOBALS['_PJ_strings'][$ggname] ?><br>
<?php
			}
?>
						</td>
<?php
		} else {
			$sg_colour_string = substr($sg_name, strlen($sg_name)-2);
			if(!empty($sg_colour_string) and $sg_colour_string == '_r') {
				$colour_prefix = substr($sg_name, 0, strlen($sg_name)-1);
?>
						<td align="right" width="50%"><?php if(isset($show_name)) echo $show_name; ?>:&nbsp;</td>
						<td>
						<input name="<?php if(isset($sg_name)) echo $sg_name; ?>" value="<?php if(isset($gvalue)) echo $gvalue; ?>" size="3" maxlength="3"> (R)
						<input name="<?= $colour_prefix . 'g' ?>" value="<?= $GLOBALS['_PJ_' . $colour_prefix . 'g'] ?>" size="3" maxlength="3"> (G)
						<input name="<?= $colour_prefix . 'b' ?>" value="<?= $GLOBALS['_PJ_' . $colour_prefix . 'b'] ?>" size="3" maxlength="3"> (B)
<?php
					"</td>";
			} else if($sg_colour_string != '_g' && $sg_colour_string != '_b') {
				if(!empty($sg_name) and $sg_name == 'pdf_footer_string') {
?>
					<td align="right" width="50%"><?php if(isset($show_name)) echo $show_name; ?>:&nbsp;</td>
					<td><textarea rows="3" cols="50" name="<?php if(isset($sg_name)) echo $sg_name; ?>"><?php if(isset($gvalue)) echo $gvalue; ?></textarea></td>
<?php
				} else {
?>
					<td align="right" width="50%"><?php if(isset($show_name)) echo $show_name; ?>:&nbsp;</td>
					<td><input name="<?php if(isset($sg_name)) echo $sg_name; ?>" value="<?php if(isset($gvalue)) echo $gvalue; ?>" size="50"></td>
<?php
				}
			} else {
				continue;
			}
		}
	}
}
?>
					<tr>
						<td colspan="2" align="center"><br><input type="submit" value="<?php if(!empty($GLOBALS['_PJ_strings']['save'])) echo $GLOBALS['_PJ_strings']['save'] ?> >>"></td>
					</tr>
</form>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table>
<!-- admin/pdflayout/list.ihtml - END -->
