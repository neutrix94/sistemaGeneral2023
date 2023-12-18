<?php
/*version casa 1.0*/
	include('../../../../conectMin.php');
	//$llave='';
//listamos las cajas en efectivo
	$sql="SELECT id_caja_cuenta,nombre FROM ec_caja_o_cuenta WHERE id_tipo_caja=1 AND id_caja_cuenta>0";
	$eje=mysql_query($sql)or die("Error al consultar las cajas en efectivo!!<br>".mysql_error());
	
	$cajas_efe='<select id="caja_destino_efectivo" class="filtro"><option value="0">--SELECCIONAR--</option>';
	while($r=mysql_fetch_row($eje)){
		$cajas_efe.='<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	$cajas_efe.='</select>';
	//echo '<input type="hidden" id="cont_emerg" value="'.$cajas_efe.'">';
//	echo $cajas_efe;
	if(isset($_GET['id_corte'])){
		$llave=$_GET['id_corte'];
	}else{$llave='-1';}

		$sql="SELECT id_sesion_caja,folio,fecha,hora_inicio,IF(hora_fin='00:00:00','23:59:59',hora_fin)as hora_fin FROM ec_sesion_caja WHERE id_sesion_caja=$llave";
		
		$eje=mysql_query($sql)or die("Error al consultar datos de la sesion de caja!!!<br>".mysql_error());
		$r=mysql_fetch_row($eje);
		$id_sesion_caja=$r[0];
		if(isset($_GET['id_corte'])){
			$info_folio=' disabled value="'.$r[1].'"';
		}
		
		$fecha_sesion=$r[2];
		$hora_inicio_sesion=$r[3];
		echo '<input type="hidden" id="id_sesion" value="'.$id_sesion_caja.'">';
		echo '<input type="hidden" id="fecha_del_corte" value="'.$fecha_sesion.'">';
		echo '<input type="hidden" id="hora_de_inicio" value="'.$hora_inicio_sesion.'">';
		echo '<input type="hidden" id="hora_de_cierre" value="'.$r[4].'">';
		$info_completa_sesion='Fecha: '.$fecha_sesion.' Hora de inicio: '.$hora_inicio_sesion;

?>
<!DOCTYPE html>
<html>
<head>
	<title>Validacion de Arqueo de caja</title>

<link rel="stylesheet" type="text/css" href="../../../../css/gridSW_l.css"/>
<script type="text/javascript" src="../../../../js/calendar.js"></script>
<script type="text/javascript" src="../../../../js/calendar-es.js"></script>
<script type="text/javascript" src="../../../../js/calendar-setup.js"></script>
<script type="text/JavaScript" src="../../../../js/jquery-1.10.2.min.js"></script>
<script type="text/JavaScript" src="../../../../js/passteriscoByNeutrix.js"></script>
</head>
<body background="../../../../img/img_casadelasluces/bg8.jpg">
	<center>
	<div class="encabezado"><b>Validación de Arqueo de Caja <?php echo $info_completa_sesion;?></b>
	</div>
<?php
	$sql="SELECT multicajero FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
	$eje_suc=mysql_query($sql)or die("Error al verificar si la sucursal es multicajero!!!");
	$r=mysql_fetch_row($eje_suc);
	$multicajero=$r[0];
//	die($multicajero);
	if($multicajer0==1){
		include('encabezadoMulticajero.php');
	}else{
		include('encabezadoUnicajero.php');
	}
?>

<!--Implementación Oscar 17.06.2019 para meter pantalla emergente-->
	<div id="emergente">
		<div id="contenido_emergente">	
		</div>
	</div>
<!--Fin de cambio Oscar 17.06.2019-->

		<div id="reporte">

		</div>
		<div class="footer">
			<input type="button" value="Regresar al panel" style="padding:8px;" onclick="salir();">	
			<!--<input type="hidden" id="nota_observacion" value="">-->
		</div>
	</center>
</body>
</html>
<style>
	.footer{position:absolute;bottom:0;width:100%;/*background:#83B141;*/}
	body{margin: 0;}
	.encabezado{padding:5px;
/*		background:#83B141;*/
		width:99%;
		height:30px;
		top:0;
		color:black;
		font-size:25px; 
	}
	.boton{
		padding:10px;
		border-radius: 5px;
	}
	.filtro{padding:10px;border-radius: 5px;}
	.entrada{padding:0px;height:40px;width:100px;border-radius: 5px;font-size: 20px;}
	#reporte{border:2px;background: rgba(0,0,0,.2);width:69%;border-radius: 5px;right: 10px;position: absolute;top:120px;}
	.fecha{
		padding:6px;
		width:30%;
	}
	.titulo{
		font-family:Arial;
		font-size: 18px;	
	}
	.hora{
		padding: 10px;
		border-radius: 6px;
	}
	#tarjetas{position:absolute;top:40px;width: 30%;}
	#opciones_arqueo{position: absolute;top:40px;width: 68%;left:30%;border:1px solid;}
	#emergente{position: absolute; z-index:10;background: rgba(0,0,0,.7);width: 100%;height: 100%;top:0;left: 0;display: none;}
	.td_oculto{display:none;}
	#referencia_cheque_transferencia{width: 50%; height: 300px;}
	#listado_cheque_transferencia{top: 0;position: relative;}
	th{padding: 5px;background:#83B141;color: white;height: 30px;}
	#res_busc{position:fixed;width: 18%;height:250px;background: white;display: none;overflow:auto;}
	.subtitulo{background: #F39C12;}
	.btn_add{padding: 6px;border-radius: 50%;color:white;background:#83B141;font-size: 18px; }
</style>
<script type="text/JavaScript">
	function salir(){
		location.href="../../../../index.php";
	}
	function activaBusqueda(){
		
	}
	
	function seleccionar_banco_caja(){
		var contenido='<div style="position:absolute;width:50%;left:25%;top:15%;border:1px solid white;border-radius:20px;background:rgba(0,0,0,.3);">';
		contenido+='<button style="position:absolute;top:-15px; right:-15px;padding:10px;border-radius:50%;background:red;color:white;" title="Cancelar"';
		contenido+=' onclick="document.getElementById(\'emergente\').style.display=\'none\';"><b>X</b></button>';
		contenido+='<p align="center" style="color:white;font-size:30px;">Seleccionar la caja de destino de efectivo:</p><br>';
		contenido+='<?php echo $cajas_efe;?>';
		contenido+='<br><br><textarea style="width:50%;height:250px;" placeholder="Notas/Observaciones" id="nota_observacion"></textarea>';
		contenido+='<br><br>';
		contenido+='<button onclick="generaTicket();" style="padding:10px;">Aceptar</button><br><br></div>';
		$("#contenido_emergente").html(contenido);//$("#cont_emerg").val()
		$("#emergente").css("display","block");

		//generaTicket();

	}

	function generaTicket(){
		var caja_destino=$("#caja_destino_efectivo").val();
		if(caja_destino==0){
			alert("Es necesario que elija la caja de destino del efectivo para continuar!!!");
			$("#caja_destino_efectivo").focus();
			return false;
		}
		var cantidad_tarjetas=$("#no_tarjetas").val();
		var cantidad_cheque=$("#no_cheque_transferencia").val();
		var id_corte=$("#id_sesion").val();
		var tarjetas='',cheques='',password='',fecha_ultimo_corte='';
		var ingreso_efe=0;
	//obtenemos fecha y horas de la sesión de caja
		fecha_ultimo_corte=$("#fecha_del_corte").val();
		var hora_inicio=$("#hora_de_inicio").val();
		var hora_fin=$("#hora_de_cierre").val();

	//obtenemos la contraseña
		password=$("#password1").val();
	//extraemos los valores de las tarjetas
		for(var i=1;i<=cantidad_tarjetas;i++){
			if($("#tarjeta_"+i).val()!=0){
				tarjetas+=$("#tarjeta_"+i).val()+'~';//id de registros de tarjeta
			
		/*Implementacion Oscar 04.12.2019 para evitar el error cuando las tarjetas estan vacias*/
				if($("#t"+i).val()!=''){
					tarjetas+=$("#t"+i).val()+'°';//monto
				}else{
					tarjetas+='0°';//monto					
				}
		/*Fin de cambio Oscar 04.12.2019*/
		
			}
		}
	//extraemos los valores de las tarjetas
		for(var i=1;i<=cantidad_cheque;i++){
				cheques+=$("#caja_"+i).html()+'~';//id de registro de banco
				cheques+=$("#monto_"+i).html()+'~';//monto
				cheques+=$("#referencia_"+i).html()+'~';//referencia
				cheques+=$("#caja_nueva_"+i).html()+'°';//nuevo id de caja
		}
		
	//extraemos los datos del ingreso en efectivo
		//alert($("#monto_en_efectivo").val());return false;

		if($("#monto_en_efectivo").val()!=0){
			ingreso_efe=$("#efectivo_pagos").val()+"~";
			ingreso_efe+=$("#monto_en_efectivo").val()+"~";
			ingreso_efe+=$("#caja_destino_efectivo").val();
			//alert(ingreso_efe);return false;
		}
	//ingresos
		var ingresos='';
		ingresos+=$("#ing_int").html()+'|';
		if(document.getElementById('efe_ext')){
    		ingresos+=$('#efe_ext').html();//6
    	}else{
    		ingresos+='0';//6
    	}
	//extraemos los gastos
		var gast="";
		var obj1=document.getElementById('gastos');
        var trs=obj1.getElementsByTagName('tr');
        var hora=document.getElementById('horaFinal').value;
        for(i=1;i<trs.length-2;i++){
            var tds=trs[i+1].getElementsByTagName('td');
            if(tds[3]){
            	gast+=tds[0].innerHTML+"|"+tds[2].innerHTML+"|"+tds[3].innerHTML+"~";
        	}
        }

		$.ajax({
			type:'post',
			url:'ajax/imprimeTicketValidacion.php',
			cache:false,
			data:{tar:tarjetas,cheq_trans:cheques,corte:id_corte,pss:password,fcha_corte:fecha_ultimo_corte,
				efectivo:ingreso_efe,inicio:hora_inicio,fin:hora_fin,arr_ing:ingresos,gastos:gast,nota_obs:$("#nota_observacion").val()},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]=='ok'){
					alert('Validación exitosa e Impresion generada');
				//abrimos el archivo en pdf
					window.open(aux[1]);
					location.href="validacion_arqueo.php?";
				}else{
					alert(dat);
					alert('Ocurrió un problema al imprimir, actualice la pantalla y vuelva a intentar!!!\n'+dat);
					//location.reload();
				}
			}
		});
	}

	function llenaReporte(){
		var cantidad_tarjetas=$("#no_tarjetas").val();
		var cantidad_cheque=$("#no_cheque_transferencia").val();
		var id_corte=$("#id_sesion").val();
		var tarjetas='',cheques='',password='';
	//sacamos la fecha del corte 
		var fecha_ultimo_corte=$("#fecha_del_corte").val();
		var hora_inicio=$("#hora_de_inicio").val();
		var hora_fin=$("#hora_de_cierre").val();
	//extraemos los valores de las tarjetas
		for(var i=1;i<=cantidad_tarjetas;i++){
			if($("#tarjeta_"+i).val()!=0){
				tarjetas+=$("#tarjeta_"+i).val()+'~';//id de afiliacion
			
			/*Implementacion Oscar 04.12.2019 para evitar el error cuando las tarjetas estan vacias*/
				if($("#t"+i).val()!=''){
					tarjetas+=$("#t"+i).val()+'°';//monto
				}else{
					tarjetas+='0°';//monto					
				}
			/*Fin de cambio Oscar 04.12.2019*/
			
			}
		}
	//extraemos los valores de las tarjetas
		for(var i=1;i<=cantidad_cheque;i++){
				cheques+=$("#caja_"+i).html()+'~';//id de banco
				cheques+=$("#monto_"+i).html()+'~';//monto
				cheques+=$("#referencia_"+i).html()+'°';//monto
		}
//		alert(cheques);
	//generamos el reporte
		$.ajax({
			type:'post',
			url:'ajax/detalle.php',//'ajax/detalle.php'
			cache:false,
			data:{tar:tarjetas,
				cheq_trans:cheques,
				corte:id_corte,
				pss:password,
				fcha_corte:fecha_ultimo_corte,
				inicio:hora_inicio,
				fin:hora_fin,
				ingreso_efect:$("#monto_en_efectivo").val()
			},
			success:function(dat){
				$("#reporte").html(dat);
		//alert(fF);
			}
		});
	}
	function calendario(objeto){
    	Calendar.setup({
    	    inputField     :    objeto.id,
    	    ifFormat       :    "%Y-%m-%d",
        	align          :    "BR",
        	singleClick    :    true
		});
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
		var observacion='<p style="color:white;font-size:30px;">Ingrese la referencia del Cheque/Transferencia</p>';
		observacion+='<textarea id="referencia_cheque_transferencia"></textarea>';
		observacion+='<br><br><button class="boton" onclick="agregar_fila('+id_caja+','+monto+',\''+txt_select+'\')">Aceptar</button>';
		$("#contenido_emergente").html(observacion);
		$("#emergente").css("display","block");
		return true;
	}
var cont_cheques_transferencia=0;
	function agregar_fila(caja,monto,texto){
		cont_cheques_transferencia=parseInt($("#no_cheque_transferencia").val());
		var observacion=$("#referencia_cheque_transferencia").val();
		if(observacion.length<=0){
			alert("La referencia no puede ir vacía!!!");
			return false;
		}
		cont_cheques_transferencia+=1;
//		alert(cont_cheques_transferencia);
		var tabla=$("#listado_cheque_transferencia");
		var htmlTags='<tr>'+
        '<td id="caja_'+cont_cheques_transferencia+'" class="td_oculto">nuevo</td>'+
        '<td align="left">'+texto+'</td>'+
        '<td id="monto_'+cont_cheques_transferencia+'" align="center" onclick="edita_celda(this,1);">'+monto+'</td>'+
        '<td id="referencia_'+cont_cheques_transferencia+'" onclick="edita_celda(this,2);" align="left">'+observacion+'</td>'+
        '<td id="caja_nueva_'+cont_cheques_transferencia+'" class="td_oculto">'+caja+'</td>'+
      '</tr>';
     	tabla.append(htmlTags);

    	$("#caja_o_cuenta option[value=0]").attr("selected",true);//reseteamos el combo de banco
    	$("#monto_cheque_transferencia").val(0);//reseteamos el valor del campo monto
     	$("#contenido_emergente").html("");//limpiamos la emergente
     	$("#emergente").css("display","none");//ocultamos la emergente
		var cont=parseInt(parseInt($("#no_cheque_transferencia").val())+1);
		$("#no_cheque_transferencia").val(cont);
//		alert(cont);
	//si ya existe el botón porque el reporte ya fue genrado recargamos informacion
		if (document.getElementById('btn_cierra_caja')) {llenaReporte();}
	}
		function valida_tca_opc(e,num){
		var tca=e.keyCode;
	//enter
		if(tca=='13'){
			$("#opc_"+num).click();return true;
		}
	//tecla arriba
		if(tca=='38'){
			if(num==1){
				$("#buscador").select();return true;
			}else{
				$("#opc_"+parseInt(parseInt(num)-1)).focus();return true;
			}
		}
	//tecla abajo
		if(tca=='40'){
			$("#opc_"+parseInt(parseInt(num)+1)).focus();return true;	
		}
	}

/*funciones del buscador*/
	function busca(e){
		if(e.keyCode==40){
			$("#opc_1").focus();return true;
		}
		var txt=$("#buscador").val();
		if(txt.length<=2){
			$("#res_busc").html("");
			$("#res_busc").css("display","none");
			return true;
		}
	//enviamos detos por ajax
		$.ajax({
			type:'post',
			url:'ajax/detalle.php',
			cache:false,
			data:{flag:'buscador',valor:txt},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert(dat);return false;
				}else{
					$("#res_busc").html(aux[1]);
					$("#res_busc").css("display","block");
				}
			}		
		});
	}

	function marca(num){
		$("#opc_"+num).css("background","rgba(0,0,225,.5)");
	}

	function desmarca(num){
		$("#opc_"+num).css("background","white");
	}
	function carga_folio(id,hecho){
		//alert(id);
		if(hecho==1){
			if(!confirm("Este corte de caja ya fue validado; si lo modifica va a afectar los movimientos de caja\nRealmente desea continuar?")){
				return false;
			}
		}
		//if(confirm("Realmente dese salir sin guardar?")==true){
			location.href="validacion_arqueo.php?&id_corte="+id;
		//}
	}
/*fin de funciones del buscador*/
/**/
var editando=0;
	function edita_celda(obj,flag){
		if(editando!=0){return false;}
		editando=1;
		var tipo="",campo="",valor_anterior;
	//obtenemos el valor anterior
		valor_anterior=$(obj).html();		
		if(flag==1){tipo="number";}else if(flag==2){tipo="text"}
		campo='<input type="'+tipo+'" id="celda_tmp" value="'+valor_anterior+'" style="width:95%;padding:10px;" onblur="desedita_celda('+$(obj).attr("id")+');">';
		$(obj).html(campo);
		$("#celda_tmp").select();
	}

	function desedita_celda(obj){
	//extraemos el nuevo valor de la caja de texto
		var nvo_valor=$("#celda_tmp").val();
		if(nvo_valor==''){
			alert("Este campo no puede ir vacío!!!");
			$("#celda_tmp").focus();
			return false;
		}
		$(obj).html(nvo_valor);
		editando=0;
		llenaReporte();
	}

	function cambia_valor(obj,id_elemento){
		$("#"+id_elemento).html($(obj).val());
		llenaReporte();
	}

/**/
</script>
