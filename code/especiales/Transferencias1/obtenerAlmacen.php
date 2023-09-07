<?php
	include('../../../conectMin.php');
	extract($_POST);
	$sucursal=$_POST['id_sucursal'];
	$accion=$_POST['a'];
//generamos combo de sucursal destino
	
	if($accion==3){//en caso de que accion sea igual a 3
		echo'<select style="padding:10px; border-radius:10px;" id="destino" onclick="prueba(2);">';//declaramos select
        echo'<option value="0">---------</option>';
        	$query="SELECT id_sucursal,nombre from sys_sucursales";//generamos consulta
			$extraer=mysql_query($query) or die(mysql_error());//ejecutamos consulta
			if($extraer){//si la consulta se genero;
				while($row=mysql_fetch_row($extraer)){//mientras se encuentren resultados;
					echo '<option value="'.$row[0].'"><font color="#000099">'.$row[1].'</font></option>';//(generamos opciones)
				}
		echo'</select>';//cerramos select
			}else{//si no se realizó la consulta
				echo 'algo anda mal en la consulta';//retornamos error
			}
	}
//generamos combos de almacenes
	else if($accion==1){
		echo'<select style="padding:10px; border-radius:10px;" id="id_almacen_origen";">';
		}else if($accion==2){
			echo'<select style="padding:10px; border-radius:10px;" id="id_almacen_destino">';
			}
	$sql="SELECT id_almacen, nombre FROM ec_almacen WHERE id_sucursal ='$sucursal' order by prioridad";
	
	$dato=mysql_query($sql);
	if($dato){
	while($row=mysql_fetch_assoc($dato)){
?>
	<option style="width:10px; font-size:10px;" value="<?php echo $row['id_almacen'];?>"><?php echo $row['nombre'];?></option>
<?php
	}
	echo '</select></td>';
	}
?>