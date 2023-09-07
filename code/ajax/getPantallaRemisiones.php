<?php
	include('../../conectMin.php');
	$fl=$_GET['flag'];
	//die('ok|'.$fl);
	if($fl=='obtener'){
	//armamos la lista de proveedores
		$eje=mysql_query("SELECT id_proveedor,nombre_comercial FROM ec_proveedor WHERE id_proveedor>1") or die("Error al consultar los datos de proveedores!!!".mysql_error());
		$proveedores='<select id="remision_proveedores" style="padding:10px;"><option value="-1">--SELECCIONAR--</option>';
		while($r=mysql_fetch_row($eje)){
			$proveedores.='<option value="'.$r[0].'">'.$r[1].'</option>';
		}
		$proveedores.='</select>';
	//mandamos respuesta
		echo '<button style="padding:15px;position:absolute;top:12%;right:19%;color:white;background:red;"';
		echo ' onclick="document.getElementById(\'emergenteAutorizaTransfer\').style.display=\'none\';">X</button>';
		echo '<table style="color:white;font-size:20px;">';
			echo '<tr><th colspan="2" font-size="30px">Alta de Remision<br><br></th></tr>';
		//seleccion de proveedor
			echo '<tr>';
				echo '<td>Seleccionar proveedor</td>';
				echo '<td>'.$proveedores.'</td>';
			echo '</tr>';
		//folio del proveedor
			echo '<tr>';
				echo '<td>Igrese el folio de proveedor</td>';
				echo '<td><input type="text" id="remision_folio" class="entrada"></td>';
			echo '</tr>';
		//monto
			echo '<tr>';
				echo '<td>Igrese el monto de la remision</td>';
				echo '<td><input type="number" id="remision_monto" style="padding:10px;"></td>';
			echo '</tr>';
		//piezas
			echo '<tr>';
				echo '<td>Igrese el total de piezas de la remision</td>';
				echo '<td><input type="number" id="remision_piezas" style="padding:10px;"></td>';
			echo '</tr>';
		//fecha_remision
			echo '<tr>';
				echo '<td>Igrese la fecha de remision</td>';
				echo '<td><input type="text" id="remision_fecha" onfocus="calendario(this);" class="entrada"></td>';
			echo '</tr>';

			echo '<tr>';
				echo '<td colspan="2" align="center"><br><button id="btn_gda_remision" onclick="guarda_remision();" style="padding:10px;border-radius:10px;">Guardar</button></td>';
			echo '</tr>';
			echo '<br>';

	}

	if($fl=='insertar'){
		$arr=explode("~",$_GET['dats']);
		$sql="INSERT INTO ec_oc_recepcion VALUES(/*1*/null,/*2*/'$arr[0]',/*3*/'$arr[1]',/*4*/0,/*5*/'$arr[2]',/*6*/0,/*7*/0,/*8*/$user_id,/*9*/1,
			/*10*/$arr[3],/*11*/0,/*12*/'$arr[4]',/*13*/now(),/*14*/'0000-00-00 00:00:00')";
		$eje=mysql_query($sql)or die("Error!!!\n".mysql_error()."\n".$sql);
		echo 'Remision registrada exitosamente!!!';
	}
?>