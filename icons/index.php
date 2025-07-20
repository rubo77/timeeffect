<?php
	$num_cols = 4;
?>
<HTML>
<HEAD>
<TITLE>Image-Browser</TITLE>
</HEAD>

<BODY BGCOLOR="CCCCCC">
<TABLE CELLPADDING="10" CELLSPACING="0" BORDER="1">
	<TR>
<?php
	if(!($DIR = opendir("./")))
		exit("Konnte Verzeichnis nicht öffnen");

	$FILES = array();

	while ($file = readdir($DIR)) {
		if(($type= preg_match("/.gif$/i", $file)) ||
		   ($type= preg_match("/.jpg$/i", $file)) ||
		   ($type= preg_match("/.jpeg$/i", $file)) ||
		   ($type= preg_match("/.png$/i", $file))) {
		   		$FILES[] = $file;
		}
	}
	closedir($DIR);

	sort($FILES);
	$count = count($FILES);
	$j = 0;
	for ($i=0;$i < $count; $i++) {
		$file = $FILES[$i];
		if($j == $num_cols) {?>
	</TR><TR>	
<?php
			$j = 0;
		}
		++$j;
?>
		<TD VALIGN="bottom" ALIGN="center"><FONT FACE="Verdana,Arial,Helvetica" SIZE="-1"><IMG SRC="<?php print $file; ?>"><br><?php print $file; ?></FONT></TD>
<?php
	}

?>

	</TR>
</TABLE>

</BODY>
</HTML>