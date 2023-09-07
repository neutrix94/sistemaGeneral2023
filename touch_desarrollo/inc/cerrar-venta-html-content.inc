<style type="text/css">
	.input_aprt{
		position:absolute;
		top:80px;
		width:160px;
	}
/*implementación oscar 03.11.2018 para emergente*/
	#emergente_cerrar{position: fixed;z-index: 1000;width: 100%;height: 150%;background: rgba(0,0,0,.8);top: 0;display:none;}
	#cont_emergente{width: 80%;height:50%;position: relative;border-radius: 20px;border:2px solid red;top:5%;left:10%;background:rgba(0,0,0,.5);color: white;}
/*fin de cambio Oscar 03.11.2018*/

</style>
<?php
	if(isset($ids_devoluciones)){
		echo '<input type="hidden" id="ids_de_devoluciones" value="'.$ids_devoluciones.'">';
	}
    if($tv!=null||$tv!=''){
?>
    
<?php
    }
    if(isset($sald_fav) && ($id_aprt==''||$id_aprt==null) ){
    	
    	$pagoFinal=$sald_fav-$total;
    	
    	$saldo_real=$pagoFinal;
    	$dev_color = "red";
    	if($pagoFinal>0){
    		$accion="Devolver ";
    		$dev_color = "green";
    	}else{
    		$pagoFinal=$pagoFinal*-1;
    		$accion="Cobrar ";
    	}
    //varibale implementada el 08.09.208 Oscar para tomar valor real de saldo a favor 
    	echo '<input type="hidden"  value="'.$saldo_real.'" id="saldoAFavor">';
?>
	<div class="row">
      <div class="col-1"></div>
      <div class="col-10 text-center">
      		<b id="accion_devolucion"><?php echo $accion.' ';?> al cliente: </b>
          	<input type="hidden" value="<?php echo $sald_fav;?>" id="saldoFavorOriginal">
      	<div class="row">
            <div class="col-1 text-end"><h1 style="font-size : 180% !important;">$</h1></div>
            <div class="col-10 text-center">
            	<input type="text" id="saldoFavorFinal" value="<?php echo $pagoFinal;?>" style="font-size:120% !important;background:font-weight:bold; color : <?php echo $dev_color;?>;" class="form-control">
            </div>
      	</div>
      </div>
    </div>
<?php
	}//termina  if(!isset(sald_fav))
	
