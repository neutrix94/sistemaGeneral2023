<?php
        include( '../../../../../conect.php' );
        include( '../../../../../conexionMysqli.php' );

        if( isset( $_GET['TagsGeneratorFl'] ) || isset( $_POST['TagsGeneratorFl'] ) ){
            $action = ( isset( $_GET['TagsGeneratorFl'] ) ? $_GET['TagsGeneratorFl'] : $_POST['TagsGeneratorFl'] );
            $TagsGenerator = new TagsGenerator( $link );
            switch ($action) {
                case 'getPreviousPrices' :
                    $product_id = ( isset( $_GET['product_id'] ) ? $_GET['product_id'] : $_POST['product_id'] );
                    echo $TagsGenerator->getProductCounterPrices( $product_id, $sucursal_id );
                break;

                case 'MediumTagPriceEPL' :
                    $product = ( isset( $_GET['product'] ) ? $_GET['product'] : $_POST['product'] );//recibe json
                    $product = json_decode( $product, true );//convierte JSON en Array
                    echo $TagsGenerator->MediumTagPriceEPL( $sucursal_id, $user_id, $product );
                break;

                case 'MediumTagTwoPricesEPL' :
                    $product = ( isset( $_GET['product'] ) ? $_GET['product'] : $_POST['product'] );//recibe json
                    $product = json_decode( $product, true );//convierte JSON en Array
                    echo $TagsGenerator->MediumTagTwoPricesEPL( $sucursal_id, $user_id, $product );//
                break;

                case 'BigTagPriceEPL' :
                    $product = ( isset( $_GET['product'] ) ? $_GET['product'] : $_POST['product'] );//recibe json
                    $product = json_decode( $product, true );//convierte JSON en Array
                    echo $TagsGenerator->BigTagPriceEPL( $sucursal_id, $user_id, $product );
                break;

                case 'BigTagTwoPricesEPL' :
                    $product = ( isset( $_GET['product'] ) ? $_GET['product'] : $_POST['product'] );//recibe json
                    $product = json_decode( $product, true );//convierte JSON en Array
                    echo $TagsGenerator->BigTagTwoPricesEPL( $sucursal_id, $user_id, $product );
                break;

                case 'createLocationTags' :
                    $number_from = ( isset( $_GET['number_from'] ) ? $_GET['number_from'] : $_POST['number_from'] );
                    $number_to = ( isset( $_GET['number_to'] ) ? $_GET['number_to'] : $_POST['number_to'] );
                    $letter_from = ( isset( $_GET['letter_from'] ) ? $_GET['letter_from'] : $_POST['letter_from'] );
                    $letter_to = ( isset( $_GET['letter_to'] ) ? $_GET['letter_to'] : $_POST['letter_to'] );
                    echo $TagsGenerator->createLocationTags( $sucursal_id, $user_id, $number_from, $number_to, $letter_from, $letter_to );
                break;
                
                default :
                    die( "Access denied on '{$action}'" );
                break;
            }
        }
        final class TagsGenerator{
            private $link;
            public function __construct( $connection ) {
                $this->link = $connection;
            }
            function createLocationTags( $store_id, $user_id, $number_from, $number_to, $letter_from, $letter_to ){
                $letra_inicio = $letter_from;
                $letra_fin = $letter_to;
                $epl_code = "";
                // Usar la función range para generar el rango de letras
                for ($i = $number_from; $i <= $number_to ; $i++ ) {
                    foreach (range($letra_inicio, $letra_fin) as $letra) {
                        $prefix = strtoupper( $letra );
                        $location = "{$i}-{$prefix}";
                        if( sizeof($location) == 3 ){
                            $location = " {$location}";
                        }
                        $epl_code .= $this->LocationTag( $location );
                    }
                }
            //crea el archivo
                $module_id = 15;
                $file_route = $this->getFileRoute( $store_id, $user_id, $module_id );
                $file_name = date("Y_m_d_H_i_s");
            //creacion de archivo
                $file = fopen("{$file_route}/locationTag_{$file_name}.txt", "a");
                fwrite($file, $epl_code );
                fclose($file);
                die( "ok" );
            }

            function LocationTag( $location ){
                $epl_code = "\nI8,A,001\n";
                $epl_code .= "Q408,024\n";
                $epl_code .= "q448\n";
                $epl_code .= "rN\n";
                $epl_code .= "S3\n";
                $epl_code .= "D7\n";
                $epl_code .= "ZT\n";
                $epl_code .= "JF\n";
                $epl_code .= "O\n";
                $epl_code .= "R112,0\n";
                $epl_code .= "f100\n";
                $epl_code .= "N\n";
                $epl_code .= "A595,370,2,5,4,7,N,\"{$location}\"\n";
                $epl_code .= "P1\n";
                return $epl_code;
            }
            function getProductCounterPrices( $product_id, $store_id ){
                $sql = "SELECT
                            pd.id_precio_detalle AS price_id,
                            pd.de_valor As number_since,
                            pd.precio_venta AS price,
                            pd.es_oferta AS is_special_price,
                            CONCAT( pr.nombre_etiqueta, ' (', pr.orden_lista, ')' ) AS product_tag_name,
                            pr.orden_lista AS list_order,
                            pr.nombre AS product_name,
                            pd.precio_anterior AS before_price
                        FROM ec_precios_detalle pd
                        LEFT JOIN ec_precios p
                        ON pd.id_precio = p.id_precio
                        LEFT JOIN sys_sucursales s
                        ON p.id_precio = s.id_precio
                        LEFT JOIN ec_productos pr
                        ON pr.id_productos = pd.id_producto
                    WHERE pd.id_producto = {$product_id}
                    AND s.id_sucursal = {$store_id}
                    ORDER BY pd.de_valor";
                $stm = $this->link->query( $sql ) or die( "Error al buscar los precios del producto : {$sql} : {$this->link->error}" );
                if( $stm->num_rows <= 0 ){//sin precio
                    return json_encode( array( "status"=> 200, "error"=>"El producto no tiene precio en la lista configurada en la sucursal.") );
                }else if( $stm->num_rows == 1 ){//un precio
                    $product = $stm->fetch_assoc();
                    $name_tmp = $this->part_word( $product['product_tag_name'] );
                    $product['name_part_one'] = $name_tmp[0];
                    $product['name_part_two'] = $name_tmp[1];
                    $price = $this->buildOnePriceHtml( $product );
                    return json_encode( array( "status"=> 200, "templates"=>$price, "product"=>$product) );
                }else{//mas de un precio
                    $product = array();
                    $row = $stm->fetch_assoc();
                    $name_tmp = $this->part_word( $row['product_tag_name'] );
                    $product['name_part_one'] = $name_tmp[0];
                    $product['name_part_two'] = $name_tmp[1];
                    $product['list_order'] = $row['list_order'];
                    $product['price_1'] = $row['price'];
                    $row = $stm->fetch_assoc();
                    $product['number_since'] = $row['number_since'];
                    $product['price_2'] = ( $row['price'] * $row['number_since'] );
                //calcula descuento
                    $discount = ( $product['number_since'] * $product['price_1'] ) - ( $product['number_since'] * $row['price'] );
                    $product['discount'] = $discount;
                    $price = $this->buildTwoPriceHtml( $product );
                    return json_encode( array( "status"=> 200, "templates"=>$price, "product"=>$product) );
                }
            }
            function buildOnePriceHtml( $product ){
            //etiqueta mediana
                $resp = "<div style=\"width:30%;border:2px solid;\" >
                        <h5>Da click en la etiqueta para imprimir Precio ( etiqueta mediana )</h5>
                        <button class=\"btn btn-light\" onclick=\"printTag( 'MediumTagPriceEPL' );\">
                            <div class=\"row\">
                                <div class=\"col-3\">
                                    <img src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/QR_code_for_mobile_English_Wikipedia.svg/1200px-QR_code_for_mobile_English_Wikipedia.svg.png\" width=\"90%\">
                                    <h1 class=\"text-center\">$</h1>
                                </div>
                                <div class=\"col-9\" style=\"font-size : 700%;margin : 0; padding: 0;font-weight:bold;line-height: 100%;transform: scale(.9, 1.5);\">
                                    {$product['price']}
                                </div>
                                <div style=\"font-size : 200%;margin : 0; padding: 0;\">
                                    {$product['name_part_one']}
                                </div>
                                <div style=\"font-size : 200%;margin : 0; padding: 0;\">
                                    {$product['name_part_two']}
                                </div>
                            </div>
                        </button>
                    </div>
                    <div style=\"width:50%;border:1px solid;\" >
                        <h5>Da click en la etiqueta para imprimir Precio ( etiqueta grande )</h5>
                        <button class=\"btn btn-light\" onclick=\"printTag( 'BigTagPriceEPL' );\">
                            <div class=\"row\">
                                <div class=\"col-3\">
                                    <img src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/QR_code_for_mobile_English_Wikipedia.svg/1200px-QR_code_for_mobile_English_Wikipedia.svg.png\" width=\"90%\">
                                </div>
                                <div class=\"col-9\" style=\"font-size : 1500%;font-weight:bold;\">
                                    {$product['price']}
                                </div>
                                <div style=\"font-size : 400%;\">
                                    {$product['name_part_one']}
                                </div>
                                <div style=\"font-size : 400%;\">
                                    {$product['name_part_two']}
                                </div>
                            </div>
                        </button>
                    </div>";
                return $resp;
            }
            function buildTwoPriceHtml( $product ){
            //etiqueta mediana
                $resp = "<div style=\"width:35%;border:2px solid;\" >
                        <h5 class=\"text-center\">Da click en la etiqueta para imprimir Precio ( etiqueta mediana )</h5>
                        <button class=\"btn btn-light\" onclick=\"printTag( 'MediumTagTwoPricesEPL' );\">
                            <div class=\"row\">
                                <div class=\"col-3\">
                                    <img src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/QR_code_for_mobile_English_Wikipedia.svg/1200px-QR_code_for_mobile_English_Wikipedia.svg.png\" width=\"90%\">
                                </div>
                                <div class=\"col-9\" style=\"font-size : 320%;margin : 0; padding: 0;font-weight:bold;line-height: 140%;\">
                                    $ {$product['price_1']}
                                </div>
                                <div class=\"col-3\" style=\"font-size : 200%;margin : 0; padding: 0;\">
                                    <span style=\"font-size : 300%;margin : 0; padding: 0;font-weight:bold;\">{$product['number_since']}</span>
                                </div>
                                <div class=\"col-3\" style=\"font-size : 200%;margin : 0; padding: 0; vertical-align:middle;\">
                                    <br>X $ 
                                </div>
                                <div class=\"col-3\" style=\"font-size : 200%;margin : 0; padding: 0; vertical-align:middle;\">
                                    <span style=\"font-size : 300%;margin : 0; padding: 0;font-weight:bold;\">{$product['price_2']}</span>
                                </div>
                                <div class=\"bg-dark text-light text-center pd2\">
                                    Ahorra: \${$product['discount']}
                                </div>
                                <div style=\"font-size : 200%;margin : 0; padding: 0;\">
                                    {$product['name_part_one']}
                                </div>
                                <div style=\"font-size : 200%;margin : 0; padding: 0;\">
                                    {$product['name_part_two']}
                                </div>
                            </div>
                        </button>
                    </div>
                    <div style=\"width:55%;border:2px solid;\" >
                        <h5 class=\"text-center\">Da click en la etiqueta para imprimir Precio ( etiqueta grande )</h5>
                        <button class=\"btn btn-light\" onclick=\"printTag( 'BigTagTwoPricesEPL' );\">
                            <div class=\"row\">
                                <div class=\"col-3\">
                                    <img src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/QR_code_for_mobile_English_Wikipedia.svg/1200px-QR_code_for_mobile_English_Wikipedia.svg.png\" width=\"90%\">
                                </div>
                                <div class=\"col-9\" style=\"font-size :500%;margin : 0; padding: 0;font-weight:bold;line-height: 140%;\">
                                    $ <b>{$product['price_1']}</b>
                                </div>
                                <div class=\"col-3\" style=\"font-size : 200%;margin : 0; padding: 0;\">
                                    <span style=\"font-size : 500%;margin : 0; padding: 0;font-weight:bold;\">{$product['number_since']}</span>
                                </div>
                                <div class=\"col-3\" style=\"font-size : 300%;margin : 0; padding: 0; vertical-align:middle;\">
                                <br> <b style=\"font-size : 200%;\">X $</b>
                                </div>
                                <div class=\"col-3\" style=\"font-size : 200%;margin : 0; padding: 0; vertical-align:middle;\">
                                    <span style=\"font-size : 500%;margin : 0; padding: 0;font-weight:bold;\">{$product['price_2']}</span>
                                </div>
                                <div class=\"bg-dark text-light text-center pd2\" style=\"font-size:300%;\">
                                    Ahorra: \${$product['discount']}
                                </div>
                                <div style=\"font-size : 400%;margin : 0; padding: 0;\">
                                    {$product['name_part_one']}
                                </div>
                                <div style=\"font-size : 400%;margin : 0; padding: 0;\">
                                    {$product['name_part_two']}
                                </div>
                            </div>
                        </button>
                    </div>
                    <div style=\"width:35%;border:2px solid;\" >
                        <h5 class=\"text-center\">Da click en la etiqueta para imprimir Precio anterior ( etiqueta mediana )</h5>
                        <button class=\"btn btn-light\" onclick=\"printTag( 'MediumTagPriceEPL' );>
                            <div class=\"row\">
                                <div class=\"text-center\" style=\"font-size:200%;\"><!--transform: scale(.9, 1.5);-->
                                    Precio Anterior :
                                </div>
                                <div class=\"col-3\" style=\"font-size : 200%;margin : 0; padding: 0;\">
                                    <span style=\"font-size : 400%;margin : 0; padding: 0;font-weight:bold;\">$<del>{$product['price']}</del></span>
                                </div>
                                <div style=\"font-size : 200%;margin : 0; padding: 0;\">
                                    {$product['name_part_one']}
                                </div>
                                <div style=\"font-size : 200%;margin : 0; padding: 0;\">
                                    {$product['name_part_two']}
                                </div>
                            </div>
                        </button>
                    </div>";
                return $resp;
            }
            function MediumTagPriceEPL( $store_id, $user_id, $product ){
                $price_size = 4;
                $space_1 = ( $product['price'] <= 99 ? ' ' : '' );
                $space_2 = ( $product['price'] <= 99 ? '  ' : '' );
                $epl_code = "\nI8,A,001\n";
                $epl_code .= "Q408,024\n";
                $epl_code .= "q448\n";
                $epl_code .= "rN\n";
                $epl_code .= "S1\n";
                $epl_code .= "D5\n";
                $epl_code .= "ZT\n";
                $epl_code .= "JF\n";
                $epl_code .= "O\n";
                $epl_code .= "R112,0\n";
                $epl_code .= "f100\n";
                $epl_code .= "N\n";
                if( $product['price'] > 999 ){
                    $price_size = 3;
                    //$epl_code .= "A400,255,2,5,2,2,N,\",\"\n";
                }
                $epl_code .= "b500,290,Q,m2,s5,\"{$product['list_order']}\"\n";
                $epl_code .= "A486,380,2,5,{$price_size},4,N,\"{$space_1}{$product['price']}\"\n";
                $epl_code .= "A590,280,2,4,4,4,N,\"{$space_2}$\"\n";
                $epl_code .= "A612,150,2,3,2,3,N,\"{$product['name_part_one']}\"\n";
                $epl_code .= "A612,80,2,3,2,3,N,\"{$product['name_part_two']}\"\n";
                $epl_code .= "P1\n";
                $module_id = ( $product['is_special_price'] == 0 ? 15 : 18 );
                $file_route = $this->getFileRoute( $store_id, $user_id, $module_id );
                $file_name = date("Y_m_d_H_i_s");
            //creacion de archivo
                $file = fopen("{$file_route}/tag_{$file_name}.txt", "a");
                fwrite($file, $epl_code );
                fclose($file);
                die( "ok" );
                //return $epl_code;
            }
            function MediumTagTwoPricesEPL( $store_id, $user_id, $product ){
                $price_size = ( $product['price_1'] <= 999 ? 3 : 2 );
                $space_1 = ( $product['price'] <= 99 ? ' ' : '' );
                $space_2 = ( $product['number_since'] <= 9 ? ' ' : '' );
                $epl_code = "\nI8,A,001\n";
                $epl_code .= "Q408,024\n";
                $epl_code .= "q448\n";
                $epl_code .= "rN\n";
                $epl_code .= "S1\n";
                $epl_code .= "D5\n";
                $epl_code .= "ZT\n";
                $epl_code .= "JF\n";
                $epl_code .= "O\n";
                $epl_code .= "R112,0\n";
                $epl_code .= "f100\n";
                $epl_code .= "N\n";
                $epl_code .= "b40,60,Q,m2,s5,\"{$product['list_order']}\"\n";
                $epl_code .= "A240,60,0,4,5,{$price_size},N,\"{$space_1}{$product['price_1']}\"\n";
                $epl_code .= "A190,60,0,3,3,3,N,\"{$space_1}$\"\n";
                $epl_code .= "A40,180,0,4,3,5,N,\"{$space_2}{$product['number_since']}\"\n";
                $epl_code .= "A160,215,0,3,3,3,N,\"X\"\n";
                $epl_code .= "A220,210,0,3,3,3,N,\"$\"\n";
                $epl_code .= "A290,140,0,5,2,{$price_size},N,\"{$product['price_2']}\"\n";
                $epl_code .= "A40,290,0,2,3,2,R,\"  Ahorra: \${$product['discount']}  \"\n";
                $epl_code .= "A40,330,0,3,2,2,N,\"{$product['name_part_one']}\"\n";
                $epl_code .= "A40,370,0,3,2,2,N,\"{$product['name_part_two']}\"\n";
                $epl_code .= "P1\n";
                $module_id = ( $product['is_special_price'] == 0 ? 15 : 18 );
                $file_route = $this->getFileRoute( $store_id, $user_id, $module_id );
                $file_name = date("Y_m_d_H_i_s");
            //creacion de archivo
                $file = fopen("{$file_route}/tag_{$file_name}.txt", "a");
                fwrite($file, $epl_code );
                fclose($file);
                die( "ok" );
            }
            function BigTagPriceEPL( $store_id, $user_id, $product ){
                $price_width = ( $product['price'] <= 999 ? 5 : 4 );
                $price_height = ( $product['price'] <= 999 ? 7 : 7 );
                $space_1 = ( $product['price'] <= 99 ? ' ' : '' );
                $space_2 = ( $product['price'] <= 99 ? '  ' : '' );
                $epl_code = "\nI8,A,001\n";
                $epl_code .= "Q1215,024\n";
                $epl_code .= "q863\n";
                $epl_code .= "rN\n";
                $epl_code .= "S6\n";
                $epl_code .= "D5\n";
                $epl_code .= "ZT\n";
                $epl_code .= "JF\n";
                $epl_code .= "O\n";
                $epl_code .= "R24,0\n";
                $epl_code .= "f100\n";
                $epl_code .= "N\n";
                $epl_code .= "b630,450,Q,m2,s8,\"{$product['list_order']}\"\n";
                $epl_code .= "A600,590,2,5,{$price_width},{$price_height},N,\"{$space_1}{$product['price']}\"\n";
                $epl_code .= "A740,430,2,4,5,6,N,\"{$space_2}$\"\n";
                $epl_code .= "A795,230,2,4,2,4,N,\"{$product['name_part_one']}\"\n";
                $epl_code .= "A795,140,2,4,2,4,N,\"{$product['name_part_two']}\"\n";

                $epl_code .= "b10,680,Q,m2,s8,\"{$product['list_order']}\"\n";
                $epl_code .= "A210,690,0,5,{$price_width},{$price_height},N,\"{$space_1}{$product['price']}\"\n";
                $epl_code .= "A50,870,0,4,5,6,N,\"{$space_2}$\"\n";
                $epl_code .= "A10,1050,0,4,2,4,N,\"{$product['name_part_one']}\"\n";
                $epl_code .= "A10,1150,0,4,2,4,N,\"{$product['name_part_two']}\"\n";
                $epl_code .= "P1\n";
                $module_id = ( $product['is_special_price'] == 0 ? 16 : 19 );
                $file_route = $this->getFileRoute( $store_id, $user_id, $module_id );
                $file_name = date("Y_m_d_H_i_s");
            //creacion de archivo
                $file = fopen("{$file_route}/tag_{$file_name}.txt", "a");
                fwrite($file, $epl_code );
                fclose($file);
                die( "ok" );
            }
            function BigTagTwoPricesEPL( $store_id, $user_id, $product ){
                $space_1 = ( $product['price'] <= 99 ? ' ' : '' );
                $space_2 = ( $product['number_since'] <= 9 ? ' ' : '' );
                $price_size = ( $product['price'] <= 999 ? 4 : 3 );
                $epl_code = "\nI8,A,001\n";
                $epl_code .= "Q1215,024\n";
                $epl_code .= "q863\n";
                $epl_code .= "rN\n";
                $epl_code .= "S6\n";
                $epl_code .= "D5\n";
                $epl_code .= "ZT\n";
                $epl_code .= "JF\n";
                $epl_code .= "O\n";
                $epl_code .= "R24,0\n";
                $epl_code .= "f100\n";
                $epl_code .= "N\n";
                $epl_code .= "b680,480,Q,m2,s5,\"{$product['list_order']}\"\n";
                $epl_code .= "A500,570,2,5,3,2,N,\"{$space_1}{$product['price_1']}\"\n";
                $epl_code .= "A620,560,2,4,5,3,N,\"{$space_1}$\"\n";
                $epl_code .= "A800,440,2,5,3,4,N,\"{$space_2}{$product['number_since']}\"\n";
                $epl_code .= "A570,370,2,4,4,3,N,\"X\"\n";
                $epl_code .= "A490,370,2,4,4,3,N,\"$\"\n";
                $epl_code .= "A440,440,2,5,3,{$price_size},N,\"{$product['price_2']}\"\n";
                $epl_code .= "A800,220,2,2,4,4,R,\"  Ahorra: \${$product['discount']}   \"\n";
                $epl_code .= "A800,140,2,3,3,3,N,\"{$product['name_part_one']}\"\n";
                $epl_code .= "A800,80,2,3,3,3,N,\"{$product['name_part_two']}\"\n";

                $epl_code .= "b10,670,Q,m2,s5,\"{$product['list_order']}\"\n";
                $epl_code .= "A280,670,0,5,3,2,N,\"{$space_1}{$product['price_1']}\"\n";
                $epl_code .= "A160,690,0,4,5,3,N,\"{$space_1}$\"\n";
                $epl_code .= "A10,800,0,5,3,4,N,\"{$space_2}{$product['number_since']}\"\n";
                $epl_code .= "A235,890,0,4,4,3,N,\"X\"\n";
                $epl_code .= "A365,950,2,4,4,3,N,\"$\"\n";
                $epl_code .= "A375,800,0,5,3,{$price_size},N,\"{$product['price_2']}\"\n";
                $epl_code .= "A10,1020,0,2,4,4,R,\"  Ahorra: \${$product['discount']}   \"\n";
                $epl_code .= "A10,1100,0,3,3,3,N,\"{$product['name_part_one']}\"\n";
                $epl_code .= "A10,1160,0,3,3,3,N,\"{$product['name_part_two']}\"\n";
                $epl_code .= "P1\n";
                $module_id = ( $product['is_special_price'] == 0 ? 16 : 19 );
                $file_route = $this->getFileRoute( $store_id, $user_id, $module_id );
                $file_name = date("Y_m_d_H_i_s");
            //creacion de archivo
                $file = fopen("{$file_route}/tag_{$file_name}.txt", "a");
                fwrite($file, $epl_code );
                fclose($file);
                die( "ok" );
            }
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
            function getFileRoute( $store_id, $user_id, $module_id ){
                if( ! include( '../../../controladores/SysModulosImpresionUsuarios.php' ) ){
                    die( "No se pudo incluir la libreria de descargar de archivos : 'SysModulosImpresionUsuarios'" );
                }
                $SysModulosImpresionUsuarios = new SysModulosImpresionUsuarios( $this->link );
                if( ! include( '../../../controladores/SysModulosImpresion.php' ) ){
                    die( "No se pudo incluir la libreria de descargar de archivos : 'SysModulosImpresion'" );
                }
                $SysModulosImpresion = new SysModulosImpresion( $this->link );
                $ruta_salida = '';
                $ruta_salida = $SysModulosImpresionUsuarios->obtener_ruta_modulo_usuario( $user_id, $module_id );//etiqueta empaquetado pieza
                if( $ruta_salida == 'no' ){
                    $ruta_salida = "cache/" . $SysModulosImpresion->obtener_ruta_modulo( $store_id, $module_id );//etiqueta empaquetado pieza
                }
                //die( "../../../../{$ruta_salida}"  );
                return "../../../../../{$ruta_salida}";
                //$this->routes["{$ruta_salida}"] = "";
                //$ruta_salida = $SysModulosImpresionUsuarios->obtener_ruta_modulo_usuario( $user_id, 13 );//etiqueta empaquetado paquete
                //if( $ruta_salida == 'no' ){
                //	$ruta_salida = "cache/" . $SysModulosImpresion->obtener_ruta_modulo( $store_id, 13 );//etiqueta empaquetado paquete
                //}
                //$this->routes["{$ruta_salida}"] = "";
                //return true;
            }
        }
        
?>
