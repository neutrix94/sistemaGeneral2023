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

	echo 'ok|<br><br><br><div class="row text-center" style="background : white; font-size : 150% !important;">';
		echo '<p style="color:black; font-size : 200% !important;">Paquete: '.$nombre_pqt[0].'</p>';
		echo '<table>';
		echo '<thead>';
			echo '<tr>';
				echo '<th class="text-center">Sucursal</th>';
				echo '<th class="text-center">Estado</th>';
			echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
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
			echo '<td class="text-center">'.$r[1].'</td>';
			echo '<td class="text-center"><input type="checkbox" style="transform : scale( 1.5 );" id="sp_1_'.$c.'" '.$check.'></td>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo '<input type="hidden" id="total_sp" value="'.$c.'">';
?>
	<br><br><br>
	<div class="row">
		<div class="col-3"></div>
		<div class="col-6">
			<button 
				class="btn btn-success form-control"
				onclick="guarda_config_pqt(<?php echo $c;?>);">
				<i class="icon-floppy">Guardar</i>
			</button>
	<br><br>
		</div>
	</div>
</div>