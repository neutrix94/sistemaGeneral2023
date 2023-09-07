<?php 
	if(isset($_POST['fl']) && $_POST['fl']==1){
			//recibimos datos
		$info=$_POST['datos'];
	//creamos el nombre del archivo
		$nombre="comprobacion_imagenes.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($info));
		die('');
	}
?>
<link rel="stylesheet" type="text/css" href="../../css/bootstrap/css/bootstrap.css">
<script type="text/javascript" src="../../js/jquery-1.10.2.min.js"></script>
<div class="row">
	<div class="col-4 text-center">
		<span style="background-color : red" class="btn">Faltantes</span>
	</div>
	<div class="col-4 text-center">
		<span style="background-color : orange" class="btn">No tomadas</span>
	</div>
	<div class="col-4 text-center">
		<button
			class="btn btn-primary"
			onclick="export_table_rows();"
		>
			Exportar
		</button>
	</div>
</div>
<form id="TheForm" method="post" action="verificacion_imagenes_paquetes.php" target="TheWindow">
	<input type="hidden" name="fl" value="1" />
	<input type="hidden" id="datos" name="datos" value=""/>
</form>
<table class="table table-striped table-bordered">
	<thead class="hedaer_fixed" id="list_header">
		<tr>
			<th class="text-center">Nombre</th>
			<th class="text-center">Modelo</th>
			<th class="text-center">Inventario</th>
			<th class="text-center">Ubicacion</th>
			<th class="text-center">Ruta img</th>
			<th class="text-center">Orden Lista</th>
			<th class="text-center">Id Proveedor Producto</th>
			<th class="text-center">Piezas por caja</th>
			<th class="text-center">Inventario Matriz</th>
		</tr>
	</thead>
	<tbody id="list_content">
