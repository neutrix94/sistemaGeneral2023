<?php
	$redirect="SI";
	include("../conectMin.php");
//verificamos que haya sesion de caja
	/*implementación Oscar 04.06.2019 para validar que haya un logueo de cajero en la sucursal*
	//sacamos la fecha actual desde mysql
		$sql="SELECT DATE_FORMAT(now(),'%Y-%m-%d')";
		$eje=mysql_query($sql)or die("Error al consultar la fecha actual!!!");
		$fecha_actual=mysql_fetch_row($eje);
	//comprobamos que haya una sesion abierta en el dia actual
		$sql="SELECT count(*) FROM ec_sesion_caja WHERE fecha='$fecha_actual[0]' AND id_sucursal=$user_sucursal AND hora_fin='00:00:00'";
		$eje=mysql_query($sql)or die("Error al verificar que haya sesión de caja abierta!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
		if($r[0]<1){
			die("<script>alert('Pida al cajero que inicie sesion de caja para poder acceder a esta pantalla!!!');location.href='../index.php?';</script>");
		}
*/
	$sql="SELECT id_sucursal FROM sys_sucursales WHERE acceso=1
		UNION
		SELECT permite_ventas_linea FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
	$eje=mysql_query($sql)or die("Error al consultar si se puede iniciar sesion de caja en linea!!!<br>".mysql_error());
	$r=mysql_fetch_row($eje);
	$r1=mysql_fetch_row($eje);
	if($r[0]==-1 && $r1[0]==0 && $perfil_usuario!=1 && $perfil_usuario!=5 && $_GET['scr']!='paquetes'){

		echo '<script type="text/Javascript">';
			echo 'alert("No se puede a ventas desde el sistema en linea; hagalo localmente o contacte al administrador!!!");';
			echo 'location.href="../"';
		echo '</script>';
		return;
	}

	header("Content-Type: text/html;charset=utf-8");
	mysql_set_charset("utf8");
	if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
	
	extract($_GET);
	
	$scr = isset($scr) ? $scr : "home";
	
	if(!(file_exists("inc/{$scr}-php-header.inc") || file_exists("inc/{$scr}.inc"))) $src = "error404";
	
	if(file_exists("inc/{$scr}-php-head.inc")) include "inc/{$scr}-php-head.inc";
	
?>
<!DOCTYPE html>
<html>
<head>
<!--Inmplementación Oscar 26.11.2018 para emergente-->
<style type="text/css">
	#emergente_bloqueo{position:fixed;width:100%;height:100%;background:rgba(0,0,0,.6);top:0;left:0;z-index:200;display:none;}
</style>
<!--Fin de cambio Oscar 26.11.2018-->

<meta http-equiv="Expires" content="0">
 
<meta http-equiv="Last-Modified" content="0">
 
<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
 
<meta http-equiv="Pragma" content="no-cache">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,height=device-height, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<base href="http://<?php echo $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/"; ?>" />
	
	<title>Casa de las luces aplicación</title>
	
	<link href="casaluces.css" rel="stylesheet" type="text/css"/>
	<link rel="stylesheet" href="../jquery-mobile/jquery.mobile.structure-1.3.2.min.css"/>
    <link rel="stylesheet" href="../css/grid_touch_nuevo.css" />
	<script src="../jquery-mobile/jquery-1.8.3.min.js" type="text/javascript"></script>
	<script src="../jquery-mobile/jquery.mobile-1.3.0.min.js" type="text/javascript"></script>
	<script src="js/funciones.js"></script>
<!--Implementación de librería creada por Oscar para usar un cuadro de texto como password 10.11.2018-->
	<script type="text/javascript" src="../js/passteriscoByNeutrix.js"></script>
<!---->
	<?php if (file_exists("inc/{$scr}-html-head.inc")) include "inc/{$scr}-html-head.inc"; elseif (file_exists("inc/html-head.inc")) include "inc/html-head.inc"; ?>
	
</head>
<body style="width:99%;" onload="focusCampo();verificaTono();">

<!--implementación Oscar 26.11.2018-->
	<div id="emergente_bloqueo">
		<center><br><br><br><br><br><br><br><br><br><br>
			<b style="font-size:30px;color:white;">Cargando...</b><br><br>
			<img src="../img/img_casadelasluces/load.gif" width="150px">
		</center>
	</div>
<!--Fin de cambio Oscar 26.11.2018-->

<div id="page" data-role="page" style="width:99.5%;height:100%;margin:0;padding:0;"><!---->
<!--incluimos el menu de la parte superior-->
  <?php include "inc/top.inc"; ?>
  <!--Contenido-->
  <div id="d1">

  <?php if (file_exists("inc/{$scr}-html-content.inc")) include "inc/{$scr}-html-content.inc"; ?><!--aqui se incluye el archivo que llega desde el menu por cabecera-->
  <!--<div id="d2" style="">
  	prueba de Oscar
  </div>-->
	<?php include "inc/footer.inc"; ?>
	<?php $thisFlag=""?>
  </div>
