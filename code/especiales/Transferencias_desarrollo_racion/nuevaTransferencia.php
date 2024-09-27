<?php
//1. Incluye archivo %/conectMin.php%%
	require('../../../conectMin.php');
	require('../../../conexionMysqli.php');
/*implementacion Oscar 2023 para revisar la estacionalidad configurada en la sucursal */
	$sql = "SELECT
				CONCAT( 'La sucursal <b>', s.nombre, '</b> tiene configurada estacionalidad : <b>', e.nombre, '</b>' ) AS store_config,
				( SELECT racionar_transferencias_productos FROM sys_configuracion_sistema LIMIT 1 ) AS ration_config
			FROM sys_sucursales s
			LEFT JOIN ec_estacionalidad e
			ON s.id_estacionalidad = e.id_estacionalidad
			WHERE s.id_sucursal = {$sucursal_id}";
	$stm = mysql_query( $sql ) or die( "Error al consultar configuracion de estacionalidad y racion : " . mysql_error() );
	$row = mysql_fetch_assoc( $stm );
	$ration_config = $row['ration_config'];
//racion de transferencias
	if ( ! isset( $_GET['idTransfer'] ) && $row['ration_config'] == 1 ){
		include( 'ajax/racionTransferencia.php' );
		//var_dump($link); 
		$rT = new racionTransferencia( $link, false );//true
		echo $rT->calculate_ration();
	}
//2. Incluye archivo %consultaTransferencia.php%%
	require('consultaTransferencia.php');
	//sleep( 5 );//tiempo para que termine los calculos
//2.1. Incluye libreria de implementacion proveedor-producto
	require( 'ajax/productProvider.php' );
/**/
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">	

<!-- 3. Incluye hijas de estilos CSS -->
<link href="css2/estilo.css" rel="stylesheet" type="text/css"  media="all" />
<!--link href="css2/fontello.css" rel="stylesheet" type="text/css"  media="all" /-->
<!-- 5. Incluye archivo %/js/jquery-1.10.2.min.js%% -->
<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>	
<!-- 4. Incluye archivo %js/scriptFunciones.js%% -->
<script language="JavaScript" src="js/scriptFunciones.js"></script>
<!-- 4.1. funciones 2022 -->
<script language="JavaScript" src="js/functions.js"></script>
<!-- 6. Incluye archivo %/js/passteriscoByNeutrix.js%% -->
<script type="text/javascript" src="../../../js/passteriscoByNeutrix.js"></script>

<?php
	if ( isset( $_GET['idTransfer'] ) ){
?>
	<link href="../../../css/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"  media="all" />
	<link href="../../../css/icons/css/fontello.css" rel="stylesheet" type="text/css"  media="all" />
<?php
	}
?>
<style type="text/css">
	input[type=number]::-webkit-inner-spin-button, 
	input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none; 
    margin: 0; 
    -moz-appearance: textfield;
}
</style>

<body onload="enfocar();">
<?php
	if ( isset( $_GET['idTransfer'] ) ){
?>
	<div class="emergent">
		<button onclick="close_emergent();" class="emergent_btn_close">X</button>
		<div class="emergent_content" tabindex="1">
		</div>
	</div>
<?php
	}
?>
<center>
<!-- 7. Ventana emergente para verificacion de presentaciones -->
<div id='cargandoPres' style="width:100%;height:100%;position:absolute;background:rgba(0,0,0,.8);display:none;z-index:100;">
	<center><br><br><br><br><br><br>
		<p style="border:0px solid;width:60%;height:200px;padding:30px; background:rgba(255,0,0,.5);color:white;font-size:30px;" id="mensaje_pres">
		</p>
		<p>
		</p>
	</center>
</div>
<!-- 7.1. ventana emergente para guardar cambios -->
	<div id='cargando' style="width:100%;height:100%;position:absolute; background:rgba(0,0,0,.5);display:none;">
		<center><br><br><br><br><br><br>
			<p style="border: 0px solid;width:60%;padding:30px;background:rgba(0,255,0,.3);"><font color="white" size="5px"><span id="proceso">Generando Transferencia</font></span><br>
			<img src="img/load.gif" witdh="20%" height="20%"><!--montamos imagen-->
			</p>
		</center>
	</div>
