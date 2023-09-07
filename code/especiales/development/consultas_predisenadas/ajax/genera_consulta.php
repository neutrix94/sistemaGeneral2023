<?php
/*implementación Oscar 2021 para ejecutar consultas con MYSQLI*/
	include('../../../../../config.inc.php');
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
	$sql = "SELECT 
	        	TRIM(value) AS path
	        FROM api_config WHERE name = 'path'";
	$stm = $link->query( $sql ) or die( "Error al consultar path de api : {$this->link->error}" );
	$config_row = $stm->fetch_assoc();
	$api_path = $config_row['path']."/rest/v1/";
	//$api_path = "https://www.sistemageneralcasa.com/test2023/sistemaGeneral2023/rest/v1/";
	//die( $api_path );

	$id_herr=$_POST['id'];
	$filtros=explode("°",$_POST['arr']);
	//sacamos la consulta
		$sql = "SELECT 
					consulta AS query, 
					tipo_herramienta AS tool_type 
				FROM sys_herramientas_desarrollo 
				WHERE id_herramienta_desarrollo ='{$id_herr}'";
		$eje = $link->query($sql)or die("Error al consultar la base de la herramienta!!!<br>".$link->error."<br>".$sql);
       
		$r = $eje->fetch_row();
		$sql = $r[0];
		$tool_type = $r[1];
	//sacamos los filtros
		for($i=0;$i<sizeof($filtros);$i++){
			if($filtros[$i]!='' && $filtros[$i]!=null){
				$campos_filtro=explode("~", $filtros[$i]);
				/*if($id_herr==1 && ($campos_filtro[1]=='$FECHA_1' || $campos_filtro[1]=='$FECHA_2') ){//si es verificacion de pedidos
					$sql_sub="SELECT DATE_FORMAT('$campos_filtro[2]','%Y')";
					$eje_sub = $link->query($sql_sub)or die("Error al formatear la fecha!!!!<br>".$sql_sub);
					$r_sub = $eje_sub->fetch_row();
					$campos_filtro[2] = $r_sub[0];
				}*/
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

	/*API*/
		$petition_data = array( "QUERY"=>$sql );
		$post_data = json_encode( $petition_data );
		$resp = "";
		$crl = curl_init( "{$api_path}" );
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($crl, CURLINFO_HEADER_OUT, true);
		curl_setopt($crl, CURLOPT_POST, true);
		curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
		//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
		curl_setopt($crl, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'token: ' . $token)
		);
		$resp = curl_exec($crl);//envia peticion
		curl_close($crl);
		//var_dump($resp);
		$response = json_decode($resp);
		//var_dump($response);
	/*API*/
		echo '<table class="result" id="grid_resultado" width="100%">';
		/*Oscar 2021 para sumar montos de ventas*/
			$suma_montos = 0;
		/*fin de cambio Oscar 2021*/
		$c=0;
		while( $r = $eje->fetch_row() ){
		if($c==0){
			echo '<thead class="header_sticky">';
			echo '<tr>';
			for($i=1;$i<sizeof($names);$i++){
				echo '<th class="text-center">'.$names[$i].'</th>';
			}
			if( $tool_type == 'Horizontal' ){
				for($i=1;$i<sizeof($names);$i++){
					echo '<th class="text-center">'.$names[$i].'</th>';
				}
			}
			echo '</tr>';
			echo '</thead>';
			echo '<tbody id="rows_list">';
		}
			echo '<tr>';
			for($i=1;$i<sizeof($r);$i++){
					echo '<td>'.$r[$i].'</td>';
			}
			if ( $tool_type == 'Vertical' ) {
				echo '</tr><tr>';
			}
			//echo '</tr>';
			foreach ($response as $key => $reg) {
				if( $reg->PRIMARY_KEY_FIELD == $r[0] ){
				//echo '<tr>';
					$c2 = 0;
					foreach ( $reg as $key2 => $value ) {
						echo ( $c2 == 0 ? "" : "<td>{$value}</td>" );
						$c2 ++;
					}
					echo '</tr>';
				}
			}
			$c++;
		}

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