<?php
	include( '../../conexionMysqli.php' );
		$sql = "SELECT
					ax.id,
					ax.provider_clue,
					ax.product_name,
					ax.up_img,
					ax.front_img,
					ax.lateral_img,
					ax.inventory,
					IF( ppua.id_ubicacion_matriz IS NULL,
						'Sin Ubicacion',
						CONCAT( 'DE : ', ppua.letra_ubicacion_desde, ppua.numero_ubicacion_desde,
							' HASTA : ',  ppua.letra_ubicacion_desde, ppua.numero_ubicacion_desde,
							IF( ppua.pasillo_desde <> '', CONCAT( '<br> Pasillo : ', ppua.pasillo_desde, ' - ', ppua.pasillo_hasta  ), '' ),
							IF( ppua.altura_desde <> '', CONCAT( '<br> Altura : ', ppua.altura_desde, ' - ', ppua.altura_hasta  ), '' )
						)
					 ) AS location,
					ax.list_order,
					ax.id_proveedor_producto,
					ax.presentacion_caja,
					ax.matrizInventory
				FROM(
				SELECT
					pp.id_proveedor_producto AS id,
					pp.clave_proveedor AS provider_clue,
					p.nombre AS product_name, 
					ppm.imagen_paquete_superior AS up_img,
					ppm.imagen_paquete_frontal AS front_img,
					ppm.imagen_paquete_lateral AS lateral_img,
					ROUND( SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL, 
							0, 
							( mdpp.cantidad * tm.afecta ) 
						) 
					) ) AS inventory,
					p.orden_lista AS list_order,
					pp.id_proveedor_producto,
					pp.presentacion_caja,
					ROUND( SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL OR mdpp.id_almacen != 1, 
							0, 
							( mdpp.cantidad * tm.afecta ) 
						) 
					) ) AS matrizInventory
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_productos p
				ON p.id_productos = pp.id_producto
				LEFT JOIN ec_proveedor_producto_medidas ppm
				ON pp.id_proveedor_producto = ppm.id_proveedor_producto
				LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
				ON mdpp.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN ec_tipos_movimiento tm 
				ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
				WHERE pp.id_proveedor_producto IS NOT NULL
				AND p.id_productos > 0
				GROUP BY pp.id_proveedor_producto
				ORDER BY p.orden_lista
			)ax
			LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
			ON ppua.id_proveedor_producto = ax.id_proveedor_producto
			GROUP BY ax.id_proveedor_producto
			ORDER BY ax.list_order";

	$stm = $link->query( $sql ) or die( "Error al consultar las medidas de proveedores producto : " . $link->error );
	while ( $row = $stm->fetch_row() ) {
		if( $row[3] == '' || $row[3] == 'null' || $row[3] == null ){
			echo "<tr class=\"no_tomadas\">
					<td>{$row[2]}</td>
					<td>{$row[1]}</td>
					<td>{$row[6]}</td>
					<td>{$row[7]}</td>
					<td></td>
					<td>{$row[8]}</td>
					<td>{$row[9]}</td>
					<td>{$row[10]}</td>
					<td>{$row[11]}</td>
				</tr>";
		}else if( ! file_exists( "../../files/packs_img/{$row[3]}" ) 
			|| !file_exists( "../../files/packs_img/{$row[4]}" ) 
			|| !file_exists( "../../files/packs_img/{$row[5]}" ) 
		){
			echo "<tr class=\"tomadas_faltantes\">
					<td>{$row[2]}</td>
					<td>{$row[1]}</td>
					<td>{$row[6]}</td>
					<td>{$row[7]}</td>
					<td>";
					echo ( ! file_exists( "../../files/packs_img/{$row[3]}" ) ? "<br>{$row[3]}" : "" );
					echo ( ! file_exists( "../../files/packs_img/{$row[4]}" ) ? "<br>{$row[4]}" : "" );
					echo ( ! file_exists( "../../files/packs_img/{$row[5]}" ) ? "<br>{$row[5]}" : "" );

				echo "</td>";
				echo "<td>{$row[8]}</td>
					<td>{$row[9]}</td>
					<td>{$row[10]}</td>
					<td>{$row[11]}</td>";
			echo "</tr>";
			//echo "<br><br>Falta fotografia del producto {$row[2]}  <b>{$row[1]}</b> : " . " ruta : ";
			//echo "<br>Falta fotografia del producto {$row[2]}  <b>{$row[1]}</b> : " . " ruta : ../../files/packs_img/{$row[4]}";
			//echo "<br>Falta fotografia del producto {$row[2]}  <b>{$row[1]}</b> : " . " ruta : ../../files/packs_img/{$row[5]}";
		}
	}
?>
	</tbody>
	<tfoot>
		<tr>

		</tr>
	</tfoot>
</table>

<style type="text/css">
	
	.hedaer_fixed{
		position: sticky;
		top : 0;
		background-color : white; 
	}
	.no_tomadas{
		background-color: orange;
		color : white !important;
	}
	.tomadas_faltantes{
		background-color: red;
		color : white !important;
	}

</style>

<script type="text/javascript">
	var ventana_abierta;
	function export_table_rows(){
		var header, datos;
			datos = "NOMBRE,MODELO,INVENTARIO,UBICACIÓN,RUTA IMÁGEN,ORDEN LISTA,ID PROVEEDOR PRODUCTO,PIEZAS POR CAJA,INVENTARIO MATRIZ";
		$( '#list_content tr' ).each( function ( index ) {
			$( this ).children( 'td' ).each( function( index_2 ){
				datos += ( index_2 > 0 ? "," : "\n" );
				datos += $( this ).html();
			});
	    });//termina for i
	//asignamos el valor a la variable del formulario
		$("#datos").val(datos);
	//enviamos datos al archivo que genera el archivo en Excel
		ventana_abierta=window.open('', 'TheWindow');	
		document.getElementById('TheForm').submit();
		setTimeout(cierra_pestana, 3000);			
	}

	function cierra_pestana(){
		$("#datos").val("");//resteamos variable de datos
		ventana_abierta.close();//cerramos la ventana
	}
	
</script>