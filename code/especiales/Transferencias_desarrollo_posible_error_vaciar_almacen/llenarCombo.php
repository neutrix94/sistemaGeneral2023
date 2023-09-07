<?php
class llenarCombo{
	public function llenarSucursal(){
		$query='select nombre from sys_sucursales';
		$extraer=mysql_query($query);
		if($extraer){
		while($row=mysql_fetch_assoc($extraer)){
			echo 'resultado:<br>';
			?>
			<option><?php echo'<font color="#000099">'.$row['nombre'];?></option>
			<?php
			}
			}else{
							echo 'algo anda mal en la consulta';
						}
		$sucursales=$row;
		}
	}
?>