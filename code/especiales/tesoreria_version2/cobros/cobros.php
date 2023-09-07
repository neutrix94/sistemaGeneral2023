<?php
	include('../../../../conectMin.php');
	/*if($perfil_usuario!=7){
		die('<script>alert("Este tipo de usuario no puede acceder a esta pantalla!!!\nContacte al administrador desl sistema!!!");location.href="../../../../index.php?";</script>');
	}*/
	$sql="SELECT IF(p.ver=1 OR p.modificar=1,1,0) 
			FROM sys_permisos p
			LEFT JOIN sys_users_perfiles perf ON perf.id_perfil=p.id_perfil
			LEFT JOIN sys_users u ON u.tipo_perfil=perf.id_perfil 
			WHERE p.id_menu=200
			AND u.id_usuario=$user_id";
	//die($sql);
	$eje=mysql_query($sql)or die("Error al consultar el permiso de cajero!!!<br>".mysql_error()."<br>".$sql);
	$es_cajero=mysql_fetch_row($eje);
	if($es_cajero[0]==0){
		die('<script>alert("Este tipo de usuario no puede acceder a esta pantalla!!!\nContacte al administrador desl sistema!!!");location.href="../../../../index.php?";</script>');
	}
//validamos que haya una sesion de caja iniciada con este cajero; de lo contrario avisamos que no hay sesión de caja y no dejamos acceder a esta pantalla
	$sql="SELECT count(id_sesion_caja) FROM ec_sesion_caja WHERE id_cajero=$user_id AND hora_fin='00:00:00' AND fecha=current_date()";
//	die($sql);
	$eje=mysql_query($sql)or die("Error al verificar si ya existe una sesion de caja para este cajero!!!\n".mysql_error());
	$r=mysql_fetch_row($eje);
	if($r[0]!=1){
		die('<script>alert("Es necesario abrir caja antes de cobrar!!!");location.href="../../../../code/especiales/tesoreria/abreCaja/abrirCaja.php?";</script>');
	}
//sacamos información de las afiliaciones
	$sql="SELECT a.id_afiliacion,a.no_afiliacion 
		FROM ec_afiliaciones a
		LEFT JOIN ec_afiliaciones_cajero ac ON ac.id_afiliacion=a.id_afiliacion
		WHERE ac.id_cajero='$user_id' AND ac.activo=1";
	$eje=mysql_query($sql)or die("Error al consultar las afiliaciones para este cajero!!!<br>".mysql_error());
	//$afiliacion_1='<select id="tarjeta_1" class="filtro"><option value="0">--SELECCIONAR--</option>';
	$tarjetas_cajero='';
	$c=0;
	while($r=mysql_fetch_row($eje)){
		$c++;
		$tarjetas_cajero.='<tr>';
			$tarjetas_cajero.='<td colspan="2" class="subtitulo"><p style="font-size:20px;margin:0;" align="center">Tarjeta '.$c.':</p></td>';
		$tarjetas_cajero.='</tr>';
		$tarjetas_cajero.='<tr>';
			$tarjetas_cajero.='<td align="center">';
				$tarjetas_cajero.='<select id="tarjeta_'.$c.'" class="filtro"><option value="'.$r[0].'">'.$r[1].'</option>';
			$tarjetas_cajero.='</td>';
			$tarjetas_cajero.='<td>';
				$tarjetas_cajero.='<input type="number" class="entrada" id="t'.$c.'" value="0" onkeydown="prevenir(event);" onkeyup="valida_tca(this,event,1,'.$c.');">';
			$tarjetas_cajero.='</td>';
		$tarjetas_cajero.='</tr>';	
	}
	echo '<input type="hidden" id="cantidad_tarjetas" value="'.$c.'">';
