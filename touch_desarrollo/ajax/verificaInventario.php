<?php
	include("../../conectMin.php");
/*implementación de Oscar 13.08.2018*/
	$is_exhibition = 0;
	$info_btn = "";
	if( isset($_GET['is_exhibition']) && $_GET['is_exhibition'] == 1 ){
		$is_exhibition = 1;
		$info_btn = "<button class=\"btn btn-info\" onclick=\"getExhibition_info();\">
			<i class=\"icon-help-circled-1\">Más información</i>
		</button>";
	}//die( 'here : ' . $_GET['is_exhibition'] );
	
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
			//die('ok|ok|sinTemporal');
		}else{
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
		}
	/*fin de cambio Oscar 2021*/
	
    //insertamos el producto tomado de exhibicón en la tabla temporal
        $cantidad=$_GET['cant'];
        $id_producto=$_GET['id_pr'];
       	if($cantidad <= 0){
       	//si no hay productos para insertar en temporal, entonces, solo regresamos respuesta de contraseña correcta
       		die('ok|ok|sinTemporal|{$cantidad}');
       	}else{
       		$sql="INSERT INTO ec_temporal_exhibicion ( id_temporal_exhibicion, id_producto, cantidad, piezas_exhibidas, 
       			piezas_ya_no_se_exhiben, id_sucursal, id_usuario, fecha_modificacion, fecha_alta, es_valido ) 
				VALUES( NULL, {$id_producto}, {$cantidad}, 0, 0, {$user_sucursal}, 
       			{$user_id}, '0000-00-00 00:00:00', NOW(), '1' )";
       		//die($sql);
       		$eje=mysql_query($sql)or die("Error al insertar el producto de exhibición en temporal!!!\n\n".$sql."\n\n".mysql_error());
       		$exhibition_id = mysql_insert_id();
       	//inserta el detalle a nivel proveedor producto
       		$product_providers_details = explode( ',', $_GET['pp_detail'] );
       		for( $i = 0; $i < sizeof( $product_providers_details ); $i ++ ){
       			$product_provider_id = $product_providers_details[$i];
       			$i ++;
       			$quantity = $product_providers_details[$i];
       			$sql = "INSERT INTO ec_temporal_exhibicion_proveedor_producto ( id_temporal_exhibicion, 
       				id_producto, id_proveedor_producto, cantidad ) VALUES ( {$exhibition_id}, {$id_producto}, 
       				{$product_provider_id}, {$quantity} )";
				$eje=mysql_query($sql)or die("Error al insertar el detalle de proveedor producto de exhibición en temporal!!!\n\n".$sql."\n\n".mysql_error());
       		}
       		die( "ok|ok|ok|{$exhibition_id}" );
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
			cs.solicitar_password_inventario_insuficiente,
			( SELECT id_almacen FROM ec_almacen WHERE id_sucursal = {$user_sucursal} AND es_almacen = 1 LIMIT 1 ) 
		FROM sys_sucursales s 
		LEFT JOIN sys_sucursales_producto sp ON s.id_sucursal=sp.id_sucursal
		LEFT JOIN ec_configuracion_sucursal cs ON cs.id_sucursal = s.id_sucursal 
		WHERE sp.id_producto=$id_prod AND s.id_sucursal=$user_sucursal";
