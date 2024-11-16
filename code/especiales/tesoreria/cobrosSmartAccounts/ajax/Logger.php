<?
    final class Logger
    {
        private $link;
        function __construct( $connect ){
            $this->link = $connect;
        }
        public function insertLoggerRow( $unique_folio, $user_id, $table_name, $origin_id, $destinity_id ){
            $sql = "INSERT INTO LOG_cobros ( folio_unico_cobro, id_usuario, tabla, fecha_alta, origen, destino )
                VALUES ( '{$unique_folio}', '{$user_id}', '{$table_name}', NOW(), {$origin_id}, {$destinity_id} )";
            $stm = $this->link->query( $sql ) or die( "Error en insertLoggerRow : {$sql} : {$this->link->error}" );
        //recupera el registro
            $sql = "SELECT MAX( id_log_cobro ) AS last_id FROM LOG_cobros";
            $stm = $this->link->query( $sql ) or die( "Error en recuperar ultimo id insertado en log de cobros : {$sql} : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            return array( "status"=>"ok", "id_log"=>$row['last_id'] );
        }

        public function insertLoggerSteepRow( $log_id, $description, $sql_query ){
            $sql_query = str_replace( "'", "\'", $sql_query );
            $sql = "INSERT INTO LOG_cobros_pasos ( id_log_cobro, descripcion, consulta_sql, fecha_alta )
                VALUES ( {$log_id}, '{$description}', '{$sql_query}', NOW() )";
            $stm = $this->link->query( $sql ) or die( "Error en insertLoggerSteepRow : {$sql} : {$this->link->error}" );
        //recupera el registro
            $sql = "SELECT MAX( id_cobro_paso ) AS last_id FROM LOG_cobros_pasos";
            $stm = $this->link->query( $sql ) or die( "Error en recuperar ultimo id insertado en log de pago paso : {$sql} : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            return $row['last_id'];
            //return array( "status"=>"ok", "id_cobro_paso"=>$row['last_id'] );
        }

        public function insertErrorSteepRow( $step_log_id, $table_name, $row_unique_folio, $sql_query, $sql_error ){
            $sql = "INSERT INTO LOG_cobros_pasos_errores ( id_cobro_paso, tabla, folio_unico_registro, instruccion_sql, error_sql, fecha_alta )
                VALUES ( {$step_log_id}, '{$table_name}', '{$row_unique_folio}', '{$sql_query}', '{$sql_error}', NOW() )";
            $stm = $this->link->query( $sql ) or die( "Error en insertErrorSteepRow : {$sql} : {$this->link->error}" );
        //recupera el registro
            $sql = "SELECT MAX( id_cobro_error ) AS last_id FROM LOG_cobros_pasos_errores";
            $stm = $this->link->query( $sql ) or die( "Error en recuperar ultimo id insertado en log de cobro paso error : {$sql} : {$this->link->error}" );
            $row = $stm->fetch_assoc();
            return array( "status"=>"ok", "id_cobro_error"=>$row['last_id'] );
        }
    }
    
?>