<?php
//valida si la peticion es sobre este mismo archivo
    if (basename($_SERVER['PHP_SELF']) == 'db.php') {
       echo "<center><br><br><br><br><br><br><br><br><br><h2>Error 404 : La peticion no puede ser respondida...</h2></center>";
        exit;
    }
    
    $conexion = new Db( $dbHost, $dbUser, $dbPassword, $dbName );
    class Db{
        private $connection;
        private $stm;
        private $rs;
        
        public function __construct( $host, $user, $pass, $dbName) {
            try{
                $this->connection = new mysqli( $host, $user, $pass, $dbName );
                $this->connection->set_charset("utf8mb4");
            } catch ( Exception $e){
                  echo "Error al conectar con la Base de Datos : {$e}";
            }
        }//fin de constructor
        
        public function desconectar(){
            $this->connection->close();
            $this->rs = null;
            $this->stm = null;
            $this->connection = null;
        }
        
        public function execQuery( $sql = null, $where = "", $type = null ){
            $resp;
            $this->stm = $this->connection->query( $sql . " " . $where );
            if( ! $this->stm ){
                die("Error al ejecutar consulta: " . $this->connection->error . $sql);
            }
            if( $type == "SELECT" ){
                return $this->FetchData($this->stm);
            }else if ( $type == "INSERT" ){
                return array( 'ok', $this->connection->insert_id );
            }
            return $resp;
        }
        
        public function FetchData( $results ){
            $resp;
            while ( $r = $results->fetch_assoc() ){
                $resp[] = $r;
            }
            return ( $resp == null ? null : $resp);//array('Sin datos')
        }
    }
?>
