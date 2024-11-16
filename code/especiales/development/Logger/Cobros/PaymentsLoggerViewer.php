<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
<script src="../../../../../js/jquery-1.10.2.min.js"></script>
<style>
    .hidden{
        display:none;
    }
    .mg_30{
        padding : 30px;
    }
    .emergent{
        position : fixed;
        width : 100%;
        top : 0;
        height: 100%;
        left : 0;
        background : rgba( 0,0,0,.5 );
        display : none;
    }
    .emergent_content{
        position : absolute;
        width : 90%;
        top : 10%;
        height: 80%;
        left : 5%;
        background : white;
    }
    .textarea_full{
        position: relative;
        width : calc( 100% - 40px );
        height : calc( 100% - 100px );
        margin : 20px;
    }
</style>

<script>
    function show_or_hidde( counter ){
        if( $( '#body_' + counter ).hasClass( "hidden" ) ){
            $( '#body_' + counter ).removeClass( "hidden" );
        }else{
            $( '#body_' + counter ).addClass( "hidden" );
        }
    }
    function close_emergent(){
        $( '.emergent_content' ).html( '' );
        $( '.emergent' ).css( 'display', 'none' );
    }
    function show_detail( obj ){
        var button = `<div class="text-end">
            <button
                class="btn btn-danger"
                onclick="close_emergent();"
            >
                X
            </button>
        </div>`;
        var val = $( obj ).val();
        $( '.emergent_content' ).html( `${button}<textarea class="textarea_full">${val}</textarea>` );
        $( '.emergent' ).css( 'display', 'block' );
    }
</script>

<div class="emergent">
    <div class="emergent_content"></div>
</div>
<?php
    if( !include( '../../../../../conexionMysqli.php' ) ){
        die( "../../../../../conexionMysqli.php" );
    }
    $LoggerViewer = new LoggerViewer( $link );
    $contents = $LoggerViewer->getLoggerRows();
    //var_dump( $contents );
    foreach ($contents as $key => $content) {
        echo "<div class=\"row mg_30\" >
            <table class=\"table table-striped\">
            <thead>
            <tr class=\"btn-primary\">
                <th>ID</th>
                <th>Folio Unico Pet.</th>
                <th>Tabla</th>
                <th>Fecha Alta</th>
                <th>Origen</th>
                <th>Destino</th>
            </tr>
        </thead>
        <tbody>";
        echo "<tr>
            <td>{$content['id_log_cobro']}</td>
            <td>{$content['folio_unico_cobro']}</td>
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
                    <td>{$content['id_log_cobro']}.{$key2}</td>
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
    final class LoggerViewer
    {
        private $link;
        function __construct( $connect ){
            $this->link = $connect;
        }

        private function connect( $connect ){
           //echo "here";
            /*$dbHost = "localhost"; 
            $dbUser = "root"; 
            $dbPassword = ""; 
            $dbName = "logs_sincronizacion";
            $this->link = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
            if( $this->link->connect_error ){
                die( "Error al conectar con la Base de Datos : {$this->link->connect_error}");
            }*/
            $this->link = $connect;
        }
        public function getLoggerRows(){
            $resp = array();
            $sql = "SELECT
                        id_log_cobro,
                        folio_unico_cobro, 
                        tabla, 
                        fecha_alta, 
                        origen, 
                        destino
                    FROM LOG_cobros
                    ORDER BY id_log_cobro DESC";
            $stm = $this->link->query( $sql ) or die( "Error en getLoggerRows : {$sql} : {$this->link->error}" );
        //recupera el registro
            while ( $row = $stm->fetch_assoc() ){
                $row['steps'] = $this->getLoggerSteepRow( $row['id_log_cobro'] );
                $resp[] = $row;
            }
            return $resp;
        }

        public function getLoggerSteepRow( $id_log_cobro ){
            $resp = array();
            $sql_query = str_replace( "'", "\'", $sql_query );
            $sql = "SELECT
                        id_cobro_paso,
                        descripcion,
                        consulta_sql,
                        fecha_alta
                    FROM LOG_cobros_pasos
                    WHERE id_log_cobro = {$id_log_cobro}";
            $stm = $this->link->query( $sql ) or die( "Error en getLoggerSteepRow : {$sql} : {$this->link->error}" );
        //recupera el registro
            while( $row = $stm->fetch_assoc() ){
                $row['errors'] = $this->getErrorSteepRow( $row['id_cobro_paso'] );
                $resp[] = $row;
            }
            return $resp;
        }

        public function getErrorSteepRow( $synchronization_steep_id ){
            $resp = array();
            $sql = "SELECT 
                        id_cobro_error, 
                        tabla, 
                        folio_unico_registro, 
                        instruccion_sql, 
                        error_sql,
                        fecha_alta
                    FROM LOG_cobros_pasos_errores
                    WHERE id_cobro_paso = {$synchronization_steep_id}";
            $stm = $this->link->query( $sql ) or die( "Error en getErrorSteepRow : {$sql} : {$this->link->error}" );
            while( $row = $stm->fetch_assoc() ){
                $resp[] = $row;
            }
            return $resp;
        }
    }
    
?>