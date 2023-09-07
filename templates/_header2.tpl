<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title>Casa de las luces</title>
  <link rel="stylesheet" type="text/css" href="{$rooturl}css/estilo_final1.css"/>
   <link rel="stylesheet" type="text/css" href="{$rooturl}css/gridSW_l.css"/>
   <!--link rel="stylesheet" type="text/css" href="{$rooturl}css/cssrumi/css/estilosuperiores.css" />
   <link rel="stylesheet" type="text/css" href="{$rooturl}css/cssrumi/css/gridsuperiores.css" /-->
 
   <link href='http://fonts.googleapis.com/css?family=Gafata' rel='stylesheet' type='text/css'>
  
  <script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
  	<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>-->
  <script language="JavaScript" type="text/javascript" src="{$rooturl}js/funciones.js"></script>
  <!-- Librerias para el grid-->
  <script language="javascript" src="{$rooturl}js/grid/RedCatGrid.js"></script>
  <script language="JavaScript" type="text/javascript" src="{$rooturl}js/grid/yahoo.js"></script>
  <script language="JavaScript" type="text/javascript" src="{$rooturl}js/grid/event.js"></script>
  <script language="JavaScript" type="text/javascript" src="{$rooturl}js/grid/dom.js"></script>
  <script language="JavaScript" type="text/javascript" src="{$rooturl}js/grid/fix.js"></script>
  <script type="text/javascript" src="{$rooturl}js/calendar.js"></script>
  <script type="text/javascript" src="{$rooturl}js/calendar-es.js"></script>
  <script type="text/javascript" src="{$rooturl}js/calendar-setup.js"></script>
  <script type="text/javascript" src="{$rooturl}js/buzz.js"></script>
  <script type="text/javascript" src="{$rooturl}js/buzz.min.js"></script>
  <script src="{$rooturl}js/menusistema.min.js"></script>
  <script language="javascript" src="{$rooturl}js/presentacion.js"></script>
  {literal}
  
  	<script>
	
	var mySound = new buzz.sound({/literal}"{$rooturl}files/caralarm"{literal}, {
   	 formats: [ "mp3" ]
	});
	
	//buzz.all().play();
	
	function buscaAlerta()
	{
	
		//Buscamos si hay alertas para el usuario
		{/literal}
		url="{$rooturl}/code/ajax/buscaAlertas.php";
		{literal}
		var res=ajaxR(url);
		aux=res.split('|');
		
		if(aux[0] == 'SI')
		{
			buzz.all().play();
			
			document.getElementById('textoAlerta').innerHTML="<b>Motivo: </b>"+aux[2];
			document.getElementById('fechaAlerta').innerHTML="<b>Fecha: </b>"+aux[3];
			document.getElementById('horaAlerta').innerHTML="<b>Hora: </b>"+aux[4];
			{/literal}
			document.getElementById('linkAlerta').innerHTML='Revisar: <a href="{$rooturl}'+aux[5]+'" target="_blank" class="linksd" onclick="cierraAlerta('+aux[1]+')">Clic</a></p>';
			{literal}
			
			document.getElementById('botonCerrarAlerta').onclick=function(){cierraAlerta(aux[1])};
			
			var obj=document.getElementById('alertaSistema');
			obj.style.display='block';
			
		}
	
		//document.getElementById('alertaSistema').style.display='none';
		
	}
	
	function cierraAlerta(val)
	{
		{/literal}
		url="{$rooturl}/code/ajax/cancelaAlerta.php?id="+val;
		{literal}
		
		var res=ajaxR(url);
		if(res == 'exito')
		{
			var obj=document.getElementById('alertaSistema');
			obj.style.display='none';
		}
		
		
	}
	
	function Parar()
	{
		//document.all.sound.src = ""
	}
	
	setInterval('buscaAlerta()', 10000);
	
	
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

<div id="alertaSistema" class="alerta" style="display:block">
<a
<input type="button" id="botonCerrarAlerta" name="cerrar" class="cerrarse" onclick="cierraAlerta()" value="X">
  <div>
	<h3>Hay una nueva alerta que necesita revisar</h3>
	<p id="textoAlerta">Motivo: Puede ver</p>
	<p id="fechaAlerta">Fecha: 2018-03-26</p>
	<p id="horaAlerta">Hora:13:00</p>
	<p id="linkAlerta">Revisar<a href="" class="linksd">  Clic</a></p>
	</div>
	</a>
	<!--<input type="button" class="acep" value="Aceptar">-->
</div>


<div id="pantalla" style="display:none; background:rgba(255,255,255,0.5)"></div>
<div id="contenido">

<header>

     <!--Comeinza el header--> 
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
          <div class="h1">Panel de administraci&oacute;n</div> 
    	<!--Termina los leemenetos del header--->
         <!--comienza el menu princuipal-->
       <a  href="{$rooturl}touch/index.php">
    <div class="boton" id="interfazbtn" style="color: #ffff;
    font-size: 13px;
    font-weight: bold;
    margin-right: 54px;
    margin-top: -118px;
    text-align: center;
    text-decoration: none !important;
    width: 81px;"><img src="{$rooturl}img/interfazVbtn.jpg"/>Punto de Venta</div></a>  
     
		<div id="botones">		
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
   