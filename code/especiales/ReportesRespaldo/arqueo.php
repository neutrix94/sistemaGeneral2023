
<?php
	include('../../../conectMin.php');
	$llave='';
	if(isset($_GET['id_corte'])){
		$llave=$_GET['id_corte'];
	}

		$sql="SELECT id_sesion_caja,folio,fecha,hora_inicio FROM ec_sesion_caja WHERE "; //IF('$llave'!='',(id_cajero='$user_id' AND hora_fin='00:00:00'), id_sesion_caja='$llave') LIMIT 1";
		
		if($llave==''){
			$sql.="id_cajero='".$user_id."' AND hora_fin='00:00:00'";
		}else{
			$sql.="id_sesion_caja=".$llave;
		}
	//	die($sql);
		$eje=mysql_query($sql)or die("Error al consultar datos de la sesion de caja!!!<br>".mysql_error());
		$r=mysql_fetch_row($eje);
		$id_sesion_caja=$r[0];
		$info_folio=' disabled value="'.$r[1].'"';
		$fecha_sesion=$r[2];
		$hora_inicio_sesion=$r[3];
		echo '<input type="hidden" id="id_sesion" value="'.$id_sesion_caja.'">';
		echo '<input type="hidden" id="fecha_del_corte" value="'.$fecha_sesion.'">';
		$info_completa_sesion='Fecha: '.$fecha_sesion.' Hora de inicio: '.$hora_inicio_sesion;
/*	if(isset($_GET['id_corte'])){
		$llave=$_GET['id_corte'];
		$sql="SELECT folio,fecha FROM ec_sesion_caja WHERE id_sesion_caja=$llave";
		$eje=mysql_query($sql)or die("Error al consultar datos del corte<br>".mysql_error());
		$r=mysql_fetch_row($eje);
		$folio=$r[0];
		$info_folio=' disabled value="'.$folio.'"';
		echo '<input type="hidden" id="id_corte" value="'.$llave.'">';
	}else{
		$sql="SELECT id_sesion_caja,folio,fecha,hora_inicio FROM ec_sesion_caja WHERE id_cajero='$user_id' AND hora_fin='00:00:00' LIMIT 1";
		$eje=mysql_query($sql)or die("Error al consultar datos de la sesion de caja!!!<br>".mysql_error());
		$r=mysql_fetch_row($eje);
		$id_sesion_caja=$r[0];
		$info_folio=' disabled value="'.$r[1].'"';
		$fecha_sesion=$r[2];
		$hora_inicio_sesion=$r[3];
		echo '<input type="hidden" id="id_sesion" value="'.$id_sesion_caja.'">';
		$info_completa_sesion='Fecha: '.$fecha_sesion.' Hora de inicio: '.$hora_inicio_sesion;
	}*/
?>
<!DOCTYPE html>
<html>
<head>
	<title>Arqueo de caja</title>

<link rel="stylesheet" type="text/css" href="../../../css/gridSW_l.css"/>
<script type="text/javascript" src="../../../js/calendar.js"></script>
<script type="text/javascript" src="../../../js/calendar-es.js"></script>
<script type="text/javascript" src="../../../js/calendar-setup.js"></script>
<script type="text/JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
<script type="text/JavaScript" src="../../../js/passteriscoByNeutrix.js"></script>
</head>
<body background="../../../img/img_casadelasluces/bg8.jpg">
	<center>
	<div class="encabezado">Arqueo de Caja <?php echo $info_completa_sesion;?>
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
		</div>
	</center>
