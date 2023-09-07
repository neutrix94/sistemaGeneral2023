<?php
	include('../../../../conectMin.php');
//armamos combo de conceptos de movimientos
	$sql="SELECT id_concepto_movimiento,nombre FROM ec_concepto_movimiento WHERE id_concepto_movimiento!=5 AND id_concepto_movimiento!=6 AND id_concepto_movimiento!=1
		UNION
		SELECT 5 as id_concepto_movimiento,'Traspaso entre cajas' as nombre";
	$eje=mysql_query($sql)or die("Error al consultar los conceptos de movimientos\n".mysql_error());
	$conceptos='<select id="concepto" onchange="cambia_concepto();" class="entrada_txt">';
	while($r=mysql_fetch_row($eje)){
		$conceptos.='<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	$conceptos.='</select>';
	//die('perfil: '.$perfil_usuario);
//armamos combo de caja 1
	$sql="SELECT
			ax.id_caja_cuenta as id,
			CONCAT(
					ax.nombre,' $',
					IF((SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_menu=195 AND id_perfil=$perfil_usuario)=0,
						'-',
						(SELECT 
							IF(SUM(IF(mb.id_movimiento_banco IS NULL,0,(mb.monto*cm.afecta))) IS NULL,
								0,
								SUM(IF(mb.id_movimiento_banco IS NULL,0,(mb.monto*cm.afecta)))				
							) 
						FROM ec_movimiento_banco mb
						JOIN ec_concepto_movimiento cm ON cm.id_concepto_movimiento=mb.id_concepto
						WHERE mb.id_caja=ax.id_caja_cuenta
						)
					)
			) as descripcion
		FROM(
			SELECT 
				id_caja_cuenta,
				nombre
			FROM ec_caja_o_cuenta 
			WHERE id_caja_cuenta>0
			GROUP BY id_caja_cuenta
			)ax";
	$eje=mysql_query($sql)or die("Error al consultar los conceptos de movimientos\n".mysql_error());
	$caja='<select id="caja" class="entrada_txt"><option value="-1">--SELECCIONAR--</option>';
	while($r=mysql_fetch_row($eje)){
		$caja.='<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	$caja.='</select>';

	$caja1=str_replace("caja", "caja_1", $caja);

	$caja2=str_replace("caja_1", "caja_2", $caja1);
//sacamos la hora desde mysql
	$eje=mysql_query("SELECT now()")or die("Error al consultar la fecha!!\n".mysql_error());
	$fecha=mysql_fetch_row($eje);

?>
<!DOCTYPE html>
<html>
<head>
	<title>Movimiento de Caja</title>
<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="../../../../js/passteriscoByNeutrix.js"></script>

<style type="text/css">
	.global{position: absolute;width: 100%;height: 100%;top:0;left: 0;background-image: url('../../../../img/img_casadelasluces/bg8.jpg');}
	#buscador{padding: 15px; width: 20%;}
	#res_busc{position:relative;width: 30%;border: 1px solid;height:300px;top:-17px;left: 3.8%;display: none;background:white;z-index: 10;overflow-y: auto;}
	.header{position: absolute;height: 50px;background:#83B141;width:100%;}
	.footer{position: absolute;bottom: 0px; height: 60px;background:#83B141;width:100%;}
	.form{position: absolute;top:60px;width: 60%;left: 20%;}
	.entrada_txt{padding: 10px;font-family: monospace;font-size: 15px;}
	.oculto{display:none;}
	.opcion_resultado{/*border: 1px solid;*/padding: 10px;}
	.btn{padding: 10px;border:1px solid white;border-radius:10px;}
	.btn:hover{color: white;background: gray;}
	.mnu{text-decoration: none; position: absolute; padding: 10px;border:1px solid white;background: gray;top:15px;color: white;left: 45%;}
</style>
</head>
<body>
	<div class="global">	
		<div class="header">
			<p align="left" style="color:white;font-size: 20px;"><b>Alta y edición de ingresos</b></p>
		</div>
	<table class="form">
		<tr>
			<td>
				Id de Movimiento:
			</td>
			<td>
				<input type="text" id="id_movimiento" class="entrada_txt" disabled>
			</td>
		</tr>
		<tr>
			<td>
				Folio:
			</td>
			<td>
				<input type="text" placeholder="Folio..." id="buscador" onkeyup="busca(event);">
			<div id="res_busc"></div>
		</p>
			</td>
		</tr>
		<tr>
			<td>
				Fecha:
			</td>
			<td>
				<input type="text" id="fecha" class="entrada_txt" value="<?php echo $fecha[0];?>" disabled>
			</td>
		</tr>
		<tr>
			<td>
				Tipo:
			</td>
			<td>
				<?php echo $conceptos; ?>
			</td>
		</tr>
	<!---->
		<tr id="una_caja">
			<td>
				Caja:
			</td>
			<td>
			<?php echo $caja;?></td>	
		</tr>
	<!---->
	<!---->
		<tr class="oculto" id="dos_cajas">
			<td align="center">Caja origen:<br>
			<?php echo $caja1;?></td>
			<td align="center">Caja destino:<br>
			<?php echo $caja2;?></td>
		</tr>
	<!---->
		<tr>
			<td>
				Monto: 
			</td>
			<td><input type="number" id="monto" class="entrada_txt"></td>
		</tr>
		<tr>
			<td>
				Observaciones:
			</td>
			<td>
				<textarea id="observaciones"></textarea>
			</td>
		</tr>
		<tr>
			<td align="center">
				<button class="btn" id="cancela" onclick="link(1);">Salir sin guardar</button>
			</td>
			<td align="center">
				Password de tesoreria <br>
				<input type="text" class="entrada_txt" id="password" onkeyDown="cambiar(this,event,'password1');" placeholder="**Password***"><br><br>
				<input type="hidden" id="password1" value="">
				<button class="btn" onclick="guardar_movimiento();">Guardar e imprimir</button>
			</td>
		</tr>
	</table>
		<div class="footer">
			<a href="javascript:link(2);" class="mnu">
				Regresar al panel
			</a>
		</div>
	</div>
</body>
</html>

<script type="text/javascript">
	function cambia_concepto(){
		if($("#concepto").val()=='5'){
			$("#una_caja").css("display","none");
			$("#dos_cajas").css("display","block");
		}else{
			$("#una_caja").css("display","block");
			$("#dos_cajas").css("display","none");
		}
	}

	function guardar_movimiento(){
		var id_mov=0,concepto,valor,observ,pssword,caja_cta='';
	//obtenemos el id de movimiento en caso de que este aplique
		if($("#id_movimiento").val()!=''){
			id_mov=$("#id_movimiento").val();
		}
	//obtenemos el tipo de movimiento
		concepto=$("#concepto").val();
	//obtenemos el valor del movimiento
		valor=$("#monto").val();
	//obtenemos las observaciones del movimiento
		observ=$("#observaciones").val();
	//obtenemos el password del usuario
		pssword=$("#password1").val();
	//validamos formulario
		if(concepto=='5'){
			if($("#caja_1").val()=='-1'){
				alert("La caja de origen no puede ir vacia!!!");
				$("#caja_1").focus();return false;
			}
			caja_cta+=$("#caja_1").val()+"~";//obtenemos el alor de la caja 1
			if($("#caja_2").val()=='-1'){
				alert("La caja de destino no puede ir vacia!!!");
				$("#caja_2").focus();return false;
			}
			caja_cta+=$("#caja_2").val();//obtenemos el alor de la caja 1
			if($("#caja_1").val()==$("#caja_2").val()){
				alert("La caja de origen y destino no puede ser la misma!!!");
				$("#caja_2").focus();return false;
			}
		}else if(concepto!=5){
			if($("#caja").val()=='-1'){
				alert("La caja o cuenta no puede ir vacia!!!");
				$("#caja").focus();return false;
			}
			caja_cta=$("#caja").val();
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'bd.php',
			cache:false,
			data:{flag:'inserta',id:id_mov,conc:concepto,val:valor,observacion:observ,pss:pssword,id_caja:caja_cta},
			success:function(dat){
				//alert(dat);
				if(dat!='ok'){
					alert(dat);
				}else{
					alert("movimeinto guardado exitosamente!!!");
					location.reload();
				}
			}
		})
	
	}
	
	function busca(e){
		if(e.keyCode==40){
			enfoca(1);return false;
		}
		var txt=$("#buscador").val();
		if(txt.length<=2){
			$("#res_busc").html('');
			$("#res_busc").css("display","none");
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'bd.php',
			cache:false,
			data:{flag:'buscador',dato:txt},
			success:function(dat){
			//	alert(dat);
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert(dat);
					return false;	
				}
				$("#res_busc").html(aux[1]);
				$("#res_busc").css("display","block");
			}
		});
	}
	function enfoca(num){
		$("#opcion_"+num).focus();9
	}
	function carga_movimiento(id){
		$("#buscador").attr("disabled",true);
		$("#res_busc").html('');
		$("#res_busc").css('display','none');
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'bd.php',
			cache:false,
			data:{flag:'carga_mov',id_mov:id},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert(dat);return false;
				}else{
					$("#id_movimiento").val(aux[1]);//id
					$("#buscador").val(aux[2]);//folio
					$("#fecha").val(aux[3]);//fecha
					$("#caja option[value="+aux[4]+"]").attr("selected",true);//caja
					$("#monto").val(aux[5]);//monto
					$("#observaciones").val(aux[6]);//observaciones
					$("#concepto option[value="+aux[7]+"]").attr("selected",true);
				}
			}
		});

	}
	function link(flag){
		if(flag==1 && confirm("Realmente desea cancelar la edición, ningun cambio sera guardado?")==true){
			location.reload();
		}
		if(flag==2 && confirm("Realmente desea regresar al panel?")==true){
			location.href='../../../../index.php?';
		}
	}
</script>