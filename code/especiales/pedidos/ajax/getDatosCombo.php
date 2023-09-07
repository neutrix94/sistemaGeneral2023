<?php
//	include("../../../../conectMin.php");

	if( isset( $_POST['flag']) || isset( $_GET['flag']) ){
		include("../../../../conexionMysqli.php");
		$combos = new Combos();
		$action = ( isset( $_POST['flag'] ) ? $_POST['flag'] : $_GET['flag'] );
		switch ( $action ) {
			case 'productProviderCombo':
				$product_id = ( isset( $_POST['id'] ) ? $_POST['id'] : $_GET['id'] );
				$counter = ( isset( $_POST['c'] ) ? $_POST['c'] : $_GET['c'] );
				$without_price = ( isset( $_POST['precio_ceros'] ) ? $_POST['precio_ceros'] : $_GET['precio_ceros'] );
				$product_provider_type = ( isset( $_POST['product_provider_type'] ) ? $_POST['product_provider_type'] : $_GET['product_provider_type'] );
				$option_selected = ( isset( $_POST['option'] ) ? $_POST['option'] : $_GET['option'] );
				$providers = ( $_POST['providers'] == '' ? null : str_replace('|', ',', $_POST['providers'] ) );
				echo $combos->productProviderCombo( $product_id, $counter, $without_price, $product_provider_type, $option_selected, $providers, $link  );
				return;
			break;

			case 'getCombo':
				$reference = ( isset( $_POST['id'] ) ? $_POST['id'] : $_GET['id'] );
				$campo = ( isset( $_POST['campo'] ) ? $_POST['campo'] : $_GET['campo'] );
				echo $combos->getCombo( $reference, $campo, $link );
				return;
			break;
			
			default:
				die( 'Permission denied!' );
			break;
		}
	}
 
	class Combos
	{
		
		function __construct(){
		}

		public function productProviderCombo( $product_id, $counter, $without_price, $product_provider_type, $option_selected = null, $providers = null, $link ){
			$resp = "";
			$product_provider_order = "";
			switch ( $product_provider_type ) {
				case '1':
					$product_provider_order = "ORDER BY pr.fecha_ultima_compra DESC";
				break;
				case '2':
					$product_provider_order = "ORDER BY pr.precio_pieza ASC";
				break;
				case '3':
					$product_provider_order = "ORDER BY pr.fecha_ultima_actualizacion_precio DESC";
				break;
			}
			$where_providers = "";
			if( $providers != null ){
				$where_providers = " AND pr.id_proveedor IN( {$providers} )";
			}
			//die('sin_precio='.$precio_ceros);
			$sql="SELECT 
				/*0*/pr.id_proveedor_producto AS product_provider_id,
				/*1*/CONCAT('$',ROUND(pr.precio_pieza,2),':',p.nombre_comercial) AS description,
				/*2*/pr.presentacion_caja AS pieces_per_box,
				/*3*/p.id_proveedor AS provider_id,
				/*4*/pr.clave_proveedor AS provider_clue
				FROM ec_proveedor_producto pr
				LEFT JOIN ec_proveedor p ON p.id_proveedor=pr.id_proveedor
				WHERE pr.id_producto='{$product_id}'
				{$where_providers} /*AND IF($precio_ceros=1,pr.id_proveedor_producto>0,pr.precio>0)*/
				{$product_provider_order}";//ORDER BY pr.precio_pieza ASC
			//die($sql);
			$eje = $link->query( $sql )or die("Error al buscar proveedores para llenar el combo : {$link->error}");
			$resp .= 'ok|';
		//armamos combo
			$resp .= '<select onchange="muestra_prov(this,'. $counter .',2);" class="comb" id="c_p_'. $counter .'" style="width:100%;">';//onclick="carga_proveedor_prod('.$c.','.$id.');" 
			//if( $eje->num_rows <= 0 ){
				$resp .= '<option value="-1">--Seleccionar--</option>';//comobo prueba
			//}
		//retornamos valores
			$counter_rows = 0;
			while($r=$eje->fetch_assoc() ){
				$resp .= '<option value="'. $r['provider_id'] . '" ';
				if( $option_selected !=	null ){
					$resp .= ( $option_selected == $r['provider_clue'] ? ' selected' : '' ); 
				}else{
					$resp .= ( $counter_rows == 0 ? ' selected' : '' ); 
				}
				$resp .= '>'.$r['description'].':'.$r['pieces_per_box'].'pzas//'.base64_encode($r['product_provider_id']).'//' . $r['provider_clue'] . '</option>';
				$counter_rows ++;
			}
				$resp .= '<option value="nvo">Administar Proveedores</option>';
			$resp .= '</select>';
			return $resp;//fin
		}


	//consultamos datos de combos por tipo de producto
		public function getCombo( $id, $campo, $link ){
			if($campo==1){
				$t="ec_subcategoria";
				$c_t="id_subcategoria,nombre";
				$comp="id_categoria";
			}
			if($campo==2){
				$t="ec_subtipos";
				$c_t="id_subtipos,nombre";
				$comp="id_tipo";
			}
			$sql="SELECT {$c_t} FROM {$t} WHERE {$comp}='{$id}'";
			$eje = $link->query($sql)or die("Error al buscar datos del combo!!!\n\n{$link->error}\n\n");
			$resp = "ok|";
		//enviamos datos
			while( $r = $eje->fetch_row() ){
				$resp .= $r[0]."~".$r[1]."°";
			}
			return $resp;
		}
	}
//extraemos datos por post

?>