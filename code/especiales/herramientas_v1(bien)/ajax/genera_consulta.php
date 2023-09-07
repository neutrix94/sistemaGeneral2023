<?php
	include('../../../../conectMin.php');
//descarga de csv
	if(isset($_POST['fl']) && $_POST['fl']==1){
			//recibimos datos
		$info=$_POST['datos'];
	//creamos el nombre del archivo
		$nombre="exportacion_tabla.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($info));
		//echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";//cerramos ventana
		die('');//<script>window.close();</script>
	}
	$id_herr=$_POST['id'];
	$filtros=explode("°",$_POST['arr']);
	//sacamos la consulta
		$sql="SELECT consulta FROM sys_herramientas WHERE id_herramienta='$id_herr'";
		$eje=mysql_query($sql)or die("Error al consultar la base de la herramienta!!!<br>".mysql_error()."<br>".$sql);
		$r=mysql_fetch_row($eje);
		$sql=$r[0];
	//sacamos los filtros
		for($i=0;$i<sizeof($filtros);$i++){
			if($filtros[$i]!='' && $filtros[$i]!=null){
				$campos_filtro=explode("~", $filtros[$i]);
				if($id_herr==1 && ($campos_filtro[1]=='$FECHA_1' || $campos_filtro[1]=='$FECHA_2') ){//si es verificacion de pedidos
					$sql_sub="SELECT DATE_FORMAT('$campos_filtro[2]','%Y')";
					//echo $sql_sub;
					$eje_sub=mysql_query($sql_sub)or die("Error al formatear la fecha!!!!<br>".$sql_sub);
					$r_sub=mysql_fetch_row($eje_sub);
					$campos_filtro[2]=$r_sub[0];
				}
			//reemplazamos filtros
				if($campos_filtro[2]==0){
					$campos_filtro[0]='';
					$campos_filtro[2]='';
				}
				$sql=str_replace($campos_filtro[1], $campos_filtro[0]."".$campos_filtro[2], $sql);
			}
		}
		echo 'ok|'.$sql.'|';
	//ejecutamos la consulta
		$eje=mysql_query($sql)or die("Error al ejecutar la consulta!!!<br>".mysql_error()."<br>".$sql);
		//echo mysql_field_name($eje,0);
		$field = mysql_num_fields($eje);
    	$names; 
        for ( $i = 0; $i < $field; $i++ ) { 
            $names[$i] = mysql_field_name($eje, $i);
        }
        //print_r($names);
		//echo 'ok|select|';
		echo '<table class="result" id="grid_resultado" width="100%">';
		$c=0;
		while($r=mysql_fetch_row($eje)){
		if($c==0){
			echo '<tr>';
			for($i=0;$i<sizeof($names);$i++){
				echo '<th>'.$names[$i].'</th>';
			}
			echo '</tr>';
		}
			echo '<tr>';
			for($i=0;$i<sizeof($r);$i++){
				echo '<td>'.$r[$i].'</td>';
			}
			echo '</tr>';
			$c++;
		}
		echo '</table>';
		/*<html>
	<link rel="stylesheet" href="css/estilos.css">
</html>*/
?>

