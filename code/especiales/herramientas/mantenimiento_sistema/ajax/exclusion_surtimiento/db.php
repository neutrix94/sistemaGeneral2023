<?php
	if ( ! include("../../../../../../conectMin.php") ){
        die( "Error al incluir archivo : '../../../../../../conectMin.php'" );
    }
	if ( ! include("../../../../../../conexionMysqli.php") ){
        die( "Error al incluir archivo : '../../../../../../conexionMysqli.php'" );
    }
    if( isset( $_POST['exclusionFl'] ) || isset( $_GET['exclusionFl'] ) ){
        $action = ( isset( $_POST['exclusionFl'] ) ? $_POST['exclusionFl'] : $_GET['exclusionFl'] );
        $PE = new ProductsExclusion( $link ); 
        switch( $action ){
            case 'seek' :
                $txt = ( isset( $_POST['clave'] ) ? $_POST['clave'] : $_GET['clave'] );
                echo $PE->seekProduct( $txt );
            break;

            case 'insertProductExclusion' :
                $product_id = ( isset( $_POST['product_id'] ) ? $_POST['product_id'] : $_GET['product_id'] );
                echo $PE->insertProductExclusion( $product_id );
            break;

            case 'deleteProductExclusion' :
                $exclusion_id = ( isset( $_POST['exclusion_id'] ) ? $_POST['exclusion_id'] : $_GET['exclusion_id'] );
                $product_id = ( isset( $_POST['product_id'] ) ? $_POST['product_id'] : $_GET['product_id'] );
                echo $PE->deleteProductExclusion( $exclusion_id, $product_id );
            break;

            default :
                die( "Permission denied on : '{$action}'" );
            break;
        }
    }

    final class ProductsExclusion{
        private $link;
        public function __construct( $connection ) {
            $this->link = $connection;
        }
    //buscar productos
        public function seekProduct( $txt ){
            $resp = "";
            $txt= trim( $txt );
            $sql="SELECT 
                    id_productos AS product_id,
                    nombre AS product_name 
                FROM ec_productos 
                WHERE id_productos > 1 
                AND orden_lista NOT IN( 0 )
                AND (id_productos = '$txt' 
                OR orden_lista = '$txt'";
        //agudiza la búsqueda
            $aux=explode(" ",$txt);
            for($i=0;$i<sizeof($aux);$i++){
                if($aux[$i]!='' && $aux[$i]!=null){
                    if($i==0){
                        $sql.=" OR (";
                    }else{
                        $sql.=" AND ";
                    }
                    $sql.="nombre LIKE '%".$aux[$i]."%'";
                }
                if($i==sizeof($aux)-1){
                    $sql.=")";//cerramos el OR
                }
            }//fin de for $i
            $sql.=")";//cerramos la condicion and
            $eje = $this->link->query( $sql )or die( "Error al buscar productos : {$sql} : {$this->link->error}" );
            $resp = "ok|<table class=\"table table-striped\">";
            $cont=0;//declaramos contador en 0
            while( $r = $eje->fetch_assoc() ){
                $cont++;//incrementamos contador
                $resp .= "<tr id=\"resultado_{$cont}\" tabindex=\"{$cont}\" onclick=\"validaProducto( {$r['product_id']} );\" 
                                onkeyup=\"valida_mov_resultados(event, {$cont}, {$r['product_id']} );\">
                            <td width\"100%\">{$r['product_name']}</td>
                        </tr>";
            }
            $resp .= "</table>";
            return $resp;
        }
    //inserta exclusion de producto 
        public function insertProductExclusion( $product_id ){
            $this->link->autocommit( false );
        //inserta la insercion de exclusion de producto en surtimiento
            $sql = "INSERT INTO ec_exclusion_productos_surtimiento_venta ( id_producto, fecha_alta, sincronizar ) 
                VALUES ( $product_id, NOW(), 1 )";
            $stm = $this->link->query( $sql ) or die( "Error al insertar la exclusion de surtimiento de producto : {$sql} : {$this->link->error}" );
        //actualiza a surtir en 1 los productos en tabla de sucursal producto
            $sql = "UPDATE sys_sucursales_producto SET surtir = 1 WHERE id_producto = {$product_id} AND id_sucursal > 1";
            $stm = $this->link->query( $sql ) or die( "Error al actualizar surtimiento en tabla de sucursal producto : {$sql} : {$this->link->error}" );
            $this->link->commit();
            return "ok|El producto fue puesto en exclusión de surtimiento exitosamente.";
        }
    //elimina exclusion de producto
        public function deleteProductExclusion( $exclusion_id, $product_id ){
            $this->link->autocommit( false );
        //elimina la exclusion de suertimiento del producto
            $sql = "DELETE FROM ec_exclusion_productos_surtimiento_venta WHERE id_exclusion_productos_surtimiento_venta = {$exclusion_id}";
            $stm = $this->link->query( $sql ) or die( "Error al eliminar exclusion de surtimiento : {$sql} : {$this->link->error}" );
        //actualiza a surtir en 1 los productos en tabla de sucursal producto
            $sql = "UPDATE sys_sucursales_producto SET surtir = 1 WHERE id_producto = {$product_id} AND id_sucursal > 1";
            $stm = $this->link->query( $sql ) or die( "Error al actualizar surtimiento en tabla de sucursal producto : {$sql} : {$this->link->error}" );
            $this->link->commit();
            return "ok|La exclusión del producto en surtimiento fue eliminada exitosamente.";
        }
    }
?>