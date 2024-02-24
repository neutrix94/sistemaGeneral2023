<?php
	include('../../../../conectMin.php');
//armamos el combo de las sucursales
	$sql="SELECT id_sucursal,nombre FROM sys_sucursales WHERe id_sucursal>0";
	$eje=mysql_query($sql)or die("Error al consultar las sucursales!!!<br>".mysql_error());
	$combo_sucs='Sucursal: <select id="filtro_sucursales" class="filtro"><option value="0">--SELECCIONAR--</option>';
	while($r=mysql_fetch_row($eje)){
		$combo_sucs.='<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	$combo_sucs.='<option value="-1">TODAS</option>';
	$combo_sucs.='</select>';
//	echo $combo_sucs;
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>

  <link rel="stylesheet" type="text/css" href="../../../../css/gridSW_l.css"/>
<script type="text/javascript" src="../../../../js/calendar.js"></script>
<script type="text/javascript" src="../../../../js/calendar-es.js"></script>
<script type="text/javascript" src="../../../../js/calendar-setup.js"></script>
<script type="text/JavaScript" src="../../../../js/jquery-1.10.2.min.js"></script>
</head>
<body background="../../../../img/img_casadelasluces/bg8.jpg">
	<center>
	<div class="encabezado">Reporte de Arqueo de Caja
	</div>
		<table width="100%;" border="1">
			<tr>
			<!--	<th colspan="2" align="center" class="titulo">Ingrese los pagos con Tarjeta</th>
				<th>Fecha</th>
				<th>Hora</th>
			</tr>
			<tr>
				<td align="center"><p>
					<p style="font-size:20px;margin:0;">Tarjeta 1:</p> <input type="text" class="entrada" id="t1" value="0"></p>
				</td>
				<td align="center">
					<p style="font-size:20px;margin:0;">Tarjeta 2:</p><input type="text" class="entrada" id="t2" value="0"></p>
				</td>
			-->
				<td align="center" style="display:none;">
					<!--<p>Seleccione un tipo de Arqueo</p>-->
					<p>
						<select class="filtro" id="f1">
							<option value="1">Simplificado</option>
							<option value="2">Completo</option>
						</select>
					</p>
				</td>
				<td align="center">
					<p>
						<select class="filtro" id="f2"  onchange="activaBusqueda();">
							<option value="-1">Hoy</option>
			<!--Cambio del 14-12-2017-->
							<option value="1">Ayer</option>

						
						<?php
							if($user_id==2||$user_id==35||$user_id==255){
						?>
							<option value="2">Personalizado</option>
						<?php
							}
						?>
						</select>
					</p>
				</td>
				<td>
						<p>
							<p>DE LA:<select id="h1" class="hora">
									<?php
										for($i=0;$i<=23;$i++){
											$hora="0".$i;
											if($i>9){
												$hora=$i;
											}
									?>
										<option value="<?php echo $hora;?>"><?php echo $hora;?></option>
									<?php
										}
									?>
									</select>
									<select id="m1" class="hora">
									<?php
										for($i=0;$i<=59;$i++){
											$hora="0".$i;
											if($i>9){
												$hora=$i;
											}
									?>
										<option value="<?php echo $hora;?>"><?php echo $hora;?></option>
									<?php
										}
									?>
									</select>A LA:<select id="h2" class="hora">
									<?php
										for($i=0;$i<=23;$i++){
											$hora="0".$i;
											if($i>9){
												$hora=$i;
											}
									?>
										<option value="<?php echo $hora;?>"><?php echo $hora;?></option>
									<?php
										}
									?>
									</select>
									<select id="m2" class="hora">
									<?php
										for($i=0;$i<=59;$i++){
											$hora="0".$i;
											if($i>9){
												$hora=$i;
											}
									?>
										<option value="<?php echo $hora;?>"><?php echo $hora;?></option>
									<?php
										}
									?>
									</select>
									</p>
						</p>
					<!--Calendario-->
						<p id="calend" style="display:none;">
							Del: <input type="text" class="fecha" id="fecdel" onclick="calendario(this);">
							Al:
							<input type="text" class="fecha" id="fecal" onclick="calendario(this);">
						</p>
					</p>
				</td>

				<td><?php echo $combo_sucs;?>						
				</td>

				<td align="center">
					<input type="button" value="Gererar" onclick="llenaReporte();" class="boton">
				</td>
			</tr>	
		</table>
		<div id="reporte">
			<?php //include('ajax/detalle.php');?>
		</div>
		<div class="footer">
			<input type="button" value="Regresar al panel" style="padding:8px;" onclick="salir();">	
		</div>
	</center>
</body>
</html>
<style>
	.footer{position:absolute;bottom:15px;width:10%;right:15px;}
	body{margin: 0;}
	.encabezado{padding:5px;background:#83B141;width:99%;height:30px;top:0;color:white;font-size:25px;}
	.boton{padding:10px;border-radius: 5px;}
	.filtro{padding:10px;border-radius: 5px;}
	.entrada{padding:0px;height:30px;width:100px;border-radius: 5px;font-size: 20px;}
	#reporte{
		border:2px;
		background: rgba(0,0,0,.2);
		width:80%;
		border-radius: 10px;
	}
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
</style>
<script type="text/JavaScript">
	function salir(){
		location.href="../../../../index.php";
	}
	function activaBusqueda(){
		var fl=document.getElementById('f2').value;
		if(fl==-1){
			document.getElementById('calend').style.display="none";
		}else if(fl==2){
			document.getElementById('calend').style.display="block";
		}
		return false;
	}
	
	function generaTicket(){
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
   /*implementación Oscar 15.08.2018*/
    	if(document.getElementById('efe_ext')){
    		cantidades+=document.getElementById('efe_ext').innerHTML+"|";//6
    	}else{
    		cantidades+='0|';//6
    	}
    /*fin de cambio Oscar 15.08.2018*/
    	
    	cantidades+=document.getElementById('ing_int').innerHTML;

    	var registros=document.getElementById('regist').value;
//alert('gastos: \n'+gast+"\ncanidades: \n"+cantidades);
//return false;
    //extraemos fecha
    	var fec=document.getElementById('fechaFinal').value;
		$.ajax({
			type:'post',
			url:'ajax/imprimeTicket.php',
			cache:false,
			data:{gastos:gast,datos:cantidades,fechaFin:fec,resAprox:registros,horaFin:hora},
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
		/*var tar1,tar2;
		tar1=document.getElementById('t1').value;
		tar2=document.getElementById('t2').value;
	//validamos que los campos de las tarjetas no esten vacios
		if(tar1==""){
			alert("El campo de Tarjeta 1 no puede ir vacío\n si no tuvo pagos con esta tarjeta Ingrese 0");
			document.getElementById('t1').value=0;
			document.getElementById('t1').select();
			return false;
		}
		if(tar2==""){
			alert("El campo de Tarjeta 2 no puede ir vacío\n si no tuvo pagos con esta tarjeta Ingrese 0");
			document.getElementById('t2').value=0;
			document.getElementById('t2').select();
			return false;
		}*/
	//obtenmos los filtros
		var fT,fF; 
		fT=document.getElementById('f1').value;
		fF=document.getElementById('f2').value;
		
		if(fF==2){
			fF=""+document.getElementById('fecdel').value+"|"+document.getElementById('fecal').value;
			if(document.getElementById('fecdel').value>document.getElementById('fecal').value){
				alert('La fecha limite no puede ser menor a la fecha inicial!!!');
				document.getElementById('fecal').select();			
				return false;
			}
		}
	//obtenemos horas
		var hora1,min1,hora2,min2;
		hora1=document.getElementById('h1').value;
		min1=document.getElementById('m1').value;
		hora2=document.getElementById('h2').value;
		min2=document.getElementById('m2').value;
		var horas=hora1+"|"+min1+"~"+hora2+"|"+min2;
		if(hora1=='00' && min1=='00' && hora2=='00' && min2=='00'){
			horas=0;
		}
		//alert(fF);return false;
	//generamos el reporte
		$.ajax({
			type:'post',
			url:'ajax/detalle.php',
			cache:false,
			data:{fecha:fF,hrs:horas},//t1:tar1,t2:tar2,
			success:function(dat){alert(dat);
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
</script>
