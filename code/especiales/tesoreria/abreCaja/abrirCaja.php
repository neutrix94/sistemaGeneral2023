<?php
/*version casa 1.1*/
	include('../../../../conectMin.php');
	include('../../../../conexionMysqli.php');

	$sql="SELECT id_sucursal FROM sys_sucursales WHERE acceso=1
		UNION
		SELECT permite_abrir_caja_linea FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
	$eje=mysql_query($sql)or die("Error al consultar si se puede iniciar sesion de caja en linea!!!<br>".mysql_error());
	$r=mysql_fetch_row($eje);
	$r1=mysql_fetch_row($eje);
	if($r[0]==-1 && $r1[0]==0){
		//die($sql."<br>".$r[0]."|".$r1[0]);
		echo '<script type="text/Javascript">';
			echo 'alert("No se puede acceder a abrir caja desde el sistema en línea; hagalo localmente o contacte al administrador!!!");';
			echo 'location.href="../../../../"';
		echo '</script>';
		return;
	}

	$sql="SELECT IF(p.ver=1 OR p.modificar=1,1,0) 
			FROM sys_permisos p
			LEFT JOIN sys_users_perfiles perf ON perf.id_perfil=p.id_perfil
			LEFT JOIN sys_users u ON u.tipo_perfil=perf.id_perfil 
			WHERE p.id_menu=200
			AND u.id_usuario=$user_id";
	//die($sql);
	$eje=mysql_query($sql)or die("Error al consultar el permiso de cajero!!!<br>".mysql_error()."<br>".$sql);
	$es_cajero=mysql_fetch_row($eje);
	if($es_cajero[0]==0){
		echo '<script type="text/Javascript">';
			echo 'alert("No tiene acceso para esta pantalla;\nNecesita ser cajero!!!");';
			echo 'location.href="../../../../"';
		echo '</script>';
		return;
	}
//consultamos el login del cajero
	$sql="SELECT login FROM sys_users WHERE id_usuario=$user_id";
	$eje=mysql_query($sql)or die("Error al consultar el login del usuario logueado en este sistema!!!<br>".mysql_error());
	$r=mysql_fetch_row($eje);
	$login_cajero=$r[0];

	function getTerminalsOptions( $user_id, $link ){
		//die( 'here' );
		$resp = "";
		$sql = "SELECT 
				a.id_afiliacion AS afiliation_id,
				a.no_afiliacion AS afiliation_number
			FROM ec_afiliaciones a
			LEFT JOIN ec_afiliaciones_cajero ac 
			ON ac.id_afiliacion=a.id_afiliacion
			WHERE ac.id_cajero='{$user_id}' 
			AND ac.activo=1";
		$stm = $link->query( $sql ) or die( "Error al consultar las terminales : {$link->error}" );
		//die( $sql );
		while( $row = $stm->fetch_assoc() ){
			$resp .= "<option value=\"{$row['afiliation_id']}\">{$row['afiliation_number']}</option>";
		}
		return $resp;
	}
?>
<!DOCTYPE html>
<head>
	<title>Abrir Caja</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>

	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body onload="focus_password();">
	<div id="emergente">
		<div id="contenido_emergente">
		</div>
	</div>
	<div id="global">
		<img src="../../../../img/img_casadelasluces/Logo.png" 
			width="7%"
			onclick="link(1);" 
			title="Click para regresar al Panel de administración"
			class="logo_img"
		>
		<div class="row">
			<div class="col-1"></div>
			<div class="col-10">
				<form id="log_caja" class="bg-primary text-center">
					<br>
					<h3>Abrir caja</h3>
					<br>
					<i class="icon-money border border-light rounded-circle" style="font-size : 300%; padding : 2%;" ></i>
					<br><br>
				<div class="row">
					<div class="col-1"></div>
					<div class="col-10">
						<input 
							type="text" 
							id="user" 
							class="form-control" 
							placeholder="CAJERO.." 
							value="<?php echo $login_cajero;?>" 
							disabled>
							<br>
						<br>
						<input 
							type="password" 
							id="password" 
							class="form-control" 
							placeholder="Contraseña">
						<br>
						<p align="center">Monto de cambio en Caja</p>
						<input type="number" id="cambio_caja" class="form-control" placeholder="Monto de cambio en Caja">
						<br>
						<p align="center">Terminal</p>
						<div class="input-group">
							<select id="principal_terminal" 
								class="form-select">
								<option value="">-- Seleccionar --</option>
								<?php
									echo getTerminalsOptions( $user_id, $link );
								?>
							</select>
							<button
								type="button"
								class="btn btn-success"
								onclick="setTerminal();"
							>
								<i class="icon-plus"></i>
							</button>
						</div>
						<br>
						<div class="bg-light">
							<table class="table table-striped">
								<thead class="">
									<tr>
										<th>Terminales</th>
										<th>X</th>
									</tr>
								</thead>
								<tbody id="terminals_list"></tbody>
							</table>
						</div>
						<br><br>
						<button 
							type="button" 
							class="btn btn-success"
							id="bot_abre" 
							onclick="abrir_caja();">
							<i class="icon-key-inv"></i><br>
							Abrir Caja</button>
						<br><br>
					</div>
				</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>


<!-- implementacion Oscar 2023 para que salgan o no salgan los tickets de validaciones pendientes -->
<?php
	$sql = "SELECT 
				imprimir_validaciones_pendientes AS print_pending_validations
			FROM ec_configuracion_sucursal
			WHERE id_sucursal = {$sucursal_id}";
	$stm = mysql_query( $sql ) or die( "Error al consultar configuracion de ventas siin vlidar : {$link->error}" );
	$row = mysql_fetch_assoc( $stm );
	if( $row['print_pending_validations'] == 1 ){
?>
	<script type="text/javascript">

		pending_sales_validation();

	</script>
<?php 
	}
?>
<!-- fin  de cambio Oscar 2023 -->