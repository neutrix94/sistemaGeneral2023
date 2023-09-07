<?php

	if( isset( $_POST['action'] ) && $_POST['action'] == 'maquila' ){
		include( '../../../../conexionMysqli.php' );
		include( '../../plugins/maquila.php' );
		$maquila = new maquila( $link );
		echo $maquila->make_form( $_POST['product'], ( $_POST['quantity'] ?  : 0), "putDecimalValue( {$_POST['count']}, {$_POST['product']}, {$_POST['block_count']} );" );
		return '';
	}	
	

	function make_row ( $row, $counter, $is_master ){
		//var_dump( $row );
		//die('');
		if( $counter % 2 == 0 ){
			$color='#FFFF99';
		}else{
			$color='#CCCCCC';
		}
		static $c = 0;
		if ( $is_master == null ){
			$c ++;
		}
		$row['product_inventory'] = ($row['product_inventory'] == null ? 0 : $row['product_inventory']);
	//fila // style=\"background: $color;\" 
					
		$resp = "<tr id=\"fila" . ( $is_master == null ? $c : ('_'.$row['product_id'])  ) . "\" class=\"fi\"" . ($is_master == null ? '' : " is_master=\"1\"") 
				. " onclick=\"resalta( " . ( $is_master == null ? $c : ('\'_'.$row['product_id'].'\'')  ) . ", 0 );\"
					value=\"{$row['product_id']}\" tabindex=\"$c\" group_counter=\"$counter\" product_id=\"{$row['product_id']}\" product_provider=\"{$row['product_location']}\">";
		//ubicación
			$resp .= "<td width=\"10%\" align=\"center\" id=\"ubicacion_$c;\" onclick=\"campo_temporal( $c );\" class=\"invisible\">
						". ($is_master == null ? $row['product_location'] : '')  . "</td>";
		//orden lista
			$resp .= "<td width=\"10%\" align=\"center\" id=\"ubicacion_$c;\" onclick=\"campo_temporal( $c );\">
						". ($is_master == null ? $row['order_list'] : '')  . "</td>";
		//id de producto
			$resp .= "<td class=\"invisible\" id=\"0" . ( $is_master == null ? (','.$c) : ('_'.$row['product_id'])  ) . "\" value=\"{$row['product_id']}\"></td>";
		//id de producto
			$resp .= "<td class=\"invisible\" id=\"{$row['product_id']}\" value=\"$c\"></td>";
		//nombre de producto
			$resp .= "<td width=\"35%\" id=\"1" . ( $is_master == null ? (','.$c) : ('_'.$row['product_id']) ) . "\">" . ($is_master == null ? $row['product_name'] : "<div class=\"product_resumen\">{$row['product_name']}</div>") . "</td>";
		//columna del temporal
			$resp .= "<td width=\"10%\" align=\"right\" id=\"temporal_" . ( $is_master == null ? $c : $row['product_id']  ) . "\" class=\"invisible\">>0</td>";
		//inventario virtual
			$row['product_inventory'] = str_replace(',', '', $row['product_inventory'] );
			$row['product_provider_inventories'] = str_replace(',', '', $row['product_provider_inventories'] );
			$resp .= "<td width=\"10%\"
						id=\"2" . ( $is_master == null ? (','.$c) : ('_'.$row['product_id'])  ) . "\" 
						value=\"" . str_replace( '.00', '', $row['product_inventory']) . "\" 
						align=\"right\"
						class=\"\">";
			$resp .= str_replace( '.00', '', $row['product_inventory']);		
			$resp .= "</td>";
		//inventario físico
			$resp .= "<td width=\"10%\"
							id=\"3" . ( $is_master == null ? (','.$c) : ('_'.$row['product_id'])  ) . "\" 
							value=\"" . str_replace( '.0000', '', $row['product_provider_inventories']) . "\" 
							align=\"right\" 
							onkeyup=\"validar(event, $c, {$row['product_id']}, $counter );\" 
							$is_master
						>";

			/*$resp .= "<td width=\"10%\">
						<input type=\"number\" id=\"3" . ( $is_master == null ? (','.$c) : ('_'.{$row['product_id']})  ) . "\" value=\"" . str_replace( '.0000', '', $row['product_provider_inventories']) . "\" class=\"informa\"  
						onkeyup=\"validar(event, $c, {{$row['product_id']}}, $counter );\" tabindex=\"$c\"";*/

			/*if( $is_master == null && $row[6] != null && $row[6] != '' ){
				$resp .= " onfocus=\"showEmergent( $c, {{$row['product_id']}}, $counter );\"";
			}else{			
				$resp .= " onfocus=\"verificaTemporal( $c, {{$row['product_id']}} );\"";
			}*/
				$resp .=  str_replace( '.0000', '', $row['product_provider_inventories']);
				$resp .="</td>";
		//diferencia entre los inventarios
			$resp .= "<td width=\"15%\"
						id=\"4" . ( $is_master == null ? (','.$c) : ('_'.$row['product_id']) ) . "\" 
						align=\"right\"
						value=\"" . ($row['product_provider_inventories'] - $row['product_inventory'] ) . "\"
					>";
			$resp  .=  ($row['product_provider_inventories'] - $row['product_inventory'] );
			$resp .= "</td>";
		$resp .= '</tr>';
		
		return $resp;
	}
?>