<?php
//die( 'here' );
	if( isset( $_POST['fl'] ) || isset( $_GET['fl'] ) ){
		include( '../../../../config.inc.php' );
		include( '../../../../conexionMysqli.php' );
		$action = ( isset( $_POST['fl'] ) ? $_POST['fl'] : $_GET['fl'] );
		$DR = new DinamicReports( $link );
		switch ( $action ) {
			case 'download_csv' :
				$info = ( isset( $_POST['datos'] ) ? $_POST['datos'] : $_GET['datos'] );
				$DR->download_csv( $info );
			break;

			case 'queryExecution' :
				$report_id = ( isset( $_POST['id'] ) ? $_POST['id'] : $_GET['id'] );
				$filters = explode("°", ( isset( $_POST['arr'] ) ? $_POST['arr'] : $_GET['arr'] ) );
				if( $report_id == 1 ){
					echo $DR->queryComparation( $report_id, $filters );
				}else{
					echo $DR->execute_query( $report_id, $filters );
				}
			break;
			
	//comparaciones
			case 'queryComparation' :
				$report_id = ( isset( $_POST['id'] ) ? $_POST['id'] : $_GET['id'] );
				$filters = explode("°", ( isset( $_POST['arr'] ) ? $_POST['arr'] : $_GET['arr'] ) );
				echo $DR->queryComparation( $report_id, $filters );
			break;
			
			default:
				die( "Access Denied on '{$action}'" );
			break;
		}
	}

	class DinamicReports
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}

		public function download_csv( $info ){
			$nombre="exportacionReporteDinamico.csv";//nombre del archivo
		//genera descarga
			header('Content-Type: aplication/octect-stream');
			header('Content-Transfer-Encoding: Binary');
			header('Content-Disposition: attachment; filename="'.$nombre.'"');
			echo( utf8_decode( $info ) );
			die('');
		}

		public function execute_query( $id_herr, $filtros ){
		//consulta
			$sql = "SELECT consulta, tipo_herramienta FROM sys_reportes_dinamicos WHERE id_reporte_dinamico = '$id_herr'";
			$eje = $this->link->query( $sql )or die("Error al consultar la base de la herramienta!!!<br> {$this->link->error} <br>{$sql}" );
	       
			$r = $eje->fetch_row(); 
			$sql = $r[0];
		//sacamos los filtros
			for($i=0;$i<sizeof($filtros);$i++){
				if($filtros[$i]!='' && $filtros[$i]!=null){
					$campos_filtro=explode("~", $filtros[$i]);
					if($id_herr==1 && ($campos_filtro[1]=='$FECHA_1' || $campos_filtro[1]=='$FECHA_2') ){//si es verificacion de pedidos
						$sql_sub="SELECT DATE_FORMAT('$campos_filtro[2]','%Y')";
						$eje_sub = $this->link->query($sql_sub)or die("Error al formatear la fecha!!!!<br>".$sql_sub);
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
			$stm = '';
			$is_update_or_insert = 0;
		//ejecutamos la consulta
			if( ! $stm = $this->link->query($sql) ){
				$is_update_or_insert = 1;
				$stm = $this->link->multi_query( $sql ) or die( "Error al ejecutar la consulta!!!<br>{$this->link->error}<br>{$sql}" );	
				die("La herramienta fue ejecutada exitosamente!");
			}
	//die('here');
	        if( $r[1] == 'Herramienta' ){
	            die("Consulta ejecutada exitosamente." );
			}
			echo $this->buildReportFrontEnd( $stm, $id_herr );
		}

		public function buildReportFrontEnd( $stm, $report_id ){
	    	$resp = "";
	    	$names;

	    //consulta de tiempo validadores
	    	$hours_sum = 0;
	    	$sales_sum = 0;
	    	$products_sum = 0;
	    	$current_user = 0;
	    	$username = "";
	    	$first_date = "";
	    	$last_date = "";

			$info_campo = $stm->fetch_fields();

	        foreach ($info_campo as $key => $valor) {
	            $names[$key] = $valor->name;
	        }
			$resp .= '<table class="result" id="grid_resultado" width="100%">';
			$c=0;
			while( $r = $stm->fetch_row() ){
				if($c==0){
				//formacion encabezado
					$resp .= '<thead class="header_sticky">';
					$resp .= '<tr>';
					for($i=0;$i<sizeof($names);$i++){
						$resp .= '<th class="text-center">'.$names[$i].'</th>';
					}
					$resp .= '</tr>';
					$resp .= '</thead>';
					$resp .= '<tbody id="rows_list">';
				}
				if( $report_id == 2 ){
				  //  die( 'here' );
					if( $current_user != $r[0] ){//si es un usuario diferente
						if( $current_user != 0 ){
							$resp .= "<tr>
								<td></td><td></td>
								<td class=\"text-success\">Total {$username}</td>
								<td class=\"text-success\">{$first_date}</td>
								<td class=\"text-success\">{$last_date}</td>
								<td class=\"text-success\">{$hours_sum}</td>
								<td class=\"text-success\">{$products_sum}</td>
								<td class=\"text-success\">{$sales_sum}</td>
							</tr>";
						}
						$current_user = $r[0];
						
				    	$hours_sum = 0;
				    	$sales_sum = 0;
				    	$products_sum = 0;
				    	//$current_user = 0;
						$first_date = $r[3];
				    	//$last_date = "";
					}//else{
						$username = $r[2];
						$hours_sum += $r[5];
						$products_sum += $r[6];
						$sales_sum += $r[7];
						$last_date = $r[4];

					//}
				}
			//formacion filas
				$resp .= '<tr>';
				for($i=0;$i<sizeof($r);$i++){
					$resp .= '<td>'.$r[$i].'</td>';
				}
				$resp .= '</tr>';
				$c++;
			}

				$resp .= '</tbody>';
			$resp .= '</table>';
			return $resp;
		}
/*comparaciones*/
		public function queryComparation( $report_id, $filters ){
			$first_array = array();
			$second_array = array();
			$filter = explode('~', $filters[0] );
			$store_filter = $filter[2];
			$filter = explode('~', $filters[1] );
			$sql = "SELECT
						p.id_productos,
						p.orden_lista,
						p.nombre,
						REPLACE(p.clave, ',', '' ) AS clave,
						SUM(tp.cantidad) AS cantidad
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_transferencias t
					ON tp.id_transferencia = t.id_transferencia
					LEFT JOIN ec_productos p
					ON p.id_productos = tp.id_producto_or
					WHERE t.titulo_transferencia = '{$filter[2]}' 
					OR t.folio = '{$filter[2]}'
					AND t.id_sucursal_destino = {$store_filter}
					GROUP BY tp.id_producto_or
					ORDER BY p.orden_lista";
			$stm = $this->link->query( $sql ) or die( "Error al consultar la primera consulta de comparacion : {$this->link->error} : {$sql}" );
			while( $row = $stm->fetch_assoc() ){
				$first_array[] = $row;
			}
			//echo $sql . "<br>";
			
			$filter = explode('~', $filters[2] );
			//var_dump( $filters );
			$sql = "SELECT
						p.id_productos,
						p.orden_lista,
						p.nombre,
						REPLACE(p.clave, ',', '' ) AS clave,
						pd.cantidad_producto
					FROM ec_paquete_detalle pd
					LEFT JOIN ec_paquetes pq
					ON pd.id_paquete = pq.id_paquete
					LEFT JOIN ec_productos p
					ON p.id_productos = pd.id_producto
					WHERE pq.id_sucursal_creacion = {$store_filter}
					AND pq.id_paquete = '{$filter[2]}'
					OR pq.nombre = '{$filter[2]}'
					GROUP BY pd.id_producto
					ORDER BY p.orden_lista";
			$stm = $this->link->query( $sql ) or die( "Error al consultar la segunda consulta de comparacion : {$this->link->error} : {$sql}" );
			while( $row = $stm->fetch_assoc() ){
				$second_array[] = $row;
			}
			//echo $sql . "\n";
			$this->createComparation( $first_array, $second_array );
		}

		public function createComparation( $first_array, $second_array ){
			//var_dump($first_array);
			//var_dump($second_array);
			$correctly_rows = array();
			$not_in_pack = array();
			$not_in_transfer = array();
			$different_quantity = array();
		//busca sobre transferencias
		//	var_dump($first_array[0]);
		//	die( "HERE : "  );
			for( $i = 0; $i < sizeof( $first_array ); $i++ ){
				$exists_in_pack = false;
				for( $j = 0; $j < sizeof( $second_array ); $j++ ){
					if( $first_array[$i]['id_productos'] == $second_array[$j]['id_productos'] ){
						$exists_in_pack = true;
						if( $first_array[$i]['cantidad'] != $second_array[$j]['cantidad_producto'] ){
							$different_quantity[] = array( $first_array[$i], $second_array[$j] );//cantidades diferente
						}else{
							$correctly_rows[] = array( $first_array[$i], $second_array[$j] );//match correcto
						}
					}
				}	
				if( $exists_in_pack == false ){//no existe en el paquete
					$not_in_pack[] = $first_array[$i];
				}		
			}
		//busca sobre paquete
			for( $i = 0; $i < sizeof( $second_array ); $i++ ){
				$exists_in_transfer = false;
				for( $j = 0; $j < sizeof( $first_array ); $j++ ){
					if( $second_array[$i]['id_productos'] == $first_array[$j]['id_productos'] ){
						$exists_in_transfer = true;
						/*if( $second_array[$i]['cantidad_producto'] != $first_array[$j]['cantidad'] ){
							$different_quantity[] = array( $second_array[$i], $first_array[$j] );//cantidades diferente
						}else{
							$correctly_rows[] = array( $second_array[$i], $first_array[$j] );//match correcto
						}*/
					}
				}
				if( $exists_in_transfer == false ){//no existe en el paquete
					$not_in_transfer[] = $second_array[$i];
				}			
			}
			return $this->BuildReportComparation( $correctly_rows, $not_in_pack, $not_in_transfer, $different_quantity );
		}

		public function BuildReportComparation( $correctly_rows, $not_in_pack, $not_in_transfer, $different_quantity ){
		//
			$resp = 'ok|Comparacion1|<table class="result" id="grid_resultado" width="100%">';
			$resp .= "<thead class=\"header_sticky\">
					<tr>
						<th colspan=\"5\" class=\"text-center\">Transferencia</th>
						<th colspan=\"5\" class=\"text-center\">Paquete</th>
					</tr>
					<tr>
						<th>Id Producto</th>
						<th>Orden Lista</th>
						<th>Nombre</th>
						<th>Modelo</th>
						<th>Cantidad</th>
						<th>Id Producto</th>
						<th>Orden Lista</th>
						<th>Nombre</th>
						<th>Modelo</th>
						<th>Cantidad</th>
					</tr>";
			$resp .= '</tr>';
			$resp .= '</thead>';
			$resp .= '<tbody id="rows_list">';

		//correctos
			foreach ($correctly_rows as $key => $value) {
				$resp .= "<tr>
					<td class=\"text-success\">{$correctly_rows[$key][0]['id_productos']}</td>
					<td class=\"text-success\">{$correctly_rows[$key][0]['orden_lista']}</td>
					<td class=\"text-success\">{$correctly_rows[$key][0]['nombre']}</td>
					<td class=\"text-success\">{$correctly_rows[$key][0]['clave']}</td>
					<td class=\"text-success\">{$correctly_rows[$key][0]['cantidad']}</td>
					<td class=\"text-success\">{$correctly_rows[$key][1]['id_productos']}</td>
					<td class=\"text-success\">{$correctly_rows[$key][1]['orden_lista']}</td>
					<td class=\"text-success\">{$correctly_rows[$key][1]['nombre']}</td>
					<td class=\"text-success\">{$correctly_rows[$key][1]['clave']}</td>
					<td class=\"text-success\">{$correctly_rows[$key][1]['cantidad_producto']}</td>
				</tr>";
			}
		//diferencia en cantidades
			foreach ($different_quantity as $key => $value) {
				$resp .= "<tr>
					<td class=\"text-danger\">{$different_quantity[$key][0]['id_productos']}</td>
					<td class=\"text-danger\">{$different_quantity[$key][0]['orden_lista']}</td>
					<td class=\"text-danger\">{$different_quantity[$key][0]['nombre']}</td>
					<td class=\"text-danger\">{$different_quantity[$key][0]['clave']}</td>
					<td class=\"text-danger\">{$different_quantity[$key][0]['cantidad']}</td>
					<td class=\"text-danger\">{$different_quantity[$key][1]['id_productos']}</td>
					<td class=\"text-danger\">{$different_quantity[$key][1]['orden_lista']}</td>
					<td class=\"text-danger\">{$different_quantity[$key][1]['nombre']}</td>
					<td class=\"text-danger\">{$different_quantity[$key][1]['clave']}</td>
					<td class=\"text-danger\">{$different_quantity[$key][1]['cantidad_producto']}</td>
				</tr>";
			}
		//no estan en paquetes
			foreach ($not_in_pack as $key => $value) {
				//echo 'here ';
				$resp .= "<tr>
					<td class=\"text-primary\">{$not_in_pack[$key]['id_productos']}</td>
					<td class=\"text-primary\">{$not_in_pack[$key]['orden_lista']}</td>
					<td class=\"text-primary\">{$not_in_pack[$key]['nombre']}</td>
					<td class=\"text-primary\">{$not_in_pack[$key]['clave']}</td>
					<td class=\"text-primary\">{$not_in_pack[$key]['cantidad']}</td>
					<td class=\"text-primary\"></td>
					<td class=\"text-primary\"></td>
					<td class=\"text-primary\"></td>
					<td class=\"text-primary\"></td>
					<td class=\"text-primary\"></td>
				</tr>";
			}
		//	echo $resp;
		//no estan en transferencias
			foreach ($not_in_transfer as $key => $value) {
				$resp .= "<tr>
					<td class=\"text-warning\"></td>
					<td class=\"text-warning\"></td>
					<td class=\"text-warning\"></td>
					<td class=\"text-warning\"></td>
					<td class=\"text-warning\"></td>
					<td class=\"text-warning\">{$not_in_transfer[$key]['id_productos']}</td>
					<td class=\"text-warning\">{$not_in_transfer[$key]['orden_lista']}</td>
					<td class=\"text-warning\">{$not_in_transfer[$key]['nombre']}</td>
					<td class=\"text-warning\">{$not_in_transfer[$key]['clave']}</td>
					<td class=\"text-warning\">{$not_in_transfer[$key]['cantidad_producto']}</td>
				</tr>";
			}

			$resp .= "</tbody>
					</table>
			<div class=\"row\">
				<div class=\"col-3\">
					<i class=\"icon-bookmark text-success\">Coincide</i>
				</div>
				<div class=\"col-3\">
					<i class=\"icon-bookmark text-dager\">Diferencia en cantidad</i>
				</div>
				<div class=\"col-3\">
					<i class=\"icon-bookmark text-primary\">No está en paquete</i>
				</div>
				<div class=\"col-3\">
					<i class=\"icon-bookmark text-warning\">No está en Transferencia</i>
				</div>
			</div>";
			die( $resp );
		}
	}
	
	
?>
