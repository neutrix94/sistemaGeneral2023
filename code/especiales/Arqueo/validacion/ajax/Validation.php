<?php
    if( isset( $_POST['validation_flag'] ) || isset( $_GET['validation_flag'] ) ){
        include( '../../../../../conexionMysqli.php' );
        $action = ( isset( $_POST['validation_flag'] ) ? $_POST['validation_flag'] : $_GET['validation_flag'] );
        $Validation = new Validation( $link );
        switch ($action) {
            case 'getValidationAmmounts':
                $teller_session_id = ( isset( $_POST['teller_session_id'] ) ? $_POST['teller_session_id'] : $_GET['teller_session_id'] );
                echo json_encode( $Validation->getValidationAmmounts( $teller_session_id ) );
            break;

            default:
            break;
        }
    }
    class Validation{
        private $link;
        public function __construct( $connection ) {
            $this->link = $connection;
        }
         
        public function getValidationAmmounts( $teller_session_id ){
            $resp = array();
            $resp['afiliations'] = array();
            $resp['terminals'] = array();
            $resp['checks'] = array();
            $resp['transfers'] = array();

            $sql = "SELECT
				SUM( monto ) AS ingreso_total,
				SUM( IF( id_tipo_pago = 1 OR id_tipo_pago = 2, monto, 0 ) ) AS ingreso_efectivo,
				SUM( IF( id_tipo_pago = 7, monto, 0 ) ) AS ingreso_tarjetas,
				SUM( IF( id_tipo_pago = 8, monto, 0 ) ) AS ingreso_transferencias,
				SUM( IF( id_tipo_pago = 9, monto, 0 ) ) AS ingreso_cheques
			FROM ec_cajero_cobros
			WHERE id_sesion_caja = {$teller_session_id}
			AND cobro_cancelado = 0";
            $stm = $this->link->query( $sql ) or die( "Error al consultar ingresos cobrados : {$sql} : {$this->link->error}" );
            $cajero_cobros = $stm->fetch_assoc();//mysql_fetch_assoc($eje );
            $resp['entrada'] = $cajero_cobros['ingreso_total'];
            $resp['entrada_efectivo'] = $cajero_cobros['ingreso_efectivo'];
            $resp['entrada_tarjeta'] = $cajero_cobros['ingreso_tarjetas'];
            $resp['entrada_transferencia'] = $cajero_cobros['ingreso_transferencias'];
            $resp['entrada_cheque'] = $cajero_cobros['ingreso_cheques'];

            $resp['afiliations'] = $this->getValidationInbursaPayments( $teller_session_id );
            $resp['terminals'] = $this->getValidationNetPayPayments( $teller_session_id );
            
            return $resp;
        }

    //Consulta pagos con tarjeta ( Inbursa )
        public function getValidationInbursaPayments( $teller_session_id ){
            $resp = array();
            $sql = "SELECT 
                a.id_afiliacion,
                a.no_afiliacion AS afiliation_name,
                CONCAT( a.observaciones ) As observations,
                SUM( IF( cc.id_cajero_cobro IS NULL, 0, cc.monto ) ) AS ammount_sum
            FROM ec_afiliaciones a
            LEFT JOIN ec_cajero_cobros cc
            ON cc.id_afiliacion = a.id_afiliacion
            WHERE a.id_afiliacion>0
            AND cc.id_sesion_caja = '{$teller_session_id}'
            GROUP BY cc.id_afiliacion";
            $stm = $this->link->query( $sql ) or die( "Error al consultar cobros con terminales de inbursa : {$sql} : {$this->link->error}" );
            while( $row = $stm->fetch_assoc() ){
                array_push( $resp, $row );
            }
            return $resp;
        }
    //Consulta pagos con terminales NetPay
        public function getValidationNetPayPayments( $teller_session_id ){
            $resp = array();
            $sql="SELECT 
                tis.id_terminal_integracion,
                CONCAT( tis.nombre_terminal, ' - ', tis.numero_serie_terminal, ' - ', tis.store_id ) AS terminal_name,
                SUM( IF( cc.id_cajero_cobro IS NULL, 0, cc.monto ) ) AS ammount_sum
            FROM ec_terminales_integracion_smartaccounts tis
            LEFT JOIN ec_cajero_cobros cc
            ON tis.id_terminal_integracion = cc.id_terminal
            WHERE tis.id_terminal_integracion > 0
            AND cc.id_sesion_caja = '{$teller_session_id}'
            GROUP BY cc.id_terminal";
            $stm = $this->link->query( $sql ) or die( "Error al consultar cobros con terminales de NetPay : {$sql} : {$this->link->error}" );
            while( $row = $stm->fetch_assoc() ){
                array_push( $resp, $row );
            }
            return $resp;
        }

    }
    
?>