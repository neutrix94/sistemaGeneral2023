<?php
    if( isset( $_POST['log_flag'] ) || isset( $_GET['log_flag'] ) ){
        if( !include('../../../../../../conexionMysqli.php') ){
            die( "Error al incluir : ../../../../../../conexionMysqli.php" );
        }
        $LoggerViewer = new LoggerViewer($link);
        $action = ( isset( $_POST['log_flag'] ) ? $_POST['log_flag'] : $_GET['log_flag'] );
        switch( $action ){
            case 'filter_by_table':
                $table = ( isset( $_POST['table'] ) ? $_POST['table'] : $_GET['table'] );
                $table = ( $table == -1 ? null : $table );
                echo $LoggerViewer->getLoggerRows( $table, null );
            break;
            case 'filter_by_folio':
                $folio = ( isset( $_POST['folio'] ) ? $_POST['folio'] : $_GET['folio'] );
                echo $LoggerViewer->getLoggerRows( null, $folio );
            break;

        }
    }
final class LoggerViewer
    {
        private $link;
        function __construct( $connect ){
            $this->link = $connect;
        }

        private function connect( $connect ){
            $this->link = $connect;
        }

        public function getTables(){
            $resp = "<select class=\"form-select\" style=\"padding:7px;\" onchange=\"filtra_por_tabla( this );\">
            <option value=\"-1\">Todas</option>";
            $sql = "SELECT DISTINCT( tabla ) AS table_name FROM LOG_sincronizaciones";
            $stm = $this->link->query( $sql ) or die( "Error al consultar tablas de logs : {$sql} : {$this->link->error}" );
            while( $row = $stm->fetch_assoc() ){
                $resp .= "<option value=\"{$row['table_name']}\">{$row['table_name']}</option>";
            }
            $resp .= "</select>";
            return $resp;
        }
        public function getLoggerRows( $table = null, $unique_folio = null ){
            $resp = array();
            $sql = "SELECT
                        id_sincronizacion,
                        folio_unico_sincronizacion, 
                        tabla, 
                        fecha_alta, 
                        origen, 
                        destino
                    FROM LOG_sincronizaciones
                    WHERE 1=1";
            $sql .= ( $table != null ? " AND tabla = '{$table}'" : "" );
            $sql .= ( $unique_folio != null ? " AND folio_unico_sincronizacion = '{$unique_folio}'" : "" );

            $sql .= " ORDER BY id_sincronizacion DESC";
            $stm = $this->link->query( $sql ) or die( "Error en getLoggerRows : {$sql} : {$this->link->error}" );
        //recupera el registro
            while ( $row = $stm->fetch_assoc() ){
                $row['steps'] = $this->getLoggerSteepRow( $row['id_sincronizacion'] );
                $resp[] = $row;
            }
            return $this->buildRows( $resp );
        }

        public function buildRows( $contents ){
            //$contents = $LoggerViewer->getLoggerRows();
            //var_dump( $contents );
            foreach ($contents as $key => $content) {
                echo "<div class=\"row mg_30\" >
                    <table class=\"table table-striped\">
                    <thead>
                    <tr class=\"btn-primary\">
                        <th>ID</th>
                        <th>Folio Unico Sinc</th>
                        <th>Tabla</th>
                        <th>Fecha Alta</th>
                        <th>Origen</th>
                        <th>Destino</th>
                    </tr>
                </thead>
                <tbody>";
                echo "<tr>
                    <td>{$content['id_sincronizacion']}</td>
                    <td>{$content['folio_unico_sincronizacion']}</td>
                    <td>{$content['tabla']}</td>
                    <td>{$content['fecha_alta']}</td>
                    <td>{$content['id_origen']}</td>
                    <td>{$content['id_destino']}</td>
                </tr>";
                if( $content['steps'] ){
                    echo "<tr>
                    <td colspan=\"7\">
                    <table class=\"table\">
                    <thead onclick=\"show_or_hidde( {$key} );\">
                        <tr>
                            <th class=\"text-center btn-info\" colspan=\"7\">PASOS : <th>
                        </tr>
                    </thead>
                    <tbody class=\"hidden\" id=\"body_{$key}\">
                        <tr>
                            <th class=\"text-center\">#</th>
                            <th class=\"text-center\">Descripción</th>
                            <th class=\"text-center\">Fecha</th>
                            <th class=\"text-center\">SQL</th>
                        </tr>";
                    foreach ($content['steps'] as $key2 => $step) {
                        echo "<tr>
                            <td>{$content['id_sincronizacion']}.{$key2}</td>
                            <td>{$step['descripcion']}</td>
                            <td>{$step['fecha_alta']}</td>
                            <td>
                                <textarea style=\"width:100%;\" 
                                    onclick=\"show_detail( this )\"
                                >
                                    {$step['consulta_sql']}
                                </textarea>
                            </td>
                        </tr>";
                        if( $step['errors'] ){
                            echo "<tr>
                            <td colspan=\"3\">
                                <table class=\"table\">
                                    <thead>
                                        <tr>
                                            <th class=\"text-center btn-danger\" collspan=\"5\">ERRORES : <th>
                                        </tr>
                                        <tr>
                                            <th class=\"text-center\">Tabla</th>
                                            <th class=\"text-center\">Folio unico reg</th>
                                            <th class=\"text-center\">SQL</th>
                                            <th class=\"text-center\">Error</th>
                                            <th class=\"text-center\">Fecha</th>
                                        </tr>
                                    </thead>";
                            foreach ($step['errors'] as $key3 => $error) {
                                echo "<tr>
                                    <td>{$error['tabla']}</td>
                                    <td>{$error['folio_unico_registro']}</td>
                                    <td>{$error['instruccion_sql']}</td>
                                    <td>{$error['error_sql']}</td>
                                    <td>{$error['fecha_alta']}</td>
                                </tr>";
                            }
                            echo "</table>
                            </td>
                        </tr>";
                        }
                    }
                    echo "</table>
                        </td>
                    </tr>";
                }
                echo "</tbody>
                    </table>
                </div>";
            }
        }

        public function getLoggerSteepRow( $synchronization_id ){
            $resp = array();
            $sql_query = str_replace( "'", "\'", $sql_query );
            $sql = "SELECT
                        id_sincronizacion_paso,
                        descripcion,
                        consulta_sql,
                        fecha_alta
                    FROM LOG_sincronizacion_pasos
                    WHERE id_sincronizacion = {$synchronization_id}";
            $stm = $this->link->query( $sql ) or die( "Error en getLoggerSteepRow : {$sql} : {$this->link->error}" );
        //recupera el registro
            while( $row = $stm->fetch_assoc() ){
                $row['errors'] = $this->getErrorSteepRow( $row['id_sincronizacion_paso'] );
                $resp[] = $row;
            }
            return $resp;
        }

        public function getErrorSteepRow( $synchronization_steep_id ){
            $resp = array();
            $sql = "SELECT 
                        id_sincronizacion_error, 
                        tabla, 
                        folio_unico_registro, 
                        instruccion_sql, 
                        error_sql,
                        fecha_alta
                    FROM LOG_sincronizacion_pasos_errores
                    WHERE id_sincronizacion_paso = {$synchronization_steep_id}";
            $stm = $this->link->query( $sql ) or die( "Error en getErrorSteepRow : {$sql} : {$this->link->error}" );
            while( $row = $stm->fetch_assoc() ){
                $resp[] = $row;
            }
            return $resp;
        }
    }
?>