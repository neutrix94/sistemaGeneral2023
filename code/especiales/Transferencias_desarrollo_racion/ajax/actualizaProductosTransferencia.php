<?php
	extract($_POST);
	include('../../../../conect.php');
	$consulta=explode("%",$cons);
	//print_r($consulta);

	for($i=1;$i<=sizeof($consulta)-1;$i++){
		//echo 'consulta: '.$consulta[$i].'<br>';
		$sql=$consulta[$i];
		$ejecuta=mysql_query($sql) or die(mysql_error());//ejecutamos consultas generadas
		if($ejecuta){
			$sql="";
		}else{//detectamos error
			echo 'error:    '.mysql_error();
			return false;//cortamos ciclo
		}
	}
	echo 'ok';//retornamos satisfactorio
?>