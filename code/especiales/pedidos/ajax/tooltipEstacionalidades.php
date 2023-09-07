<?php
	include('../../../../conectMin.php');//incluimos libreria de conexion
	$id_sucursal=$_POST['id_suc_'];
	$sql="SELECT 
   			ax.nombre,
   			IF(eh.cantidad IS NULL,0, eh.cantidad),/*implementacion Oscar 2021 para mostrar todas las categorias*/
   			ax.actual,
   			ax.suma_actual,
   			IF(eh.suma_estacionalidades IS NULL, 0, eh.suma_estacionalidades)/*implementacion Oscar 2021 para mostrar todas las categorias*/
   		FROM(
   			SELECT
   				c.nombre,
    			SUM(/*implementacion Oscar 2021 para mostrar todas las categorias*/
    				IF(
    					ep.id_estacionalidad = e.id_estacionalidad
    					AND ep.maximo > 0,
    					1,
    					0
    				)
    				) as actual,
			
    			c.id_categoria,
    			SUM(ep.maximo) as suma_actual,
    			e.id_sucursal,
    			e.id_estacionalidad
			FROM ec_estacionalidad e
			LEFT JOIN ec_estacionalidad_producto ep ON ep.id_estacionalidad=e.id_estacionalidad
			LEFT JOIN ec_productos p ON p.id_productos=ep.id_producto
			LEFT JOIN ec_categoria c ON c.id_categoria=p.id_categoria
			WHERE p.id_productos>0 
			/*AND ep.maximo>0 /*comentado por Oscar 2021*/
			AND e.id_sucursal=$id_sucursal
			AND e.es_alta=1
			/*AND c.id_categoria IS NOT NULL /*comentado por Oscar 2021*/
			GROUP by e.id_sucursal,p.id_categoria,e.id_estacionalidad
			ORDER by e.id_sucursal
		)ax
		LEFT JOIN ec_historico_estacionalidad_resumen eh ON ax.id_categoria=eh.id_categoria
		/*implementacion Oscar 2021 para mostrar todas las categorias*/
		AND eh.id_sucursal=$id_sucursal
		GROUP by ax.id_sucursal,ax.id_categoria,ax.id_estacionalidad
		ORDER by ax.id_sucursal
		/*Fin de cambio Oscar 2021*/";

	$eje=mysql_query($sql)or die("Error al consultar histórico y esatcionalidad actual!!!".mysql_error());
	echo '<button style="background:red;position:absolute;right:0;top:0px;"'.
	'onclick="document.getElementById(\'simula_tooltip\').style.display=\'none\';" class="btn btn-danger">X</button>';
//armamos la tabla
	echo '<center><br><table width="90%" class="table table-striped table-bordered;">';
		echo '<thead style="position:sticky; top : 0;"><tr>';
			echo '<th width="20%" style="background-color : red;">FAMILIA</th>';
			echo '<th width="20%" style="background-color : red;">CANT ANT</th>';
			echo '<th width="20%" style="background-color : red;">CANT ACT</th>';
			echo '<th width="20%" style="background-color : red;">SUMA ANT</th>';
			echo '<th width="20%" style="background-color : red;">SUMA ACT</th>';
		echo '</tr></thead><tbody>';
	$suma_1=0;$suma_2=0;$suma_3=0;$suma_4=0;
	$cont_tool=0;
	while($r=mysql_fetch_row($eje)){
		$cont_tool++;
		if($cont_tool%2==0){
			$color_fila_tooltip='style="background:#D3D3D3;"';
		}else{
			$color_fila_tooltip='style="background:white;"';
		}		
		echo '<tr '.$color_fila_tooltip.'>';
			echo '<td width="20%" align="left">'.$r[0].'</th>';
			echo '<td width="20%" align="center">'.$r[1].'</th>';
			echo '<td width="20%" align="center">'.$r[2].'</th>';
			echo '<td width="20%" align="center">'.$r[4].'</th>';
			echo '<td width="20%" align="center">'.$r[3].'</th>';
		echo '</tr>';
		echo '</tr>';
		$suma_1+=$r[1];$suma_2+=$r[2];$suma_3+=$r[4];$suma_4+=$r[3];
	}
	if($cont_tool%2!=0){
			$color_fila_tooltip='style="background:#D3D3D3;"';
		}else{
			$color_fila_tooltip='style="background:white;"';
		}
	echo '</tbody><tr '.$color_fila_tooltip.'><td></td><td align="right">'.$suma_1.'</td><td align="right">'.$suma_2.'</td></td><td align="right">'.$suma_3.'</td></td><td align="right">'.$suma_4.'</td><tr>';

	echo '</table></center>';

?>