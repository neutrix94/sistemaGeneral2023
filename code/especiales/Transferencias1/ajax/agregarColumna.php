<?php
	include('../../../../conectMin.php');
	$id=$_POST['id_paq'];//recibimos el id del paquete
	$sql="SELECT 
				id_producto,
				cantidad_producto 
			FROM ec_paquete_detalle 
			WHERE id_paquete=$id";
	$eje=mysql_query($sql)or die("Error al consultar los datos del paquete!!!\n\n".$sql."\n\n".mysql_error());
//regresamos datos
	echo 'ok|';
	while($r=mysql_fetch_row($eje)){
		echo $r[0]."~".$r[1]."|";
	}
?>

<?php
	/*$c=$_POST['c'];
		if($c%2==0){
			echo '<tr id="fila'.$c.'" bgcolor="#FFFF99">';
			}else{
				echo '<tr id="fila'.$c.'" bgcolor="#CCCCCC">';
				}

		echo '<td align="center" width="6%"><input type="text" class="pedir"></td>
		<td align="left" width="40%"></td>
		<td align="center" width="10%"></td>
		<td align="center" width="10%"></td>
		<td width="10%" align="center"><select class="pedir" id="emp'.$row['ID']' onchange="operacion(.'$row['ID']')">';
		$sql1="select cantidad from ec_productos_presentaciones where id_producto=".$row['ID'];
		$extrae=mysql_query($sql1);
		while($rowPresentacion=mysql_fetch_assoc($extrae)){
		?>
        <option value="'.$rowPresentacion['cantidad']'">emp='.$rowPresentacion['cantidad']'</option>
        <?php
		}
		?>
        <option value="1">Pieza</option>
        </select>
		</td>
		<td align="center" width="7.5%"><input type="text" class="pedir" id="cant'.$row['ID'].'" value="'.$row['CantidadPresentacion'].'" onkeyup="operacion('.$row['ID'].')/></td>
		<td align="center" width="7.5%"><p id="<?php echo "tot".$row['ID'];?>"><?php echo $row['cantidadSurtir'];?></p></td>
		<td align="center" width="9%"><a href="javascript:eliminarFila('<?php echo $c;?>','1');" style="text-decoration:none;">
		<font color="#FF0000" size="+3"><i class="icon-cancel-circled"></i></font><font size="-1">&nbsp;</font><font size="-1">.</font></td></a>';
</tr>';
	$c++;
*/?>

<?php