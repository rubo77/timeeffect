<!-- note.ihtml - START -->
<?php
// Include unified header
include_once(__DIR__ . '/shared/header.ihtml.php');
?>
<script>
// Initialize user theme preference from PHP
(function() {
    var userTheme = '<?php echo isset($_PJ_auth) ? ($_PJ_auth->giveValue("theme_preference") ?: "system") : "system"; ?>';
    if (userTheme !== 'system') {
        document.documentElement.setAttribute('data-theme', userTheme);
    }
})();
</script>
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" HEIGHT="100%">
	<TR>
		<TD VALIGN="top">
<!-- START - content -->
<?php
	if(!empty($center_template)) {
		include("$_PJ_root/templates/$center_template/note.ihtml.php");
	} else {
		// Display success or error message with modern app styling
		echo '<div class="container" style="max-width: 600px; margin: 2rem auto; padding: 0 10px;">';
		echo '<div class="card" style="padding: 2rem; text-align: center;">';
		
		if (isset($success_message) && $success_message != '') {
			echo '<div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">';
			echo '<h2 style="margin: 0 0 0.5rem 0;">✅ ' . htmlspecialchars($GLOBALS['_PJ_strings']['success']) . '</h2>';
			echo '<p style="margin: 0; font-size: 1.1rem;">' . htmlspecialchars($success_message) . '</p>';
			echo '</div>';
		} elseif (isset($error_message) && $error_message != '') {
			echo '<div class="alert alert-error" style="background-color: #f8d7da; color: #721c24; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">';
			echo '<h2 style="margin: 0 0 0.5rem 0;">❌ ' . htmlspecialchars($GLOBALS['_PJ_strings']['error']) . '</h2>';
			echo '<p style="margin: 0; font-size: 1.1rem;">' . htmlspecialchars($error_message) . '</p>';
			echo '</div>';
		} else {
			echo '<div class="alert alert-info" style="background-color: #d1ecf1; color: #0c5460; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">';
			echo '<h2 style="margin: 0 0 0.5rem 0;">ℹ️ ' . htmlspecialchars($GLOBALS['_PJ_strings']['information']) . '</h2>';
			echo '<p style="margin: 0; font-size: 1.1rem;">';
			if (!empty($info_message)) {
				echo htmlspecialchars($info_message);
			} else {
				echo htmlspecialchars($GLOBALS['_PJ_strings']['no_message_to_display']);
			}
			echo '</p>';
			echo '</div>';
		}
		
		if(isset($redirect_url)) {
			// add link to redirect to URL
			echo '<div class="form-actions" style="margin-top: 1.5rem;">';
			echo '<a href="' . $redirect_url . '" class="btn btn-primary" style="display: inline-block; padding: 0.75rem 1.5rem; background-color: #007bff; color: white; text-decoration: none; border-radius: 0.375rem; font-weight: 500;">Continue</a>';
			echo '</div>';
		} else {
			// Add navigation link back to main page
			echo '<div class="form-actions" style="margin-top: 1.5rem;">';
			echo '<a href="' . (isset($_PJ_http_root) ? $_PJ_http_root : '') . '/" class="btn btn-primary" style="display: inline-block; padding: 0.75rem 1.5rem; background-color: #007bff; color: white; text-decoration: none; border-radius: 0.375rem; font-weight: 500;">Back to Main Page</a>';
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
	}
?>
		</TD>
<!-- END - content -->
	</TR><TR>
		<TD class="version">&nbsp;TIMEEFFECT Version:&nbsp;<?php if(isset($_PJ_timeeffect_version)) echo $_PJ_timeeffect_version; ?> (Revision: <?php if(isset($_PJ_timeeffect_revision)) echo $_PJ_timeeffect_revision; ?>, <?= date($_PJ_format_datetime, strtotime($_PJ_timeeffect_date)) ?>)</td>
	</TR>
</TABLE>
<!-- Theme Management JavaScript -->
<SCRIPT SRC="<?php print $_PJ_http_root; ?>/js/theme.js" type="text/javascript"></SCRIPT>
</BODY>
</HTML>
<!-- note.ihtml - END -->
