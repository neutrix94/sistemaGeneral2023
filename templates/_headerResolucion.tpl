<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
  <title>Casa de las luces</title>
  <link rel="stylesheet" type="text/css" href="{$rooturl}css/estilo_final1.css"/>
   <link rel="stylesheet" type="text/css" href="{$rooturl}css/gridSW_l.css"/>
   <!--link rel="stylesheet" type="text/css" href="{$rooturl}css/cssrumi/css/estilosuperiores.css" />
   <link rel="stylesheet" type="text/css" href="{$rooturl}css/cssrumi/css/gridsuperiores.css" /-->


  <!-- <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script> -->
  <!-- <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script> -->
  	<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>-->
  <script language="JavaScript" type="text/javascript" src="{$rooturl}js/funciones.js"></script>
  <!-- Librerias para el grid-->
  <script language="javascript" src="{$rooturl}js/grid/RedCatGridResoluciones.js"></script>
  <script language="JavaScript" type="text/javascript" src="{$rooturl}js/grid/yahoo.js"></script>
  <script language="JavaScript" type="text/javascript" src="{$rooturl}js/grid/event.js"></script>
  <script language="JavaScript" type="text/javascript" src="{$rooturl}js/grid/dom.js"></script>
  <script language="JavaScript" type="text/javascript" src="{$rooturl}js/grid/fix.js"></script>
  <script type="text/javascript" src="{$rooturl}js/calendar.js"></script>
  <script type="text/javascript" src="{$rooturl}js/calendar-es.js"></script>
  <script type="text/javascript" src="{$rooturl}js/calendar-setup.js"></script>
  <script type="text/javascript" src="{$rooturl}js/buzz.js"></script>
  <script type="text/javascript" src="{$rooturl}js/buzz.min.js"></script>
  <script type="text/javascript" src="{$rooturl}js/menusistema.min.js"></script>
  <script language="javascript" src="{$rooturl}js/presentacion.js"></script>

	<style>
	
	{literal}
	@font-face {
 		 font-family: 'Gafata';
	     font-style: normal;
	  	 font-weight: 400;
		 src: local('Gafata'), local('Gafata-Regular'), url({/literal}{$rooturl}{literal}/css/fuentegafata.woff) format('woff');
	}
	{/literal}
	
	</style>

  
  <script type="text/javascript" src="{$rooturl}js/jquery-1.10.2.min.js"></script>
    <link rel="stylesheet" href="{$rooturl}/css/jquery-ui.css">
