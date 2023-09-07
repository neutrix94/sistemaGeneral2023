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

    	if($pagoFinal>0){
    		$accion="Devolver ";
    	}else{
    		$pagoFinal=$pagoFinal*-1;
    		$accion="Cobrar ";
    	}
    //varibale implementada el 08.09.208 Oscar para tomar valor real de saldo a favor 
    	echo '<input type="hidden"  value="'.$saldo_real.'" id="saldoAFavor">';
?>
	<div style="width:150px;" style="border:0;position:fixed;right:30px;">
      <table style="border:0;position:absolute;right:25%;width:50%;background:transparent;">
        <tr>
          <td align="right" style="font-size:20px;border: inset 0pt;"><b id="accion_devolucion"><?php echo $accion.' ';?> al cliente: $</b></td>
          <td style="border: inset 0pt;">
          	<input type="hidden" value="<?php echo $sald_fav;?>" id="saldoFavorOriginal">
            <input type="text" id="saldoFavorFinal" value="<?php echo $pagoFinal;?>" style="color:black;font-size:20px;background:font-weight:bold;">
          </td>
        </tr>
      </table>
    </div><br>
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
						/*SUM(IF(pp.id_pedido_pago IS NULL,0,pp.monto))*/$abonado
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

<div id="divApartado" style="display: none; position:fixed; overflow:hidden; z-index:1000; top:0px; left:0px; bottom:0px; right:0px;background-color: black; opacity: 0.98; filter: alpha(opacity=98);">
	<div class="espera_ctn_form" >
		<p id="cant_minima_pago" style="position:absolute;top:10px;color:white;font-size:30;">
		</p>
		<h3>Ingresa lo datos del cliente </h3>
		<label>Nombre:</label> 
        <input type="text" name="nombre"  id="cliente_nombre" onkeyup="enterNext(this, event)">
		<label>Teléfono:</label> 
        <input type="text" name="telefono"  id="cliente_telefono" onkeyup="enterNext(this, event)">
		<label>Celular:</label> 
        <input type="text" name="movil"  id="cliente_movil" onkeyup="enterNext(this, event)">
		<label>Mail:</label> 
        <input type="text" name="email"  id="cliente_email" onkeyup="enterNext(this, event)">
        <div data-inline="true">
			<button data-inline="true" name="guardar_apartado" id="guardar_apartado"  data-role="button" onkeyup="enterNext(this, event)">Guardar</button>
			<button data-inline="true" name="cancelar_apartado" id="cancelar_apartado" data-role="button" onkeyup="enterNext(this, event)">Cancelar</button>
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


	<div class="ui-grid-solo" style="width:100%">
		<div style="width:90%;">
			<div width="70%"; style="float:left;"><center>
			<form data-role="controlgroup"  data-type="horizontal" data-mini="false" data-ajax="false">
        		<label for="producto">Total de la orden</label>
    			<center><input style="font-size:30px;width:250px;" type="text" name="total_orden" id="total_orden" readonly="true" 
    			value="$ <?php echo number_format($total, 2); ?>"></center>
    			<input type="hidden" name="totalOrd" id="totalOrd" value="<?php echo $total; ?>">
			</form></center>
			</div>
			<div width="45%"; style="float:right;">
				<div title="ocultar teclad"  style="position:relative; display:block;float:right;">
					<img style="padding-left:13px;" src="img/teclado.png" width="70" height="70">
				<p style="display:block; position:relative; top:-1.3em; left:2em;">
				Ocultar<br>
				teclado
				</p>
			</div>
			</div>
		</div>
		<br><br><br><br><br><br><br>
		<div class="formaPago">
   			      
			<table cellpadding="0" cellspacing="0" id="listaPagos" class="gridPago">
				<tr class="cabecera">
					<td width="5%"><p>No.</p></td>
					<td width="23%"><p>Tipo de pago</p></td>
					<td width="23%"><p> Monto</p></td>
				</tr>
				<tr class="move">
					<td class="cabecera"><p>1</p></td>
					<td>
						<input type="text" value="       Efectivo" name="efectivo" readonly="true">
						<input type="hidden"  class="id_tipo"  value="1">
					</td>
					<td>
						<?php
			/*Impementación Oscar 03.03.2019 para bloquear pagos cuando hay saldo a favor del cliente en la devolución*/
							if(isset($sald_fav) && ($id_aprt==''||$id_aprt==null) ){echo '<p style="position: absolute;width:280px;height: 40px;background:transparent;top:34%;left:55%;z-index: 2;"></p>';}
			/*Fin de cambio Oscar 03.03.2019*/
						?>
						<input type="text" value="<?php if($id_aprt!='' && $id_aprt!=null){echo 0;}else{echo $total;} ?>" class="montoVal" name="dn" id="monEfe" onkeypress="return validarNumero(event,1,'monEfe')" <?php if($id_aprt){ echo 'onkeyup="cambiar_por_pagar();"';}?> onblur="validaMontos(1)">
					
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
			<div>
			<form style="float:right; margin-right:16em; position:relative;" data-role="controlgroup" data-type="horizontal" data-mini="false" data-ajax="false">
				<center>
				<label for="nombre">Por pagar</label>
				<div>
					<div style="position:absolute;width:100%;height:70%;z-index:100;border:0px solid;border-radius:8px;"></div>
					<input style="font-size:30px;width:150px;color:black;" type="text" name="monto" id="monto" value="<?php if($id_aprt!='' && $id_aprt!=null){echo $saldo_pendiente;}else{echo 0;} ?>">
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
			</center>
			</form>
			</div>
		</div>
		<!--Aqui se implementan facebook y correo del cliente-->
    <div class="datosCliente" style="width:100%;border:0;">
      <div >
      <table style="border-radius:5px;border:0;width:100%;">
        <tr style="border-radius:5px;">
        	<td colspan="2"><p align="center"><b>Ingrese los datos del cliente:</b></p></td>
        </tr>
        <tr>
        	<td align="center" width="40px;"><img src="../img/especiales/whatsapp_icono.png" width="80px"></td>
        	<td><input type="text" id="fa" placeholder="Whatsapp.."></td>
        </tr>
        <tr>
        	<td align="center" width="40px;"><img src="../img/especiales/correo_icono.png" width="60px"></td>
        	<td><input type="text" id="co" style="height:10px;" placeholder="Dirección de correo/Instagram..."></td>
        </tr>
      </table>
      </div>
    </div>
    <br>
    	<?php
        /**/
           	if($id_aprt!='' && $id_aprt!=null){
        ?>
  		   		<p style="position:absolute;top:77.8%;left:20.5%;z-index:3;">
  		   			<button  data-theme="a" onclick="imprime_cambio();" class="btn1_1" style="min-width: 180px; margin-right: 50px;">✔ Finalizar Cambio</button>
  		   		</p><br><br>
  		<?php
  			}
  		?>
  		<!--seccion-->
  		<div data-role="fieldcontain">
  		<center>
           	<?php
        /**/
           	if($id_aprt=='' || $id_aprt==null){
           	?>
           		<form data-role="controlgroup" data-type="horizontal" data-mini="false" data-ajax="false" name="fticket" id="fticket" action="index.php">
           		<label for="es_apartado" id="label_apartado">Es apartado</label>
           		<input type="checkbox" name="es_apartado" id="es_apartado">
  		   		<!-- <button type="submit" name="submit" class="cerrar"> <span>Cerrar</span></button> -->
  		   		<button  data-theme="a" name="cerrar" id="cerrar" class="btn1" style="min-width: 180px; margin-right: 50px;">✔ Cerrar venta</button>
  		   		<!--✗Cancelar--> 
           	<?php
           	}
        /**/
           	?>

	  		</form>
	  	</center>
		</div>
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