//die( $sql );
	$eje=mysql_query($sql)or die("Error al consultar funciones de sucursal respecto al inventario!!!\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
	if(($r[0]==0&&$r[2]==0)||($r[1]==0&&$r[2]==1)){
	//si la sucursal está configurada para no validar;
		die('ok');//regresamos repuesta ok
	}
	
	$solicitar_password_inventario = $r[3];
	$aux_tmp = "";
	$original_name = "";
	$principal_warehouse = $r[4];
	$maquile_factor = 1;//implementacion Oscar 2023 para error en maquilados
/*implementacion Oscar 2023 para que se tome el inventario en relacion al producto maquilado*/
//verifica si el producto es maquilado
	$sql = "SELECT 
				pd.id_producto_ordigen AS product_id,
				p.nombre AS maquiled_name,
				pd.cantidad AS maquile_quantity/*implementacion Oscar 2023 para error en maquilados*/
			FROM ec_productos_detalle pd
			LEFT JOIN ec_productos p
			ON p.id_productos = pd.id_producto
			WHERE pd.id_producto = {$id_prod}";
	//die( $sql );
	$stm = mysql_query( $sql ) or die( "Error al consultar si el producto es maquilado : " . mysql_error() );
	if( mysql_num_rows( $stm ) > 0 ){
		$maquile_row = mysql_fetch_assoc( $stm );
		$aux_tmp = $maquile_row['product_id'];
		$original_name = $maquile_row['maquiled_name'];
		$maquile_factor = $maquile_row['maquile_quantity'];//implementacion Oscar 2023 para error en maquilados
	}else{
		$aux_tmp = $id_prod;
	}
/*fin de cambio Oscar 2023
	$sql="SELECT 
			SUM(IF(md.id_movimiento IS NULL OR ma.id_almacen!=(SELECT id_almacen FROM ec_almacen WHERE id_sucursal=$user_sucursal AND es_almacen=1),0,md.cantidad*tm.afecta)) AS existencia,
			CONCAT(IF($cant=1,'Se pidió ','Se pidieron '),$cant,' piezas de ',
				IF( '{$original_name}' != '', '{$original_name}', p.nombre ),
				'<br>La cantidad de venta es mayor al inventario del producto\nVerifique existencia en almacen!!!'),
			p.es_maquilado AS is_maquiled/*agregado por Oscar 2023 para omitir productos maquilados
			FROM ec_productos p
			LEFT JOIN ec_movimiento_detalle md ON p.id_productos = md.id_producto
			LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
			LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
			WHERE p.id_productos = {$aux_tmp}/*$id_prod
			AND ma.id_sucursal=$user_sucursal";*/
//implementacion Oscar 2023 para que el inventario sea tomado de la tabla de inventario acumulado
	$sql="SELECT 
			( ap.inventario / {$maquile_factor} ) AS existencia,/*implementacion Oscar 2023 para error en maquilados*/
			CONCAT(IF($cant=1,'Se pidió ','Se pidieron '),$cant,' piezas de ',
				IF( '{$original_name}' != '', '{$original_name}', p.nombre ),
				'<br>La cantidad de venta es mayor al inventario del producto\nVerifica existencia en almacen!!!'),
			p.es_maquilado AS is_maquiled/*agregado por Oscar 2023 para omitir productos maquilados*/
			FROM ec_productos p
			LEFT JOIN ec_almacen_producto ap
			ON ap.id_producto = p.id_productos
			AND ap.id_almacen = {$principal_warehouse}
			WHERE p.id_productos = {$aux_tmp}/*$id_prod*/
			AND ap.id_almacen = {$principal_warehouse}";
//die( $sql );

	$eje=mysql_query($sql);
	if(!$eje){
		die("Error al checar existencias de producto\n".$sql."\n".mysql_error());
	}
	$rw=mysql_fetch_row($eje);
/*implementacion Oscar 2023 para redondear cantidades a validar*/
	$cant = round( $cant, 2 );
	$rw[0] = round( $rw[0], 2 );
/*fin de cambio Oscar 2023*/
	if( ( $cant>$rw[0] && $rw[2] == 0 ) || $is_exhibition == 1 ){/* ( && $rw[2] == 0 ) agregado por Oscar 2023 para omitir productos maquilados*/
//die( "response : {$cant}>{$rw[0]} , {$rw[2]}" );
/*Implementación Oscar 14.11.2018*/
	$msg=str_replace("<br>La cantidad de venta es mayor al inventario del producto\nVerifique existencia en almacen!!!",'<br>',$rw[1]);
	$sql="INSERT INTO ec_productos_sin_inventario VALUES(null,$id_prod,$user_sucursal,$user_id,now(),'".$msg." existencias: ".$rw[0]."',1)";
	$ejecuta=mysql_query($sql)or die("Error al insertar el registro de falta de inventario en la sucursal!!!\n\n".mysql_error());
	$second_onclick= "";
	if( $is_exhibition == 1 ){
		$rw[1] = "<br>Has seleccionado la opcion de tomar productos de exhibición, captura el total de productos que tomaras de exhibición y bodega : ";
		$second_onclick = "reset_exhibition();";
	}
/*Fin de cambio Oscar 14.11.2018*/
?>	
	<br><br><br>
	<div class="row"><!-- style="background-color : white;" -->
		
		<div class="col-2 text-center">
			<button
				type="button"
				class="btn btn-warning"
				onclick="carga_existencias(<?php echo $id_prod;?>);"
			>
				<i class="icon-warehouse">Ver existencias</i>
			</button>
		</div>
	    <div class="col-8 text-center">
	    <?php
	    	echo $info_btn;
	    ?>
	    </div>
	    <div class="col-2 text-center">
<?php 
	if( 1==2 ){
?>
	    	<button 
	    		class="btn btn-danger" 
	    		onclick="document.getElementById('emergente_1').style.display='none';">
	    		X
	    	</button>
<?php 
	} 

?>
	    </div>
  	</div>
	<div style="position: fixed;width: 100%;height: 100%;background:rgba(0,0,0,.5);border-radius: 15px;z-index: 2000;top:0;display:none;" id="sub-emergente-existencias">
		<div style="position: absolute;width: 80%;height: 80%;background:white;color:black;border-radius: 15px;top:10%;left: 10%;" id="contenido-sub-emergente-existencias">
		</div>
	</div>
	<br>
	<?php 
		echo $rw[1];
	?>
	<br>
	<div class="row" style="color : black;"><!-- tabla_emerge1 -->
		<h4 style="color : white;">Captura la cantidad de productos tomada de cada almacén</h4>
		<div class="row">
			<h4 class="text-center" style="color : white;">Exhibición :</h4> 
			<div class="col-8">
				<div class="input-group">
					<input 
						class="form-control"
						id="exhibition_seeker"
						placeholder="Escanear/buscar"
						onkeyup="seek_exhibition_products( <?php echo $id_prod;?>, event );"
					>
					<button
						type="button"
						class="btn btn-warning"
						onclick="seek_exhibition_products( <?php echo $id_prod;?>, 'intro' );"
					>
						<i class="icon-barcode"></i>
					</button>
				</div>
				<div id="exhibition_seeker_response"
					style="position:relative;z-index:2; top : 0; left: 0; width : 150%; 
					max-height : 100%; background-color : white; overflow : auto;"
				>
				</div>
			</div>
			<div class="col-4">
				<input 
					type="text" 
					id="cant_alm_2" 
					value="<?php echo $principal;?>" 
					class="form-control text-end"
					disabled
				>
			</div>
		</div>
		<div class="col-12 center">
			<br><br>
			<h4 class="text-center" style="color : white;">Bodega :</h4>
			<div class="row">
				<div class="col-4"></div>
				<div class="col-4">
					<input 
						type="text" 
						id="cant_alm_1" 
						value="<?php echo $exhibicion;?>" 
						class="form-control text-end">
				</div>
			</div>
		</div>
	</div>
<?php
	if( $solicitar_password_inventario == 1 ){ 
?>
	<p align="center">
		Contraseña de encargado:
	</p>
	<div class="row">
		<div class="col-2"></div>
		<div class="col-8">
			<input 
				id="pss_encargado" 
				type="password"
				placeholder="*** password ***"
				class="form-control"
			>
		</div>
	</div><br>
<?php
	}
?>
	<div class="row">
		<div class="col-2"></div>
		<div class="col-8">
			<br><br>
			<button 
				type="button" 
				class="btn btn-success form-control" 
				onclick="insertaMovExhibTemporal(<?php echo $id_pr;?>);<?php echo $second_onclick;?>">
				<i class="icon-ok-circle">Aceptar</i>
			</button>
		</div>
		<div class="col-4"></div>
	<!--Modificación Oscar 10.11.2018 para convertir cuadro de texto en cuadro de contraseña-->
		<!--input type="hidden" value="" id="pss_encargado" onkeydown="cambiar(this,event,'pss_encargado');"-->
	<!--Fin de cambio-->
	</div>

	<style type="text/css">
		.ent_txt_emerg{
			padding: 10px;border-radius: 5px;
		}
		.tabla_emerge1{
			position:relative;width:80%;left:0;background: transparent;border:0;border-collapse:collapse;
		}
		.group_card{
			box-shadow : 1px 1px 5px rgba( 0,0,0,.5 );
			box-shadow: 1px 1px 10px rgba( 0,0,0,.5 ) !important;
			padding : 2px;
			margin : 0;
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

		function seek_exhibition_products( product_id, e ){
			var key = e.keyCode;
			if( key != 13 && e != 'intro' ){
				return false;
			}
			var txt = $( '#exhibition_seeker' ).val();
			if( txt == '' ){
				alert( "El buscador no puede ir vacio!" );
				$( '#exhibition_seeker' ).focus();
				return false;
			}
			var url = "ajax/getExhibitionProducts.php?product_id=" + product_id + "&txt=" + txt;
			url += "&p_p_used=" + json_to_insert;
			//alert( url );
			var resp = ajaxR( url ).split( '|' );
			if( resp[0] == 'ok' ){
				setTemporalMovement( resp[1], resp[2], resp[3] );
			}else{
				if( resp[0] == 'seeker' ){
					$( '#exhibition_seeker_response' ).html( resp[1].trim() );
					$( '#exhibition_seeker_response' ).css( 'display', 'block' );
				}else{
					$( '#exhibition_seeker_response' ).html( '' );
					$( '#exhibition_seeker_response' ).css( 'display', 'none' );
					alert( resp );
					$( '#exhibition_seeker' ).select();
				}
			}
		}

		function setProductByName( product_provider_id ){

		}

		function setTemporalMovement_emergent( product_provider_id, inventory ){
			var quantity = $( '#maquiled_quantity' ).val();
			setTemporalMovement( product_provider_id, inventory, quantity );
				$( '#contenido-sub-emergente-existencias' ).html( '' );
				$( '#sub-emergente-existencias' ).css( 'display', 'none' );
		}

		function setTemporalMovement( product_provider_id, inventory, quantity, is_maquiled = 0 ){
			if( quantity > inventory ){
				alert( "La cantidad no puede ser mayor al inventario!" );
				return false;
			}
			if( is_maquiled == 1 ){
				var content = `<div class="row text-center">
					<h2 class="text-center" style="font-size : 200% !important;"><br><br><br>Ingresa el numero de piezas</h2>
					<br>
					<div class="col-2"></div>
					<div class="col-8 text-center">
						<br>
						<input type="number" id="maquiled_quantity" class="form-control">
						<br>
						<button
							type="button"
							class="btn btn-success form-control"
							onclick="setTemporalMovement_emergent( ${product_provider_id}, ${inventory}, ${quantity} )"
						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
						<br><br>
						<button
							type="button"
							class="btn btn-danger form-control"
							onclick="cierra_sub_emergente_existencias();"
						>
							<i class="icon-cancel-circled">Cancelar</i>
						</button>
					</div>
				</div>`;
				$( '#contenido-sub-emergente-existencias' ).html( content );
				$( '#sub-emergente-existencias' ).css( 'display', 'block' );
				$( '#maquiled_quantity' ).focus();
				return false;
			}
			quantity = parseInt( quantity );
			var aux = parseInt( $( '#cant_alm_2' ).val() );
		//verifica que la cantidad no sea mayor al inventario
			/*for ( var i = 0; i < json_to_insert.length; i++ ) {
				if( json_to_insert[i][0] == product_provider_id ){
					var comparation = parseInt( inventory ) - (parseInt( json_to_insert[i][1] ) + parseInt( quantity )); 
					if( comparation < 0 ){
						alert( "Ya no hay mas inventario para este producto en exhibicion, si vas a vender mas captura el restante en el campo de Bodega!" );
						$( '#exhibition_seeker_response' ).html( '' );
						$( '#exhibition_seeker_response' ).css( 'display', 'none' );
						return false;
					}
				}
			}*/
			//var array_tmp = new Array();
			//array_tmp.push( product_provider_id );
			//array_tmp.push( quantity );
			//json_to_insert.push( array_tmp );
			product_provider_exists = false;
			for( var i = 0; i < json_to_insert.length; i ++ ){
			//si encuentra el proveedor producto
				if( json_to_insert[i][0] == product_provider_id ){
					json_to_insert[i][1] = parseInt( json_to_insert[i][1] ) + parseInt( quantity );
					product_provider_exists = true;
				}
			}
		//si no encuentra el proveedor producto
			if( ! product_provider_exists ){
				var array_tmp = new Array();
				array_tmp.push( product_provider_id );
				array_tmp.push( quantity );
				json_to_insert.push( array_tmp );
			}
			$( '#cant_alm_2' ).val( parseInt( quantity + aux ) );
			$( '#exhibition_seeker' ).val( '' );
			$( '#exhibition_seeker_response' ).html( '' );
			$( '#exhibition_seeker_response' ).css( 'display', 'none' );
		}

		function getExhibition_info(){
			var content = `<div style="font-size : 130% !important;">
				<br><br>
				<h2 class="text-danger">IMPORTANTE</h2>
				<ul>
					<p>1- Si ya habias capturado algo de este producto capturalo nuevamente entre exhibicion y bodega</p>
					<p>2- Escanea los productos tomados de exhibicion</p>
					<p>3- Anota en el campo de bodega los productos tomados de bodega</p>
				</ul>
				<div class="row text-center">
					<div class="col-3"></div>
					<div class="col-6">
						<br><br>
						<button
							class="btn btn-success form-control"
							onclick="cierra_sub_emergente_existencias();"
						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
					</div>
				</div>
			</div>`;
			$("#contenido-sub-emergente-existencias").html( content );//cargamos la respuesta
			$("#sub-emergente-existencias").css("display","block");//hacemos visible la sub-emergente 
		}
	</script>
<?php
	die('');	
	}
	die('ok');
?>
