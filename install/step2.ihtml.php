<?php
$locale_info = localeconv();
if(!isset($currency)) {
	$currency = 'EUR';
}
if(!isset($decimal_point)) {
	$decimal_point = ',';
}
if(!isset($thousands_seperator)) {
	$thousands_seperator = '.';
}
if($thousands_seperator == '') {
	if($decimal_point == '.') {
		$thousands_seperator = ',';
	} else {
		$thousands_seperator = '.';
	}
}
if(!isset($session_length)) {
	$session_length = 3600;
}
if(!isset($admin_user)) {
	$admin_user = 'admin';
}
if(!isset($admin_password)) {
	$admin_password = 'admin';
}
?>
				<b>TIMEEFFECT Installation - Step <?php if(isset($step)) echo $step; ?></b><br><br>
				Please enter the appropriate values in the following fields and press 'next &gt;&gt;'<br><br>
				<table cellpadding="0" cellspacing="0" border="0">
<form action="<?php if(isset($PHP_SELF)) echo $PHP_SELF; ?>" method="POST">
<input type="hidden" name="step" value="3">
<input type="hidden" name="db_prefix" value="<?php if(isset($db_prefix)) echo $db_prefix; ?>">
<input type="hidden" name="db_name" value="<?php if(isset($db_name)) echo $db_name; ?>">
<input type="hidden" name="db_host" value="<?php if(isset($db_host)) echo $db_host; ?>">
<input type="hidden" name="db_user" value="<?php if(isset($db_user)) echo $db_user; ?>">
<input type="hidden" name="db_password" value="<?php if(isset($db_password)) echo $db_password; ?>">
					<tr>
						<td colspan="2"><IMG SRC="../images/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td width="500" colspan="2">Select the TIMEEFFECT default interface language.<br>
						<span class="warning">This determines the default interface language. You will be able to select
						a different language at login time:</span></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr>
					<tr>
						<td class="FormFieldName">language:</td>
						<td class="FormField" width="300"><select class="FormField" name="language">
<?php
$dir = opendir('../include/languages');
if(!isset($language)) $language="de";
while($file = readdir($dir)) {
	if(strstr($file, 'inc.php')) {
		$lang = str_replace('.inc.php', '', $file);
		print '<option';
		if($lang == $language) {
		print ' SELECTED';
		}
		print ">$lang\n";
	}
}
?>
						</select></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td colspan="2"><IMG SRC="../images/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td width="500" colspan="2">Enter the TIMEEFFECT currency:</td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td class="FormFieldName" width="200">currency:</td>
						<td class="FormFieldName"><input class="FormField" name="currency" value="<?= ($currency) ?>"> ('$', 'USD' or 'EUR')</td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td colspan="2"><IMG SRC="../images/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td width="500" colspan="2">Enter decimal point:</td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td class="FormFieldName" width="200">decimal point:</td>
						<td class="FormFieldName"><input class="FormField" name="decimal_point" value="<?php if(isset($decimal_point)) echo $decimal_point; ?>"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td colspan="2"><IMG SRC="../images/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td width="500" colspan="2">Enter thousands seperator:</td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td class="FormFieldName" width="200">thousands seperator:</td>
						<td class="FormFieldName"><input class="FormField" name="thousands_seperator" value="<?php if(isset($thousands_seperator)) echo $thousands_seperator; ?>"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td colspan="2"><IMG SRC="../images/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td width="500" colspan="2">Enter session length in seconds.<br>
						<span class="warning">The 'session length' value enables a security feature of TIMEEFFECT.
						Users will be automatically logged off after the numbers of seconds entered for 'session length'.<br>
						<b>If set to '0' automated logoff will be disabled!</b></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td class="FormFieldName" width="200">session length:</td>
						<td class="FormFieldName"><input class="FormField" name="session_length" value="<?php if(isset($session_length)) echo $session_length; ?>"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td colspan="2"><IMG SRC="../images/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td width="500" colspan="2">Enter the user name of the TIMEEFFECT admin user:</td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr>
					<tr>
						<td class="FormFieldName">admin user:</td>
						<td class="FormFieldName"><input class="FormField" name="admin_user" value="<?php if(isset($admin_user)) echo $admin_user; ?>"></td>
					</tr>
					<tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td colspan="2"><IMG SRC="../images/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td width="500" colspan="2">Enter the password of the TIMEEFFECT admin user:</td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr>
					<tr>
						<td class="FormFieldName">admin password:</td>
						<td class="FormFieldName"><input class="FormField" name="admin_password" value="<?php if(isset($admin_password)) echo $admin_password; ?>"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td width="500" colspan="2">Select whether agents are allowd to delete data:</td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr>
					<tr>
						<td class="FormFieldName">allow delete:</td>
						<td class="FormField" width="300"><select class="FormField" name="allow_delete">
							<option Value="1" selected>Yes
							<option value="0">No
						</select></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td colspan="2"><IMG SRC="../images/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></td>
					</tr><tr>
						<td colspan="2"><img src="../images/abstand.gif" height="10" width="1" border="0"></td>
					</tr><tr>
						<td width="500" colspan="2" align="right"><input type="submit" value="next &gt;&gt;"></td>
					</tr>
</form>
				</table>
