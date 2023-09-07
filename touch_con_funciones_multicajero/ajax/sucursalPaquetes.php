<?php
	include("../../conectMin.php");
	$id_paq=$_POST['id_pqt'];
	$sql="SELECT nombre FROm ec_paquetes WHERE id_paquete=$id_paq";
	$eje=mysql_query($sql)or die("Error al iconsultar el nombre del Paquete!!!\n\n".mysql_error());
	$nombre_pqt=mysql_fetch_row($eje);
	$sql="SELECT 
			sp.id_sucursal_paquete,
			s.nombre,
			sp.estado_suc
		FROM sys_sucursales_paquete sp
		LEFT JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal
		WHERE sp.id_paquete='$id_paq'
		AND IF('$user_sucursal'=-1,s.id_sucursal>0,s.id_sucursal='$user_sucursal')";

	$eje=mysql_query($sql)or die("Error el consultar listados de paquetes!!!\n".$sql);

	echo 'ok|<center><p style="color:white;">Paquete: '.$nombre_pqt[0].'</p>';
	echo '<table>';
		echo '<tr>';
			echo '<th>Sucursal</th>';
			echo '<th>Estado</th>';
		echo '</tr>';
	$c=0;
	while($r=mysql_fetch_row($eje)){
		$c++;
		if($r[2]==1){
			$check='checked';
		}else{
			$check='';
		}
		echo '<tr>';
			echo '<td id="sp_0_'.$c.'" style="display:none;">'.$r[0].'</td>';
			echo '<td>'.$r[1].'</td>';
			echo '<td align="center"><input type="checkbox" id="sp_1_'.$c.'" '.$check.'></td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '<input type="hidden" id="total_sp" value="'.$c.'">';
?>
<br>
<button onclick="guarda_config_pqt(<?php echo $c;?>);" style="border-radius: 50%;padding: 10px;">
	<img src="img/save.png" width="40px"><br>
	<b style="color:white;">Guardar<b/>
</button>
</center>