<script src="{$rooturl}/js/jquery-ui.js"></script>
   
  
  {literal}
  
  	<script>
	
	var mySound = new buzz.sound({/literal}"{$rooturl}files/caralarm"{literal}, {
   	 formats: [ "mp3" ]
	});
	
	//buzz.all().play();
	
	function buscaAlerta(){
		//Buscamos si hay alertas para el usuario
		{/literal}
		url="{$rooturl}/code/ajax/buscaAlertas.php";
		{literal}
		var res=ajaxR(url);
		aux=res.split('|');
		
		if(aux[0] != 'SI') {
			document.getElementById('IDAutorizacion').value = "";
			var obj=document.getElementById('alertaSistema');
			obj.style.display='none';
		}
		
		if(aux[0] == 'SI')
		{
			buzz.all().play();
			//mySound.play();
			
			document.getElementById('textoAlerta').innerHTML="<b>Motivo: </b>"+aux[2];
			document.getElementById('fechaAlerta').innerHTML="<b>Fecha: </b>"+aux[3];
			document.getElementById('horaAlerta').innerHTML="<b>Hora: </b>"+aux[4];
			document.getElementById('IDAutorizacion').value=aux[6];
			{/literal}
			document.getElementById('linkAlerta').href='{$rooturl}'+aux[5];
			document.getElementById('linkAlerta').target="_blank";
			
			{literal}
			
			document.getElementById('botonCerrarAlerta').onclick=function(){cierraAlerta(aux[1])};
			
			var obj=document.getElementById('alertaSistema');
			obj.style.display='block';
			
		}
	
		//document.getElementById('alertaSistema').style.display='none';
		
	}
	var contMsg=0;
	/*function buscaTransfer(){
		{/literal}
		url="{$rooturl}/code/especiales/sincronizacion/buscaTransfer.php?suc={$sucursal_id}";
		{literal}
		var res=ajaxR(url);
		aux=res.split('|');
		//alert(res);
		if(aux[0] != 'SI') {
			if(aux[0]=='Hay productos no existentes, sincroniza manualmente'){
				
				//window.location.href="{$rooturl}/index.php";
			}
			if(aux[0]!='NO'){
				if(contMsg==1||contMsg==40){
					//alert("Hay productos nuevos pendientes por insertar!!!\n"+"Pregunte si puede sincronizar...");	
					return false;
				}
				if(aux[0]=='servidor ocupado'){
					//alert("El servidor esta en proceso de sincronizacion\n"+"Intente en 5 minutos!!!");
					return false;
				}
				//alert(aux[0]);
			}
		}
		
		if(aux[0] == 'SI'){
			//alert('here'+aux[0]+aux[1]+aux[2]);
			//buzz.all().play();
		//	document.getElementById('folT').value=aux[2];
			{/literal}
			
			{literal}
			var obj=document.getElementById('alertaTrans');
			/*
		DESHABILITADO TEMPORALMENTE*/
		//	obj.style.display='block';
	/*	}
	}

	function cierraAviso(){
		//alert('No olvide que esta transferencia ya quedo registrada y la puede consultar en el módulo de TRANSFERENCIAS');
		document.getElementById('alertaTrans').style.display='none';
		return false;//finalizamos funcion
	}
	
	function cierraAlerta(val){
		{/literal}
		url="{$rooturl}/code/ajax/cancelaAlerta.php?id="+val;
		{literal}
		
		var res=ajaxR(url);
		if(res == 'exito'){
			var obj=document.getElementById('alertaSistema');
			obj.style.display='none';
		}
	}
	
	function Parar()
	{
		//document.all.sound.src = ""
	}
	
	//linkAlerta
	
	$(document).ready(function() {
	//metemos busquedad de transferencia
			buscaTransfer();
			setInterval('buscaTransfer()','30000');	
		$("#linkAlerta").on ("click", function () {
			$("#alertaSistema").css ("display", "none");
			var res=ajaxR("{/literal}{$rooturl}{literal}code/ajax/aunexisteAlerta.php?id_aut=" + $("#IDAutorizacion").val());
			if (res.match (/SI/i)) {
				return true;
			} else {
				alert ("El vendedor en turno ha cancelado la solicitud");
				return false;
			}
		});
		{/literal}{if $tabla neq 'ec_autorizacion'}
		buscaAlerta();  
		setInterval('buscaAlerta()', 10000);
		{/if}{literal}
	});
*/
//setTimeInterval();
	
	</script>
	
  <script type="text/javascript">

			
jQuery(window).load(function() {

    $("#nav > li > a").click(function (e) { // binding onclick
        if ($(this).parent().hasClass('selected')) {
            $("#nav .selected div div").slideUp(100); // hiding popups
            $("#nav .selected").removeClass("selected");
        } else {
            $("#nav .selected div div").slideUp(100); // hiding popups
            $("#nav .selected").removeClass("selected");

            if ($(this).next(".subs").length) {
                $(this).parent().addClass("selected"); // display popup
                $(this).next(".subs").children().slideDown(200);
            }
        }
        e.stopPropagation();
    }); 

    $("body").click(function () { // binding onclick to body
        $("#nav .selected div div").slideUp(100); // hiding popups
        $("#nav .selected").removeClass("selected");
    }); 

});


	</script>
 {/literal}	
</head>

<body>

<!--Busqueda de transferencia-->
     <div id="alertaTrans" style="padding:5px;border:2px solid;position:fixed;width:35%;left:30%;right:35%;top:0;
     height:200px;background:rgba(220,220,0,0.5);display:none;">
     	<div style="width:100%;text-align:right;">
     	<a href="javascript:cierraAviso();"><b style="border:solid 1px red;text-decoration:none;">X</b></a>
     	</div>
     	<a href="{$rooturl}/code/general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==">
     		<div style="width=90%;height:90%;" onclick="cierraAviso();">
     			<p align="center">Nueva transferencia insertada o actualizada</p>
     			<p align="center">Folio:</p>
     			<p align="center"><input type="text" id="folT" style="width:50%;background:transparent;" disabled></p>  			
     		</div>
     	</a>
     </div>