/*Implementación Oscar 04.09.2018 para cambios en apartados*/
//extraemos el id del apartado
$id_apart=$_GET['id_aprt'];
if($id_aprt!=''&&$id_aprt!=null){
//consultamos el total
	$qry=mysql_query("SELECT 
						pe.id_pedido,
						pe.total,
						/*SUM(IF(pp.id_pedido_pago IS NULL,0,pp.monto))*/'$abonado'
					FROM ec_pedidos pe
					LEFT JOIN ec_pedido_pagos pp ON pe.id_pedido=pp.id_pedido 
					WHERE pe.id_pedido=$id_aprt GROUP BY pe.id_pedido")or die("Error al consultar monto de la nota\n\n".mysql_error());
	$row=mysql_fetch_row($qry);
	$total=$row[1];
	$abonado=$row[2];
	$saldo_pendiente=round($total-$abonado);
	$a_favor_cliente=0;
	if($saldo_pendiente<0){
		$a_favor_cliente=$saldo_pendiente*-1;
		$saldo_pendiente=0;
	}
//creamos campo de total de abonos
	echo '<div class="input_aprt" style="left:300px;" align="center">';
	    echo '<div style="position:absolute;width:100%;height:70%;z-index:100;border:0px solid;border-radius:8px;top:25px;"></div>';
		echo 'Abonado<br><input type="text" id="total_pagos" value="$ '.number_format($abonado,2);
	echo '" style="font-size:30px;"></div>';
//creamos campo de 
}
//creamos variable oculta de apartado
	echo '<input type="hidden" id="id_de_apartado" value="'.$id_aprt.'">';
/*fin de cambio 04.09.2018*/

/*implementación de Oscar 14.08.2018 para pedir datos del cliente*/
//consultamos que campo son obligatorios
	$sql="SELECT requiere_info_cliente FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
	$eje=mysql_query($sql)or die("Error al consultar campos obligatorios para cerrar venta en la sucursal!!!\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
//validamos las posibles opciones
	if($r[0]==-1){
		$pide_correo=0;
		$pide_facebook=0;
	}
	if($r[0]==1){
		$pide_correo=0;
		$pide_facebook=1;
	}
	if($r[0]==2){
		$pide_correo=1;
		$pide_facebook=0;
	}
	if($r[0]==3){
		$pide_correo=1;
		$pide_facebook=1;
	}
//creamos las variables ocultas para dar referencia de los permisos
	echo '<input type="hidden" id="obligatorio_pedir_correo" value="'.$pide_correo.'">';
	echo '<input type="hidden" id="obligatorio_pedir_facebook" value="'.$pide_facebook.'">';
/**/
?>


<!--Implementación para emergente que arroja mínimo de apartado Oscar 09.05.2018
<div id="emergente_apartados" style="position:absolute;width:100%;height:100%;background:rgba(0,0,0,.6);z-index:100;top:0;">
	
</div>-->
<!--Fin de cmabio-->

<!--Implementación Oscar 2017 para venta por mayoreo-->
<input type="hidden" id="tipo_venta" value="<?php echo $tv;?>">
<!--Fin de cambio-->

<!--implementación oscar 03.11.2018 para emergente-->
	<div id="emergente_cerrar">
		<div id="cont_emergente">
			
		</div>
	</div>
<!--Fin de cambio Oscar 03.11.2018-->

<div id="divApartado" 
	style="display: none; position:fixed; overflow:hidden; z-index:1000; top:0px; left:0px; bottom:0px; right:0px;background-color: black; opacity: 0.98; filter: alpha(opacity=98);">
	<div class="row" style="position : relative; top : 10%;">
		<div class="col-1"></div>
		<div class="col-10" style="background-color : white;">
			<h5 id="cant_minima_pago" style="position:absolute;top:10px;color:white;font-size:30;">
			</h5>
			<div class="row">
					<h3>Ingresa los datos del cliente </h3>
				<div class="col-3"><br>
					<label>Nombre:</label> 
				</div>
				<div class="col-9"><br>
		        	<input type="text" name="nombre" class="form-control" id="cliente_nombre" onkeyup="enterNext(this, event)">
				</div>
				<div class="col-3"><br>
					<label>Teléfono:</label> 
				</div>
				<div class="col-9"><br>
		        	<input type="text" name="telefono" class="form-control" id="cliente_telefono" onkeyup="enterNext(this, event)">
				</div>
				<div class="col-3"><br>
					<label>Celular:</label> 
		       	</div>
				<div class="col-9"><br>
		        	<input type="text" name="movil" class="form-control" id="cliente_movil" onkeyup="enterNext(this, event)">
				</div>
				<div class="col-3"><br>
					<label>Mail:</label> 
		        </div>
				<div class="col-9"><br>
		        	<input type="text" name="email" class="form-control" id="cliente_email" onkeyup="enterNext(this, event)">
		       	</div>
		       	<div class="col-1"></div>
		        <div class="col-4">
		        	<br>
					<button 
						data-inline="true" 
						name="guardar_apartado" 
						id="guardar_apartado"  
						data-role="button" 
						onkeyup="enterNext(this, event)"
						class="btn btn-success form-control"
					>
						<i class="icon-save">Guardar</i>
					</button>
				</div>
		       	<div class="col-2"></div>
				<div class="col-4">	
					<br>
					<button 
						data-inline="true" 
						name="cancelar_apartado" 
						id="cancelar_apartado" 
						data-role="button" 
						onkeyup="enterNext(this, event)"
						class="btn btn-danger form-control"
					>Cancelar</button>
					<br><br>
				</div>
			</div>
		</div>
  	</div>
</div>
<div data-role="content">
	
  	<!---Grid-->

	<style>
	
		.formaPago{
			width:60%;
			display:block;
			margin:0 auto;
			text-align:left;
		}
		
		.gridPago{
			position:relative;
			left:-200px;
		}
	
	</style>


	<div class="">
		<div class="row">
			<div class="col-7 text-center"><center>
			<form data-role="controlgroup"  data-type="horizontal" data-mini="false" data-ajax="false">
        		<label for="producto" style="font-size : 150% !important; padding-top : 10px;">Total de la orden</label>
    			<center>
	    			<input 
	    				style="font-size:30px !important;" 
	    				type="text" name="total_orden" id="total_orden" 
	    				readonly="true" 
	    				value="$ <?php echo number_format($total, 2); ?>"
	    				class="form-control"
	    			>
    			</center>
    			<input type="hidden" name="totalOrd" id="totalOrd" value="<?php echo $total; ?>">
			</form>
			</center>
			</div>
			<div class="col-5 text-center">
				<div class="text-center" title="ocultar teclado"><!--style="position:relative; display:block;float:right;"-->
					<img src="img/teclado.png" width="70" height="70"><!-- style="padding-left:13px;"-->
					<p align="center"><!--  style="display:block; position:relative; top:-1.3em; left:2em;"-->
					Ocultar<br>
					teclado
					</p>
				</div>
			</div>
		</div>

		<div class="row" style="padding : 20px;"> 
			<table cellpadding="0" cellspacing="0" id="listaPagos" class=""><!--gridPago-->
				<tr class="cabecera">
					<td width="5%"><p>No.</p></td>
					<td width="23%"><p>Tipo de pago</p></td>
					<td width="23%"><p> Monto</p></td>
				</tr>
				<tr class="move">
					<td class="cabecera"><p>1</p></td>
					<td>
						<input type="text" value="Efectivo" name="efectivo" class="form-control" readonly="true" style="background-color : white;">
						<input type="hidden"  class="id_tipo"  value="1">
					</td>
					<td>
						<?php
			/*Impementación Oscar 03.03.2019 para bloquear pagos cuando hay saldo a favor del cliente en la devolución*/
							if(isset($sald_fav) && ($id_aprt==''||$id_aprt==null) ){echo '<p style="position: absolute;width:280px;height: 40px;background:transparent;top:34%;left:55%;z-index: 2;"></p>';}
			/*Fin de cambio Oscar 03.03.2019*/
						?>
						<input type="text" value="<?php if($id_aprt!='' && $id_aprt!=null){echo 0;}else{echo $total;} ?>" class="montoVal form-control" name="dn" id="monEfe" onkeypress="return validarNumero(event,1,'monEfe')" <?php if($id_aprt){ echo 'onkeyup="cambiar_por_pagar();"';}?> onblur="validaMontos(1)">
					
					</td>
				</tr>
				<tr class="move" style="display:none;" >
					<td class="cabecera"><p>2</p></td>
					<td>
						<input type="text" value="      Tarjeta" name="tarjeta">
						<input type="hidden"  class="id_tipo"  value="7">
					</td>
					<td>
						<input type="text" value="0" class="montoVal" name="mon" id="monTar" onkeypress="return validarNumero(event,1,'monTar')" onblur="validaMontos(2);">
					</td>
	         	</tr>
			</table>
		</div>
		<div class="row"><!-- formaPago -->
			<div class="col-12">
			<form data-mini="false" data-ajax="false"><!--  style="float:right; margin-right:16em; position:relative;" data-role="controlgroup" data-type="horizontal"-->
				<center>
				<label for="nombre">Por pagar</label>
				<div>
					<!--div style="position:absolute;width:100%;height:70%;z-index:100;border:0px solid;border-radius:8px;"></div-->
					<div class="row">
						<div class="col-2"></div>
						<div class="col-8">
							<input 
								class="form-control" 
								type="text" 
								name="monto" 
								id="monto" 
								value="<?php if($id_aprt!='' && $id_aprt!=null){echo $saldo_pendiente;}else{echo 0;} ?>"
								style="color : red;"
							>
							<?php 
								if($id_aprt!='' && $id_aprt!=null){
									//creamos campo de total de abonos
									echo '<p class="input_aprt" style="left:300px;top:-18px;" align="center">';
			    					echo '<span style="position:absolute;width:100%;height:70%;z-index:100;border:0px solid;border-radius:8px;top:25px;"></span>';	
									echo '<b>$ A favor de Cliente</b><br><input type="text" id="total_fc" value="'.$a_favor_cliente;
									echo '" style="font-size:30px;"></p>';
								}
							?>
						</div>
				</div>
			</center>
			</form>
			</div>
		</div>
		<!--Aqui se implementan facebook y correo del cliente-->
    <div class="row"><!-- class="datosCliente" style="width:100%;border:0;" -->
      <!--table style="border-radius:5px;border:0;width:100%;">
        <tr style="border-radius:5px;">
        	<td colspan="2"-->
    	<h4 class="text-center"><b>Ingresa los datos del cliente:</b></h4><!--/td>
        </tr>
        <tr-->
		<div class="col-11 text-center">
			<div class="input-group">
				<button type="button" class="btn">
					<i class="icon-whatsapp icon-big" style="color : green;"></i>
				</button>
				<input type="text" id="fa" placeholder="Whatsapp.." class="form-control">
			</div>
		</div>
		<div class="col-1 text-center"></div>
    	<div class="col-11 text-center"><br>
			<div class="input-group">
				<button type="button" class="btn">
					<i class="icon-mail icon-big"></i>
				</button>
	    		<input type="text" id="co" placeholder="Dirección de correo/Instagram..." class="form-control">
    		</div>
    	</div>
		<div class="col-1 text-center"></div>
    </div>
    <br>
<?php
    /**/
    if($id_aprt!='' && $id_aprt!=null){
?>
    <div class="row">
   		<div class="col-1"></div>
   		<div class="col-10">
   			<button type="button" onclick="imprime_cambio();" 
   					class="btn1_1 btn btn-success" style="min-width: 180px; margin-right: 50px;">✔ Finalizar Cambio</button>
   		</div>
   	</div>
<?php
	}
?>
	
	<div class="row">
		<div class="col-1"></div>
<?php
   	if($id_aprt=='' || $id_aprt==null){
?>
		<form class="col-10 text-center" data-role="controlgroup" data-type="horizontal" data-mini="false" data-ajax="false" name="fticket" id="fticket" action="index.php">
		<input type="checkbox" name="es_apartado" id="es_apartado">
		<label for="es_apartado" id="label_apartado">Es apartado</label>
   		<!-- <button type="submit" name="submit" class="cerrar"> <span>Cerrar</span></button> -->
   		<button  
   			name="cerrar" 
   			id="cerrar" 
   			class="btn1 btn btn-success form-control">
   		<!-- 
   			style="min-width: 180px; margin-right: 50px;"-->
   			✔ Cerrar venta
   		</button>
   		<!--✗Cancelar--> 
		</form>
       	<?php
       	}
    /**/
       	?>

	</div>
  		<!--seccion-->
	</div>
</div>
<!--
<div id="codigo_folio"></div>-->
<!--Implementación Oscar 07.03.2019 para recibir el id de pedido original en caso de provenir de una devolución o cambio-->
<?php
	if(isset($_GET[id_pedido_original]) && $_GET[id_pedido_original]!=''){
		echo '<input type="hidden" id="id_pedido_original" value="'.$id_pedido_original.'">';
	}
?>
<!--Fin de cambio Oscar 07.03.2019-->

<!--Implementacion Oscar 13.11.2018 para guardar el temporal del detalle antes de ser modificado
	<input type="hidden" id="arr_prods_antes" value="<?php //echo $_GET['prods_ant'];?>">
Fin de cambio Oscar 13.11.2018-->
  <!--Termina seccion-->
  <script type="text/javascript">
  	window.onload =function(){
  		//$("#monEfe").click();
  		/*var objet=document.getElementById("monEfe");
		objet.focus();
		if(objet.value!=""){
			objet.value+="";
		}*/
	<?php
		if(isset($sald_fav) && ($id_aprt==''||$id_aprt==null) ){}else{

		?>
			$("#monEfe").focus();
	<?php 
		} 
	?>
		/*$("#monEfe").select();*/
  	}	
  </script>
