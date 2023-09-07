<?php
//1. Incluye archivo %/conectMin.php%%
	require('../../../conectMin.php');
	require('../../../conexionMysqli.php');
//2. Incluye archivo %consultaTransferencia.php%%
	require('consultaTransferencia.php');
//2.1. Incluye libreria de implementacion proveedor-producto
	require( 'ajax/productProvider.php' );
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
<!-- 3. Incluye hijas de estilos CSS -->
<link href="css2/estilo.css" rel="stylesheet" type="text/css"  media="all" />
<link href="css2/fontello.css" rel="stylesheet" type="text/css"  media="all" />
<!-- 4. Incluye archivo %js/scriptFunciones.js%% -->
<script language="JavaScript" src="js/scriptFunciones.js"></script>
<script language="JavaScript" src="js/functions.js"></script>
<!-- 5. Incluye archivo %/js/jquery-1.10.2.min.js%% -->
<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>	
<!-- 6. Incluye archivo %/js/passteriscoByNeutrix.js%% -->
<script type="text/javascript" src="../../../js/passteriscoByNeutrix.js"></script>
<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
<style type="text/css">
	input[type=number]::-webkit-inner-spin-button, 
	input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none; 
    margin: 0; 
    -moz-appearance: textfield;
}
</style>

<body onload="enfocar();">

<!--div class="emergent">
	<button onclick="" class="emergent_btn_close">X</button>
	<div class="emergent_content">
	</div>
</div-->

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

<!-- 8. Div de titulo -->
<div id="general" style="width:100%;height:85%; background-image:url(img/bg8.jpg);">
<div id="resultado" style="border:0"></div>
<div id="titulo" style="width:100%;background:#83B141;">
	<table width="100%">
		<tr>
			<td width="50%">	
				<div style="float:left;width:100%;">
					<span align="left" style="padding:5px;">
						<font size="5px" color="#000000"><i class="icon-telegram"></i><?php echo /*$n1.' hacia '.$n2*/$datos_encabezado[0];?></font>
					</span>	
					<table style="float:right;">
						<tr>
							<td width="50%">
								<input type="button" value="<?php if($status==1){echo 'Guardar Cambios';}else{echo 'Guardar';}?>" id="confirma" 
								onclick="<?php if($status==1){ echo 'desenfocar(2);';}else{echo 'desenfocar(1);';}?>;" <?php if($status>1){echo 'style="display:none;"';} ?>/>
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
									<p style="color:white;font-size:120%;">Producto:
									<input type="text" style="padding:8px; width:70%; border-radius:5px;z-index:100;" onkeyup="buscar(event);" 
									id="buscador" class="buscador" />
									</p>
									<input type="hidden" id="auxBusqueda">
								</td>
								<td width="20%" align="center">
									<span style="color:white;font-size:120%;">Cantidad</span>
									<input type="text" style="width:40px; padding:8px; border-radius:5px;" onkeyup="accionar(event);" placeholder="cant." id="agrega">
								</td>
								<td width="10%" align="center">
									<a href="javascript:agregarFila();"><font size="5px" color="#000000"><i class="icon-plus-circled"></i></font></a>
								</td>
								<td width="10%" align="left" valign="bottom">
									<button style="color:gray;background:white;padding:5px;border-radius:50%;font-size:20px;"
									onclick="accionar(event);">
										<b>+</b>
									</button>
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
<BR>
<!-- 10. Div de tabla de transferencia -->
<div id="tabla" style="width:98%;background:rgba(0,0,0,.3);border-radius:5px;" onclick="ocultaResultados();">
<table  width="100%" class="rellenar" id="encabezado" style="border-radius:10px;"><!--COMIENZA TABLA-->
	<!-- 10.1. Encabezados de tabla-->
    <thead>
    <tr>
    	<th colspan="9" bgcolor="#FFFFFF" align="left"></th>
	</tr>	
		<th width="10%" height="40px">Orden</th>
		<th width="34%">Descripción</th>
		<th width="11%">I. Origen</th>
		<th width="11%">I. Destino</th>
		<th width="10%">E. Máxima</th>
		<th width="10%">Pedir</th>
		<th width="8%">Quitar</th>
		<th width="5%">Det</th>
	</thead>
   </table>
    <!-- 10.2.Tabla de productos -->
    <div id="contenidoTabla" style="width:101%;height:360px;">
    <table class="rellenar table table-bordered" id="transferencias" border="0">
	<tbody>
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
	
	//10.3.1. Proceso de la racion por producto
		include_once( 'ajax/racion.php' );


