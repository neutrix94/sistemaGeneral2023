<?php
	include("../../../../conectMin.php");
	
	
	//extract($_GET);
	//extract($_POST);
	$texto=$_POST['texto'];
//if(){
	$query = "SELECT
			  id_productos,
			  CONCAT(orden_lista,'|',nombre) 
			  FROM ec_productos
			  WHERE (";
//}else if(){

//}
//ampliamos exactitud de búsqueda
	$arr=explode(" ",$texto);
	for($i=0;$i<sizeof($arr);$i++){
		if($arr[$i]!=''){
			if($i>0){
				$query.=" AND ";
			}
			$query.="nombre  LIKE '%".$arr[$i]."%'";
		}
	}
	$query.=")";//cerramos el paréntesis del WHERE

	$result  = mysql_query($query) or die('Prod: '.mysql_error());		  

		while($fila = mysql_fetch_row($result))
		{
			$data[]= array(
							'id_pr'     => $fila[0],
							'nombre'    => $fila[1]
						);
		}

		echo json_encode($data);

?>