<!-- 8. Div de titulo -->
<div id="general" style="width:100%;height:80%; background-image:url(img/bg8.jpg);">
<div id="resultado" style="border:0"></div>
<div id="titulo" class="bg-primary" style="width:100%;">
	<table width="100%" class="table">
		<tr>
			<td width="50%">	
				<div style="float:left;width:100%;">
					<span align="left" style="padding:5px;">
						<font size="5px" color="white"><i class="icon-telegram"></i><?php echo /*$n1.' hacia '.$n2*/$datos_encabezado[0];?></font>
					</span>	
					<table style="float:right;">
						<tr>
							<td width="50%">
								<button 
									type="button" 
									id="confirma"
									class="btn btn-light" 
									onclick="<?php if($status==1){ echo 'desenfocar(2);';}else{echo 'desenfocar(1);';}?>;" 
									<?php if($status>1){echo 'style="display:none;"';} ?>
								>
									<i class="icon-floppy"></i><?php if($status==1){echo 'Guardar Cambios';}else{echo 'Guardar';}?>
								</button>
								<!--
						<?php //if($status==1){echo 'actualizar();';}else{ echo 'confirmar();';} ?>" <?php //if($status>1){echo 'disabled';}?>
								-->
								<!--src="../../../img/confirma.png"-->
							</td>
							<td width="50%"></td>
						</tr>
					</table>
					</span>
				</div>
			</td>
			<td width="50%">
		<!-- 9. Buscador -->
				<div id="titulo" style="text-align:right;float:right;width:100%;">
					<p style="padding:5px" align="center">
						<table width="100%" border="0">
							<tr>
								<td width="60%" align="center">
									<!--p style="color:white;font-size:120%;">Producto:</p-->
									<input 
										type="text" style="padding:8px; width:70%; border-radius:5px;z-index:100;" 
										onkeyup="buscar(event);" 
										id="buscador" 
										class="buscador" 
										placeholder="Buscar Productos"
									/>
									
									<input type="hidden" id="auxBusqueda">
								</td>
								<td width="20%" align="center">
									<!--p class="text-center" style="color:white;font-size:120%;">Cantidad</p-->
									<div class="input-group text-center">
										<input 
											type="text" 
											class="form-control"
											onkeyup="accionar(event);" 
											placeholder="cant." 
											id="agrega"
										>
								<!--/td>
								<td width="10%" align="center">
									<a href="javascript:agregarFila();">
										<font size="5px" color="#000000">
											<i class="icon-plus-circled"></i>
										</font>
									</a>
								</td>
								<td width="10%" align="left" valign="bottom"-->
									<button 
										class="btn btn-success"
										onclick="accionar(event);"
										id="seeker_btn_add"
									>
										<i class="icon-plus-circled"></i>
									</button>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div id="resBus" class="lista_producto" style="display:none; position:fixed; z-index:3;width:40%;border: 2px solid blue; 
									background:white;"></div>  
  		               				<input type="hidden" name="id_productoN" value="" />
								</td>
							</tr>
						</table>
					</p>
				</div>
			</td>
		</tr>
	</table>
</div>
<?php
	echo "<div class=\"bg-warning bg-gradient\">{$row['store_config']}<br>";
	echo "La racion esta : <b>" . ( $row['ration_config'] == 1 ? "HABILITADA" : "DESHABILITADA" ) . "</b>";
	echo "</div>";
?>
<!-- 10. Div de tabla de transferencia >
<div id="tabla" style="width:98%;background:rgba(0,0,0,.3);border-radius:5px;" onclick="ocultaResultados();"-->
<div style="position : relative; width : 100%; max-height : 70%; overflow : auto;">
<table  width="100%" class="table table-striped" id="encabezado" style="border-radius:10px;"><!--COMIENZA TABLA-->
	<!-- 10.1. Encabezados de tabla-->
    <thead style="position : sticky; top : 0;">
    <!--tr>
    	<th colspan="9" bgcolor="#FFFFFF" align="left"></th>
	</tr-->	
		<th width="10%" height="40px" class="text-center bg-danger">Orden</th>
		<th width="29%" class="text-center bg-danger">Descripción</th>
		<th width="10%" class="text-center bg-danger">I. Origen</th>
		<th width="10%" class="text-center bg-danger">I. Destino</th>
		<th width="6%" class="text-center bg-danger">E. Máx</th>
		<th width="4%" class="text-center bg-danger">P</th>
		<th width="6%" class="text-center bg-danger">Pedir</th>
		<th width="6%" class="text-center bg-danger">Racion</th>
		<th width="8%" class="text-center bg-danger">Quitar</th>
		<th width="8%" class="text-center bg-danger">en Pedido</th>
		<th width="5%" class="text-center bg-danger">Det</th>
	</thead>
   <!-- /table -->
    <!-- 10.2.Tabla de productos >
    <div id="contenidoTabla" style="width:101%;height:360px;"-->
    <!--table class="table table-bordered" id="transferencias"><!-- rellenar  border="0"-->
	<tbody id="transferencias">
	<?php
