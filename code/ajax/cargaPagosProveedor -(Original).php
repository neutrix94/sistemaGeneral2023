<?php
	include("../../conectMin.php");
//verificamos si es insertar pagos
	if(isset($_POST['flag'])&&$_POST['flag']=='reg_pgo'){
		
		mysql_query("BEGIN");//declartamos el inicio de transacción
		$datos=explode("¬",$_POST['dats']);
	//insertamos los pagos
		$dat=explode("°",$datos[0]);
		for($i=0;$i<sizeof($dat)-1;$i++){
			$d=explode("~",$dat[$i]);
			if($d[0]!="" && $d[0]!=null){
				$sql="INSERT INTO ec_oc_pagos VALUES(null,$d[0],1,now(),now(),$d[2],'$d[1]',1,'',-1,-1)";	
				//echo $sql;
				$eje=mysql_query($sql);
				if(!$eje){
					$error=mysql_error();
					mysql_query("ROLLBACK");//cancelamos la transacción
					die("Error al insertar pagos de rececpión de Órden de compra!!!\n\n".$sql."\n\n".$error);
				}
			}
		}//fin de for i
	//actualizamos el descuento de las recepciones
		$dat=explode("°",$datos[1]);
		for($i=0;$i<sizeof($dat)-1;$i++){
			$d=explode("~",$dat[$i]);
			if($d[0]!="" && $d[0]!=null){
				$sql="UPDATE ec_oc_recepcion SET descuento=$d[1] WHERE id_oc_recepcion=$d[0]";
			//echo $sql;
				$eje=mysql_query($sql);
				if(!$eje){
					$error=mysql_error();
					mysql_query("ROLLBAK");//cancelamos la transacción
					die("Error al insertar pagos de rececpión de Órden de compra!!!\n\n".$sql."\n\n".$error);
				}
			}
		}
		mysql_query("COMMIT");//autorizamos la transacción
		die('ok*');
	}
//recibimos el id de la orden de compra para consultar al proveedor y filtros
	$id_oc=$_GET['oc'];

	$filtro_periodo="";
	if(isset($_GET['periodo'])&&$_GET['periodo']!=""){
		$filtro_periodo=$_GET['periodo'];
	}
	
	$tipo_status="";
	if(isset($_GET['status'])&&$_GET['status']!=-1){
		$tipo_status=" AND ocr.status='".$_GET['status']."'";	
	}