//cehque o transferencia 
	$sql="SELECT bc.id_caja_cuenta,bc.nombre 
		FROM ec_caja_o_cuenta bc
		LEFT JOIN ec_caja_o_cuenta_sucursal bcs ON bc.id_caja_cuenta=bcs.id_caja_o_cuenta 
		WHERE bcs.estado_suc=1
		AND bcs.id_sucursal='$user_sucursal'";
	$eje=mysql_query($sql)or die("Error al listar los bancos o cajas!!!<br>".mysql_error());
	$cajas='<select id="caja_o_cuenta" class="filtro"><option value="0">--SELECCIONAR--</option>';
	while($r=mysql_fetch_row($eje)){
		$cajas.='<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	$cajas.='</select>';
	$eje=mysql_query($sql)or die("Error al consultar afiliaciones del id_cajero!!!<br>".mysql_error());
	$sql="SELECT CONCAT(u.nombre,' ',u.apellido_paterno,' ',u.apellido_materno) as nombre,s.nombre
		FROM sys_users u 
		LEFT JOIN sys_sucursales s ON s.id_sucursal=u.id_sucursal
		WHERE u.id_usuario=$user_id";
	$eje_datos=mysql_query($sql)or die("Eror al consultar los datos de usuario y sucursal");
	$r=mysql_fetch_row($eje_datos);
	$usuario=$r[0];
	$sucursal=$r[1];
/*informacion de bancos
	$sql="SELECT cc.id_caja_cuenta,cc.nombre
		FROM ec_caja_o_cuenta cc
		LEFT JOIN ec_caja_o_cuenta_sucursal cs ON cs.id_caja_o_cuenta=cc.id_caja_cuenta
		WHERE cs.id_sucursal='$user_sucursal'
		AND cs.estado_suc=1";
	$eje_bancos=mysql_query($sql)or die("Error al consultar las cajas por susucrsal!!!<br>".mysql_error());
	$bancos='<select id="baco_o caja" class="entrada_num"><option value="0">--SELECCIONAR--</option>';
	if(mysql_num_rows($eje_bancos)<=0){
		$bancos.='<option value="0">No hay cajas o cuentas par esta sucursal</option>';
	}
	while($r=mysql_fetch_row($eje_bancos)){
		$bancos.='<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	$bancos.='</option>';*/
?>
<!DOCTYPE html>
<html>
<head>
	<title>Cobrar</title>
<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<style type="text/css">
	.global{position: absolute;width: 100%;height: 100%;top:0;left: 0;background-image: url('../../../../img/img_casadelasluces/bg8.jpg');}
	.header{position: absolute;height: 60px;background:#83B141;width:100%;}
	.footer{position: absolute;bottom: 0px; height: 60px;background:#83B141;width:100%;}
	.contenido{position: absolute;top:50px;width: 100%;}
	th{color: white; font-size: 30px;align-items: center;}
	.pagos{background:#83B141;color: white;font-size: 20px;}
	.entrada_num{padding: 10px;width:80%;font-size: 25px;}
	#buscador{padding: 10px;width: 80%;font-size: 25px;}
	#res_busc{position: absolute;width: 40%;border: 1px solid;top:90px;background: white;height: 250px;overflow-y: auto;display:none;}
	.btn{padding: 10px;border-radius: 10px;}
	/*td{border:1px solid;}*/
	.mnu{text-decoration: none; position: absolute; padding: 10px;border:1px solid white;background: gray;top:15px;color: white;left: 45%;}
	.informativo{font-size: 20px;}
	.entrada{padding:10px;height:30px;width:100px;border-radius: 5px;font-size: 20px;}
	.filtro{padding:10px;border-radius: 5px;}
	#listado_cheque_transferencia{position: absolute;left: 3px;top: 50%;width: 24%;height: 250px;border: 1px solid #83B141;background: white;display: none;}
	.subtabla{font-size: 20px;background:#83B141; }
	#emergente{position: absolute; z-index:10;background: rgba(0,0,0,.7);width: 100%;height: 100%;top:0;left: 0;display: none;}
	#contenido_emergente{align-items: center;position: absolute;width: 60%;left:20%;height: 50%;top:20%;border-radius: 20px;border:1px solid white;background: rgba(0,0,0,.4);}
	.btn_cierra{border-radius: 50%;padding: 12px;color: white;background: red;position: absolute;top:18.5%;left:79%;z-index:100;}
	.td_oculto{display:none;}
	.opc_buscador{padding: 10px;}
	#referencia_cheque_transferencia{width: 50%; height: 150px;}
	input[type=number]::-webkit-inner-spin-button, 
	input[type=number]::-webkit-outer-spin-button { 
	-webkit-appearance: none; 
  	margin: 0; 
	}
	input[type=number] { -moz-appearance:textfield; }
/*	.deshabilita{width: 100%;height: 100%;background: red;position: absolute;top:0;}*/
</style>
</head>
<body onload="document.getElementById('buscador').focus();">
<div class="global">
<!--emergente-->
	<div id="emergente">
		<button class="btn_cierra" onclick="document.getElementById('emergente').style.display='none';">X</button>
		<div id="contenido_emergente">	
		</div>
	</div>

	<div class="header">
		<table  width="100%" style="color:white;font-size: 25px;">
			<tr>
				<td>
					<b>Cobro de Tickets</b>	
				</td>
				<td align="right">
					<b>Sucursal:</b> <?php echo $sucursal;?><br>
					<b>Cajero:</b> <?php echo $usuario;?>					
				</td>
			</tr>
		</table>
	</div>
	<div class="contenido" align="center">
	<!---->
	<table id="listado_cheque_transferencia">
						<tr style="height: 30px;">
							<th class="subtabla">Banco</th>
							<th class="subtabla">Monto</th>
							<th class="subtabla">observaciones</th>
						</tr>
						<tr></tr>
					</table>
	<input type="hidden" id="no_cheque_transferencia" value="0">

		<table width="50%" border="0">
			<tr>
				<td align="left" width="50%">
					<p class="informativo" align="left">Buscar:<br><input type="text"  id="buscador" placeholder="Folio..." onkeyup="busca(event);">
					<button title="Buscar de nuevo" onclick="link(2);">
						<img src="../../../../img/ver.png" width="25px">
					</button>
					<!--<img src="../../../../img/especiales/buscar.png" width="50px"></p>-->
					<div id="res_busc"></div>
				</td>
				<td width="25%">
					<p class="informativo" align="center">Monto:<br><input type="text" id="monto_total" class="entrada_num" style="background:white;" disabled></p>
				</td>
				<td width="25%">
					<p class="informativo" align="center">Saldo a favor:<br><input type="text" id="saldo_favor" class="entrada_num" style="background:white;" disabled></p>
				</td>
			</tr>	
		</table>
		<input type="hidden" id="id_venta" value="0">
		<input type="hidden" id="venta_pagada" value="0">
	<!---->
		<table width="50%" class="pagos">
			<tr>
				<th width="70%">
					Tipo de Pago
				</th>
				<th width="30%">
					Monto
				</th>
			</tr>
		<?php
			echo $tarjetas_cajero;
		?>
	
			<tr>
				<td align="center"><b>Efectivo:</b></td>
				<td align="left"><input type="number" id="efectivo" class="entrada"  onkeydown="prevenir(event);" onkeyup="valida_tca(this,event,2);calcula_cambio();"></td>
			</tr>
			<tr>
				<td align="center"><b>Recibido:</b></td>
				<td align="left"><input type="number" id="efectivo_recibido" class="entrada" onkeydown="prevenir(event);" onkeyup="valida_tca(this,event,3);calcula_cambio();"></td>
			</tr>
			<tr>
				<td align="center"><b>Cambio:</b></td>
				<td align="left"><input type="number" id="efectivo_devolver" class="entrada" style="background: white;" disabled></td>
			</tr>
			<tr>
				<td align="center"><b>Cheque o transferencia</b><br><?php echo $cajas;?></td>
				<td align="left"><input type="number" id="monto_cheque_transferencia" class="entrada"><button onclick="agrega_cheque_transferencia();">Agregar</button></td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<button id="cobrar" class="btn"  onclick="cobrar();">
						<img src="../../../../img/especiales/imprime.png" width="30px""><br>
						Cobrar e Imprimir
					</button>
				</td>
			</tr>
		</table>
	</div>
	<div class="footer">
		<a href="javascript:link(1);" class="mnu">
			Regresar al panel
		</a>
		
	</div>
</div>
</body>
</html>
<script type="text/javascript">
var total_cobros=0,monto_real=0;
	function link(flag){
		if(flag==1 && confirm("Realmente desea regresar al panel?")==true){
			location.href='../../../../index.php?';
		}
		if(flag==2 && confirm("Realmente desea buscar otra venta para cobrar?")==true){
			location.reload();
		}
	}
	function valida_tca_opc(e,num){
		var tca=e.keyCode;
			//alert(num);
	//enter
		if(tca=='13'){
			$("#opc_"+num).click();return true;
		}
	//tecla arriba
		if(tca=='38'){
			if(num==1){
				$("#buscador").focus();return true;
			}else{
				$("#opc_"+parseInt(parseInt(num)-1)).focus();return true;
			}
		}
	//tecla abajo
		if(tca=='40'){
			$("#opc_"+parseInt(parseInt(num)+1)).focus();return true;	
		}
	}

	function marca(num){
		$("#opc_"+num).css("background","rgba(0,0,225,.5)");
	}

	function desmarca(num){
		$("#opc_"+num).css("background","white");
	}

	function busca(e){
		/*if(e.keyCode==40){
			$("#opc_1").focus();return true;
		}*/
		var txt=$("#buscador").val();
		if(txt.length<=2){
			$("#res_busc").html("");
			$("#res_busc").css("display","none");
			return true;
		}
		//alert(e.keyCode);
	if(e.keyCode==13 || e.keyCode==40){
	//enviamos detos por ajax
		$.ajax({
			type:'post',
			url:'cobrosBd.php',
			cache:false,
			data:{flag:'buscador',valor:txt},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert(dat);return false;
				}else{
					$("#res_busc").html(aux[1]);
					$("#res_busc").css("display","block");
					if(e.keyCode==13){
						$("#opc_1").click();
					}else if(e.keyCode==40){
						$("#opc_1").focus();return true;
					}
				}
			}		
		});
	}
	}

	function carga_pedido(id,pagado){
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'cobrosBd.php',
			cache:false,
			data:{flag:'carga_datos',valor:id},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert(dat);return false;
				}else{
					$("#efectivo").val(aux[3]-aux[4]);
					$("#monto_total").val(aux[3]);
					$("#buscador").val(aux[2]);
					$("#id_venta").val(aux[1]);
					$("#saldo_favor").val(aux[4]);
					$("#venta_pagada").val(pagado);
					$("#buscador").attr('disabled','true');
					$("#res_busc").html('');
					$("#res_busc").css("display","none");
					total_cobros=aux[3]-aux[4];
					//alert(aux[3]);
					monto_real=aux[3]-aux[4];
				}
			}		
		});
	}

	function prevenir(e){
		var tca=e.keyCode;
		if(tca==38||tca==40){
			event.preventDefault();
		}
	}

	function valida_tca(obj,e,flag,num){
	//sacamos el id del objeto
	//sacamos el evento
		var tca=e.keyCode;
	//campos de tarjetas (afiliaciones)
		if(flag==1){
			if(tca==40 && document.getElementById("t"+parseInt(num+1))){
				$("#t"+parseInt(num+1)).select();
			}
			
			if(tca==38 && document.getElementById("t"+parseInt(num-1))){
				//if(num==1)//alert();$("#buscador").focus();return true;}
				$("#t"+parseInt(num-1)).select();
			}

			if(num==$("#cantidad_tarjetas").val() && tca==40){//!document.getElementById("t"+parseInt(num-1)
				//alert(tca);
				$("#efectivo").select();
			}
		}
	//campo de efectivo
		if(flag==2){
			if(tca==40){$("#efectivo_recibido").focus();return true;}
			if(tca==38){$("#t"+$("#cantidad_tarjetas").val()).select();}
		}
	//campo de efectivo recibido
		if(flag==3){
			if(tca==38){$("#efectivo").select();}
		}
		recalcula();
		return true;
	}

	function recalcula(){
		total_cobros=0;
		var total_tarjetas=0,total_cheques=0,a_favor=0,total=0,monto_total=0;
		//var recibido=$("#efectivo_recibido").val();
	//sacamos los pagos por tarjeta
		var tope_tarjetas=$("#cantidad_tarjetas").val();
		for(var i=1;i<=tope_tarjetas;i++){
			if($("#t"+i).val()!=''){
				total_tarjetas+=parseFloat($("#t"+i).val());
			}else{
				$("#t"+i).val(0);
			}
		}
		total_cobros+=parseFloat(total_tarjetas);
	//	alert(total_tarjetas);
	//total de pagos con cheque/transferencias
		var tope_cheques=$("#no_cheque_transferencia").val();
		for(var i=1;i<=tope_cheques;i++){
			if($("#monto_"+i).html()!=''){
				total_cheques+=parseFloat($("#monto_"+i).html());
			}else{
				$("#monto_"+i).html(0);
			}
		}
		
		total_cobros=total_cobros+parseFloat(total_cheques);

	//extraemos e monto a favor
		a_favor=$("#saldo_favor").val();
		if(a_favor==''){a_favor=0;}
		a_favor=parseFloat(a_favor);
	//extraemos el monto total
		monto_total=$("#monto_total").val();
		if(monto_total==''){monto_total=0;}
		monto_total=parseFloat(monto_total);
		//alert(monto_total+'\n'+total_cobros);
		total=monto_total-(total_cobros+a_favor);
		//alert('total:'+total);
		$("#efectivo").val(total);
		total_cobros=total_cobros+total;
		calcula_cambio();
	}

	function calcula_cambio(){
		var total_tarjetas=0,total_cheques=0,total_cobros=0;
		var recibido=$("#efectivo_recibido").val();
		var devolver=$("#efectivo_devolver").val();
		if(recibido<=0){
			return true;
		}
		total_cobros+=parseFloat($("#efectivo").val());
		$("#efectivo_devolver").val(parseFloat(recibido-total_cobros));
	}
	
	/*Agregar cheque o transferencia*/
	function agrega_cheque_transferencia(){
	//obtenemos el valor de la caja
		var id_caja=$("#caja_o_cuenta").val();
		if(id_caja==0){
			alert("Elija una cuenta valida!!!");
			$("#caja_o_cuenta").focus();
			return false;
		}
		var txt_select=$('#caja_o_cuenta option:selected').text();
		//alert(txt_select);
	//obtenemos el monto
		var monto=$("#monto_cheque_transferencia").val();
		if(monto<=0){
			alert("El monto no puede ir vacío!!!");
			$("#monto_cheque_transferencia").focus();
			return false;
		}
	//obtenemos la referencia
		var observacion='<p style="color:white;font-size:30px;" align="center">Ingrese la referencia del Cheque/Transferencia</p>';
		observacion+='<p align="center"><textarea id="referencia_cheque_transferencia"></textarea>';
		observacion+='<br><br><button class="boton" onclick="agregar_fila('+id_caja+','+monto+',\''+txt_select+'\')">Aceptar</button></p>';
		$("#contenido_emergente").html(observacion);
		$("#emergente").css("display","block");
		return true;
	}
