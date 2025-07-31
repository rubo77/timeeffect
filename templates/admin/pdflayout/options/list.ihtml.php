<!-- admin/phplayout/option/list.ihtml - START -->
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
			<TR VALIGN="center">
				<TD WIDTH="40"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="40" HEIGHT="60" BORDER="0"></TD>
				<TD HEIGHT="60" CLASS="path"><?php include($GLOBALS['_PJ_root'] . '/templates/shared/path.ihtml.php'); ?>&nbsp;</TD>
			</TR>
		</TABLE>
		<div class="subnav-container">
			<a class="modern-tab active" href="<?= $GLOBALS['_PJ_pdf_admin_script'] . "?list=1&cid=$cid&pid=$pid" ?>"><?= $GLOBALS['_PJ_strings']['pdf_layout']?></a>
		</div>
<!-- admin/phplayout/option/list.ihtml - END -->
