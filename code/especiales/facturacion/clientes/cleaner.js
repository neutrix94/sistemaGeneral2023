var global_omit = new Array();
var global_replace = new Array();

	global_omit.push( `<?xml version="1.0" encoding="UTF-8" ?>

    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type" /><!DOCTYPE html>
<html><head><meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1" /><title>Validador QR</title><link type="text/css" rel="stylesheet" href="/app/qr/faces/javax.faces.resource/mobile.css;jsessionid=BEfyeTaPLB34qWdexo6fMJih76PC9fsdrcoeGf4DFBrpYQcEQy8C!-837837947?ln=primefaces-mobile" /><script type="text/javascript" src="/app/qr/faces/javax.faces.resource/jquery/jquery.js;jsessionid=BEfyeTaPLB34qWdexo6fMJih76PC9fsdrcoeGf4DFBrpYQcEQy8C!-837837947?ln=primefaces"></script><script type="text/javascript">$(document).bind('mobileinit', function(){$.mobile.ajaxEnabled = false;$.mobile.linkBindingEnabled = false;$.mobile.hashListeningEnabled = false;$.mobile.pushStateEnabled = false;});</script><script type="text/javascript" src="/app/qr/faces/javax.faces.resource/mobile.js;jsessionid=BEfyeTaPLB34qWdexo6fMJih76PC9fsdrcoeGf4DFBrpYQcEQy8C!-837837947?ln=primefaces-mobile"></script><script type="text/javascript" src="/app/qr/faces/javax.faces.resource/primefaces.js;jsessionid=BEfyeTaPLB34qWdexo6fMJih76PC9fsdrcoeGf4DFBrpYQcEQy8C!-837837947?ln=primefaces"></script><script type="text/javascript" src="/app/qr/faces/javax.faces.resource/primefaces-mobile.js;jsessionid=BEfyeTaPLB34qWdexo6fMJih76PC9fsdrcoeGf4DFBrpYQcEQy8C!-837837947?ln=primefaces-mobile"></script>
<link type="text/css" rel="stylesheet" href="/app/qr/faces/javax.faces.resource/primefaces.css;jsessionid=BEfyeTaPLB34qWdexo6fMJih76PC9fsdrcoeGf4DFBrpYQcEQy8C!-837837947?ln=primefaces" />` );
	global_omit.push( `</head><body>
        <link href="../../css/prueba.css" rel="stylesheet" type="text/css" />
        <link href="../../css/validacionCIF.css" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css" />
        <center><img id="j_idt4" src="/app/qr/images/HACIENDA-SAT.jpg;jsessionid=BEfyeTaPLB34qWdexo6fMJih76PC9fsdrcoeGf4DFBrpYQcEQy8C!-837837947" alt="" width="320px" />
        </center>
		<script language="javascript" type="text/javascript">
		    window.onload = function getUbicacion(){
                console.log("entrando::::");
				var requiereUbicacion = $("#ubicacionForm\\:iptRequiereUbicacion").val();
        		console.log("Requiere ubicacion ???? " + requiereUbicacion);
                if(requiereUbicacion === "1"){
                    console.log('El servicio pertenece a Marbetes');
                    getVars();
                }
            };
                    
            function getVars(){
                console.log('este es un mensaje de inicio de ubicacion ::::');
                if ("geolocation" in navigator){ 
                    navigator.geolocation.getCurrentPosition(onSuccessGeolocating,onErrorGeolocating,{
                        enableHighAccuracy: true,
                        maximumAge:         5000
                    });
                }else{
                    console.log("El Navegador no pudo acceder a la geolocalizacion!, Se mandan datos nulos");
                    var ubicacion = "0,0";
                    setHiddenValue(ubicacion);
                }
            };
    
	        function onSuccessGeolocating(position){
                var longitud = position.coords.longitude;
                var latitud = position.coords.latitude;
                var ubicacion = latitud +","+longitud;
			    console.log("Datos de ubicacion :: "+ubicacion);
                setHiddenValue(ubicacion);
            };
                        
			function onErrorGeolocating(error){
                var ubicacion = "0,0";
                switch(error.code){
                    case error.PERMISSION_DENIED:
                        console.log('ERROR: Se nego el acceso a la geolocalizacion!');
                        setHiddenValue(ubicacion);
                        break;
                    case error.POSITION_UNAVAILABLE:
                        console.log("ERROR: Hay un problema para obtener la posicion del dispositivo!");                                   
                        setHiddenValue(ubicacion);
                        break;
                    case error.TIMEOUT:
                        console.log("ERROR: La aplicacion agoto el tiempo de espera para obtener la posicion del dispositivo!");
                        setHiddenValue(ubicacion);
                        break;
                    default:
                        console.log("ERROR: Problema desconocido!");
                        setHiddenValue(ubicacion);
                        break;
                }
            };
			
            function setHiddenValue(ubicacion){
                console.log('Entrando  setHiddenValue');
                $("#ubicacionForm\\:iptUbicacion").val(ubicacion);
				$("#ubicacionForm\\:iptUbicacion").change();
            };
		</script><div id="pageContent" data-role="content">
<form id="ubicacionForm" name="ubicacionForm" method="post" action="/app/qr/faces/pages/mobile/validadorqr.jsf;jsessionid=BEfyeTaPLB34qWdexo6fMJih76PC9fsdrcoeGf4DFBrpYQcEQy8C!-837837947" class="ui-content" enctype="application/x-www-form-urlencoded">` );

