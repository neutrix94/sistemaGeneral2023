<?php
    if($tv!=null||$tv!=''){
?>
    
<?php
    }
    if(isset($sald_fav)){
    	$pagoFinal=$sald_fav-$total;
    	if($pagoFinal>0){
    		$accion="Devolver ";
    	}else{
    		$pagoFinal=$pagoFinal*-1;
    		$accion="Cobrar ";
    	}
?>
	<div style="width:150px;" style="border:0;position:fixed;right:30px;">
      <table style="border:0;position:absolute;right:25%;width:50%;background:transparent;">
        <tr>
          <td align="right" style="font-size:20px;border: inset 0pt;"><b><?php echo $accion.' ';?> al cliente: $</b></td>
          <td style="border: inset 0pt;">
          	<input type="hidden" value="<?php echo $sald_fav;?>" id="saldoFavorOriginal">
            <input type="text" value="<?php echo $pagoFinal;?>" id="saldoAFavor" style="color:black;font-size:20px;background:font-weight:bold;">
          </td>
        </tr>
      </table>
    </div><br>
<?php
	}//termina  if(!isset(sald_fav))
	
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
    			<center><input style="font-size:30px;width:300px;" type="text" name="total_orden" id="total_orden" readonly="true" 
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
						<input type="text" value="<?php echo $total; ?>" class="montoVal" name="dn" id="monEfe" onkeypress="return validarNumero(event,1,'monEfe')" onblur="validaMontos(1)">
					</td>
				</tr>
				<tr class="move" style="display:none;" >
					<td class="cabecera"><p>2</p></td>
					<td>
						<input type="text" value="      Tarjeta" name="tarjeta">
						<input type="hidden"  class="id_tipo"  value="7">
					</td>
					<td>
						<input type="text" value="0" class="montoVal" name="mon" id="monTar" onkeypress="return validarNumero(event,1,'monTar')" onblur="validaMontos(2)">
					</td>
             	</tr>
			</table>
			<div>
			<form style="float:right; margin-right:16em; position:relative;" data-role="controlgroup" data-type="horizontal" data-mini="false" data-ajax="false">
				<center>
				<label for="nombre">Por pagar</label>
				<div>
					<div style="position:absolute;width:100%;height:70%;z-index:100;border:0px solid;border-radius:8px;"></div>
					<input style="font-size:30px;width:150px;color:black;" type="text" name="monto" id="monto" value="0">
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
        	<td><p align="center"><b>Ingrese los datos del cliente:</b></p></td>
        </tr>
        <tr>
        	<td><input type="text" id="co" style="height:10px;" placeholder="Dirección de correo..."></td>
        </tr>
        <tr>
        	<td><input type="text" id="fa" placeholder="Facebook.."></td>
        </tr>
      </table>
      </div>
    </div>
    <br>
  		<!--seccion-->
  		<div data-role="fieldcontain">
  		<center>
			<form data-role="controlgroup" data-type="horizontal" data-mini="false" data-ajax="false" name="fticket" id="fticket" action="index.php">
           		<label for="es_apartado">Es apartado</label>
           		<input type="checkbox" name="es_apartado" id="es_apartado">
           		
  		   		<!-- <button type="submit" name="submit" class="cerrar"> <span>Cerrar</span></button> -->
  		   		<button  data-theme="a" name="cerrar" id="cerrar" class="btn1" style="min-width: 180px; margin-right: 50px;">✔ Cerrar venta</button>
  		   		<!--✗Cancelar--> 
	  		</form>
	  	</center>
		</div>
	</div>
</div>
  <!--Termina seccion-->
  <script type="text/javascript">
  	window.onload =function(){
  		//$("#monEfe").click();
  		/*var objet=document.getElementById("monEfe");
		objet.focus();
		if(objet.value!=""){
			objet.value+="";
		}*/
		$("#monEfe").focus();
		/*$("#monEfe").select();*/
  	}	
  </script>
