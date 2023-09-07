<?php

	include("conect.php");
	
	$s="SELECT DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
		$r=mysql_query($s);
		
		$rs=mysql_fetch_row($r);
		
		echo "Hora: ".$rs[0];


?>