var cont_cheques_transferencia=0;
	function agregar_fila(caja,monto,texto){
		var observacion=$("#referencia_cheque_transferencia").val();
		if(observacion.length<=0){
			alert("La referencia no puede ir vacía!!!");
			return false;
		}
		cont_cheques_transferencia+=1;
		var tabla=$("#listado_cheque_transferencia");
		var htmlTags='<tr>'+
        '<td id="caja_'+cont_cheques_transferencia+'" class="td_oculto">'+caja+'</td>'+
        '<td align="left">'+texto+'</td>'+
        '<td id="monto_'+cont_cheques_transferencia+'" align="center">'+monto+'</td>'+
        '<td id="referencia_'+cont_cheques_transferencia+'" align="left">'+observacion+'</td>'+
      '</tr>';
     	tabla.append(htmlTags);

    	$("#caja_o_cuenta option[value=0]").attr("selected",true);//reseteamos el combo de banco
    	$("#monto_cheque_transferencia").val(0);//reseteamos el valor del campo monto
     	$("#contenido_emergente").html("");//limpiamos la emergente
     	$("#emergente").css("display","none");//ocultamos la emergente
		var cont=parseInt(parseInt($("#no_cheque_transferencia").val())+1);
		$("#no_cheque_transferencia").val(cont);
		$("#listado_cheque_transferencia").css('display','block');
		recalcula();
	}

		function cobrar(){
			var id=$("#id_venta").val();
			//alert(id);
			if(id==0){
				alert("Es necesario que seleccione un folio de pedido antes de continuar!!!");
				$("#buscador").focus();
			}
			if(total_cobros!=monto_real){
				alert("La suma de los pagos es diferente al monto total;\nverifique sus atos y vuelva a intentar!!!"+monto_real+"\n"+total_cobros);return false;
			}
			var cantidad_tarjetas=$("#cantidad_tarjetas").val();
			var cantidad_cheque=$("#no_cheque_transferencia").val();
			var id_corte=$("#id_venta").val();
			var tarjetas='',cheques='',efectivo=0,cambio=0,recibido=0;
		//no dejamos crrrar venta si el pago es menor a el monto total y la venta no es apartado
			/*if(total_cobros<$("#monto_total").val() && $("#venta_pagada").val()==1 ){
				alert("Esta nota debe ser saldada!!!");
				$("#efectivo").focus();
				return false;
			}*/
		//extraemos los valores de las tarjetas
			for(var i=1;i<=cantidad_tarjetas;i++){
				if($("#t"+i).val()!=0){
					tarjetas+=$("#tarjeta_"+i).val()+'~';//id de afiliacion
					tarjetas+=$("#t"+i).val()+'°';//monto
				}
			}
		//extraemos los valores de las tarjetas
			for(var i=1;i<=cantidad_cheque;i++){
				cheques+=$("#caja_"+i).html()+'~';//id de banco
				cheques+=$("#monto_"+i).html()+'~';//monto
				cheques+=$("#referencia_"+i).html()+'°';//monto
			}
//			alert(tarjetas+"\n"+cheques);
		//efectivo
			efectivo=$("#efectivo").val();

			cambio=$("#efectivo_devolver").val();
			recibido=$("#efectivo_recibido").val();
		//	alert(cambio);
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'cobrosBd.php',
				cache:false,
				data:{flag:'cobrar',efe:efectivo,camb:cambio,recib:recibido,tar:tarjetas,chq:cheques,id_venta:id_corte},
				success:function(dat){
					var aux=dat.split("|");
				//	alert(dat);
					location.reload();
				}
			});
		}

</script>