global_omit.push( `<script id="ubicacionForm:iptRequiereUbicacion_s" type="text/javascript">PrimeFaces.cw('InputText','widget_ubicacionForm_iptRequiereUbicacion',{id:'ubicacionForm:iptRequiereUbicacion'});</script>`);
global_omit.push( `<script id="ubicacionForm:iptUbicacion_s" type="text/javascript">PrimeFaces.cw('InputText','widget_ubicacionForm_iptUbicacion',{id:'ubicacionForm:iptUbicacion',behaviors:{change:function(event) {PrimeFaces.ab({source:'ubicacionForm:iptUbicacion',event:'change',process:'ubicacionForm',update:'ubicacionForm'}, arguments[1]);}}});</script>` );
global_omit.push( `<script id="ubicacionForm:j_idt11:0:j_idt12:j_idt16_s" type="text/javascript">$(function(){PrimeFaces.cw('DataTable','widget_ubicacionForm_j_idt11_0_j_idt12_j_idt16',{id:'ubicacionForm:j_idt11:0:j_idt12:j_idt16'});});</script>` );
global_omit.push( `<script id="ubicacionForm:j_idt11:0:j_idt12:j_idt26_s" type="text/javascript">$(function(){PrimeFaces.cw('DataTable','widget_ubicacionForm_j_idt11_0_j_idt12_j_idt26',{id:'ubicacionForm:j_idt11:0:j_idt12:j_idt26'});});</script>` );
global_omit.push( `<script id="ubicacionForm:j_idt11:1:j_idt12:j_idt16_s" type="text/javascript">$(function(){PrimeFaces.cw('DataTable','widget_ubicacionForm_j_idt11_1_j_idt12_j_idt16',{id:'ubicacionForm:j_idt11:1:j_idt12:j_idt16'});});</script>` );
global_omit.push( `<script id="ubicacionForm:j_idt11:1:j_idt12:j_idt26_s" type="text/javascript">$(function(){PrimeFaces.cw('DataTable','widget_ubicacionForm_j_idt11_1_j_idt12_j_idt26',{id:'ubicacionForm:j_idt11:1:j_idt12:j_idt26'});});</script>` );
global_omit.push( `<script id="ubicacionForm:j_idt11:2:j_idt12:j_idt16_s" type="text/javascript">$(function(){PrimeFaces.cw('DataTable','widget_ubicacionForm_j_idt11_2_j_idt12_j_idt16',{id:'ubicacionForm:j_idt11:2:j_idt12:j_idt16'});});</script>` );
global_omit.push( `<script id="ubicacionForm:j_idt11:2:j_idt12:j_idt26_s" type="text/javascript">$(function(){PrimeFaces.cw('DataTable','widget_ubicacionForm_j_idt11_2_j_idt12_j_idt26',{id:'ubicacionForm:j_idt11:2:j_idt12:j_idt26'});});</script>` );

global_replace.push( `ubicacionForm:j_idt11:0:j_idt12:j_idt17_data` );// ubicacionForm:j_idt11:0:j_idt12:j_idt17_data
global_replace.push( `iubicacionForm:j_idt11:1:j_idt12:j_idt17_data` );
global_replace.push( `ubicacionForm:j_idt11:2:j_idt12:j_idt17_data` );

function clean_data( data ){
	var resp = data;
	for( var i = 0; i < global_omit.length; i ++ ){
		resp = resp.replace( global_omit[i], '' );
	}

	for( var i = 0; i < global_replace.length; i ++ ){
		resp = resp.replace( global_replace[i], `final_data_${i}` );
	}
	return resp;
}