/*******PRUEBA*******/
	$producto_prueba=2084;
/*************/
//sacamos el año desde mysql

	$sql="SELECT YEAR(CURRENT_DATE)";
	$eje_fcha=mysql_query($sql)or die("Error al consultar año actual!!!\n\n".mysql_error());
	$year_act=mysql_fetch_row($eje_fcha);
	$act_year=$year_act[0];
//10.3. Itera resultado de la consulta para formar la tabla	
	while($row=mysql_fetch_assoc($res)){//mientras se encuentren resultados en el arreglo de la consulta;
//racion del producto include_once( 'ajax/racion.php' );

//10.3.2. Formacion de filas
		$invalida="";
		$titulo_caja_txt='';//variabe de tooltip que indica que el producto entró en ración

	if($row['CantidadPresentacion']>0 || $id_tipo==2){//&& $row['InvOr'] >= $row['CantidadPresentacion']
	//implementacion de Oscar 21.02.2017
		if($row['InvOr']<$row['cantidadSurtir'] || $row['raciona']==1){
			$resalta_rojo='style="color:red;"';
			if($row['raciona']==1){
				$titulo_caja_txt=' title="Producto racionado entre sucursales; si requiere más solicite autorización" ';
			}
		}else{
			$resalta_rojo="";
		}
		if($id_tipo==6 AND $row['InvOr']<=0){//$row['InvOr']<0 $row['cantidadSurtir']<=0 or 
				//echo'no';
			}else{

			if( $row['stock_bajo'] == 1 && $row['racion_3'] <= 0 && $ration_config == 1 ){
				$row['CantidadPresentacion'] = 0;
			}
				if(isset($row['valido'])){
		//			echo 'valido: '.$row['valido'];
					$aux=$row['valido'];
					if($aux==1){

						$validos++;	
					}else if($aux==0){
		//				echo 'here';
						$invalida="";//aqui sehabilitamos(por ahora no se usa)
					}
				}	
				$c++;//incrementamos contador
				if($c%2==0){
					echo '<tr id="fila'.$c.'" bgcolor="#FFFF99" class="filas">';//onclick="resaltar('.$c.');"
				}else{	
					echo '<tr id="fila'.$c.'" bgcolor="#CCCCCC" class="filas">';//onclick="resaltar('.$c.');"
				}
//if($row['CantidadPresentacion']>0){
			if( !isset( $_GET['idTransfer'] ) ){
				$initial_calculate = $row['CantidadPresentacion'];
				if( $id_tipo != 6 ){
					$tmp_racion = ( $row['racion_3'] > 0  && $ration_config == 1 ? $row['racion_3'] : $row['CantidadPresentacion']*$row['Presentacion'] );
					$pieces_permission = ( $row['racion_3'] > 0 && $ration_config == 1 ? 1 : 0 );
				//	echo " permission : {$pieces_permission} <br>";
					$aux_p_p = explode( '||', calculateProductProvider( $row['ID'], ( $id_tipo ==6 ? $row['InvOr'] : $tmp_racion ), $link, $c, $pieces_permission ) );//$row['CantidadPresentacion']*$row['Presentacion']
					//if( $row['racion_3'] <= 0 && $ration_config == 1 ){//oscar 2023
						//die( 'here' );
						$row['CantidadPresentacion'] = $aux_p_p[0];
					//}
					$product_provider = $aux_p_p[1];
				}
			}else{
				$initial_calculate = $row['CantidadPresentacion'];
				
				$aux_p_p = explode( '||', $row['productProviderDetail'] );
				$row['CantidadPresentacion'] = $aux_p_p[0];
				$product_provider = $aux_p_p[1];
			}

			$row['InvOr'] = str_replace( '.0000', '', $row['InvOr'] );
			$row['InvDes'] = str_replace( '.0000', '', $row['InvDes'] );

	?>
	<!--Orden de lista-->
		<td align="center" width="9%" onclick="<?php echo 'resaltar('.$c.');';?>" id="<?php echo '0_'.$c;?>" <?php echo $resalta_rojo;?>><?php echo $row['ordenLista'];?></td>
		
	<!--Implementación Oscar 26.02.2019 para agregar el código alfanumérico (oculto) en la transferencia-->
		<td style="display:none;" id="<?php echo 'clave_'.$c;?>"><?php echo $row['clave'];?></td>
	<!--Fin de cambio Oscar 26.02.2019-->

    <!--Id del producto (celda oculta)-->
        <td id="<?php echo '1_'.$c;?>" style="display:none;"><?php echo $row['ID'];?></td>
	<!--Nombre del producto-->
		<td align="left" width="27%" title="<?php echo 'Clave: '.$row['clave'];?>" id="<?php echo '2_'.$c;?>" onclick="<?php echo 'resaltar('.$c.');';?>" <?php echo $resalta_rojo;?>><?php echo $row['Nombre'];?></td>
    <!--Inventario Almacén Origen-->
		<td align="right" width="9%" id="<?php echo '3_'.$c;?>" <?php echo $resalta_rojo;?>><?php echo $row['InvOr'];?></td>
    <!--Inventario Almacén Destino-->
		<td align="right" width="9%" id="<?php echo '4_'.$c;?>" <?php echo $resalta_rojo;?> onclick="<?php echo 'resaltar('.$c.');';if($user_sucursal==$destino){echo 'editaCelda(4,'.$c.');';}?>"><?php echo $row['InvDes'];?></td>
	<!--Estacionalidad máxima 9%-->
		<td width="5%" align="right" id="<?php echo '5_'.$c;?>"  <?php echo $resalta_rojo;?> onclick="<?php if($user_sucursal==$destino){echo 'editaCelda(5,'.$c.');';}?>"><?php echo $row['maximo'];?></td>
<!-- Desarrollo 2022 ( pedido anterior )-->
		<td width="6%" style="color : orange;" align="right"><?php echo round($initial_calculate) . ' - ' . $row['minimo_surtir']; ?></td>
	<!--Cantidad por pedir-->
		<td align="center" width="7%">
        	<input type="number" onkeydown="detiene(event);" class="pedir form-control" id="<?php echo '6_'.$c;?>" value="<?php if($id_tipo==6){echo $row['InvOr'];}else{echo $row['CantidadPresentacion']*$row['Presentacion'];}?>"
	     		onkeyup="<?php echo 'validar(event,'.$c.',2);operacion(event,'.$c.');';?>"
	     		onclick="<?php echo 'resaltar('.$c.');';?>" <?php if($status!=1 && $status!=''){echo ' disabled ';}echo $invalida;?> <?php echo $resalta_rojo.' '.$titulo_caja_txt;?>
	     		onchange="getTransferRowDetail( <?php echo $c; ?> );"
     		/>
        </td>
<!-- Racion -->
		<td class="text-end" id="<?php echo 'racion_'.$c; ?>"><?php echo $row['racion_3'];?></td>
   	<!--Id del detalle de la estacionalidad (celda oculta)-->
        <td  id="<?php echo '7_'.$c; ?>" style="display:none;"><?php echo $row['idEstProd'];?></td>
    <!--cantidad de la presentación-->
    	<td id="<?php echo '8_'.$c; ?>" style="display:none;"><?php echo $row['Presentacion'];?></td>
<!--Implementación Oscar 24.03.2019 para tener indicador de producto por racionar-->
    	<td id="<?php echo '9_'.$c; ?>" style="display:none;"><?php echo $row['raciona'];?></td>
<!--Fin de cambio Oscar 24.03.2019-->
<!--Implementación Oscar 16.04.2019 para agregar al csv ubicación de matriz y de sucursal destino-->
    	<td id="<?php echo '10_'.$c; ?>" style="display:none;"><?php echo $row['ubicacion_matriz'];?></td>
    	<td id="<?php echo '11_'.$c; ?>" style="display:none;"><?php echo $row['ubicacion_almacen_sucursal'];?></td>
    	<td id="<?php echo '12_'.$c; ?>" style="display:none;"><?php echo $row['observaciones'];?></td>
<!--Fin de cambio Oscar 16.04.2019-->
    	
    <!--opción para eliminar la fila-->
		<td align="center" width="7.5%">
			<button 
				onclick="<?php if($status==6){echo'restringe();';}else{echo 'eliminarFila('.$c.',1);';}?>" 
				style="text-decoration:none;"
				class="btn"
			>
				<font color="#FF0000" size="+1">
					<i class="icon-trash"></i></font><font size="-1">&nbsp;</font><font size="-1"></font>
			</button>    
		</td>
	<!-- FILA DE PRODUCTOS PENDIENTES DE RECIBIR -->
	<?php
	    $sql = "SELECT
					IF(
						SUM(IF(trans.id_transferencia IS NULL,0,trans_prd.cantidad)) IS NULL,
						0,
						SUM(IF(trans.id_transferencia IS NULL,0,trans_prd.cantidad))
					) AS productos_en_transferencias_pendientes
				FROM ec_transferencia_productos trans_prd
				LEFT JOIN ec_transferencias trans ON trans_prd.id_transferencia=trans.id_transferencia
				LEFT JOIN sys_sucursales s_1_1 ON trans.id_sucursal_destino=s_1_1.id_sucursal
				AND s_1_1.activo=1
				RIGHT JOIN sys_sucursales_producto sp_1_1 ON s_1_1.id_sucursal=sp_1_1.id_sucursal
				AND sp_1_1.id_sucursal>1 AND sp_1_1.estado_suc=1
				WHERE trans_prd.id_producto_or = {$row['ID']}
				AND sp_1_1.id_producto={$row['ID']}
				AND trans.id_estado BETWEEN '1' AND '8'
				AND s_1_1.id_sucursal = '{$destino}'";
				//die($sql);
		$prods_pend = mysql_query( $sql )or die("Error al consultar transferencias pendientes : " 
			. mysql_error());
		$cantidad_por_entregar = mysql_fetch_row( $prods_pend );
		$cantidad_por_entregar[0] = str_replace( '.0000', '', $cantidad_por_entregar[0] );
	?>
	<!-- fin de fila de transferencias pendientes -->
		<td id="<?php echo '13_'.$c; ?>" style="display : none;">
		<?php 
			echo $product_provider;//calculateProductProvider( $row['ID'], ( $id_tipo ==6 ? $row['InvOr'] : $row['CantidadPresentacion']*$row['Presentacion'] ), $link, $c );
		?>
		</td>

	    <td width="8%" style="text-align : center;<?php echo ( $cantidad_por_entregar[0] > 0 ? 'color:green;' : '' ); ?>" >
	    	<?php echo $cantidad_por_entregar[0];?>
	    </td>

    <!--opción para ver detalle -->
		<td align="center" width="5%">
			<button 
				type="button"
				class="btn btn-warning"
				onclick="show_transfer_product_detail( <?php echo $row['ID'];?>, <?php echo $c;?> )"
				id="show_transfer_detail_<?php echo $c;?>"
			>
				<i class="icon-eye"></i>
			</button>
		</td>
	<?php
		 echo'</tr>';
		 //print_r($extae);
		}//fin de else
	}/*Fin de cambio Oscar 28.04.2019 para no tomar los valores que no piden*/
	}//fin de while	