//10.3.2. Formacion de filas
		$invalida="";
		$titulo_caja_txt='';//variabe de tooltip que indica que el producto entró en ración
	//if($row['CantidadPresentacion']>0){
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
	?>

	<!--Orden de lista-->
		<td align="right" width="10%" onclick="<?php echo 'resaltar('.$c.');';?>" id="<?php echo '0_'.$c;?>" <?php echo $resalta_rojo;?>><?php echo $row['ordenLista'];?></td>
		
	<!--Implementación Oscar 26.02.2019 para agregar el código alfanumérico (oculto) en la transferencia-->
		<td style="display:none;" id="<?php echo 'clave_'.$c;?>"><?php echo $row['clave'];?></td>
	<!--Fin de cambio Oscar 26.02.2019-->

    <!--Id del producto (celda oculta)-->
        <td id="<?php echo '1_'.$c;?>" style="display:none;"><?php echo $row['ID'];?></td>
	<!--Nombre del producto-->
		<td align="left" width="34%" title="<?php echo 'Clave: '.$row['clave'];?>" id="<?php echo '2_'.$c;?>" onclick="<?php echo 'resaltar('.$c.');';?>" <?php echo $resalta_rojo;?>><?php echo $row['Nombre'];?></td>
    <!--Inventario Almacén Origen-->
		<td align="right" width="11%" id="<?php echo '3_'.$c;?>" <?php echo $resalta_rojo;?>><?php echo $row['InvOr'];?></td>
    <!--Inventario Almacén Destino-->
		<td align="right" width="11%" id="<?php echo '4_'.$c;?>" <?php echo $resalta_rojo;?> onclick="<?php echo 'resaltar('.$c.');';if($user_sucursal==$destino){echo 'editaCelda(4,'.$c.');';}?>"><?php echo $row['InvDes'];?></td>
	<!--Estacionalidad máxima-->
		<td width="10%" align="right" id="<?php echo '5_'.$c;?>"  <?php echo $resalta_rojo;?> onclick="<?php if($user_sucursal==$destino){echo 'editaCelda(5,'.$c.');';}?>"><?php echo $row['maximo'];?></td>
	<!--Cantidad por pedir-->
	<td align="center" width="10%">
        	<input type="number" onkeydown="detiene(event);" class="pedir" id="<?php echo '6_'.$c;?>" value="<?php if($id_tipo==6){echo $row['InvOr'];}else{echo $row['CantidadPresentacion']*$row['Presentacion'];}?>"
     		onkeyup="<?php echo 'validar(event,'.$c.',2);operacion(event,'.$c.');';?>"
     		onclick="<?php echo 'resaltar('.$c.');';?>" <?php if($status!=1 && $status!=''){echo ' disabled ';}echo $invalida;?> <?php echo $resalta_rojo.' '.$titulo_caja_txt;?>/>
        </td>
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
    	<td id="<?php echo '13_'.$c; ?>" style="display : none;">
		<?php 
			echo calculateProductProvider( $row['ID'], ( $id_tipo ==6 ? $row['InvOr'] : $row['CantidadPresentacion']*$row['Presentacion'] ), $link, $c );
		?>
		</td>
