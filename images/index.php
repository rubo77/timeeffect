<HTML>
<HEAD>
<TITLE>TIMEEFFECT - Screenshots</TITLE>
<STYLE>
BODY {
	font-family:		Verdana,Arial,sans-serif;
	font-weight:		normal;
	font-style:			normal;
	background-color:	#FFFFFF;
	color:				#000000;
	margin-top:			5;
	margin-left:		0;
	margin-bottom:		0;
	margin-right:		0;
}

</STYLE>
</HEAD>

<BODY leftmargin="0" topmargin="0">
<CENTER>
<H1>Ski-Urlaub - Oberndorf 2004</H1>

<TABLE BORDER=0 cellpadding="3" cellspacing="0">
	<TR>
<?php
	$cnt = 0;
	$DIR = opendir("thumbs");
	while($file = readdir($DIR)) {
		if(($file == '.') ||
		   ($file == '..')) {
			continue;
		}
		$files[$cnt++] = $file;
	}
	for($i = 1; $i <= $cnt; ++$i) {
		?>
		<TD><A style="text-decoration: none; color: #000000;" HREF="file.php?file=<?= $files[$i-1]; ?>"><IMG SRC="<?= 'thumbs/' . $files[$i-1]; ?>" BORDER="0"></A></TD>
		<?php
			if($i && $i % 4 == 0) {
				print "</TR><TR>\n";
			}
	}
?>
	</TR>
</TABLE>

</center>

</body>
</html>
