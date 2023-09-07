<?php
	if( isset( $_GET['status_id'] ) && $_GET['status_id'] == 6 ){
		include( '../../../../../conectMin.php' );
		include( '../../../../../conexionMysqli.php' );
		echo getDataTransfer ( 6, 'Terminado', null, $user_id, $link, $_GET['days'] );

	}

	function getWarehouses ( $link ){
		$resp = '<select style="padding:5px; border : 1px solid silver; border-radius : 5px;"'
		. ' onchange="warehouse_filter()" id="warehouses">';
		$resp .= '<option value="0"> -- Todos los Almacenes -- </option>';
		$sql = "SELECT 
					id_almacen, 
					nombre
				FROM ec_almacen 
				WHERE id_almacen > 0";
		$eje = $link->query( $sql ) or die( "Error al consultar los alamacenes : {$link->error}" );
		while ( $r = $eje->fetch_row() ){
			$resp .= '<option value="' . $r[0] . '">' . $r[1] . '</option>';
		} 
		$resp .= '</select>';
		return $resp;
	}
	function getStatusTransfers ( $link, $user_id ){
		$resp = '';
		$sql = "SELECT 
					s.id_estatus, 
					s.nombre 
				FROM ec_estatus_transferencia s
				LEFT JOIN sys_menus mnu ON mnu.liga = s.id_estatus
				LEFT JOIN sys_permisos perm ON perm.id_menu = mnu.id_menu
				/*AND ( (perm.id_menu BETWEEN 217 AND 223) OR (  perm.id_menu = 235 OR perm.id_menu = 236 ) )*/
				AND perm.id_menu IN( 217, 218, 219, 220, 221, 222, 223, 235, 236 )
				AND perm.id_perfil = (SELECT tipo_perfil FROM sys_users WHERE id_usuario = '{$user_id}') 
				WHERE perm.ver = 1
				OR perm.modificar = 1
				OR perm.eliminar = 1
				OR perm.nuevo = 1
				OR perm.imprimir = 1
				OR perm.generar = 1";
		$eje = $link->query( $sql ) or die( "Error al consultar lo diferentes status de transferencias : {$link->error}");
		$c = 0;
		while ( $r = $eje->fetch_row() ) {
			if( $r[0] == 2 ){
				$resp .= getDataTransfer( $r[0], ( $r[1] . ' - Urgente' ), 1, $user_id, $link );
			}
			$resp .= getDataTransfer( $r[0], ($r[1] . ( $r[0] == 2 ?  ' - Normal' : '' ) ), null, $user_id, $link );
			$c ++;
		}
		return $resp . '<input type="hidden" id="total_list" value="' . $c . '">';
	}

	function getDataTransfer ( $status, $status_name, $type = null, $user, $link, $days_before = null ){
		$resp = array();
		$sql = "SELECT DATE_FORMAT( NOW(), '%Y')";
		$eje = $link->query( $sql ) or die( "Error al consultar el año actual : {$link->error}");
		$r = $eje->fetch_row();
		$current_year = $r[0];
		$sql = "SELECT
					t.id_transferencia AS ID,
					t.folio AS Folio,
					a.nombre AS 'Almacen origen',
					a2.nombre AS 'Almacen destino',
					CONCAT(t.fecha, ' ', t.hora) AS 'Fecha y Hora',
					e.nombre AS 'Estatus',
					t.impresa AS 'imprimido',
					t.titulo_transferencia AS Observaciones,
					t.id_tipo
				FROM ec_transferencias t
				JOIN ec_almacen a ON t.id_almacen_origen=a.id_almacen
				JOIN ec_almacen a2 ON t.id_almacen_destino = a2.id_almacen
				JOIN ec_estatus_transferencia e ON t.id_estado = e.id_estatus
				WHERE t.id_estado = '{$status}'
				AND t.id_tipo NOT IN( 9,10,11 )
				AND t.fecha LIKE '%{$current_year}%'";
		if( $status == 6 && $days_before != null ){
			$sql .= " AND t.fecha >= (SELECT date_add( NOW(), INTERVAL -{$days_before} DAY) ) ";
		}else if( $status == 6 && $days_before == null ){
			$sql .= " AND t.fecha >= (SELECT date_add( NOW(), INTERVAL -7 DAY) ) ";
		}
		if ( $status == 2 ) {	
			$sql .= ( $type != null ? " AND t.id_tipo = {$type}" : " AND t.id_tipo != 1");//condición para mostrar la tabla de transferencias urgentes
		}
		$sql .= " ORDER BY t.id_transferencia DESC";
		$eje = $link->query( $sql )or die("Error al consultar lista de transferencias : {$link->error}");
		while ( $r = $eje->fetch_row() ) {
			$resp[] = $r;
		}
		return build_list( $resp, $status_name, $user, $link, ( $days_before == null ? 1 : 0 ), $days_before );
	}

	function build_list ( $data, $status, $user_id, $link, $build_div = 0, $days_before = 7 ){
		static $num_list = 0;
		$sql = "SELECT 
					p.ver, 
					p.modificar, 
					p.eliminar, 
					p.nuevo, 
					p.imprimir, 
					p.generar 
				FROM sys_permisos p
				LEFT JOIN sys_users_perfiles prf ON prf.id_perfil = p.id_perfil
				LEFT JOIN sys_users u ON prf.id_perfil = u.tipo_perfil
				WHERE u.id_usuario = {$user_id}
				AND p.id_menu = 216
				LIMIT 1";
		$eje = $link->query( $sql ) or die( "Error al consultar los permisos :{$link->error}");
		$permisos = $eje->fetch_assoc();
		$resp = ( $build_div == 1 && $status == 'Terminado' ? '<div id="finished_transfers">' : '') 
			. '<hr><br><h1>' . $status . ' ( ' . sizeof( $data ) . ' )' 
			. ( $status == 'Terminado' ? filter_days( $days_before ) : '' ) . ' </h1>';
		$resp .= '<div class="row list_container">';
			$resp .= '<table class="table table-striped" id="list_' . $num_list . '">';
				$resp .= '<thead>';
					$resp .= '<tr>';
						$resp .= '<th width="1%">#</th>';
						$resp .= '<th style="display:none;">ID</th>';
						$resp .= '<th width="5%">Folio</th>';
						$resp .= '<th width="10%">Almacén Origen</th>';
						$resp .= '<th width="10%">Almacén Destino</th>';
						$resp .= '<th width="10%">Fecha y hora</th>';
						$resp .= '<th style="display:none;">Estatus</th>';
						$resp .= '<th width="15%">Título</th>';
						$resp .= '<th style="display:none;">Impr</th>';
						$accion_4 = 'Continuar';
						if( $status == 'Pendiente de Surtir - Normal' 
							|| $status == 'Pendiente de Surtir - Urgente'
							|| $status == 'En Surtimiento'
							|| $status == 'Surtiendo y revisando' ){
							$accion_4 = 'Asignación';
						}
						$resp .= ($permisos['modificar'] == 1 && $status != 'Salida de Transferencia' ? '<th width="4%">' . $accion_4 . '</th>' : '');
						$resp .= ($permisos['ver'] == 1 ? '<th width="4%">Ver</th>' : '');
						$resp .= ($permisos['ver'] == 1 ? '<th width="4%">PDF</th>' : '');
						$resp .= ($permisos['imprimir'] == 1 ? '<th width="4%">Imprimir</th>' : '');
						$resp .= ($permisos['imprimir'] == 1 ? '<th width="4%">Ticket</th>' : '');
					$resp .= '</tr>';
				$resp .= '</thead>';
				$resp .= '<tbody id="list_body_' . $num_list . '">';
				$count = 0;
				foreach ($data as $key => $row) {
					$count ++;
					$resp .= '<tr ' . ( $row[6] == 1 ? ( $row[8] == 1 ? 'class="red"' : 'class="green"') : '') . 
						'id="' . $num_list . '_' . $count . '">';
						$resp .= '<td class="counter">' . $count . '</td>';
						$resp .= '<td id="' . $num_list . '_0_' . $count . '" style="display:none;">' . $row[0] . '</td>';
						$resp .= '<td id="' . $num_list . '_1_' . $count . '">' . $row[0] . '</td>';
						$resp .= '<td id="' . $num_list . '_2_' . $count . '">' . $row[2] . '</td>';
						$resp .= '<td id="' . $num_list . '_3_' . $count . '">' . $row[3] . '</td>';
						$resp .= '<td id="' . $num_list . '_4_' . $count . '">' . $row[4] . '</td>';
						$resp .= '<td id="' . $num_list . '_5_' . $count . '" style="display:none;">' . $row[5] . '</td>';
						$resp .= '<td id="' . $num_list . '_7_' . $count . '">' . $row[7] . '</td>';
						$resp .= '<td style="display:none;" id="' . $num_list . '_6_' . $count . '">' . $row[6] . '</td>';
						$resp .= '<td style="display:none;" id="' . $num_list . '_8_' . $count . '">' . $row[8] . '</td>';
						$resp .= '<td style="display:none;" id="' . $num_list . '_9_' . $count . '">' . $row[1] . '</td>';
						if( $status == 'Pendiente de Surtir - Normal' 
							|| $status == 'Pendiente de Surtir - Urgente'
							|| $status == 'En Surtimiento'
							|| $status == 'Surtiendo y revisando'
						){
					//modificacion Oscar 2022
							$sql = "SELECT id_transferencia_surtimiento FROM ec_transferencias_surtimiento WHERE id_transferencia = '{$row[0]}'";
							$stm = $link->query( $sql )or die( "Error al consultar si la transferencia esta asignada : " . $link->error );
							$assigned = $stm->num_rows;
							$btn_txt = 'Asignar';
							$onclick_action  = 'assignTransfer( ' . $row[0] . ' );';
							if( $assigned > 0 ){
								$btn_txt = '<i class="icon-users">Reasignar</i>';
								$onclick_action  = 'assignTransferAgain( ' . $row[0] . ' );';
							}
							
							$resp .= ($permisos['imprimir'] == 1 ? '<td class="cont_btn"><button type="button" class="btn btn-warning" onclick="' . $onclick_action . '">' . $btn_txt . '</button></td>' : '');
							//$resp .= '<td></td>';
							//$resp .= ($permisos['imprimir'] == 1 ? '<td class="cont_btn"><button type="button" class="btn btn-success" onclick="imprimeTicketTrans(' . $num_list . ',' . $count . ');"><img src="../../../../img/impresion_tkt.png" width="30px"></button></td>' : '');
						}else if( $status == 'No autorizada' ) {
							$resp .= ($permisos['modificar'] == 1 ? '<td class="cont_btn"><button type="button" class="btn" onclick="autorizaTrans(' . $num_list . ',' . $count . ');"><img src="../../../../img/autorizarmini.png" width="30px"></button></td>' : '');
						}else if( $status == 'Revisión Finalizada' ){
							$resp .= '<td class="cont_btn"><button class="btn btn-warning" onclick="transfer_output( ' . $row[0] . ' );"><i class="icon-play"></i></button></td>';
						}
						//$resp .= ($permisos['modificar'] == 1 ? '<td class="cont_btn"><button type="button" class="btn" onclick="autorizaTrans(' . $num_list . ',' . $count . ');"><img src="../../../../img/autorizarmini.png" width="30px"></button></td>' : '');
						$resp .= ($permisos['ver'] == 1 ? '<td class="cont_btn"><button type="button" class="btn" onclick="view_transfer( ' . $num_list . ',' . $count . ' )"><img src="../../../../img/vermini2.png" width="30px"></button></td>' : '');
						$resp .= ($permisos['ver'] == 1 ? '<td class="cont_btn"><button type="button" class="btn" onclick="viewTrans( ' . $num_list . ',' . $count . ' );"><img src="../../../../img/img_casadelasluces/pdf_icon.png" width="30px"></button></td>' : '');
						
						$resp .= ($permisos['imprimir'] == 1 ? '<td class="cont_btn"><button type="button" class="btn" onclick="imprimeTrans( ' . $num_list . ',' . $count . ' );"><img src="../../../../img/imprimir_peq.png" width="30px"></button></td>' : '');
						$resp .= ($permisos['imprimir'] == 1 ? '<td class="cont_btn"><button type="button" class="btn" onclick="imprimeTicketTrans(' . $num_list . ',' . $count . ');"><img src="../../../../img/impresion_tkt.png" width="30px"></button></td>' : '');
					$resp .= '</tr>';
				}
				if( $count <= 0 ){
					$resp .= '<tr>';
					$resp .= '<td colspan="14" align="center">No hay transferencia en este Estatus</td>';
					$resp .= '</tr>';
				}
				$resp .= '</tbody>';
			$resp .= '</table>';
		$resp .= '</div>';
		$resp .= ( $build_div == 1 && $status == 'Terminado' ? '</div>' : '');
		$num_list ++;
		return $resp;
	}

	function filter_days( $day = null ){
		$resp = 'Historial de ';
		$resp .= '<input type="number" id="days_filter" style="width : 80px; text-align : center;" value="' . ($day == null ? 7 : $day ) . '" onchange="reload_transfers();"/> días';
		return $resp;
	}
?>