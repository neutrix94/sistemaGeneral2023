<?php
	include('../../../conexionMysqli.php');
	if ( $_POST['types_movs'] == 2 ) {
		$sql = "SELECT CONCAT( nombre, ' (id=', id_tipo_movimiento ,')' ) FROM ec_tipos_movimiento";
		$stm = $link->query( $sql ) or die( "Error al consultar los tipos de movimiento : " . $link->error );
		$movs_types = "<div style=\"color : white;\"><ul>
						<li>Tipos de movimientos</li>
					<ul>";
		while ( $movs = $stm->fetch_row() ) {
			$movs_types .= "<li>{$movs[0]}</li>";
		}
		$movs_types .= "</ul>";
		$movs_types .= "<button type=\"button\" onclick=\"view_movs_types(1);\" style=\"right : 5%; position : absolute;\">Volver a la ayuda</button>";

		die( $movs_types );
	}
?>
<div style="color : white;"><br/>
	<h2 align="center">Notas Importantes</h2>
	<p style="padding : 20px;" align="justify">
		Los productos que no tienen inventario para resurtirse en los pedidos de transferencia estan en este listado, para
		poder sacarlos de este estatus hay que eliminarlos manualmente o el sistema los eliminara automáticamemte a travez
		del trigger <b>"insertaMovimientoAlmacenDetalle"</b>, siempre y cuando cumpla con los siguientes requisitos : 
		<ul>
			<li>Tipo de movimiento
				<ul>
					<li>Entrada por Compra (id=1)</li>
					<li>Entrada por Ajuste de Inventario (id=9)</li>
					<li>Entrada Manual (id=14)</li>
				</ul>
			</li>
			<li>El movimiento debe de ser en el Almacen principal de Matriz (id=1)</li>
		</ul>
	</p>
	<button type="button" class="all_movs_types" onclick="view_movs_types( 2 );">
		Ver todos los tipos de movimientos
	</button>
</div>