<?php
/*implementación Oscar 2021 para ejecutar consultas con MYSQLI*/
	include('../../../../config.inc.php');
	$link = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
	$link->set_charset("utf8");
//descarga de csv
	if(isset($_POST['fl']) && $_POST['fl']==1){
			//recibimos datos
		$info=$_POST['datos'];
	//creamos el nombre del archivo
		$nombre="exportacion_tabla.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($info));
		die('');
	}
	$id_herr=$_POST['id'];
	$filtros=explode("°",$_POST['arr']);
	//sacamos la consulta
		$sql = "SELECT consulta, tipo_herramienta FROM sys_herramientas WHERE id_herramienta='$id_herr'";
		$eje = $link->query($sql)or die("Error al consultar la base de la herramienta!!!<br>".$link->error."<br>".$sql);
       
		$r = $eje->fetch_row(); 
		$sql = $r[0];
	//sacamos los filtros
		for($i=0;$i<sizeof($filtros);$i++){
			if($filtros[$i]!='' && $filtros[$i]!=null){
				$campos_filtro=explode("~", $filtros[$i]);
				if($id_herr==1 && ($campos_filtro[1]=='$FECHA_1' || $campos_filtro[1]=='$FECHA_2') ){//si es verificacion de pedidos
					$sql_sub="SELECT DATE_FORMAT('$campos_filtro[2]','%Y')";
					$eje_sub = $link->query($sql_sub)or die("Error al formatear la fecha!!!!<br>".$sql_sub);
					$r_sub = $eje_sub->fetch_row();
					$campos_filtro[2] = $r_sub[0];
				}
			//reemplazamos filtros
				if($campos_filtro[2]==0){
					$campos_filtro[0]='';
					$campos_filtro[2]='';
				}
				$sql=str_replace($campos_filtro[1], $campos_filtro[0]."".$campos_filtro[2], $sql);
			}
		}
		echo 'ok|'.$sql.'|';
		$is_update_or_insert = 0;
	//ejecutamos la consulta
		if( ! $eje = $link->query($sql) ){
			$is_update_or_insert = 1;
			$eje = $link->multi_query($sql) or die("Error al ejecutar la consulta!!!<br>". $link->error ."<br>".$sql);	
			die("La herramienta fue ejecutada exitosamente!");
		}
//die('here');
        if( $r[1] == 'Herramienta' ){
            die("Consulta ejecutada exitosamente." );
		}
		
//		$field = mysqli_num_fields($eje);
    	$names;

		$info_campo = mysqli_fetch_fields($eje);

        foreach ($info_campo as $key => $valor) {
            $names[$key] = $valor->name;
        }
		echo '<table class="result" id="grid_resultado" width="100%">';
		/*Oscar 2021 para sumar montos de ventas*/
			$suma_montos = 0;
		/*fin de cambio Oscar 2021*/
		$c=0;
/*implementacion Oscar 2024-12-10 para sumar en consulta de validadores*/
	//consulta de tiempo validadores
		$hours_sum = 0;
		$sales_sum = 0;
		$products_sum = 0;
		$current_user = 0;
		$username = "";
		$first_date = "";
		$last_date = "";
		$total_ventas = 0;
		$total_horas = 0;
		$total_seconds = 0;
