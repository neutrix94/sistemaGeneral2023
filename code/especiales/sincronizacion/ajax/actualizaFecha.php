<?php
	if(include('../../../../conect.php')){
		//echo 'yes';
	}else{
		die('no');
	}
	$sql="SELECT ultima_sincronizacion FROM ec_sincronizacion WHERE id_sincronizacion=1";
	$eje=mysql_query($sql);
	if(!$eje){
		die('no');
	}
	$rw=mysql_fetch_row($eje);
	echo $rw[0];
?>