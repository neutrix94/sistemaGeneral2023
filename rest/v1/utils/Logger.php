<?
    final class Logger
    {
        private $link;
        function __construct( $connect ){
            include( '../../conexionMysqli.php' );
            $this->link = $link;
        }

        /*private function connect(){
            $dbHost = "localhost"; 
            $dbUser = "root"; 
            $dbPassword = ""; 
            $dbName = "logs_sincronizacion";
            $this->link = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
            if( $this->link->connect_error ){
                die( "Error al conectar con la Base de Datos : {$this->link->connect_error}");
            }
            $this->link->set_charset("utf8mb4");
        }
        public function check_log_configuration(){
            $sql = "SELECT
                        log_habilitado AS log_is_enabled
                FROM sys_configuraciones_logs  
                WHERE id_configuracion_log = 1";
            $stm = $this->link->query( $sql ) or die( "Error al consultar si el log esta habilitado : {$sql} : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            return $row['log_is_enabled'];
        }*/
        public function insertLoggerRow( $unique_folio, $table_name, $origin_id, $destinity_id ){
            $sql = "INSERT INTO LOG_sincronizaciones ( folio_unico_sincronizacion, tabla, fecha_alta, origen, destino )
                VALUES ( '{$unique_folio}', '{$table_name}', NOW(), {$origin_id}, {$destinity_id} )";
            $stm = $this->link->query( $sql ) or die( "Error en insertLoggerRow : {$sql} : {$this->link->error}" );
        //recupera el registro
            $sql = "SELECT MAX( id_sincronizacion ) AS last_id FROM LOG_sincronizaciones";
            $stm = $this->link->query( $sql ) or die( "Error en recuperar ultimo id insertado en log de sincronizacion : {$sql} : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            return array( "status"=>"ok", "id_sincronizacion"=>$row['last_id'] );
        }

        public function insertLoggerSteepRow( $synchronization_id, $description, $sql_query, $debug = false ){
            /*if( $debug ){
                var_dump($this->link);
            }*/
            $sql_query = str_replace( "'", "", $sql_query );
            $sql = "INSERT INTO LOG_sincronizacion_pasos ( id_sincronizacion, descripcion, consulta_sql, fecha_alta )
                VALUES ( {$synchronization_id}, '{$description}', '{$sql_query}', NOW() )";
            $stm = $this->link->query( $sql ) or die( "Error en insertLoggerSteepRow : {$this->link->error} : {$sql} " );
        //recupera el registro
            $sql = "SELECT MAX( id_sincronizacion_paso ) AS last_id FROM LOG_sincronizacion_pasos";
            $stm = $this->link->query( $sql ) or die( "Error en recuperar ultimo id insertado en log de sincronizacion paso : {$sql} : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            return $row['last_id'];//array( "status"=>"ok", "id_sincronizacion_paso"=>$row['last_id'] );
        }

        public function insertErrorSteepRow( $synchronization_steep_id, $table_name, $row_unique_folio, $sql_query, $sql_error ){
            
            $sql_query = str_replace( "'", "", $sql_query );
            $sql_error = str_replace( "'", "", $sql_error );
            $sql = "INSERT INTO LOG_sincronizacion_pasos_errores ( id_sincronizacion_paso, tabla, folio_unico_registro, instruccion_sql, error_sql, fecha_alta )
                VALUES ( {$synchronization_steep_id}, '{$table_name}', '{$row_unique_folio}', '{$sql_query}', '{$sql_error}', NOW() )";
            $stm = $this->link->query( $sql ) or die( "Error en insertErrorSteepRow : {$sql} : {$this->link->error}" );
        //recupera el registro
            $sql = "SELECT MAX( id_sincronizacion_error ) AS last_id FROM LOG_sincronizacion_pasos_errores";
            $stm = $this->link->query( $sql ) or die( "Error en recuperar ultimo id insertado en log de sincronizacion paso error : {$sql} : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            return array( "status"=>"ok", "id_sincronizacion_error"=>$row['last_id'] );
        }
    }
    
?>