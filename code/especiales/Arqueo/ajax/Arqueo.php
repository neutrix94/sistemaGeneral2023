<?php
	include( '../../../../conexionMysqli.php' );
	/**
	* 
	*/
	class Arqueo
	{
		private $link;
		private $cards_counter = 0;
		function __construct( $connection )
		{
			$this->link = $connection;
		}
		function getLogin( $user_id ){
		//consultamos el login del cajero
			$sql="SELECT login FROM sys_users WHERE id_usuario = $user_id";
			$eje = $this->link->query($sql)or die("Error al consultar el login del usuario logueado en este sistema!!!<br>".mysql_error());
			$r = $eje->fetch_assoc();
			$login_cajero=$r['login'];
			return $login_cajero;
		}

		function getSessionData( $user_id, $llave ){
		//consultamos los datos del corte
			$sql="SELECT 
					id_sesion_caja,
					folio,
					fecha,
					hora_inicio,
					IF(hora_fin='00:00:00','23:59:59',hora_fin)AS hora_fin,
					observaciones FROM ec_sesion_caja WHERE ";
			
			if($llave=='0'){
				$sql.="id_cajero='".$user_id."' AND ( hora_fin='00:00:00' OR (hora_fin='23:59:59' AND observaciones like '%1___%') )";
			}else{
				$sql.="id_sesion_caja = '{$llave}'";
			}
			$sql.=" ORDER BY id_sesion_caja DESC LIMIT 1";
			
			//die($sql);
			$eje = $this->link->query($sql)or die("Error al consultar datos de la sesion de caja!!!<br>".mysql_error());
			$row = $eje->fetch_row();
			return $row;
		}
	//
		function getSessionBefore( $user_id ){
			$sql="SELECT 
					id_sesion_caja As session_id 
				FROM ec_sesion_caja 
				WHERE id_cajero = {$user_id} 
				AND hora_fin!='00:00:00' 
				ORDER BY id_sesion_caja DESC LIMIT 1";
			$eje = $this->link->query($sql)or die("Error al consultar el ultmio corte!!!<br>".mysql_error());
			$r = $eje->fetch_assoc();
			return $r['session_id'];
		}

		function getAfiliaciones( $user_id, $fecha_sesion, $hora_inicio_sesion, $hora_cierre_sesion, $id_sesion_caja ){
			$tarjetas_cajero='';
			$sql="SELECT 
					a.id_afiliacion,
					a.no_afiliacion,
					sca.insertada_por_error_en_cobro,
					CONCAT( a.observaciones )/*a.no_afiliacion, ' ', */
				FROM ec_afiliaciones a
				/*LEFT JOIN ec_afiliaciones_cajero ac 
				ON ac.id_afiliacion = a.id_afiliacion*/
				LEFT JOIN ec_sesion_caja_afiliaciones sca
				ON sca.id_afiliacion = a.id_afiliacion 
				WHERE /*ac.id_cajero='{$user_id}' 
				AND ac.activo=1
				AND */a.id_afiliacion>0
				AND sca.id_sesion_caja = '{$id_sesion_caja}'";
			$eje = $this->link->query( $sql )or die("Error al consultar las afiliaciones para este cajero!!!<br>{$this->link->error}");
			//$afiliacion_1='<select id="tarjeta_1" class="filtro"><option value="0">--SELECCIONAR--</option>';
			$tarjetas_cajero='';
			$this->cards_counter=0;
			while( $r = $eje->fetch_row() ){
				$es_por_error = '';//"text-success";
				if( $r[2] == '1' ){
					$es_por_error = 'text-danger';
				}	
			//sumamos los pagos del cajero en caso de tener pagos
				$sql="SELECT 
						SUM( IF( id_cajero_cobro IS NULL,0,monto ) ) 
						FROM ec_cajero_cobros 
						WHERE id_cajero = '{$user_id}' 
						AND fecha = '{$fecha_sesion}' 
						AND hora >= '{$hora_inicio_sesion}' 
						AND hora <= '{$hora_cierre_sesion}' 
						AND id_afiliacion = '{$r[0]}'";
				$eje_tar = $this->link->query($sql)or die("Error al consultar los pagos con tarjetas!!!<br> {$this->link->error}");
				$r1 = $eje_tar->fetch_row();
				$total = $r1[0];
				if( $total == '' ){
					$total=0;
				}
				$this->cards_counter++;
				$error = "";
				if( $r[2] == 1 ){
					$error = "color : red !important;";
				}
				$tarjetas_cajero.="<tr class=\"informative_row\" style=\"{$error}\">";
					$tarjetas_cajero.="<td colspan=\"2\" class=\"bg-warning\"><p style=\"font-size:20px;margin:0;{$error}\"  align=\"center\" id=\"card_description_{$this->cards_counter}\">{$this->cards_counter}.- {$r[3]} :</p></td>";//
				$tarjetas_cajero.='</tr>';
				$tarjetas_cajero.='<tr class="is_card_row">';
					$tarjetas_cajero.='<td align="center">';
						$tarjetas_cajero.='<select id="tarjeta_'.$this->cards_counter .'" class="form-select ' . $es_por_error . '" style="width:95%"><option value="'.$r[0].'">'.$r[1].'</option>';
					$tarjetas_cajero.='</td>';
					$tarjetas_cajero.='<td>';
						$tarjetas_cajero.='<input type="text" class="form-control text-end ' . $es_por_error . '" id="t'.$this->cards_counter.'" value="'.$total.'" onkeyup="cambia_valor(this,\'ta'.$this->cards_counter.'\');" readonly>';
					$tarjetas_cajero.='</td>';
				$tarjetas_cajero.='</tr>';	
			}
			return $tarjetas_cajero;
		}

		function getSmartAccountsTerminals( $user_sucursal, $user_id, $fecha_sesion, $hora_inicio_sesion, $hora_cierre_sesion, $id_sesion_caja ){

			$SmartAccountsTerminals='';
			$sql="SELECT 
					tis.id_terminal_integracion,
					tis.nombre_terminal,
					nombre_terminal
				FROM ec_terminales_integracion_smartaccounts tis
				LEFT JOIN ec_terminales_sucursales_smartaccounts tss
				ON tss.id_terminal = tis.id_terminal_integracion
				AND tss.id_sucursal = {$user_sucursal}
				LEFT JOIN ec_terminales_cajero_smartaccounts tcs 
				ON tcs.id_terminal = tis.id_terminal_integracion
				LEFT JOIN ec_sesion_caja_terminales sct
				ON sct.id_terminal = tcs.id_terminal
				WHERE tcs.id_cajero = '{$user_id}' 
				AND tcs.activo = 1 
				AND tss.estado_suc = 1
				AND tis.id_terminal_integracion > 0
				AND sct.id_sesion_caja = '{$id_sesion_caja}'";
			$eje = $this->link->query( $sql )or die("Error al consultar las afiliaciones para este cajero!!!<br>{$this->link->error}");
			//$afiliacion_1='<select id="tarjeta_1" class="filtro"><option value="0">--SELECCIONAR--</option>';
			$SmartAccountsTerminals='';
			//$c=0;
			while( $r = $eje->fetch_row() ){
			//sumamos los pagos del cajero en caso de tener pagos
				$sql="SELECT 
						SUM( IF( id_cajero_cobro IS NULL,0,monto ) ) 
						FROM ec_cajero_cobros 
						WHERE id_cajero = '{$user_id}' 
						AND fecha = '{$fecha_sesion}' 
						AND hora >= '{$hora_inicio_sesion}' 
						AND hora <= '{$hora_cierre_sesion}' 
						AND id_terminal = '{$r[0]}'";
				$eje_tar = $this->link->query($sql)or die("Error al consultar los pagos con tarjetas!!!<br> {$this->link->error}");
				$r1 = $eje_tar->fetch_row();
				$total = $r1[0];
				if( $total == '' ){
					$total=0;
				}
				$this->cards_counter++;
				$SmartAccountsTerminals .= '<tr class="informative_row">';
					$SmartAccountsTerminals .= '<td colspan="2" class="bg-warning"><p style="font-size:20px;margin:0;" align="center" id="card_description_' . $this->cards_counter . '">' . $r[2] . '</p></td>';//Terminal :'.$this->cards_counter.'
				$SmartAccountsTerminals .= '</tr>';
				$SmartAccountsTerminals .= '<tr class="is_card_row">';
					$SmartAccountsTerminals .= '<td align="center">';
						$SmartAccountsTerminals .= '<select id="tarjeta_'.$this->cards_counter.'" class="form-select" style="width:95%"><option value="'.$r[0].'">'.$r[1].'</option>';
					$SmartAccountsTerminals .= '</td>';
					$SmartAccountsTerminals .= '<td>';
						$SmartAccountsTerminals .= '<input type="text" class="form-control text-end" id="t'.$this->cards_counter.'" value="'.$total.'" onkeyup="cambia_valor(this,\'ta'.$this->cards_counter.'\');" readonly>';
					$SmartAccountsTerminals .= '</td>';
				$SmartAccountsTerminals .= '</tr>';	
			}
			return $SmartAccountsTerminals;
		}

		function getAccounts( $user_sucursal ){
			$cajas = "";
		//cheque o transferencia 
			$sql="SELECT 
					bc.id_caja_cuenta,
					bc.nombre 
				FROM ec_caja_o_cuenta bc
				LEFT JOIN ec_caja_o_cuenta_sucursal bcs 
				ON bc.id_caja_cuenta = bcs.id_caja_o_cuenta 
				WHERE bcs.estado_suc = 1
				AND bcs.id_sucursal = '$user_sucursal'
				AND bc.id_tipo_caja IN( 2 )";
			$eje = $this->link->query( $sql ) or die( "Error al listar los bancos o cajas!!!<br>{$this->link->error}" );
			$cajas .= "<select id=\"caja_o_cuenta\" class=\"form-select\" style=\"width:95%\">
						<option value=\"0\">--SELECCIONAR--</option>";
			while($r = $eje->fetch_row() ){
				$cajas .= "<option value=\"{$r[0]}\">{$r[1]}</option>";
			}
			$cajas .= "</select>";
			return $cajas;
		}
		public function getAdittionalPayments( $user_id, $fecha_sesion, $hora_inicio_sesion ){
			$pagos_chqs = '';
			$cont_chqs = 0;
			$sql="SELECT  
					cc.id_banco,
					coc.nombre,
					cc.monto,
					cc.observaciones
				FROM ec_cajero_cobros cc
				LEFT JOIN ec_caja_o_cuenta coc ON cc.id_banco=coc.id_caja_cuenta
				WHERE cc.id_cajero = '{$user_id}' 
				AND cc.fecha = '{$fecha_sesion}'
				AND cc.hora>='{$hora_inicio_sesion}' 
				AND cc.id_tipo_pago IN( 8,9 )";
			$eje_chq = $this->link->query( $sql ) or die( "Error al consultar los pagos con cheques y transferencias!!!<br> {$this->link->error}" );
			while( $r1 = $eje_chq->fetch_row() ){
				$cont_chqs++;
				$pagos_chqs.='<tr id="fila_ch_'.$cont_chqs.'">';
	        	$pagos_chqs.='<td id="caja_'.$cont_chqs.'" class="td_oculto">'.$r1[0].'</td>';
	        	$pagos_chqs.='<td align="left">'.$r1[1].'</td>';
	        	$pagos_chqs.='<td id="monto_'.$cont_chqs.'" align="center">'.$r1[2].'</td>';
	        	$pagos_chqs.='<td id="referencia_'.$cont_chqs.'" align="left">'.$r1[3].'</td>';
	      		$pagos_chqs.='</tr>';
			}
			return $pagos_chqs;
		}

		public function getStoreConfig( $sucursal_id ){
			$sql = "SELECT 
						imprimir_validaciones_pendientes AS print_pending_validations
					FROM ec_configuracion_sucursal
					WHERE id_sucursal = {$sucursal_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar configuracion de ventas sin vlidar : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row;
		}
	}
?>