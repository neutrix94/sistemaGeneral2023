<?php
	function build_customer_group( $group, $precios, $link ){
		$resp = '<div class="col-sm-12 border border-info" style="padding : 5.5px; margin : 9px;">'
					. '<select class="form-contro" style="padding : 6.5px; width : 100%;" id="list_' . str_replace(' ', '_', $group) . '"'
					. ' onchange="change_btn_status();">';
		foreach ($precios as $key => $precio) {
			$resp .= '<option value="' . $precio[0] . '"'
			. ($precio[2] == $group ? ' selected' : null )
			. '>' . $precio[1] . '</option>';
		}
			$resp .= '</select>';
		$resp .= '</div>';
		return $resp;
	}

	include('../../../conectMin.php');
	include('../../../conexionMysqli.php');
/*Valida que tenga el permiso para acceder a la pantalla*/
	$sql = "SELECT 
				IF( ver = 1 OR modificar = 1 OR eliminar = 1 OR nuevo = 1 OR imprimir = 1 OR generar =1, 1,0)
			FROM sys_permisos 
			WHERE id_perfil = '{$perfil_usuario}'";
	$eje = $link->query( $sql );
	$row = $eje->fetch_row();
	if ( $row[0] == 0 ){
		header('Location: ../../../index.php');
	}

	$grupos_clientes_magento = array('Not Logged In', 'Mostrador','Mayoreo 1', 'Mayoreo 2' );
	$sql = "SELECT 
				id_precio, 
				nombre, 
				grupo_cliente_magento
			FROM ec_precios WHERE id_precio > 0";
	$eje = $link->query( $sql ) or die( "Error al consultar las listas de precios : {$link->error}");
	$precios = array();
	while( $r = $eje->fetch_row() ){
		$precios[] = $r;
	}

?>
<!DOCTYPE html>
<head>
	<title>Administración de precios en Magento</title>
	<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
	<script type="text/javascript" src="../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
	<div class="row">
		<div class="col-sm-2 orange">
			<img src="../../../img/img_casadelasluces/Logo.png" width="35%" onclick="return_panel();">
		</div>
		<div class="col-sm-10 orange" style="text-align : right;">
			<img src="../../../img/img_casadelasluces/magento_logo.png" width="10%" onclick="return_panel();">
		</div>

	</div>
	<div class="row" style="margin-top : 30px; ">
		<div class="col-sm-2">
		</div>
		<div class="col-sm-9">
			<div class="row">

				<div class="col-sm-5" style="text-aling : right;">
					<div class="row">
						<h6 class="subtitulo">Tipos de listas de Precios Magento</h6>
						<div class="col-sm-11 border border-info" style="padding : 10px; margin : 10px;">
							Not Logged In
						</div>
						<div class="col-sm-11 border border-info" style="padding : 10px; margin : 10px;">
							Mostrador
						</div>
						<div class="col-sm-11 border border-info" style="padding : 10px; margin : 10px;">
							Mayoreo 1
						</div>
						<div class="col-sm-11 border border-info" style="padding : 10px; margin : 10px;">
							Mayoreo 2
						</div>
					</div>
				</div>
				<div class="col-sm-5">
					<div class="row">
						<h6 class="subtitulo">Listas de Precio Casa de las Luces</h6>
						<?php
							foreach ($grupos_clientes_magento as $key => $group) {
								echo build_customer_group( $group, $precios, $link );	
							}
						?>
					</div>

					<div class="row">
						<div class="col-sm-14" style="margin-top : 10px;">
							<button class="btn btn-secondary form-control"
								onclick=""
								id="btn_save"
							>
								Cambiar precios
							</button>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
	<div class="orange" style="width:100%; height : 5%; bottom : 0;position: absolute;">
		
	</div>

<!-- Modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop"
	 style="display : none;" id="active_modal"
></button>

<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">
        	<b class="text-success">Instrucciones : </b>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
			Esta pantalla fue diseñada para actualizar las diferentes listas de precios en Magento;
			lo que se hace es asignar una lista a cada tipo de precio. <br/>
			<b>1 -</b> Cada lista asignada deberá de ser diferente (esta nueva lista será identificada al poner
			el tipo de lista en la tabla ec_precios, campo grupo_cliente_magento)<br/>
			<b>2 -</b> Al terminar de configurar las listas dar click sobre el botón cambiar precios; esto generará los registros necesarios 
			para sincronizar las listas de precios en la tienda en linea de Magento. (inserta los registros necesarios en la tabla
			ec_sync_magento para que el cron que se ejecuta cada minuto ubicado el la ruta '/gestionCL/jobs/actualizaProductos.php'
			sincronice todos los registros que tengan habilitado el campo habilitado de la tabla ec_productos_tienda_linea)<br/>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success ok" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>
<!-- Fin de Modal -->
</body>
</html>
<style type="text/css">
	.orange{
		background-color: #6C9831;
	}
	.subtitulo{
		font-size: 22px;
	}
</style>
<script type="text/javascript">
	
	$( '#active_modal' ).click();
	
	function save_prices (  ){
		$.ajax({
			type : 'post',
			url : 'ajax/changePrices.php',
			cache : false,
			data : { 
					no_log : $( '#list_Not_Logged_In' ).val(),
					counter : $( '#list_Mostrador' ).val(),
					wholesale_1 : $( '#list_Mayoreo_1' ).val(),
					wholesale_2 : $( '#list_Mayoreo_2' ).val()
				},
			success : function ( dat ){
				$( '.modal-body' ).html( dat );
				$( '#active_modal' ).click();
				$( '.btn.btn-success.ok' ).attr('onclick', 'location.reload();');
			}
		});
	}
	function change_btn_status(){
		$( '#btn_save' ).removeClass('btn-secondary');
		$( '#btn_save' ).addClass('btn-danger');
		$( '#btn_save' ).attr( 'onclick', 'save_prices()');
	}

	function return_panel (){
		if ( !confirm("Realmente desea salir de esta pantalla? ") ){
			return false;
		}
		location.href = '../../../index.php?';
	}
	
</script>