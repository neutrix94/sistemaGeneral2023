<?php
	//extract($_POST);//recibimos variables
    $producto=$_POST['producto'];
    $noms=explode(" ", $producto);//separamos las palabras en una arreglo para compararlas con mas exactitud en la consulta
	$sucursal=$_POST['suc'];

	if($_POST['stock']==1){
		$tipo_stock=" AND sp.stock_bajo=1";
	}else{
		$tipo_stock="";
	}

	require('../../../../../conect.php');//incluimos clase de conexion
	//generamos consulta
		$sql="SELECT p.nombre, p.id_productos
				FROM ec_productos p
				JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto 
				JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal
				WHERE (p.orden_lista='$producto' or p.id_productos='$producto' or p.codigo_barras_1='$producto'";
		//ampliamos coincidencias
		for($i=0;$i<sizeof($noms);$i++){
        	if($i==0){
        		$operador=' OR(';
        	}else{
        		$operador=' AND ';
        	}
        	$sql.=$operador."p.nombre LIKE '%".$noms[$i]."%'";//agregamos condicion
        	//echo 'Ssql:'.$sql;
        }
		$sql.=")) AND p.habilitado=1 AND sp.id_sucursal IN(".$sucursal.") AND sp.estado_suc=1 AND sp.es_externo=0";

/*Implementación Oscar 27.02.2019 para no mostrar resultados de prouctos maquilados ni que muestren paleta*/
		$sql.=" AND p.es_maquilado=0 AND p.muestra_paleta=0".$tipo_stock;
/*Fin de cambio Oscar 27.02.2019*/
		
		$ejecuta=mysql_query($sql) or die(mysql_error());//ejecutamos consulta
		$num=mysql_num_rows($ejecuta);//contamos resultados
//die($sql);
		if($num<1){//en caso de no haber resultados
			echo'<font color="red"><center>no hay conincidencias</center></font>';//regeresamos mensaje de que no hay coincidencias
		}
	//de lo contrario;
	echo '<table width="100%" id="resulta">';//declaramos tabla de resultados
	echo'<tr><td></td></tr>';
			
			$contador=0;//declaramos contador en cero
			while($row=mysql_fetch_row($ejecuta)){//mientras se encuantren resultados de la consulta;
			$contador++;//incrementamos contador
			//generamos opcion
				echo '<tr class="opcion" onclick="validaProducto('.$row[1].');">
				<td width=100%><span style="background-color:yellow;">
				<div id="r_'.$contador.'" tabindex="'.$contador.'" onkeyup="eje(event,'.$contador.','.$row[1].');">'
				.$row[0].'</div></td></tr>
				<input type="hidden" value="'.$row[1].'" id="id_'.$contador.'">';
			}
	echo '</table>';//cerramos tabla
?>