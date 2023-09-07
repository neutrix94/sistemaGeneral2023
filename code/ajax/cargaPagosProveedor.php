<?php
	include("../../conectMin.php");
/**/
//die('fl:'.$_GET['fl']);
	if(isset($_GET['fl']) && $_GET['fl']=='carga_caja_sobrante'){
		$id_seleccionado=$_GET['caja_pago'];
		$sql="SELECT id_caja_cuenta,nombre FROM ec_caja_o_cuenta WHERE id_caja_cuenta>0 AND id_caja_cuenta!=$id_seleccionado";
		$eje=mysql_query($sql)or die("Error al consultar las cajas o cuentas!!!".mysql_error());
		echo 'ok|<b style="color:white;font-size:20px;">';
		echo 'Caja Sobrante:</b><br><select id="id_caja_o_cuenta_sobrante" style="padding:10px;"><option value="-1">--SELECCIONAR--</option>';
		while($r=mysql_fetch_row($eje)){
			echo '<option value="'.$r[0].'">'.$r[1].'</option>';
		}
		echo '</select>';
		die('');
	}
/**/
//verificamos si es insertar pagos
	if(isset($_POST['flag'])&&$_POST['flag']=='reg_pgo'){
		$monto_pago=$_POST['monto'];
		$montoPagoOriginal=$monto_pago;
		$id_prov=$_POST['id_proveedor'];
		$id_caja_o_cta=$_POST['id_caja'];
		$monto_por_adelantado=$_POST['monto_sobra'];
		$caja_pago_adelantado=$_POST['caja_sobra'];
		if($_POST['tipo_pago_nota']!=0){
			$condicion_nota=" AND ocr.id_oc_recepcion=".$_POST['tipo_pago_nota']; 
		}
		$notas_destino="";
		$referencia_pago_prov=$_POST['referencia_pago'];
		mysql_query("BEGIN");
	//consultamos las recepciones pendientes de pagar
		$sql="SELECT
					ocr.id_oc_recepcion,
					SUM(ocr.monto_nota_proveedor-ocr.descuento)-( SELECT IF( ocp.id_oc_pagos is null,0,SUM(ocp.monto) ) FROM ec_oc_pagos ocp WHERE ocp.id_oc_recepcion IN(ocr.id_oc_recepcion)) as totalNotasNoPagadas,
					ocr.folio_referencia_proveedor 
				FROM ec_oc_recepcion ocr 
				WHERE ocr.id_proveedor=$id_prov 
				AND ocr.status IN(2)/*<3*/
				$condicion_nota
				GROUP BY ocr.id_oc_recepcion";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//hacemos rollback
			die("Error al consultar monto de notas pedientes!!!\n\n".$sql."\n".mysql_error());
		}
	//realizamos la suma del total por pagar
		$total_pendiente=0;
		$arr=array();
		while($r=mysql_fetch_row($eje)){
			array_push($arr,$r);
			$total_pendiente+=$r[1];
		} 
	//vemos si el pago no es mayor al saldo pendiente
		//die($monto_pago."\n".$total_pendiente);
		/*if($monto_pago>$total_pendiente){
			$sobrante
		}
		//	die("ok*El monto del pago es mayor al monto pendiente con este proveedor!!!*".$total_pendiente);
		}else{*/
			for($i=0;$i<sizeof($arr);$i++){
				if($monto_pago>0){
					if($monto_pago>$arr[$i][1]){
						$monto_tmp=$arr[$i][1];
					}else{
						$monto_tmp=$monto_pago;
					}
					$id_pago_remision=0;
				//insertamos el pago
					$sql="INSERT INTO ec_oc_pagos 
							SELECT
								null,
								{$arr[$i][0]},
								1,
								now(),
								now(),
								{$monto_tmp},
								'{$arr[$i][2]}',
								1,
								0,
								-1,
								-1";
					$eje_1=mysql_query($sql);
					if(!$eje_1){
						mysql_query("ROLLBACK");//hacemos rollback
						die("Error al insertar el pago!!!\n".$sql."\n".mysql_error());
					}
					$id_pago_remision=mysql_insert_id();
				//consultamos los datos del proveedor,folio,total de la remision
					$sql="SELECT 
								CONCAT('Pago al proveedor ',p.nombre_comercial,' a la nota con el folio ',ocr.folio_referencia_proveedor,
									' fecha ',ocr.fecha_remision,' total de la nota $',ocr.monto_nota_proveedor)
							FROM ec_oc_recepcion ocr
							LEFT JOIN ec_proveedor p ON p.id_proveedor=ocr.id_proveedor
							WHERE ocr.id_oc_recepcion={$arr[$i][0]}";
					$eje_2=mysql_query($sql);
					if(!$eje_2){
						mysql_query("ROLLBACK");//hacemos rollback
						die("Error al consultar los detalles para la observacion el pago!!!\n".$sql."\n".mysql_error());
					}
					$r_2=mysql_fetch_row($eje_2);
				//insertamos el detalle del pago a la caja correspondiente
					$sql="INSERT INTO ec_movimiento_banco VALUES(/*1*/null,/*2*/$id_caja_o_cta,/*3*/-1,/*4*/2,/*5*/$user_id,/*6*/$monto_tmp,
						/*7*/'',/*8*/now(),/*9*/-1,/*10*/-1,/*11*/$id_pago_remision,/*12*/'$r_2[0]',/*13*/-1,/*14*/0,/*15*/1)";
					$eje_2=mysql_query($sql);
					if(!$eje_2){
						$error=mysql_error();
						mysql_query("ROLLBACK");//hacemos rollback
						die("Error al insertar el movimiento de pago en caja o cuenta!!!\n".$sql."\n".$error);
					}
					$monto_pago-=$monto_tmp;
				//concatenamos las notas
					$notas_destino.=$r_2[0]."<br>";
				}//fin de si el monto es mayor a cero	
			}//fin de for $i
			/*$monto_por_adelantado=$_POST['monto_sobra'];
		$caja_pago_adelantado*/
		if($monto_por_adelantado>0){
		//insertamos la salida del sobrante en la caja		
			$sql="INSERT INTO ec_movimiento_banco VALUES(/*1*/null,/*2*/$id_caja_o_cta,/*3*/-1,/*4*/8,/*5*/$user_id,/*6*/$monto_por_adelantado,
				/*7*/'',/*8*/now(),/*9*/-1,/*10*/-1,/*11*/-1,/*12*/'Salida de Pago por adelantado',/*13*/-1,/*14*/0,/*15*/1)";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//hacemos rollback
				die("Error al insertar el movimiento de salida por pago adelantado en caja o cuenta!!!\n".$sql."\n".$error);
			}
		//insertamos la salida del sobrante en la caja
			$sql="INSERT INTO ec_movimiento_banco VALUES(/*1*/null,/*2*/$caja_pago_adelantado,/*3*/-1,/*4*/9,/*5*/$user_id,/*6*/$monto_por_adelantado,
				/*7*/'',/*8*/now(),/*9*/-1,/*10*/-1,/*11*/-1,/*12*/'Entrada de Pago por adelantado',/*13*/-1,/*14*/0,/*15*/1)";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//hacemos rollback
				die("Error al insertar el movimiento de entrada por pago adelantado en caja o cuenta!!!\n".$sql."\n".$error);
			}
		}
	//insertamos el registro de pago por partida
		$sql="INSERT INTO ec_pagos_prov_por_partida VALUES(null,$id_prov,$montoPagoOriginal,'$notas_destino',$user_id,now(),'$referencia_pago_prov')";
		$eje=mysql_query($sql)or die("Error al insertar el pago por partida!!!\n".$sql."\n".mysql_error());
		mysql_query("COMMIT");
		die('ok*ok');
		//}//fin de else
	}
