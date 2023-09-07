<?php
	include('conexionSincronizar.php');
	//echo 'id_sucursal: '.$sucursal_id;
//consultamos cuando fue la ultima sincronizacion
	$sqlAct="SELECT ultima_sincronizacion from ec_sincronizacion WHERE id_sincronizacion=1";
	$ejecAct=mysql_query($sqlAct,$local) or die(mysql_error($local));
	$fecha=mysql_fetch_row($ejecAct);
	$ultimaSinc=$fecha[0];
//
	$sql="SELECT s.id_movimiento_sincroniza,s.fecha_sincronizacion,s.tipo_movimiento,s.tabla,s.sincronizar,s.dato
		FROM ec_movimientos_sincronizacion s 
		WHERE s.id_sucursal_afecta=4 AND fecha_sincronizacion>'$ultimaSinc'";
//ejevcutamos consulta
	$ejecuta=mysql_query($sql, $linea);
	$num=mysql_num_rows($ejecuta);
	if($num<1){//si no hay resultados
		//die('no hay datos a sincronizar');//terminamos programa con mensaje
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<div style="float:left;width:8%;position:fixed;">
		<img src="img/<?php echo $icono;?>" height="52px" width="60px" onclick="info(1)" 
		style="background:white;border:2px solid rgba(0,0,225,.4);padding:5px;" title="<?php echo $titulo;?>">
	</div>
<center>

	<div id="general" style="border:0;width:95%;display:none;float:right;"><!--border-radius:10px;-->
	<input type="hidden" id="sucursal" value="<?php echo $sucursal_id;?>">
		<table border="0" width="100%">
			<tr style="background:<?php echo $estadoConexion;?>;">
				<td align="center">
					<input type="checkbox" id="inventarios" onclick=""><?php echo' <font color="white">';?>Sincronizar Inventarios</font>
				</td>
				<!--<td align="right">
					<input type="button" value="Actualizar Fecha" onclick="refrescarFecha();" style="padding:7px;border-radius:5px;">					
				</td>-->
				<td align="right">
					<span style="color:white;">Ultima sincronización: </span>
					<input type="text" id="lastSinc" value="<?php echo $ultimaSinc;?>" style="border-radius:5px;width:150px;"
					disabled>
				</td>
				<td align="center">
					<input type="button" id="botonSincro" value="Sincronizar" onclick="sincroniza();" style="padding:7px;border-radius:5px;">
				</td>	
			</tr>
		<!--	<tr style="background:rgba(0,0,225,.3);">
				<td colspan="3" align="center" style="background:transparent;" id='progreso'>
					<img src="img/pestaña.png" height="10px" width="100px" padding="0" onclick="info()">
				</td>
			</tr>-->
			<tr>
				<td colspan="3" style="background:rgba(0,0,255,.2);">
					<div id="infSinc" onclick="info();" style="width:100%;"></div>
				</td>
			</tr>
		</table>
	</div>

	<div id="progreso" style="width:100%;height:150%;position:absolute;background:rgba(0,225,0,.5);display:none;"><!--display:none;-->
		<div id="informacion" style="border:0;margin:0;top:0;background:rgba(0,0,225,0.4);position:relative;height:300px;width:90%;">
			<p style="font-size:50px;">
				<b>Sincronizacion en Proceso...</b>
			</p>
			<p id="imgSinc">
				<img src="img/load_sync.gif" height="400px" width="400px" border="1px" padding="0" onclick="info();">
			</p>
		</div>
	</div>
</center>
</body>
<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript">
//funcion que realiza el proceso de sincronización		
var sincro=0;
var sw1=0;
var sw=0;
var id_suc=document.getElementById('sucursal').value;
function refrescarFecha(){
	$.ajax({
		type:'post',
		url:'code/especiales/sincronizacion/ajax/actualizaFecha.php',
		cache:false,
		data:{},
		success:function(datos){
			if(datos=='no'){
				location.reload();
			}else{
				if(document.getElementById('lastSinc').value=datos){
					if($('#lastSinc').html(datos)){

   						 if(document.getElementById('botonSincro').disabled=false){
   						 	alert('si');
   						 }
					}
				}
			}
		}
	});
	return false;
}
function sincroniza(){
   //deshabilitamos botón
    document.getElementById('botonSincro').disabled=true;
    document.getElementById('progreso').style.display='block';//mostramos emergente
    document.getElementById('botones').style.display='none';//ocultamos menu
    var inv;
    if(document.getElementById('inventarios').checked==true){
    	inv=1;
    }else{
    	inv=0;
    }
	var S=document.getElementById('lastSinc').value;
	$('#infSinc').css.display='none';
	if($('#imgSinc').html('<center><img src="img/load_sync.gif" height="20%" width="20%" padding="0"></center>')){

	}else{
		$('#infSinc').html('<center><img src="img/load_sync.gif" height="20%" width="20%" padding="0"></center>');
	}
//alert('inventarios: '+inv);
	//return false;
//enviamos datos a ajax
		$.ajax({
			type:'post',
			url:'code/especiales/sincronizacion/bajaActualizaciones.php',
			cache:false,
			data:{ultimaSinc:S,sucursal_id:id_suc,sInv:inv},
			success: function datos(datos){
				if(datos=='Servidor ocupado'){
					alert('Hay una sincronizacion en proceso, Intente mas tarde!!!');
					if(document.getElementById('botCierra').style.display="block");	
				}
				$('#informacion').html(datos);
				if(document.getElementById('botCierra')){

				}
				//document.getElementById('infSinc').style.display='none';
				//$('#progreso').html('<center><a href="JavaScript:info();"><img src="img/info.png" height="50px" width="50px" padding="0"></a></center>');
				var S=document.getElementById('lastSinc').value;
				//sw=1;
				//info();
				}
			});
	}

function switchSinc(){
	if($('#sinc').checked==true){
		sincro=1;
		alert('activado');
	}else{
		sincro=0;
		alert('descativado');
	}

	if(sincro==1){
		setInterval('ejemplo()', 10000);
	}
}
function cierraSinc(){
	location.reload();
	refrescarFecha();
	document.getElementById('botones').style.display="block";
	document.getElementById('progreso').style.display="none";
}
function info(flag){
	if(flag==1){
		if(sw1==0){
			document.getElementById('general').style.display='block';
			sw1=1;
			//	window.scrollTop(0);
		}else{
			document.getElementById('general').style.display='none';
			sw1=0;
		}
	}
	if(sw==0){
		//alert('esconde');
		document.getElementById('infSinc').style.display='none';
		sw=1;
	}else{
		//alert('muestra');
		document.getElementById('infSinc').style.display='block';
		sw=0;
	}
}

function ejemplo(){
	alert('ejemplo automatico');
}
</script>
</html>