//consultamos el proveedor del que se trata
	$sql="SELECT id_proveedor FROM ec_ordenes_compra WHERE id_orden_compra=$id_oc";
	$eje=mysql_query($sql) or die("Error al consultar los datos del proveedor!!!\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
	$id_proveedor=$r[0];
//armamos consulta para extraer pagos
	$sql="SELECT 
			ax.id_oc_recepcion,
			ax.folio_referencia_proveedor,
			ROUND(ax.monto),
			ax.nombre,
			ROUND(SUM(IF(pag.id_oc_pagos IS NULL,0,pag.monto))),
			ROUND(ax.descuento)
		FROM(
			SELECT
				ocr.id_oc_recepcion,
				oc.folio,
				ocr.folio_referencia_proveedor,
				SUM(ocr.monto) AS monto,
				er.nombre,
				SUM(ocr.descuento) AS descuento
			FROM ec_oc_recepcion ocr
			LEFT JOIN ec_ordenes_compra oc ON ocr.id_orden_compra=oc.id_orden_compra
			LEFT JOIN ec_estatus_recepcion_oc er ON ocr.status=er.id_estatus
			WHERE oc.id_proveedor='$id_proveedor'$tipo_status/*filtro de estatus de oc*/$filtro_periodo/*filtro de rango de fechas*/
			GROUP BY ocr.folio_referencia_proveedor
		)ax
		LEFT JOIN ec_oc_pagos pag ON ax.id_oc_recepcion=pag.id_oc_recepcion
		GROUP BY ax.id_oc_recepcion";
	$eje=mysql_query($sql)or die("Error al consultar pagos y folios de notas del proveedor!!!\n\n".mysql_error()."\n\n".$sql);
	$cont=0;
	$tot_monto_recep=0;
	$total_descuento=0;
	echo 'ok|';
	echo '<table border="0" width="80%" style="position:fixed;top:30px;left:10%;">';
		echo '<tr>';
			echo '<td width="15%" align="right" style="color:white;font-size:20px;"><b>Periodo del:</b></td>';
			echo '<td width="20%"><input type="text" style="width:90%;" onfocus="calendario(this);" id="rango_del"></td>';
			echo '<td width="10%" align="center" style="color:white;font-size:20px;">al: </td>';
			echo '<td width="20%"><input type="text" style="width:90%;" onfocus="calendario(this);" id="rango_al"></td>';
			echo '<td width="25%" align="center">';
				echo '<select id="filtro_tipo_rec" style="padding:12px;">';
					echo '<option value="-1">Ver Todas</option>';
					echo'<option value="2">No Pagadas</option>';
					echo '<option value="3">Pagadas</option>';
				echo '</select>';
			echo '</td>';
			echo '<td width><input type="button" value="Filtrar" onclick="emerge_pagos(1);" style="padding:12px;border-radius:8px;"></td>';
		echo '</tr>';
	echo'</table>';
//boton de cerrar emergente
	echo '<button class="bt_crra" onclick="document.getElementById(\'emerge\').style.display=\'none\';"><img src="../../img/especiales/cierra.png" height="40px"></button>';
//formamos tabla de encabezado
	echo '<table class="tabla_pagos_prov"><tr><th width="25%">Folio de Nota Proveedor</th><th width="10%">Monto de Nota</th><th width="10%">Descuento</th><th width="15%">Estátus</th>';
	echo '<th width="15%">Pagado</th><th width="15%">Monto a Pagar</th></tr></table>';
	echo '<div class="con_tab_pgos"><table width="100%" id="pags_proveedor">';
	while($r=mysql_fetch_row($eje)){
		$cont++;//incrementamos el contador de 1 en 1
		$tot_monto_recep+=$r[3];
		$total_descuento+=$r[6];
			if($cont%2==0){
				$color='#CCCCCC';
			}else{
				$color='#FFFF99';
			}
		//aqui opnemos el color del estatus
			if($r[4]=='Pendiente de Pagar'){
					$col_estatus='red';
			}else{
				$col_estatus='green';
			}
		echo '<tr style="background:'.$color.';">';
		//id de recepción
			echo '<td style="display:none;" id="0_'.$cont.'">'.$r[0].'</td>';
		/*folio de recepción
			echo '<td style="padding:8px;" width="15%">'.$r[1].'</td>';*/
		//folio de nota proveedor
			echo '<td width="25%" id="1_'.$cont.'">'.$r[1].'</td>';
		//monto de recepción
			echo '<td align="right" width="10%" id="2_'.$cont.'">'.$r[2].'</td>';
		//descuento
			echo '<td align="right" width="10%" id="5_'.$cont.'" onclick="editarPago('.$cont.',5);">'.$r[5].'</td>';
		//estátus
			echo '<td align="center" width="15%" style="color:'.$col_estatus.';">'.$r[3].'(Se debe $<span id="deuda_'.$cont.'">'.($r[2]-$r[4]-$r[5]).'</span>)'.'</td>';
		//saldo pagado
			echo '<td align="right" width="15%" id="3_'.$cont.'">'.$r[4].'</td>';
		//pagar
			echo '<td align="right" width="13.5%" id="4_'.$cont.'" onclick="editarPago('.$cont.',4);">'.'0'.'</td>';
		echo '</tr>';
	}
	echo '</table></div>';
//tabla footer
	echo '<table class="tabla_pagos_prov" style="top:465px;"><tr><th width="25%"></th><th id="tot_monto_nota" width="10%" align="right">'.$tot_monto_recep.'</th>';
	echo '<th width="10%" align="right" id="tot_desc">'.$total_descuento.'</th><th width="15%"></th><th width="15%"></th><th width="15%" id="monto_total_prov"></th></tr></table>';
//botón de guardado
	echo '<button onclick="guardaPagosProveedor();" class="bt_gd_pg_prov"><img src="../../img/especiales/save.png" width="50px"><br>Guardar</button>';
?>
<style>
	.bt_gd_pg_prov{
		position:fixed;
		top:540px;
		right:10%;
		border-radius: 10px;
	}
	.bt_crra{
		position:fixed;
		top:58px;
		right:5%;
		z-index:100;
		padding: 0;
		margin: 0;
		background: rgba(225,0,0,.6);
	}
	.tabla_pagos_prov{
		position:fixed;
		background: white;
		width: 90%;
		left:5%;
		top:100px;
		/*border-radius:10px;*/
	}
	th{
		background: rgba(225,0,0,.6);
		color:white;
		padding:8px;
	}
	.con_tab_pgos{
		position:fixed;
		background: white;
		width: 90%;
		left:5%;
		top:135px;
		overflow:scroll;
		height:350px;
	}
	#tmp_pgo{
		width:98%;
		height:30px;
		padding: 0;
	}