<!--Fin de cambio Oscar 16.04.2019-->
    	
    <!--opción para eliminar la fila-->
		<td align="center" width="8%">
			<a href="<?php if($status==6){echo'javascript:restringe();';}else{echo 'javascript:eliminarFila('.$c.',1);';}?>" style="text-decoration:none;">
				<font color="#FF0000" size="+3"><i class="icon-cancel-circled"></i></font><font size="-1">&nbsp;</font><font size="-1"></font>
			</a>    
		</td>
    <!--opción para ver detalle -->
		<td align="center" width="">
			<button 
				type="button"
				class="btn btn-success"
				onclick="show_transfer_product_detail( <?php echo $row['ID'];?>, <?php echo $c;?> )"
			>
				<i>Ver</i>   
			</button>
		</td>

	<?php
		 echo'</tr>';
		 //print_r($extae);
		}//fin de else
//}/*Fin de cambio Oscar 28.04.2019 para no tomar los valores que no piden*/
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
</div>
</table>
</div>
<br/>
<div>
<table width="100%" border="1">
<?php 
//12. Contador de registros validos
	echo '<font color="black">Total:<b id="cont_visual_1">'.$c.'</b><br>validos:<b id="cont_visual_2">'.$validos.'</b></font><br>';
	//if($status==1){
		echo '<input type="hidden" value="0" id="modificaciones" disabled>';
		if(isset($status)){	
			$sql="SELECT nombre FROM ec_estatus_transferencia WHERE id_estatus='$status'";
            $ejecuta=mysql_query($sql)or die(mysql_error());
            
            if($status==6){
                $permisos=" (No Modificable)";
            }else{
                $permisos=" (modificable)";
            }
        	$nomStatus=mysql_fetch_row($ejecuta);
			echo '<font color="white">Status:'.$nomStatus[0].$permisos.'</font>';
		}
?>
<!-- 13. Implementacion de Oscar 28.05.2018 para exportacion / importacion de Tranferencias -->
	<p align="right" style="top:-65;position:relative;color:white;padding:8px;border:0;width:100%;right:30px;">
		<table style="position:absolute;right:5%;bottom:12%;">
			<tr>
				<td>
					<a href="javascript:exportaTransferencia('formato_limpio');" style="text-decoration:none;color:white;">
						<img src="../../../img/especiales/exportaCSV1.png" width="40px"><br><b style="color:black;">Exportar<br>Formato<br>en Limpio</b>
					</a>
				</td>
				<td>
					<a href="javascript:exportaTransferencia();" style="text-decoration:none;color:white;">
						<img src="../../../img/especiales/exportaCSV1.png" width="40px"><br><b style="color:black;">Exportar en<br>Orden de Lista</b>
					</a>
				</td>
				<td>
					<a href="javascript:exportaTransferencia('orden_almacen');" style="text-decoration:none;color:white;<?php if(!isset($idTransfer)){echo 'display:none;';}?>">
						<img src="../../../img/especiales/exportaCSV1.png" width="40px"><br><b style="color:black;">Exportar en<br> Orden de Caja</b>
					</a>					
				</td>
				<td id='espacio_importa' <?php if($id_tipo!=5){echo 'style="display:none;"';}?>>
					<a href="javascript:exportaTransferencia(1);" style="text-decoration:none;color:white;">
						<img src="../../../img/especiales/importaCSV1.png" width="40px"><br><b style="color:black;">Importar</b>
					</a>
				</td>
				<td>
					<form class="form-inline">
						<input type="file" id="imp_csv_prd" style="display:none;">
						<p class="nom_csv">
							<input type="text" id="txt_info_csv" style="display:none;" disabled>
						</p>
					</form>
					<td>
						<button type="submit" id="submit-file" style="display:none;background_transparent;" class="bot_imp">
							<img src="../../../img/especiales/sube.png" height="40px;">
							<br><btyle="color:black;">Cargar Archivo</b>
						</button>
					</form>
					</td>
				</td>
			</tr>
		</table>
	</p>
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
<!-- 15. Incluye archivo %pieDePagina.php%% -->
<?php include('pieDePagina.php');?>
</body>
</html>

<!-- 16. Incluye archivo %/js/papaparse.min.js%% -->
	<script language="JavaScript" src="../../../js/papaparse.min.js"></script>