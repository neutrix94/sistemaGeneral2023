<?php

	if( isset( $_POST['action'] ) && $_POST['action'] == 'maquila' ){
		include( '../../../../conexionMysqli.php' );
		include( '../../plugins/maquila.php' );
		$maquila = new maquila( $link );
		echo $maquila->make_form( $_POST['product'], ( $_POST['quantity'] ?  : 0), "putDecimalValue( {$_POST['count']}, {$_POST['product']}, {$_POST['block_count']} );" );
		return '';
	}	
	

	function make_row ( $row, $counter, $is_master ){
		if( $counter % 2 == 0 ){
			$color='#FFFF99';
		}else{
			$color='#CCCCCC';
		}
		static $c = 0;
		if ( $is_master == null ){
			$c ++;
		}
		$row[2] = ($row[2] == null ? 0 : $row[2]);
	//fila
		$resp = "<tr id=\"fila" . ( $is_master == null ? $c : ('_'.$row[0])  ) . "\" class=\"fi\"" . ($is_master == null ? '' : " is_master=\"1\"") 
				. " style=\"background: $color;\" 
					onclick=\"resalta( " . ( $is_master == null ? $c : ('\'_'.$row[0].'\'')  ) . ", 0 );\"
					value=\"$row[0]\" tabindex=\"$c\" group_counter=\"$counter\" product_id=\"$row[0]\" product_provider=\"$row[5]\">";
		//ubicación
			$resp .= "<td width=\"10%\" align=\"center\" id=\"ubicacion_$counter;\" onclick=\"campo_temporal( $c );\">
						". ($is_master == null ? $row[4] : '')  . "</td>";
		//id de producto
			$resp .= "<td class=\"invisible\" id=\"0" . ( $is_master == null ? (','.$c) : ('_'.$row[0])  ) . "\" value=\"$row[0]\"></td>";
		//orden lista
			$resp .= "<td width=\"10%\" align=\"center\" id=\"list_order_$counter;\" onclick=\"campo_temporal( $c );\">
						". ($is_master == null ? $row[3] : '')  . "</td>";
		//id de producto
			$resp .= "<td class=\"hidden\" id=\"$row[0]\" value=\"$c\"></td>";
		//nombre de producto
			$resp .= "<td width=\"35%\" id=\"1" . ( $is_master == null ? (','.$c) : ('_'.$row[0]) ) . "\">" . ($is_master == null ? $row[1] : "<div class=\"product_resumen\">$row[1]</div>") . "</td>";
		//columna del temporal
			$resp .= "<td width=\"10%\" align=\"right\" id=\"temporal_" . ( $is_master == null ? $c : $row[0]  ) . "\">0</td>";
		//inventario virtual
			$row[2] = str_replace(',', '', $row[2] );
			$resp .= "<td width=\"10%\" id=\"2" . ( $is_master == null ? (','.$c) : ('_'.$row[0])  ) . "\">" . str_replace( '.00', '', $row[2]) . "</td>";
		//inventario físico
			$resp .= "<td width=\"10%\" id=\"3" . ( $is_master == null ? (','.$c) : ('_'.$row[0])  ) . "\">" . str_replace( '.00', '', $row[2]) . "</td>";
		//diferencia entre los inventarios
		$difference = ( $row[2] * -1 );
		$difference = ( $difference == '0' || $difference == '-0' ? 0 : $difference );
		//die( $difference );
			$resp .= "<td width=\"15%\" id=\"4" . ( $is_master == null ? (','.$c) : ('_'.$row[0]) ) . "\" value=\"{$difference}\">{$difference}</td>";
		$resp .= '</tr>';
		
		return $resp;
	}
?>