</style>
<script>
var enfoc=0;
var pgo_tmp=0;	
	function editarPago(num,flag){
		if(enfoc!=0){
			return false;
		}
		pgo_tmp=$("#"+flag+"_"+num).html().trim();
	//agregamos input temporal a la celda marcada
		var celda_tmp_pgo='<input type="text" id="tmp_pgo" style="text-align:right;" onblur="deseditarPago('+num+','+flag+');" value="'+pgo_tmp+'">';
		$("#"+flag+"_"+num).html(celda_tmp_pgo);
		$("#tmp_pgo").select();
		enfoc=num;
	}
	function deseditarPago(num,flag){
		if(enfoc==0){
			return false;
		}
		var pgo_en_tmp=$("#tmp_pgo").val();
		if(pgo_en_tmp==""){
			pgo_en_tmp=0;
		}
	//validamos cantidades
		if(pgo_en_tmp>parseFloat($("#"+flag+"_"+num).html()-$("#3_"+num).html())&&flag==4){
			alert("El pago es mayor a la deuda!!!");
			$("#tmp_pgo").select();
			enfoc=0;
			return false;
		}
		$("#"+flag+"_"+num).html(pgo_en_tmp);
		operacion_pgo_prv(flag,num);
		enfoc=0;
	}
	function operacion_pgo_prv(flag,num){
	//sumamos los pagos de la columna monto a pagar
		var tot_pgo_prv=0;
		var tam_tb=$("#pags_proveedor tr").length;
		for(var i=1;i<=tam_tb;i++){
			tot_pgo_prv+=parseFloat($("#"+flag+"_"+i).html());
		}
		if(flag==4){
		//colocamos el total de pagos por realizar en el total de la columna
			$("#monto_total_prov").html(tot_pgo_prv);
		}
		if(flag==5){
		//colocamos el total de pagos por realizar en el total de la columna
			$("#tot_desc").html(tot_pgo_prv);
		}
	//actualizamos el adeudo
		$("#deuda_"+num).html(parseFloat($("#2_"+num).html()-$("#5_"+num).html())-$("#4_"+num).html());
	}

	function guardaPagosProveedor(){
	//guardamos los datos del pago
		var tam_tb=$("#pags_proveedor tr").length;
		var dats_pag_prv="";
		for(var i=1;i<=tam_tb;i++){
			if($("#4_"+i).html()!=""&&$("#4_"+i).html()!="0"){
				dats_pag_prv+=$("#0_"+i).html().trim()+"~";//id de la recepción
				dats_pag_prv+=$("#1_"+i).html().trim()+"~";//folio de referencia de la recepción
				//dats_pag_prv+=$("#2_"+i).html().trim()+"~";//monto de la oc
				//dats_pag_prv+=$("#3_"+i).html().trim()+"~";//saldo abonado
				dats_pag_prv+=$("#4_"+i).html().trim()+"°";//pagos
			}
		}//fin de for i

		dats_pag_prv+="¬";//concatenamos el separador

	//guardamos los descuentos
		for(var i=1;i<=tam_tb;i++){
			//if($("#5_"+i).html()!=""&&$("#5_"+i).html()!="0"){
				dats_pag_prv+=$("#0_"+i).html().trim()+"~";//id de la recepción
				dats_pag_prv+=$("#5_"+i).html().trim()+"°";//descuento
			//}
		}//fin de for i
	//enviamos los datos por ajax
		$.ajax({
			type:'post',
			url:'../ajax/cargaPagosProveedor.php',
			cache:false,
			data:{flag:'reg_pgo',dats:dats_pag_prv},
			success: function(dat){
				var ax_dat=dat.split("*");
				if(ax_dat[0]!='ok'){
					alert("Error!!\n"+dat);
					return false;
				}else{
				//recargamos los datos de la tabla
					alert("Pagos registrados con éxito!!!");
					$("#pags_prv").click();
				}
			}
		});
	}



</script>