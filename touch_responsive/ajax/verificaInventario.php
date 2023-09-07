<?php
	include("../../conectMin.php");
/*implementación de Oscar 13.08.2018*/
	
//si es eliminar temporal
	if(isset($_GET['flag'])&&$_GET['flag']=='eliminar_tmp_exhib'){
		$id=$_GET['id_reg'];
		//$sql="DELETE FROM ec_temporal_exhibicion WHERE id_temporal_exhibicion=$id";
		$sql="UPDATE ec_temporal_exhibicion SET es_valido=0 WHERE id_temporal_exhibicion=$id";
		
		$sql=mysql_query($sql)or die("Error al marcar como inválido el registro de exhibición temporal!!!\n\n".$sql."\n\n".mysql_error());
		die('ok|');
	}

//si es insertar el temporal
	if(isset($_GET['clave'])){//si existe la clave (para verificar contraseña de encargado)
	/*Implementación Oscar 2021*/
		$sql = "SELECT solicitar_password_inventario_insuficiente FROM ec_configuracion_sucursal WHERE id_sucursal = '{$user_sucursal}'";
		$eje = mysql_query( $sql ) or die( "Error al consultar configuración de la sucursal!!!" );
		$row = mysql_fetch_row( $eje );
		if( $row[0] == 0 ){
			die('ok|ok|sinTemporal');
		}
	/*fin de cambio Oscar 2021*/
	//verificamos el password del usuario
		$clave=md5($_GET['clave']);
		$sql="SELECT COUNT(u.id_usuario) 
			FROM sys_users u 
			LEFT JOIN sys_sucursales suc ON u.id_usuario=suc.id_encargado
            WHERE suc.id_sucursal=$user_sucursal AND u.contrasena='$clave'";
           // die($sql);
        $eje=mysql_query($sql)or die("Error al consultar verificación de usuario!!!\n\n".$sql."\n\n".mysql_error());
        $r=mysql_fetch_row($eje);
        if($r[0]==0){
        //si no se encuentran coincidencias
        	die('ok|La contraseña es incorrecta, verifique y vuelva a intentar!!!');
        }
    //insertamos el producto tomado de exhibicón en la tabla temporal
        $cantidad=$_GET['cant'];
        $id_producto=$_GET['id_pr'];
       	if($cantidad<1){
       	//si no hay productos para insertar en temporal, entonces, solo regresamos respuesta de contraseña correcta
       		die('ok|ok|sinTemporal');
       	}else{
       		$sql="INSERT INTO ec_temporal_exhibicion VALUES(NULL,$id_producto,$cantidad,0,0,$user_sucursal,$user_id,'0000-00-00 00:00:00',now(),1)";
       		//die($sql);
       		$eje=mysql_query($sql)or die("Error al insertar el producto de exhibición en temporal!!!\n\n".$sql."\n\n".mysql_error());
       		die('ok|ok|ok|'.mysql_insert_id());
       	}
	}