//sacamos almacen principal de sucursal actual
?>

<!-- 11. Declaracion de variables ocultas-->
<input type="hidden" value="<?php echo $origen;?>" id="orig"/>
<input type="hidden" value="<?php echo $destino;?>" id="dest"/>
<input type="hidden" value="<?php echo $al_origen;?>" id="almOrigen"/>
<input type="hidden" value="<?php echo $al_destino;?>" id="almDestino"/>
<input type="hidden" value="<?php echo $id_tipo;?>" id="tipo"/>
<input type="hidden" value="<?php if($c!=0 && $c!=null){echo $c;}else{ echo '0';}?>" id="cont"/>
<input type="hidden" value="" id="transfe"/>
<input type="hidden" value="0" id="mov">
<!--echo 'valor de c:'.$c;-->
<!--Finalizamos declaracion de variables ocultas-->
</tbody>
</table>
</div>
</div>
<br>
<div>
<table width="100%" border="1">
<?php 
//12. Contador de registros validos
	echo "<div class=\"row\" style=\"font-size : 80% !important; position : relative; width : 99% !important; left : .5%;\">
			<div class=\"col-1 text-center\">
				<font color=\"black\">Total: <b id=\"cont_visual_1\">{$c}</b></font>
			</div>
			<div class=\"col-1 text-center\">
				validos: <b id=\"cont_visual_2\">{$validos}</b>
				<input type=\"hidden\" value=\"0\" id=\"modificaciones\" disabled>
			</div>";
	//if($status==1){
		if(isset($status)){	
			$sql="SELECT nombre FROM ec_estatus_transferencia WHERE id_estatus='$status'";
            $ejecuta=mysql_query($sql)or die(mysql_error());
            
            if($status==6){
                $permisos=" <p class=\"text-danger text-center\">(No Modificable)</p>";
            }else{
                $permisos=" <p class=\"text-success text-center\">(Modificable)</p>";
            }
        	$nomStatus=mysql_fetch_row($ejecuta);
			echo "<div class=\"col-2 text-center\">Status: {$nomStatus[0]} {$permisos}</div>";
		}
