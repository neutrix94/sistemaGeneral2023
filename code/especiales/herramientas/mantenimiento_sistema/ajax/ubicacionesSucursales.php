<?php
    include( '../../../../../conexionMysqli.php' );
    if( isset( $_POST['storeLocationsFlag'] ) || isset( $_GET['storeLocationsFlag'] ) ){
        $action = ( isset( $_POST['storeLocationsFlag'] ) ? $_POST['storeLocationsFlag'] : $_GET['storeLocationsFlag'] );
    
        $SL = new storeLocations( $link );
        switch ($action) {
            case 'getStoresForm' :
                echo $SL->getStoresForm();
            break;
            case 'resetStoresLocations' : 
                $stores = ( isset( $_POST['stores'] ) ? $_POST['stores'] : $_GET['stores'] );
                echo $SL->resetStoresLocations( $stores );
            break;
            default:
                die( "Permission Denied on : {$action}" );
            break;
        }
    }

    final class storeLocations{
        private $link;
        public function __construct( $connecion ) {
            $this->link = $connecion;
        }

        public function getStoresForm(){
            $resp = "<div class=\"row\">
                <table class=\"table table-striped table-bordered\" >
                <thead style=\"position : sticky; top : -18px;\" class=\"bg-light\">
                    <tr>
                        <th class=\"text-center\">Sucursal</th>
                        <th class=\"text-center\">Resetear</th>
                    </tr>
                </thead>
                <tbody id=\"store_locations_list\">";
            $sql = "SELECT id_sucursal, nombre FROM sys_sucursales WHERE id_sucursal > 1";
            $stm = $this->link->query( $sql ) or die( "Error al consultar las sucursales para el formulario : {$sql} : {$this->link->error}" );
            while ( $row = $stm->fetch_assoc() ) {
                $resp .= "<tr>
                    <td>{$row['nombre']}</td>
                    <td class=\"text-center\"><input type=\"checkbox\" value=\"{$row['id_sucursal']}\" checked></td>
                </tr>";
            }
            $resp .= "</tbody>
                </table>
                <div class=\"text-center\">
                    <button
                        type=\"button\"
                        class=\"btn btn-success\"
                        onclick=\"reset_store_locations();\"
                    >
                        <i class=\"icon-ok-circled\">Resetear</i>
                    </button>
                </div>
            </div>";
            return $resp;
        }

        public function resetStoresLocations( $stores ){
        //inicio de transaccion
            $this->link->autocommit( false );
        //resetea tabla de ubicaciones por sucursal
            /*$sql = "UPDATE ec_sucursal_producto_ubicacion_almacen SET 
                        numero_ubicacion_desde = '0',
                        numero_ubicacion_hasta = '0',
                        pasillo_desde = '',
                        pasillo_hasta = '',
                        altura_desde = '',
                        altura_hasta = '',
                        habilitado = '0',
                        es_principal = '0'
                    WHERE id_sucursal IN( $stores )";*/
            $sql = "DELETE FROM ec_sucursal_producto_ubicacion_almacen WHERE id_sucursal IN( $stores )";
            $stm = $this->link->query( $sql ) or die( "Error al resetear tabla ec_sucursal_producto_ubicacion_almacen : {$sql} : {$this->link->error}" );
        //resetea ubicaciones en tabla de sucursal por producto
            $sql = "UPDATE sys_sucursales_producto SET ubicacion_almacen_sucursal = '' WHERE id_sucursal IN( $stores )";
            $stm = $this->link->query( $sql ) or die( "Error al resetear tabla ec_sucursal_producto_ubicacion_almacen : {$sql} : {$this->link->error}" );
        //autoriza transaccion
            $this->link->commit();
            return '<h2 class="text-success text-center">Ubicaciones de sucursales reseteadas exitosamente</h2>
            <div class="row text-center">
                <button
                    type="button"
                    onclick="location.reload();"
                    class="btn btn-success"
                >
                    <i class="icon-ok-circled">Aceptar y recargar pantalla</i>
                </button>
            </div>';
        }
    }
    

?>