/*fin de cambio Oscar 13.08.2018*/

	$principal=$_GET['alm_princ'];
	$exhibicion=$_GET['alm_exh'];

	extract($_GET);
	
	$id_prod=$_GET['id_pr'];
	//alm_princ="+alm1+"&alm_exh
	if($principal==''){$principal=0;}
	if($exhibicion==''){$exhibicion=0;}

	$sql="SELECT 
			s.verificar_inventario,
			s.verificar_inventario_externo,
			sp.es_externo,
			cs.solicitar_password_inventario_insuficiente
		FROM sys_sucursales s 
		LEFT JOIN sys_sucursales_producto sp ON s.id_sucursal=sp.id_sucursal
		LEFT JOIN ec_configuracion_sucursal cs ON cs.id_sucursal = s.id_sucursal
		WHERE sp.id_producto=$id_prod AND s.id_sucursal=$user_sucursal";
	$eje=mysql_query($sql)or die("Error al consultar funciones de sucursal respecto al inventario!!!\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
	if(($r[0]==0&&$r[2]==0)||($r[1]==0&&$r[2]==1)){
	//si la sucursal está configurada para no validar;
		die('ok');//regresamos repuesta ok
	}
	$solicitar_password_inventario = $r[3];
	//die($id_pr.'|'.$cant);
	//$sql="SELECT existencias FROM ec_inventario_sincronizacion WHERE id_producto=$id_pr AND id_sucursal=$user_sucursal";
	$sql="SELECT 
			SUM(IF(md.id_movimiento IS NULL OR ma.id_almacen!=(SELECT id_almacen FROM ec_almacen WHERE id_sucursal=$user_sucursal AND es_almacen=1),0,md.cantidad*tm.afecta)) AS existencia,
			CONCAT(IF($cant=1,'Se pidió ','Se pidieron '),$cant,' piezas de ',p.nombre,'<br>La cantidad de venta es mayor al inventario del producto\nVerifique existencia en almacen!!!')
			FROM ec_productos p
			LEFT JOIN ec_movimiento_detalle md ON p.id_productos = md.id_producto
			LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
			LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
			WHERE p.id_productos=$id_prod
			AND ma.id_sucursal=$user_sucursal";

	$eje=mysql_query($sql);
	if(!$eje){
		die("Error al checar existencias de producto\n".$sql."\n".mysql_error());
	}
	$rw=mysql_fetch_row($eje);
	if($cant>$rw[0]){
/*Implementación Oscar 14.11.2018*/
	$msg=str_replace("<br>La cantidad de venta es mayor al inventario del producto\nVerifique existencia en almacen!!!",'<br>',$rw[1]);
	$sql="INSERT INTO ec_productos_sin_inventario VALUES(null,$id_prod,$user_sucursal,$user_id,now(),'".$msg." existencias: ".$rw[0]."',1)";
	$ejecuta=mysql_query($sql)or die("Error al insertar el registro de falta de inventario en la sucursal!!!\n\n".mysql_error());
/*Fin de cambio Oscar 14.11.2018*/
?>
	<p>
    	<button class="bot_emerge_1 btn_close_emergente" onclick="document.getElementById('emergente_1').style.display='none';">X</button>
  	</p>
	<div style="position: fixed;width: 100%;height: 100%;background:rgba(0,0,0,.5);border-radius: 15px;z-index: 2000;top:0;display:none;" id="sub-emergente-existencias">
		<div style="position: absolute;width: 80%;height: 80%;background:white;color:black;border-radius: 15px;top:10%;left: 10%;" id="contenido-sub-emergente-existencias">
		</div>
	</div>
	<br><br>
	<?php echo $rw[1];?>
	<br>
		<!--p style="position: absolute; width:10%;height:8%;top:0;border-radius: 50%;border: 1px solid red;font-size: 70%!important;background: green;" 
			onclick="carga_existencias(<?php echo $id_prod;?>);">
			<img src="img/inventario.png" width="50%"><br>
			Existencias<br>Almacenes
		</p-->
	<br>
	<table class="tabla_emerge1" style="width : 95% !important;">
		<tr><td align="left" colspan="2">Capture la cantidad de productos tomada de cada almacén</td></tr>
		<tr>
			<td width="50%" align="center">Bodega<input type="text" id="cant_alm_1" value="<?php echo $principal;?>" style="width : 100%;"></td>
			<td width="50%" align="center">Exhibición<input type="text" id="cant_alm_2" value="<?php echo $exhibicion;?>" style="width : 100%;"></td>
		</tr>
	</table>
<?php
	if( $solicitar_password_inventario == 1 ){ 
?>
	<p align="center">
		Contraseña de encargado:
	</p>
	<p>
	<!--Modificación Oscar 10.11.2018 para convertir cuadro de texto en cuadro de contraseña-->
		<input id="pss_encargado_1" type="text" onkeydown="cambiar(this,event,'pss_encargado');" placeholder="*** password ***" style="padding:12px;width:250px;">
		<input type="hidden" value="" id="pss_encargado">
	<!--Fin de cambio-->
	</p>
	<p>
<?php
	}
?>
		<input type="button" value="Aceptar" class="ent_txt_emerg" onclick="insertaMovExhibTemporal(<?php echo $id_pr;?>);">
	</p>
	<style type="text/css">
		.ent_txt_emerg{
			padding: 10px;border-radius: 5px;
		}
		.tabla_emerge1{
			position:relative;width:80%;left:0;background: transparent;border:0;border-collapse:collapse;
		}
	</style>
	<script type="text/javascript">
		function carga_existencias(id_prod){
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'ajax/consulta_inventarios.php',
				data:{id_producto:id_prod},
				success:function (dat){
					$("#contenido-sub-emergente-existencias").html(dat);//cargamos la respuesta
					$("#sub-emergente-existencias").css("display","block");//hacemos visible la sub-emergente 
				}
			});
		}

		function cierra_sub_emergente_existencias(){
			$("#contenido-sub-emergente-existencias").html("");//limpiamos contenido de subemergente
			$("#sub-emergente-existencias").css("display","none");//ocultamos la sub-emergente 

		}
	</script>
<?php
	die('');	
	}
	die('ok');
?>