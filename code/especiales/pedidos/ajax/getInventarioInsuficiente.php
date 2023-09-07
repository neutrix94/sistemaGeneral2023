<?php
	include('../../../../conectMin.php');
	$producto=$_POST['id_prd'];
	$sucursal=$_POST['id_suc'];
	$fecha_1=$_POST['fcha_del'];
	$fecha_2=$_POST['fcha_al'];
	$sql="SELECT observaciones,alta FROM ec_productos_sin_inventario 
	WHERE id_sucursal IN($sucursal) AND id_producto=$producto AND (alta BETWEEN '$fecha_1 00:00:01' AND '$fecha_2 23:59:59')";
	//die($sql);
	$eje=mysql_query($sql)or die("Errorr al consultar productos que no tenian inventario suficiente!!!<br>".mysql_error()."<br>".$sql);	
	echo '<br>';
	echo '<p align="right"><button type="button" style="padding:10px;color:white;background:rgba(225,0,0,.8);"';//position:fixed;right:18%;top:20%;
	echo ' onclick="document.getElementById(\'subemergente\').style.display=\'none\';">';
	echo 'X</button></p><br><br>';
	echo '<table width="100%;" style="background:white;position:absolute;width:45%;left:27.5%;top:20%;">';
	echo '<tr><th>Descripción</th><th>Fecha</th></tr>';
	while($r=mysql_fetch_row($eje)){
		echo '<tr>';
			echo '<td width="70%">'.$r[0].'</td>';
			echo '<td width="30%" align="center">'.$r[1].'</td>';
		echo '</tr>';
	}
	echo '</table>';
?>