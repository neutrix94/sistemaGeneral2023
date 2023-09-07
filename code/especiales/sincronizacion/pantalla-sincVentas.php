<?php
	/*if(!require('../../../conexionDoble.php')){
		die("Sin Conexión a BD");
	}*/
	if(!require('../../../conectMin.php')){
		die("Sin Conexión a BD");
	}
//verificamos que no haya alguna pestaña de sincronización abierta
	$sql="SELECT en_uso FROM sys_menus WHERE liga='code/especiales/sincronizacion/pantalla-sincVentas.php?'";
	$eje=mysql_query($sql)or die("error al verificar si la ventana está en uso!!!\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_assoc($eje);
	//die("en uso");
	if($r['en_proceso']==1){
		die('<script>alert("Ya hay una pestaña sincronizando, cierrela para poder abrir esta pestaña!!!!");location.href="../../../index.php";</script>');
//die("en uso");
	}else{
	//si esta libre ocupamos el menú
		$sql="UPDATE sys_menus SET en_uso=1 WHERE liga='code/especiales/sincronizacion/pantalla-sincVentas.php?'";
		$eje=mysql_query($sql)or die("error al establecer que la ventana está en uso!!!\n\n".$sql."\n\n".mysql_error());
//	die("ocupa");
	}

//extraemos el intervalo de tiempo de sincronización
	$sql="SELECT intervalo_sinc FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
	$eje=mysql_query($sql) or die("Error al conultar el intervalo de tiempo de sincronización!!!\n\n".$sql."\n\n".mysql_error());
	$row_sinc=mysql_fetch_row($eje);
//formamos la variable oculta
	echo '<input type="hidden" id="int_sinc" value="'.$row_sinc[0].'">';

?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body onload="abreOtra();">

<p style="top:50px;position:fixed;font-size:45px;left:30%;" align="center">
	<b>NO Cerrar Esta ventana!!!!</b>
	<br><img src="../../../img/warning.gif">
</p>
<div id="respuesta" style="width:100%;background-image:url(../../../img/img_casadelasluces/bg8.jpg);height:100%;margin:0;border:0;padding:0;">
	<p>Sincronización de Ventas</p>
</div>
<p style="bottom:35px;position:fixed;right:15px;" align="right">
	<input type="button" value="Regresar al panel" style="padding:10px;border-radius:6px;" onclick="salir(1);">
</p>

</body>
</html>

 <script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>

<script type="text/javascript">
	window.addEventListener("beforeunload", function (e) {
  var confirmationMessage = "\o/";

  (e || window.event).returnValue = confirmationMessage; //Gecko + IE
  return confirmationMessage;                            //Webkit, Safari, Chrome
});

 	window.onbeforeunload=liberaServer;    
	
	function liberaServer(){
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/cierraSincAuto.php',
			cache:false,
			success:function(dat){
				if(dat!='ok'){
					alert("Error!!!\n\n"+dat);
					return 'Error';
				}
				return 'salir';
			}
		});
	}
</script>

<script type="text/javascript">
var interruptor=0; 

	function genera_ticket(flag,id_registro){
		var dir="";
		if(flag==1){//ticket de venta
			dir="";
		}
		if(flag==2){//ticket de pagos
			dir="";
		}
		if(flag==3){//ticket de reimpresión
			dir="";
		}
		if(flag==4){//ticket de devolución
			dir="";
		}

	//enviamos datos por ajax
		$.ajax({
			type:'get',
			url:dir,
			cache:false,
			data:{id_pedido:id_registro},
			success:function(dat){
				//alert('ok');
			}
		});

	}

	function enviaPeticion(){
		//alert('interrupror: '+interruptor);
		if(interruptor==1){
			//alert("El servidor esta ocupado esta busqueda se frena aqui desde JavaScript!!!\n\n;)");
			return false;
		}
	//ocupamos
		interruptor=1;
		//alert('interrupror: '+interruptor);
		$('#respuesta').html('<img src="../../../img/load_sync.gif">');
		$.ajax({
			type:'post',
			url:'../../../touch/ajax/sincronizaVentas.php',
			cache:false,
			data:{},
			success: function(dat){
				//alert("Respuesta:\n"+dat);
				$("#respuesta").html(dat);
				var aux_res=dat.split("|");
				if(aux_res[0]!='ok'){
					//alert("Error!!!"+dat);
				}
				for(var i=1;i<aux_res.length;i++){
					var aux=aux_res[i].split("~");
					for(var j=0;j<aux.length-1;j++){
						if(aux[j]!=""||aux[j]!=null){
							genera_ticket(i,aux[j]);		
						}
					}
				}//fin de for
				interruptor=0;
			}
		});
	//liberamos
	}


	function salir(){
		var c=confirm("ADVERTENCIA!!!!\n\nSi sale de esta pantalla el sistema dejará de sincronizar Ventas...\nRealmenete desea salir de esta pantalla???");
		if(c==false){
			return false;
		}
		window.location.href="../../../index.php";
		return true;
	}

	window.onload=function xD(){
		var tiempo=parseInt($("#int_sinc").val())*1000;//guardamos el valor del intervalo de tiempo 
		//alert(tiempo);
		setInterval('enviaPeticion()',tiempo);
	}
</script>