</body>
</html>
<style>
	.footer{position:absolute;bottom:0;width:100%;background:#83B141;}
	body{margin: 0;}
	.encabezado{padding:5px;
		background:#83B141;
		width:99%;
		height:30px;
		top:0;
		color:white;
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
	#res_busc{position:fixed;width: 18%;height:250px;background: white;display: none;}
	.subtitulo{background: #F39C12;}
	.btn_add{padding: 6px;border-radius: 50%;color:white;background:#83B141;font-size: 18px; }
</style>
<script type="text/JavaScript">
	function salir(){
		location.href="../../../index.php";
	}
	function activaBusqueda(){
		
	}
	
	function generaTicket(){
		var cantidad_tarjetas=$("#no_tarjetas").val();
		var cantidad_cheque=$("#no_cheque_transferencia").val();
		var id_corte=$("#id_sesion").val();
		var tarjetas='',cheques='',password='',fecha_ultimo_corte='';
		var ingreso_efe=0;
		fecha_ultimo_corte=$("#fecha_del_corte").val();
	//obtenemos la contraseña
		password=$("#password1").val();
		if(password==''){
			alert("La contraseña no puede ir vacía");
			$("#password").focus();
			return true;
			
		}
	//extraemos los valores de las tarjetas
		for(var i=1;i<=cantidad_tarjetas;i++){
			if($("#tarjeta_"+i).val()!=0){
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
		ingreso_efe=$('#i1').val();
		//alert(id_corte);return true;
		/*
		var gast="",cantidades="";
	//extraemos los gastos
		var obj1=document.getElementById('gastos');
        var trs=obj1.getElementsByTagName('tr');
        var hora=document.getElementById('horaFinal').value;
        for(i=1;i<trs.length-2;i++){
            var tds=trs[i+1].getElementsByTagName('td');
            if(tds[3]){
            	gast+=tds[0].innerHTML+"|"+tds[2].innerHTML+"|"+tds[3].innerHTML+"~";
        	}
        }
    //extraemos los datos de dinero                        Posición  
    	cantidades+=document.getElementById('tI').value+"|";  //0
    	cantidades+=document.getElementById('ta1').value+"|"; //1
    	cantidades+=document.getElementById('ta2').value+"|"; //2
    	cantidades+=document.getElementById('i1').value+"|";  //3
    	cantidades+=document.getElementById('tG').value+"|";  //4
    	cantidades+=document.getElementById('efeF').value+"|";//5
   /*implementación Oscar 15.08.2018*
    	if(document.getElementById('efe_ext')){
    		cantidades+=document.getElementById('efe_ext').innerHTML+"|";//6
    	}else{
    		cantidades+='0|';//6
    	}
    /*fin de cambio Oscar 15.08.2018*
    	
    	cantidades+=document.getElementById('ing_int').innerHTML;

    	var registros=document.getElementById('regist').value;
//alert('gastos: \n'+gast+"\ncanidades: \n"+cantidades);
//return false;
    //extraemos fecha
    	var fec=document.getElementById('fechaFinal').value;*/
		$.ajax({
			type:'post',
			url:'ajax/imprimeTicket.php',
			cache:false,
			data:{tar:tarjetas,cheq_trans:cheques,corte:id_corte,pss:password,fcha_corte:fecha_ultimo_corte,efectivo:ingreso_efe},
			success:function(dat){
				if(dat=='ok'){
					alert('Impresion generada');
				}else{
					alert('Ocurrió un problema al imprimir, actualice la pantalla y vuelva a intentar!!!\n'+dat);
					location.reload();
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

		password=$("#password1").val();
		if(password==''){
			alert("La contraseña no puede ir vacía");
			$("#password").focus();
			return true;	
		}
	//extraemos los valores de las tarjetas
		for(var i=1;i<=cantidad_tarjetas;i++){
			if($("#tarjeta_"+i).val()!=0){
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
	//generamos el reporte
		$.ajax({
			type:'post',
			url:'ajax/detalle.php',//'ajax/detalle.php'
			cache:false,
			data:{tar:tarjetas,cheq_trans:cheques,corte:id_corte,pss:password,fcha_corte:fecha_ultimo_corte},
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
	function carga_folio(id){
		alert(id);
		if(confirm("Realmente dese salir sin guardar?")==true){
			location.href="arqueo.php?&id_corte="+id;
		}
	}
/*fin de funciones del buscador*/
	
</script>
