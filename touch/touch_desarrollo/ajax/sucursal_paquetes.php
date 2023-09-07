<?php
	include("../../conectMin.php");

	$sql="SELECT sp.id_sucursal_paquete,
	s.nombre,
	sp.estado_suc 
	FROM sys_sucursales_paquete sp
	LEFT JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal
	WHERE sp.paquete=$id_paquete
	AND (IF $user_sucursal=-1, sp.id_sucursal>0,sp.id_sucursal=$user_sucursal)";

	$eje=mysql_query($sql)or die("Error al consultar datos de paquete en sucursal!!!\n\n".mysql_error()."\n\n".$sql);

	echo 'ok|';
?>
	<table>
		<tr>
			<th>Sucursal</th>
			<th>Habilitado</th>
		</tr>
<?php
	$c=0;
	while($r=mysql_fetch_row($eje)){		
		$c++;
		echo '<tr>';
			echo '<td id="sp_0_'.$c.'" style="display: none;">'.$r[0].'</td>';
			echo '<td id="sp_1_'.$c.'">'.$r[1].'</td>';
		if($r[2]==1){$chk=' checked';}else{$chk='';}
			echo '<td><input type="checkbox" id="sp_2_'.$c.'"'.$chk.'></td>';
		echo '</tr>';
	}//fin de while
?>
	</table>