//recibimos el id de la orden de compra para consultar al proveedor y filtros
	$id_oc=$_GET['oc'];

	$filtro_periodo="";
	if(isset($_GET['periodo'])&&$_GET['periodo']!=""){
		$filtro_periodo=$_GET['periodo'];
	}
	
	$tipo_status="AND ocr.status=2";
	if(isset($_GET['status'])&&$_GET['status']!=-1){
		$tipo_status=" AND ocr.status='".$_GET['status']."'";	
	}
	if($_GET['status']==-1){
		$tipo_status="";
	}
//consultamos el proveedor del que se trata
	$sql="SELECT id_proveedor FROM ec_oc_recepcion WHERE id_oc_recepcion=$id_oc";
	$eje=mysql_query($sql) or die("Error al consultar los datos del proveedor!!!\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
	$id_proveedor=$r[0];

//armamos consulta para extraer pagos
	/*ax.id_oc_recepcion,
			ax.folio_referencia_proveedor,
			ax.monto,
			ax.nombre,
			SUM(IF(pag.id_oc_pagos IS NULL,0,pag.monto)),
			ax.descuento,
			ax.monto-SUM(IF(pag.id_oc_pagos IS NULL,0,pag.monto)) as pendiente
		ax.id_oc_recepcion,
			ax.folio_referencia_proveedor,
			ROUND(ax.monto),
			ax.nombre,
			ROUND(SUM(IF(pag.id_oc_pagos IS NULL,0,pag.monto))),
			ROUND(ax.descuento),
			ROUND(ax.monto)-ROUND(SUM(IF(pag.id_oc_pagos IS NULL,0,pag.monto))) as pendiente
	*/
	$sql="SELECT 
			ax.id_oc_recepcion,
			ax.folio_referencia_proveedor,
			ROUND(ax.monto,2),
			ax.nombre,
			ROUND(SUM(IF(pag.id_oc_pagos IS NULL,0,pag.monto)),2),
			ROUND(ax.descuento,2),
			ROUND(ax.monto,2)-ROUND(SUM(IF(pag.id_oc_pagos IS NULL,0,pag.monto)),2) as pendiente
		FROM(
			SELECT
				ocr.id_oc_recepcion,
				'ocr.folio_referencia_proveedor',
				ocr.folio_referencia_proveedor,
				SUM(ocr.monto_nota_proveedor) AS monto,
				er.nombre,
				SUM(ocr.descuento) AS descuento
			FROM ec_oc_recepcion ocr
			/*LEFT JOIN ec_ordenes_compra oc ON ocr.id_orden_compra=oc.id_orden_compra*/
			LEFT JOIN ec_estatus_recepcion_oc er ON ocr.status=er.id_estatus
			WHERE ocr.id_proveedor='$id_proveedor'$tipo_status/*filtro de estatus de oc*/$filtro_periodo/*filtro de rango de fechas*/
			GROUP BY ocr.folio_referencia_proveedor
		)ax
		LEFT JOIN ec_oc_pagos pag ON ax.id_oc_recepcion=pag.id_oc_recepcion
		GROUP BY ax.id_oc_recepcion";
	$eje=mysql_query($sql)or die("Error al consultar pagos y folios de notas del proveedor!!!\n\n".mysql_error()."\n\n".$sql);
	$cont=0;
	$tot_monto_recep=0;
	$total_descuento=0;
	$total_por_pagar=0;
	echo 'ok|';
//variable html oculta
	echo '<input type="hidden" id="id_proveedor_pagos" value="'.$id_proveedor.'">';
//listado de notas recibidas
	echo '<table border="0" width="80%" style="position:fixed;top:30px;left:10%;">';
		echo '<tr>';
			echo '<td width="15%" align="right" style="color:white;font-size:20px;"><b>Periodo del:</b></td>';
			echo '<td width="20%"><input type="text" style="width:90%;" onfocus="calendario(this);" id="rango_del"></td>';
			echo '<td width="10%" align="center" style="color:white;font-size:20px;">al: </td>';
			echo '<td width="20%"><input type="text" style="width:90%;" onfocus="calendario(this);" id="rango_al"></td>';
			echo '<td width="25%" align="center">';
	//creamos la primera opción sis es el caso
			$primera_opcion="";
		if(isset($_GET['status'])){
			if($_GET['status']=='-1'){
				$primera_opcion='<option value="-1">Ver Todas</option>';
			}else{
				$sql="SELECT id_estatus,nombre FROM ec_estatus_recepcion_oc WHERE id_estatus=".$_GET['status'];
				$eje_combo=mysql_query($sql)or die("Error al consultar los estatus de recepciones_1!!!");
				$r_combo=mysql_fetch_row($eje_combo);
				$primera_opcion='<option value="'.$r_combo[0].'">'.$r_combo[1].'</option>';
			}
			$condicion_combo=" id_estatus!=".$_GET['status'];		
		}//fin de si existe otro status
		else{
			$condicion_combo=" id_estatus>0";
		}
			echo '<select id="filtro_tipo_rec" style="padding:12px;">';
			echo $primera_opcion;
				
		$sql="SELECT id_estatus,nombre FROM ec_estatus_recepcion_oc WHERE $condicion_combo ORDER BY observaciones ASC";
		$eje_combo=mysql_query($sql)or die("Error al consultar los estatus de recepciones!!!");
				
				while($r_combo=mysql_fetch_row($eje_combo)){
					echo'<option value="'.$r_combo[0].'">'.$r_combo[1].'</option>';
				}
//echo '<option value="3">Pagadas</option>';
				if($_GET['status']!=-1){
					echo '<option value="-1">Ver Todas</option>';
				}
				echo '</select>';
			echo '</td>';
			echo '<td width><input type="button" value="Filtrar" onclick="emerge_pagos(1);" style="padding:12px;border-radius:8px;"></td>';
		echo '</tr>';
	echo'</table>';
//boton de cerrar emergente
	echo '<button class="bt_crra" onclick="document.getElementById(\'emerge\').style.display=\'none\';"><img src="../../img/especiales/cierra.png" height="40px"></button>';
//formamos tabla de encabezado
	echo '<table class="tabla_pagos_prov"><tr><th width="29.5%">Folio de Nota Proveedor</th><th width="10%">Monto de Nota</th><th width="10%">Descuento</th><th width="20%">Estátus</th><th width="15%">Pagado</th>';
	echo '<th>Por Pagar</th>';
	echo '</tr></table>';
	echo '<div class="con_tab_pgos"><table width="100%" id="pags_proveedor">';
	$opciones_para_pagar="";
	while($r=mysql_fetch_row($eje)){
		$cont++;//incrementamos el contador de 1 en 1
		$tot_monto_recep+=$r[2];
		$total_descuento+=$r[5];
		$total_por_pagar+=$r[6];
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
			echo '<td width="30%" style="padding:10px;" id="1_'.$cont.'">'.$r[1].'</td>';
		//monto de recepción
			echo '<td align="right" width="10%" id="2_'.$cont.'">'.$r[2].'</td>';
		//descuento
			echo '<td align="right" width="10%" id="5_'.$cont.'" onclick="editarPago('.$cont.',5);">'.$r[5].'</td>';
		//estátus
			echo '<td align="center" width="20%" style="color:'.$col_estatus.';">'.$r[3].'(Se debe $<span id="deuda_'.$cont.'">'.($r[2]-$r[4]-$r[5]).'</span>)'.'</td>';
		//saldo pagado
			echo '<td align="right" width="15%" id="3_'.$cont.'">'.$r[4].'</td>';
			echo '<td align="right" width="15%" id="3_'.$cont.'">'.$r[6].'</td>';
		/*pagar
			echo '<td align="right" width="13.5%" id="4_'.$cont.'" onclick="editarPago('.$cont.',4);">'.'0'.'</td>';*/
		echo '</tr>';
		$opciones_para_pagar.='<option value="'.$r[0].'">'.$r[1].' $'.$r[6].'</option>';
	}
	echo '</table></div>';
//tabla footer
	echo '<table class="tabla_pagos_prov" style="top:465px;">';
	echo '<tr><th width="30%"></th>';
	echo '<th id="tot_monto_nota" width="10%" align="right">'.$tot_monto_recep.'</th>';
	echo '<th width="10%" align="right" id="tot_desc">'.$total_descuento.'</th>';
	echo '<th width="20%"></th>';
	echo '<th width="15%" id="monto_total_prov"></th>';
	echo '<th id="total_deuda">'.$total_por_pagar.'</th></tr></table>';

	$sql="SELECT 
			cc.id_caja_cuenta,
			IF(per.ver=1 OR per.modificar=1 OR per.nuevo=1,
				CONCAT(
					cc.nombre,
					' $ ',
					SUM(IF(mb.id_movimiento_banco IS NULL,0,(mb.monto*cm.afecta)))
				),
				cc.nombre
			) 
		FROM ec_caja_o_cuenta cc 
		LEFT JOIN ec_movimiento_banco mb ON mb.id_caja=cc.id_caja_cuenta
		LEFT JOIN ec_concepto_movimiento cm ON cm.id_concepto_movimiento=mb.id_concepto	
		JOIN sys_permisos per ON per.id_perfil=$perfil_usuario
		AND per.id_menu=199
		WHERE cc.id_caja_cuenta>0
		GROUP BY cc.id_caja_cuenta";

	$eje=mysql_query($sql)or die("Error al consultar las cajas o cuentas!!!".mysql_error());
	

	if($_GET['status']!=2  && $_GET['status']!=''){
		$prop_deshabilita='disabled="disabled"';
	}
	//die('<p>Status: '.$_GET['status'].'</p>');

	echo '<table border="0" style="position:absolute;top:350px;width:190%;left:-45%;z-index:10;">';
		echo '<tr><td align="center"><b style="color:white;font-size:20px;">Notas:</b><br>';
		echo '<select id="pagar_notas" style="padding:10px;" '.$prop_deshabilita.' onchange="pago_personalizado();"><option value="-1">Automático</option>';
		echo $opciones_para_pagar;
		echo '</select></td>';
	//echo '<p style="position:absolute;top:320px;left:-40%;">';
	echo '<td align="center"><b style="color:white;font-size:20px;">Caja:</b><br><select id="id_caja_o_cuenta" style="padding:10px;" onchange="carga_cajas_sobrante();" '.$prop_deshabilita.'><option value="-1">--SELECCIONAR--</option>';
	while($r=mysql_fetch_row($eje)){
		echo '<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	echo '</select></td>';//p class="apart_pgo"

	echo '<td align="center"><b style="color:white;font-size:20px;">Pagar:</b><br><input type="number" id="monto_pago_proveedor" onblur="validar_mono_vs_pago();" '.$prop_deshabilita.' style="padding:10px;"></td>';

//	echo '<p style="position:absolute;top:320px;right:35%;" >';
	echo '<td align="center" id="caja_de_sobrante"><b style="color:white;font-size:20px;">Caja Sobrante:</b><br><select id="id_caja_o_cuenta_sobrante" '.$prop_deshabilita.' style="padding:10px;"><option value="-1">--SELECCIONAR--</option>';
	echo '</select></td>';

	echo '<td align="center"><b style="color:white;font-size:20px;">Sobra:</b><br><input type="number" id="monto_sobrante" '.$prop_deshabilita.' style="padding:10px;background:white;color:black;" value="0" disabled></td>';
//referencia
	echo '<td align="center"><b style="color:white;font-size:20px;">Referencia</b><br><input type="text" id="ref_pago"></td>';
//botón de guardado
	echo '<td align="center"><button onclick="guardaPagosProveedor();" id="gda_pgs" class="bt_gd_pg_prov" '.$prop_deshabilita.'><img src="../../img/especiales/save.png" width="50px"><br>Guardar</button></td>';

	echo '</tr></table>';
?>
<style>
	.apart_pgo{
		position:fixed;
		top:505px;
		right:60%;
		padding: 10px;
		border-radius: 10px;
		color:white;
		font-size:20px; 
	}
	.bt_gd_pg_prov{/*
		position:fixed;
		top:540px;
		right:10%;*/
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
var es_individual=0;

	function pago_personalizado(){
		if($("#pagar_notas").val()==-1){
			es_individual=0;
		}else{
			es_individual=$("#pagar_notas").val();
			if($("#monto_pago_proveedor").val()>0){
				validar_mono_vs_pago();
			}
		}
	}
	function guardaPagosProveedor(){
	//guardamos los datos del pago
		//var tam_tb=$("#pags_proveedor tr").length;
		//var dats_pag_prv="";
	/**/
		var sobrante=$("#monto_sobrante").val();
		if(sobrante>0 && $("#id_caja_o_cuenta_sobrante").val()<=-1){
			alert("Debe de seleccionar una caja Sobrante anytes de continuar!");
			$("#id_caja_o_cuenta_sobrante").focus();
			return false;
		}
		var caja_sobrante=$("#id_caja_o_cuenta_sobrante").val();
	/**/
		if($("#id_caja_o_cuenta").val()==-1){
			alert("Es necesario que seleccione una caja antes de congtinuar con el pago");
			$("#id_caja_o_cuenta").focus();
			return false;
		}
	//obtenemos el valor del id de proveedor
		var id_prov=$("#id_proveedor_pagos").val();
		if(id_prov.length<=0){
			alert("No hay id de proveedor!!!");return false;
		}
	//obtenemos el valor del monto a pagar 
		var monto_pagar=$("#monto_pago_proveedor").val();
		if(monto_pagar.length<=0){
			alert("El monto no puede ir vacío!!!");$("#monto_pago_proveedor").focus();return false;
		}
	//ocultamos el boton
		$("#gda_pgs").css("display","none");
	//enviamos los datos por ajax
		$.ajax({
			type:'post',
			url:'../ajax/cargaPagosProveedor.php',
			cache:false,
			data:{flag:'reg_pgo',
				id_proveedor:id_prov,
				monto:monto_pagar,
				id_caja:$("#id_caja_o_cuenta").val(),
				monto_sobra:sobrante,
				caja_sobra:caja_sobrante,
				tipo_pago_nota:es_individual,
				referencia_pago:$("#ref_pago").val()
			},
			success: function(dat){
				var ax_dat=dat.split("*");
				if(ax_dat[0]!='ok'){
					alert("Error!!\n"+dat);
				//hacemos visible el boton
					$("#gda_pgs").css("display","block");
					return false;
				}else{
					if(ax_dat[1]!='ok'){
						alert(ax_dat[1]);
						$("#monto_pago_proveedor").val(ax_dat[2]);
						$("#monto_pago_proveedor").select();
					//hacemos visible el boton
						$("#gda_pgs").css("display","block");
						return false;
					}
				//recargamos los datos de la tabla
					alert("Pago registrado con éxito!!!");
					$("#pags_prv").click();
				//hacemos visible el boton
					$("#gda_pgs").css("display","block");
				}
			}
		});
	}

/*implementación Oscar 10.09.2019 para mandar el dinero al sobrante*/
	function validar_mono_vs_pago(){
	//extraemos el valor del monto pot pagar
	var pago=0,sobrante=0,deuda=0;
		pago=parseFloat($("#monto_pago_proveedor").val());
		if(pago<=0){
			alert("El campo de pago no puede ir vacío!!!");
			$("#monto_pago_proveedor").select();
			return false;
		}
	//asignamos el sobrante
		if(es_individual==0){
			deuda=parseFloat($("#total_deuda").html().trim());
		}else{
			var aux=$('#pagar_notas option:selected').text().split("$");
			deuda=aux[1];
		}
		if(pago>deuda){
			sobrante=parseFloat(pago-deuda);
			$("#monto_sobrante").val(sobrante);
		}
	}
/*Fin de cambio Oscar 10.09.2019*/
	
	/*function editarPago(num,flag){
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
	}*/

</script>