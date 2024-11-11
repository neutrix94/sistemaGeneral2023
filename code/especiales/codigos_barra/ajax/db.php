<?php
    if( isset( $_GET['fl'] ) || isset( $_POST['fl'] ) ){
        include('../../../../conect.php');
        include('../../../../conexionMysqli.php');
        $db =  new db( $link );
        $action  = ( isset( $_GET['fl'] ) ? $_GET['fl'] : $_POST['fl'] );
        switch( $action ){
        //buscador de modelos del producto
            case 'seekProductBarcode' : 
                $product_id  = ( isset( $_GET['product_id'] ) ? $_GET['product_id'] : $_POST['product_id'] );
                echo $db->getOptionsByProductId( $product_id, $sucursal_id );
            // $barcode, $ticket_id, $form_pieces, $user, $sucursal, $pieces_quantity = 0
            break;
        //creacion de codigo(s) de barras
            case 'make_barcode' :
                $product_provider_id  = ( isset( $_GET['product_provider_id'] ) ? $_GET['product_provider_id'] : $_POST['product_provider_id'] );
                echo $db->make_barcode( $product_provider_id );
            break;
            default :
                echo json_encode( array( "code"=>201, "error"=>"Permission denied on '{$action}'." ) );
            break;
        }
    }
    class db
    {
        private $link;
        public function __construct( $connection ) {
            $this->link = $connection;
        }    

        public function make_barcode( $product_provider_id ){
            $resp = "";
            $sql = "SELECT codigo_barras_pieza_1, codigo_barras_pieza_2, codigo_barras_pieza_3 FROM ec_proveedor_producto WHERE folio_unico = {$product_provider_id}";
            $stm = $this->link->query( $sql ) or die( "Error al consultar los codigos de barras de piezas de proveedores producto : {$sql} : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            foreach ($row as $key => $barcode_piece) {
                if( $barcode_piece != '' && $barcode_piece != null ){
                    $barcodePath = "../../../../img/codigos_barra/{$barcode_piece}.png";
                    if( ! file_exists( $barcodePath ) ){
                        include('../../../../include/barcode/barcode.php');
                        $barcode_name = str_replace(' ', '', $folio );
                        /*if( file_exists( $barcodePath ) ){
                            unlink( $barcodePath );
                        }*/
                        barcode( $barcodePath, $barcode_piece, '60', 'horizontal', 'code128', true, 1);
                    }
                    if(file_exists("../../img/codigos_barra/{$barcode_piece}.png")){
                        $resp .= "<img src=\"../../img/codigos_barra/{$barcode_piece}.png\">";
                    }
                }
            }
            return $resp;
        }
        public function getOptionsByProductId( $product_id, $store_id ){
                        $sql = "";
                        $is_maquiled = 0;
                    //verifica si es maquilado para intercambiar el id
                        $sql = "SELECT
                                    id_producto_ordigen AS product_id
                                FROM ec_productos_detalle 
                                WHERE id_producto = {$product_id}";
                        $stm = $this->link->query( $sql ) or die( "Error al consultar si el producto es maquilado : {$sql} {$this->link->error}" );
                        if( $stm->num_rows > 0 ){
                            $is_maquiled = 1;
                        }
                    //todos los proveedores producto	
                        $sql = "SELECT
                                    pp.id_proveedor_producto AS product_provider_id,
                                    pp.clave_proveedor AS provider_clue,
                                    pp.piezas_presentacion_cluces AS pack_pieces,
                                    pp.presentacion_caja AS box_pieces,
                                    ipp.inventario AS inventory,
                                    pp.codigo_barras_pieza_1 AS piece_barcode_1
                                FROM ec_proveedor_producto pp
                                LEFT JOIN ec_inventario_proveedor_producto ipp
                                ON ipp.id_producto = pp.id_producto 
                                AND ipp.id_proveedor_producto = pp.id_proveedor_producto
                                WHERE pp.id_producto = {$product_id}
                                AND ipp.id_almacen IN ( SELECT id_almacen FROM ec_almacen WHERE es_almacen = 1 AND id_sucursal = {$store_id} )";
                    
                        $stm_name = $this->link->query( $sql ) or die( "error|Error al consutar el detalle del producto : {$this->link->error}" ); 
                        $resp = "<div class=\"row\">";
                        $resp .= "<div class=\"col-12\">";
                        $resp .= "<h5>Selecciona el modelo del producto : </h5>";
                        $resp .= "<table class=\"table table-bordered table-striped table_70\">";
                        $resp .= "<thead>
                                    <tr>
                                        <th>Modelo</th>
                                        <th>Inventario</th>
                                        <th class=\"no_visible\">Pzs x caja</th>
                                        <th>Seleccionar</th>
                                    </tr>
                                </thead><tbody id=\"model_by_name_list\" >";
                        $counter = 0;
                        while( $row_name = $stm_name->fetch_assoc() ){
                            $resp .= "<tr>";
                                $resp .= "<td id=\"p_m_1_{$counter}\" align=\"center\">{$row_name['provider_clue']}</td>";
                                $resp .= "<td id=\"p_m_2_{$counter}\" align=\"center\">{$row_name['inventory']}</td>";
                                $resp .= "<td id=\"p_m_3_{$counter}\" class=\"no_visible\" align=\"center\">{$row_name['box_pieces']}</td>";
                                //$resp .= "<td id=\"p_m_4_{$counter}\" align=\"center\">{$row_name['pack_pieces']}</td>";
                                $resp .= "<td align=\"center\"><input type=\"radio\" id=\"p_m_5_{$counter}\" 
                                    value=\"{$row_name['piece_barcode_1']}\"  name=\"search_by_name_selection\"></td>";
                                $resp .= "<td id=\"p_m_6_{$counter}\" class=\"no_visible\">{$row_name['product_provider_id']}</td>";
                            $resp .= "</tr>";
                            $counter ++;
                        }
                        $resp .= "</tbody></table>";
                        $resp .= "</div>";
                        $resp .= "<div class=\"col-2\"></div>";
                        $resp .= "<div class=\"col-8\">";
                        /*if( $ticket_id == null ){	
                            $resp .= "<button id=\"select_p_p_by_name_btn\" class=\"btn btn-success form-control\" onclick=\"setProductModel( '{$sale_detail_id}', null, null, null, '{$is_by_name}' );\">
                                    <i class=\"icon-ok-circle\">Continuar</i>
                                </button><br><br>
                                <button class=\"btn btn-danger form-control\"
                                    onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' );\">
                                    <i class=\"icon-ok-circle\">Cancelar</i>
                                </button>";
                        }else{*/
                        $resp .= "<button id=\"select_p_p_by_name_btn\" class=\"btn btn-success form-control\" onclick=\"setProductModel( '{$sale_detail_id}', 1, {$product_id}, {$ticket_id}, '{$is_by_name}' );\">
                                <i class=\"icon-ok-circle\">Continuar</i>
                            </button><br><br>
                            <button class=\"btn btn-danger form-control\"
                                onclick=\"close_emergent_2();\">
                                <i class=\"icon-ok-circle\">Cancelar</i>
                            </button>";
                        /*}*/
        
                        $resp .= "	</div>
                            </div>|{$is_maquiled}";
                        return $resp;
                    }
    }
    
?>