<?php
	include("../../../conectMin.php");
echo '<center style="color:white; ">';
	$id_rep=$_POST['id_herramienta'];

	$sql_fam="SELECT -1,'Mostrar Todos' UNION(SELECT id_categoria,nombre FROM ec_categoria)";
	$eje_fam=mysql_query($sql_fam)or die("Erorr al consultar las familias para llenar el combo!!!\n\n".mysql_error());

if($id_rep==1){
	echo 'Familia: <select id="familia" onchange="cambia_tipo">';
	while($row_fam=mysql_fetch_row($eje_fam)){
		echo '<option value="'.$row_fam[0].'">'.$row_fam[1].'</option>';
	}
	echo '</select>';
}
echo '</center>';

/*
<select id="tipo">
	<option>Mostrar Todos</option>
</select>


<div>
	<input type="text" id="buscador_input" onkeyup="buscar_prod()">
</div>*/

?>