/*Fin de cambio Oscar 2024-12-10*/
		while( $r = $eje->fetch_row() ){
			if($c==0){
				echo '<thead class="header_sticky">';
				echo '<tr>';
				if( $id_herr != 47 ){
					for($i=0;$i<sizeof($names);$i++){
						echo '<th class="text-center">'.$names[$i].'</th>';
					}
				}else if( $id_herr == 47 ){
					echo '<th class="text-center">ORDEN LISTA</th>';
					echo '<th class="text-center">NOMBRE</th>';
					echo '<th class="text-center">NOMBRE</th>';
					echo '<th class="text-center">CANT ETIQUETAS</th>';
				}
				echo '</tr>';
				echo '</thead>';
				echo '<tbody id="rows_list">';
			}
/*implementacion Oscar 2024-12-10 para sumar en consulta de validadores*/
			if( $id_herr == 89 ){
			  //  die( 'here' );
				if( $current_user != $r[0] ){//si es un usuario diferente
					if( $current_user != 0 ){
					// Convertir los segundos totales de vuelta a horas, minutos y segundos
						$hours = floor($totalSeconds / 3600);
						$minutes = floor(($totalSeconds % 3600) / 60);
						$seconds = $totalSeconds % 60;
						$total_horas = str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . 
									str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":" . 
									str_pad($seconds, 2, "0", STR_PAD_LEFT);
						echo "<tr>
									<td></td>
									<td></td>
									<td class=\"text-success\">Total {$username}</td>
									<td class=\"text-success\">{$first_date}</td>
									<td class=\"text-success\">{$last_date}</td>
									<td class=\"text-success\">{$hours_sum}</td>
									<td class=\"text-success\">{$products_sum}</td>
									<td class=\"text-success\">{$sales_sum}</td>
									<td class=\"text-success\"></td>
									<td class=\"text-success\">{$total_horas}</td>
									<td class=\"text-success\">-</td>
								</tr>";
						$hours_sum = 0;
						$sales_sum = 0;
						$products_sum = 0;
						$totalSeconds = 0;
						$total_horas = 0;
					}
					$current_user = $r[0];
					
					$hours_sum = 0;
					$sales_sum = 0;
					$products_sum = 0;
					//$current_user = 0;
					$first_date = $r[3];
					$total_horas = 0;
					//$last_date = "";
				}//else{
					$username = $r[2];
					$hours_sum += $r[5];
					$products_sum += $r[6];
					$sales_sum += $r[7];
					$last_date = $r[4];
					$total_ventas += $r[8];
					
					list($h1, $m1, $s1) = explode(":", $r[9]);// Convertir los tiempos a segundos desde la medianoche
					$seconds1 = $h1 * 3600 + $m1 * 60 + $s1;// Calcular el total en segundos
					$totalSeconds += $seconds1;// Sumar los segundos
					//$total_horas += strtotime($r[9]);

				//}
			}
/*Fin de cambio Oscar 2024-12-10*/
/*implementacion Oscar 2024-12-10 para sumar en consulta de validadores*/
			echo '<tr>';
			if( $id_herr != 47 ){
				for($i=0;$i<sizeof($r);$i++){
						echo '<td>'.$r[$i].'</td>';
					
				/*Oscar 2021 para sumar montos de ventas*/
					if ( $id_herr == 17 && $i == 1){
						$suma_montos += $r[$i];
					}
				/*fin de cambio Oscar 2021*/
				}
			}else{
					echo '<td>'.$r[0].'</td>';
					$parts = part_word( $r[1] );
					echo '<td>'.$parts[0].'</td>';
					echo '<td>'.$parts[1].'</td>';
					echo '<td>'.$r[2].'</td>';

				}
			echo '</tr>';
			$c++;
		}
	/*Oscar 2021 para sumar montos de ventas*/
		if ( $id_herr == 17 ){
			echo "<tr><td style=\"text-align : right;\">Total : </td><td>{$suma_montos}</td><td></td></tr>";
		}
	/*fin de cambio Oscar 2021*/

			echo '</tbody>';
		echo '</table>';
?>


<?php
	function part_word( $txt ){
		$size = strlen( $txt );
		$half = round( $size / 2 );
		$words = explode(' ', $txt );
		$resp = array( '','');
		$chars_counter = 0;
		$middle_word = "";
		foreach ($words as $key => $word) {
			$is_middle = 0;
			if( $key > 0 ){
				$chars_counter ++;//espacio
				if( $chars_counter == $half ){
					$is_middle = 1;
				}
			}
			for( $i = 0; $i < strlen( $word ); $i ++ ){
				$chars_counter ++;//palabras
				if( $chars_counter == $half || $is_middle == 1){
					$middle_word = $word;
					$is_middle = 1;
				}
			}
			if( $middle_word == '' ){
				$resp[0] .= ( $resp[0] != '' ? ' ' : '' );
				$resp[0] .= $word;
			}else if( $middle_word != '' && $is_middle == 0 ){
				$resp[1] .= ( $resp[1] != '' ? ' ' : '' );
				$resp[1] .= $word;
			}
			$is_middle = 0;
		}
		if( strlen( "{$resp[0]} {$middle_word}" ) < strlen( "{$middle_word} {$resp[1]}" )  ){//asigna palabra intermedia a primera parte
			$resp[0] = "{$resp[0]} {$middle_word}";
		}else{//asigna palabra intermedia a segunda parte
			$resp[1] = "{$middle_word} {$resp[1]}";
		}
		return $resp;
	}
?>