?>
<!-- 13. Implementacion de Oscar 28.05.2018 para exportacion / importacion de Tranferencias -->
		<div class="col-2">
			<button 
				onclick="exportaTransferencia('formato_limpio');" 
				class="btn btn-light"
				style="text-decoration:none;color:white;margin-top:-20px;"
			>
				<img src="../../../img/especiales/exportaCSV1.png" width="30px">
				<b style="color:black;">Exportar Formato en Limpio</b>
			</button>
		</div>
		<div class="col-2">
			<button 
				onclick="exportaTransferencia();" 
				class="btn btn-light"
				style="text-decoration:none;color:white;margin-top:-20px;"
			>
				<img src="../../../img/especiales/exportaCSV1.png" 
				width="30px"><b style="color:black;">Exportar en Orden de Caja</b>
			</button>
		</div>
		<div class="col-2" style="<?php if(!isset($idTransfer)){echo 'display:none;';}?>">
			<button 
				onclick="exportaTransferencia('orden_almacen');"  
				class="btn btn-light"
				style="text-decoration:none;color:white;margin-top:-20px;"
			>
				<img src="../../../img/especiales/exportaCSV1.png" width="30px">
				<b style="color:black;">Exportar en Orden de Lista</b>
			</button>					
		</div>
		<div class="col-2" id='espacio_importa' <?php if($id_tipo!=5){echo 'style="display:none;"';}?>>
			<button 
				onclick="javascript:exportaTransferencia(1);" 
				class="btn btn-light"
			>
				<img src="../../../img/especiales/importaCSV1.png" width="40px"><b style="color:black;">Importar</b>
			</button>
		</div>
		<div class="col-2" style="<?php if(!isset($idTransfer)){echo 'display:none;';}?>">
			<button 
				onclick="exportaTransferencia('orden_lista_2024');"  
				class="btn btn-light"
				style="text-decoration:none;color:white;margin-top:-20px;"
			>
				<img src="../../../img/especiales/exportaCSV1.png" width="30px">
				<b style="color:black;">Exportacion con Proyeccion</b>
			</button>					
		</div>
		<div class="col-2">
			<form class="form-inline">
				<input type="file" id="imp_csv_prd" style="display:none;">
				<p class="nom_csv">
					<input type="text" id="txt_info_csv" style="display:none;" disabled>
				</p>
			</form>
				<button 
					type="submit" id="submit-file" style="display:none;background_transparent;" class=""><!--bot_imp-->
					<img src="../../../img/especiales/sube.png" height="30px;">
					<br><style="color:black;">Cargar Archivo</b>
				</button>
			</form>
		</div>
	</div>
<!--Fin de cambio-->

</table>
</div>
</div><!--FIN DE DIV GENERAL-->
</center>

<!-- 14. Implementacion Oscar 29.05.2018 para exportacion en Excel -->
	<form id="TheForm" method="post" action="ajax/modificaTransferencia.php" target="TheWindow">
			<input type="hidden" id="fl" name="fl" value="1" />
			<input type="hidden" id="datos" name="datos" value=""/>
	</form>
<!-- 14.1 Implementacion Oscar 2024 para exportacion en Excel con proyeccion-->
	<form id="proyectionForm" method="post" action="ajax/exportacion_orden_lista.php" target="TheWindow">
			<input type="hidden" id="idTransfer" name="idTransfer" value="<?php echo $idTransfer ;?>" />
			<input type="hidden" id="datos" name="datos" value=""/>
	</form>
<!-- 15. Incluye archivo %pieDePagina.php%% -->
<?php 
	//if( isset( $_GET['idTransfer'] ) ){
		include('pieDePagina.php');
	//}
?>
</body>
</html>

<!-- 16. Incluye archivo %/js/papaparse.min.js%% -->
	<script language="JavaScript" src="../../../js/papaparse.min.js"></script>