<!--div auxiliar de prueba Oscar 05-11-2017-->
<div class="eme" id="emergePermisos" style="width:100%;height:160%;position:absolute;top:0;background:rgba(0,0,0,.8);display:none;z-index:100;">
  <br><br>
  <center>
  	<div style="position:absolute;float:right;top:10px;right:10px;width:70px;height:70px;">
  		<input type="button" onclick="cierraEmer();" value="X" title="cancelar"
  		style="padding:10px;background:red;border-radius:5px;font-size:30px;;width:70px;height:70px;">
  	</div>
  <div id="contEmerge">
  	<p style="font-size:20px;color:white;"><br><br><br><br>Ingrese su folio de Transferencia:</p>
    <input class="pa" id="clave_linea_o_transferencia" type="text" style="width:25%;padding:15px;border-radius:10px;height:27px;font-size:28px;">

  	<p style="font-size:20px;color:white;"><br><br><br><br>Ingrese la clave de precio de mayoreo:</p>
    <input class="pa" id="clave_lista_precio" type="text" style="width:25%;padding:15px;border-radius:10px;height:27px;font-size:28px;">
  <!--Implementación Oscar 10.11.2018-->
  		<input type="hidden" id="passWord" value="">
  <!--Fin de cambio Oscar 10.11.2018-->


    <p style="font-size:20px;color:white;">Ingrese su contraseña para venta de mayoreo</p>
    <input class="pa" id="passWord_1" type="text" onkeydown="cambiar(this,event,'passWord');" style="width:25%;padding:15px;border-radius:10px;height:27px;font-size:28px;">


  	<p id="botEmergente"><br><input type="button" value="Acceder" onclick="verificaMayoreo();"
  		style="padding:15px;border-radius:5px;font-size:20px;width:27%;"></p>
  	<input type="hidden" value="" id="cambiaFunc">
  </center>
  </div> 
</div>
<input type="hidden" id="pss" value="<?php echo $mayoreo;?>">

<!--implementación de emergente universal Oscar 09.04.2018-->
<div id="emer_avisos" style="position:absolute;z-index:5;width:100%;height:130%;background:rgba(0,0,0,.6);display:none;"></div>
<script>
	function focusCampo(){
		if(document.getElementById("buscadorLabel"))
			document.getElementById("buscadorLabel").focus();
	}	
	function cierraEmer(){
		document.getElementById('emergePermisos').style.display="none";
	//desocultamos menu y botones
   		$('.ui-btn-inner').css('display','block');
   		$('#pie').css('display','block');
   		document.getElementById('passWord').value="";
   	//habilitamos check y botones extras
   		if($('#es_regalo')){$('#es_regalo').css('display','block');}
  		if($('#es_paquete')){$('#es_paquete').css('display','block');}
   		if($('#cerrar')){$('#cerrar').css('display','block');}
   		if($(".ui-controlgroup-controls")){$(".ui-controlgroup-controls").css("display","block")}
		return false;
	}
	function verificaMayoreo(){
		var url_ajax="ajax/verificaPassEncargado.php?flag=mayoreo";
		if(document.getElementById("clave_lista_precio")){
			if($("#clave_lista_precio").val().length<=0){
				alert("La clave de lista de precio no puede ir vacia!!!");
				$("#clave_lista_precio").focus();
				return false;
			}
			url_ajax+="&clave_may="+$("#clave_lista_precio").val();
		}

		if($("#passWord").val().length<=0){
			alert("La contraseña de venta de mayoreo no puede ir vacía!!!");
			$("#passWord_1").focus();
			return false;
		}
		url_ajax+="&clave="+$("#passWord").val();
		
		if(document.getElementById('clave_linea_o_transferencia')){
			if($("#clave_linea_o_transferencia").val().length>0){
					url_ajax+="&clave_tr_vta="+$("#clave_linea_o_transferencia").val();
				}
		//alert(url_ajax);
		}

//alert("ajax/verificaPassEncargado.php?flag=mayoreo&clave_may="+$("#clave_lista_precio").val()+"&clave="+$("#passWord").val());return false;
	//validamos que lo datos sean correctos
		$.ajax({
			type:'GET',
			url:url_ajax,
			cache:false,
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert(dat);return false;
				}else{
					sa=1;
					if(document.getElementById('tipo_venta')){
						if(document.getElementById('tipo_venta').value==1){
							var obj =document.getElementById("cerrar");
							if (obj){
  				 				obj.click('1');
							}
						}
						return false;
					}
					var url_Tmp='index.php?scr=nueva-venta&tv=1&aWRfcHJlY2lv='+aux[1];
					if(aux[2]!=''){
						url_Tmp+='&dHJhbnNmZXJlbmNpYQ='+aux[2];
					}
					location.href=url_Tmp;
				}
			}
		});
		//alert(resp);return false;
		var ver=document.getElementById('cambiaFunc').value;
		var pass=document.getElementById('passWord');
		var p=document.getElementById('pss').value;
	}

//con esta funcion hacemos cambio de color de interface
    function verificaTono(){
        if(!document.getElementById('tipo_venta')){
        	return false;
        }
        var obj=document.getElementById('tipo_venta');
        if(obj.value==""){
            //alert('ventaNormal');
            return false;
        }
        //alert('VENTA POR MAYOREO');
        $('.ui-btn-inner').css('background','red');
        $('.cabecera').css('background','red');
        $('.cabecera').css( 'border-color','white');
        $('#pie').css('background','red');
      }

</script>

</body>
</html>