<!---->
<div id="alertaSistema" class="contenedor_alerta" style="display:none">
<input type="button" id="botonCerrarAlerta" name="cerrar" class="cerrarse" onclick="cierraAlerta()" value="X">
<a href="#" id="linkAlerta">
<div  class="alerta"  >
  <div>
	<h3>Hay una nueva alerta que necesita revisar</h3>
	<p id="textoAlerta">Motivo: Puede ver</p>
	<p id="fechaAlerta">Fecha: 2018-03-26</p>
	<p id="horaAlerta">Hora:13:00</p>
	<!--<p id="linkAlerta">Revisar class="linksd">Clic</p>-->
	</div>
	<!--<input type="button" class="acep" value="Aceptar">-->
    <input type="hidden" name="IDAutorizacion" id="IDAutorizacion" value="" />
</div>
</div>
</div>
<div id="pantalla" style="display:none; background:rgba(255,255,255,0.5)"></div>
<div id="contenido">    
 <!--Comienza el header--> 
<header>
     <div class="ctn-header">
 	<div class="logoheader">
		<a href="{$rooturl}index.php">
			<img src="{$rooturl}img/img_casadelasluces/Logo.png"/>
		</a>
	</div>
    <!--Usuarios y sucursal-->
       <div class="datosusuario">
       <div class="close"><img src="{$rooturl}img/close.png" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" onclick="cierraSesion()"/></div>
       <!--Usuarios comienza lado derecho-->
            <div class="usuario1"><strong>Usuario:</strong> {$user_fullname}</div>
      <div class="sucursal"><strong>Sucursal:</strong> {$sucursal_name}</div>
        </div>
        </div>
         <!--Titulo del panel-->
          <div class="h1" style="position:relative;top:-30px;">Panel de administraci&oacute;n</div> 
    	<!--Termina los leemenetos del header-->
         <!--comienza el menu princuipal-->

	{if $ver_pantalla_ventas eq 1}

	<a  href="{$rooturl}touch/index.php">
	<!--class="boton"-->
    	<div id="interfazbtn1" style="font-size: 13px;
												    font-weight: bold;
												    margin-right: 54px;
												    margin-top: -160px;
												    text-align: center;
												    text-decoration: none !important;
												    width: 90px;
												    height:100px;
												    border-radius:5px;
												    border:1px solid green;
												    background:white;
												    float:right;
												    position:relative;">
			<center>
				<img src="{$rooturl}img/puntoVenta.webp" style="width:85px;height:100px;"><!--Punto de Venta-->
			Punto de venta
			</center>
		</div>
	</a>  
	
	{/if}
     
		<div id="botones" style="top:240px;position:absolute;">		
			<ul id="nav">
				{section loop=$menus name=indice start=0}
					<li>
					<a href="javascript:void(0)" title="{$menus[indice][1]}" class="desplegable"><img src="{$rooturl}{$menus[indice][2]}" width="16" height="16" border="0"/>{$menus[indice][1]}</a>
					{section loop=$menus[indice][3] name=ind start=0}
						{if $smarty.section.ind.first}
                            <div class="subs">
                            <div>                    
							<ul>
						{/if}
						<li>
							<a href="{if $menus[indice][3][ind][1] eq '1'}{$rooturl}code/general/listados.php?tabla={$menus[indice][3][ind][2]}{else}{$rooturl}{$menus[indice][3][ind][3]}{/if}&no_tabla={$menus[indice][3][ind][4]}">
								{$menus[indice][3][ind][0]}
							</a>
						</li>
						{if $smarty.section.ind.last}
							</ul>
                            </div>
                            </div>
						{/if}
					{/section}				
					</li>
				{/section}	
			</ul>
		
		</div>
        <!--  Final Menu general -->
      
        </header>
   <div style="position:fixed;top:1 0px;font-size:30px;color:silver;left:250px;"><b id="nom_sistema"></b></div>
   {literal}
   <script>

		window.onload=function carga_informe(){
			{/literal}
				var dir="{$rooturl}/code/ajax/especiales/Seguimiento.php?fl=1";
			{literal}
				var res=ajaxR(dir);
				var ax=res.split('|');
				if(ax[0]!='ok'){
					alert(res);
				}else{
					$("#nom_sistema").html(ax[1]);
				}
				return true;
			}
   </script>
   {/literal}