<?php
	//consultamos registros modificados localmente
	$sql="SELECT id_producto,id_sucursal,existencias
		  		 FROM ec_inventario_sincronizacion 
		  		 WHERE /*ultima_modificacion>'$ultimaSinc'
		  		 AND */id_sucursal=$suc";
    $eje=mysql_query($sql,$local);//ejecutamos localmente
    if(!$eje){
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		die("Error al sincronizar sucursales\n".mysql_error($local)."\n".$sql);//mandamos error generado localmente
    }
    $num=mysql_num_rows($eje);
    $c=0;//inicamos contador
    if($num<1){//si no hay resultados;
    }else{//de lo contrario
    	while($row=mysql_fetch_row($eje)){
    	//creamos consulta para actualizar en linea
    		$sql2="UPDATE ec_inventario_sincronizacion SET existencias=$row[2] WHERE id_sucursal=$suc AND id_producto=$row[0]";
    		$ejecuta2=mysql_query($sql2,$linea);//ejecutamos en linea
    		if(!$ejecuta2){
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				die("Error al subir inventarios!!!\n".mysql_error($linea)."\n".$sql2);//mandamos error generado en linea
    		}
    		$c++;//aumentamos contador
    	}//fin de while
    }//fin de else


//bajamos inventario
    if($suc==1){
    	$WHERE="WHERE id_sucursal!=".$suc." AND fecha_sincronizacion>'$ultimaSinc'";
    }else{
    	$WHERE="WHERE id_sucursal=1";
    }
	$sql="SELECT id_producto,id_sucursal,existencias
		  		 FROM ec_inventario_sincronizacion ".$WHERE;
    $eje=mysql_query($sql,$linea);//ejecutamos en linea
    if(!$eje){
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		die("Error al consultar inventario en linea\n".mysql_error($linea)."\n".$sql);//mandamos error generado en linea
	}
    $nums=mysql_num_rows($eje);
//die($sqls);
    $cs=0;
    if($nums>0){
    	//echo 'sube';
    	while($rows=mysql_fetch_row($eje)){
    		$sql2="UPDATE ec_inventario_sincronizacion 
   						SET existencias=$rows[2] 
    						WHERE id_sucursal=$rows[1] AND id_producto=$rows[0]";
    		$actInv=mysql_query($sql2,$local);//ejecutamos loaclmente
    		if(!$actInv){
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				die("Error al insertar inventario localmente\n".mysql_error($local)."\n".$sql2);//mandamos error generado localmente	
    		}
    		$cs++;//incrementa contador
    	}
//    echo 'registros actualizados en bdLocal: '.$cs;
    }

    echo '<tr style="background:rgba(0,0,225,.5);"><td colspan="4" align="center"><font color="white">Inventarios</font></td></tr>'.
    '<tr><td>Inventarios por subir:</td><td>'.$num.'</td>';
    echo '<td>Inventarios subidos:</td><td>'.$c.'</td></tr>';
    echo '<tr><td>Inventarios por bajar:</td><td>'.$nums.'</td>';
    echo '<td>Inventarios bajados:</td><td>'.$cs.'</td></tr>';

?>