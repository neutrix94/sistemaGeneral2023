<?php
	
	include("../../../conectMin.php");	
/*Implementacion Oscar 2023/09/19 Comparar la url del host contra la url de linea y si detecta que es linea que no debe de redireccionar a la pantalla de restauracion*/
	$server = $_SERVER['HTTP_HOST'];
	$sql = "SELECT
				url_api
			FROM versionador_configuracion";
	$stm = mysql_query( $sql ) or die( "Error al consultar url de api de versionador : " . mysql_error() );
	$row = mysql_fetch_row( $stm );
	$versioner_host = str_replace("https://", "", $row[0] );
	$versioner_host = str_replace("http://", "", $versioner_host );
	$versioner_host = explode( "/", $versioner_host );
	$versioner_host = $versioner_host[0];
	if( $versioner_host == $server ){
		die( "<script>alert( 'Es el mismo host y seras redireccionado al index!' ); location.href = '../../../index.php?';</script>" );
	}
/*fin de cambio Oscar 2023/09/19*/

	$sql="SELECT CONCAT(fecha,' ',hora) FROM sys_respaldos WHERE realizado=0 LIMIT 1";
	$eje=mysql_query($sql)or die("Error al consultar fecha del respaldo!!!\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
	$tiempo_respaldo=$r[0];
//consulta los datos del registros de respaldo
	$sql = "SELECT 
				fecha,
				hora,
				folio_unico
			FROM sys_respaldos
			WHERE realizado = 0
			ORDER BY id_respaldo DESC";
	$stm = mysql_query( $sql )or die( "Error al consultar datos de restauracion : " . mysql_error() );
	if( mysql_num_rows( $stm ) >= 1 ){
	//	die( "here" );
//implementacion Oscar 2023 para eliminar valores de apis
		$sql = "UPDATE api_config SET value = '' WHERE name = 'path' AND `key` = 'api'";
		$stm_1 = mysql_query( $sql ) or die( "Error al resetear el path del api : " . mysql_error() );
		$sql = "UPDATE versionador_configuracion SET url_api = '' WHERE 1";
		$stm_1 = mysql_query( $sql ) or die( "Error al resetear el path del versionador : " . mysql_error() );
	}
	$initial_data = mysql_fetch_assoc( $stm );
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Restauración BD</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
<!--importaciónd del calendario-->
	<link rel="stylesheet" type="text/css" href="../../../css/gridSW_l.css"/>
	<script type="text/javascript" src="../../../js/calendar.js"></script>
	<script type="text/javascript" src="../../../js/calendar-es.js"></script>
	<script type="text/javascript" src="../../../js/calendar-setup.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="../../../css/icons/css/animation.css">

	<link rel="stylesheet" type="text/css" href="../../../css/icons/css/fontello-codes.css">
	<link rel="stylesheet" type="text/css" href="../../../css/icons/css/fontello-embedded.css">
	<link rel="stylesheet" type="text/css" href="../../../css/icons/css/fontello-ie7-codes.css">
	<link rel="stylesheet" type="text/css" href="../../../css/icons/css/fontello-ie7.css">
	<!--link rel="stylesheet" type="text/css" href="../../../css/icons/css/.css"-->

	<script type="text/javascript" src="js/functions.js"></script>

	<style type="text/css">
		#principal{
			background-image: url('../../../img/img_casadelasluces/bg8.jpg');
			position: absolute;
			top:0;
			width: 100%;
			height:100%;
			left:0;
		}

		.emergent{
			position: fixed;
			width: 100%;
			height: 100%;
			left : 0;
			top : 0;
			background-color: rgba( 0, 0, 0, .3);
			z-index: 3;
			display: none;
		}
		.emergent_content{
			position: relative;
			top : 100px;
			width: 95%;
			left: 2.5%;
			background-color: white;
			box-shadow: 1px 1px 15px rgba( 0, 0, 0, .5 );
			padding: 10px;
			max-height: 80%;
			overflow-y : auto;
		}
		/*#emergente{position: absolute;width: 100%;height: 100%;top:0;background: rgba(0,0,0,.8);z-index: 5;display: none;}
		#cont_emergente{position: relative;border:1px solid white; width: 80%;left: 10%;height:40%;top:25%;border-radius: 15px;background: rgba(225,0,0,.5)}
*/
		input[type=checkbox]{
			-ms-transform:scale(1.5);/*IE*/ 
			-moz-transform:scale(1.5);/*FF*/
			-webkit-transform: scale(1.5);/*Safari and Chrome*/
			-o-transform: scale(1.5);/*Opera*/}
		/*.titulo{color:black;padding: 10px;font-size:30px;top:45px;position: relative;left:-20%;}
		.logo{position:absolute;top:0;left:0;z-index: 2;}
		.combo{padding: 10px;font-size:15px;width: 100%;}
		.txt{font-size: 20px;}
		td{padding: 3px;}
		.btn{background:transparent;border-radius:10px;}
		.btn:hover{background: rgba(0,0,0,.5);color: white;}
			*/
	</style>
</head>
<body>
	<div class="emergent" id="emergente">
		<div class="emergent_content" id="cont_emergente"></div>
	</div>
<!--emergente>
	<div id="emergente" style="display:bloc;">
		<p id="cont_emergente" align="center">
			<b style="color:white;font-size:40px;">Esta acción agrupará los movimientos, ventas y devoluciones de 3 años hacia atrás<br>Realmente desea continuar??</b>
			<table>
				<tr>
					<<td width="50%">
						<button>Continuar</button>
					</td>
					<td width="50%">
						<button>Cancelar</button>
					</td>
				</tr>
			</table>
		</p>
	</div-->
<!--contenido -->
	<div id="principal">
		
		<div class="row bg-warning">
			<!--div class="col-2">
				<button class="btn">
					<img src="../../../img/img_casadelasluces/logo.png" width="80px">
				</button>
			</div>
			<div class="col-10 text-center"-->
				<h2 class="text-center text-light icon-spin6" style="padding:10px;">Restauración de Base de Datos</h2>

			<!--/div-->
		</div>
		<!--p class="titulo" align="center"><b>Restauración y Configuración inicial del sistema</b></p background:rgba(225,225,0,.2);-->
		
		<div class="row" style="padding : 10px;"><!-- style="width:100%;height:100%;position:absolute;left:0;color:black;top:0;" border="0"-->
			<div class="col-sm-6 row">
				<div class="col-6 text-end"><br>
					<b class="txt">Accion : </b>
				</div>
				<div class="col-6" width="15%"><br>
					<select class="form-select" id="tipo_bd">
						<!--<option value="0">--Seleccionar--</option>
						<option value="1">Nueva BD</option>-->
						<option value="2">Restauración de BD</option>
					</select>
				</div>
				<div class="col-6 text-end">
					<b class="txt">Tipo de sistema:</b>
				</div>
				<div class="col-6">
					<select class="form-select" id="tipo_sys" onchange="cambia_combo(this);">
						<option value="0">--Seleccionar--</option>
						<option value="1">Local</option>
						<option value="2">Línea</option>
					</select>
				</div>
			</div>
			<div class="col-sm-6 row">
				<div class="col-6 text-end">
					<b class="txt">Sucursal:</b>
				</div>
				<div class="col-6" id="combo_sucs">
					<select class="form-select" id="id_suc" onchange="prepara_acciones(this);">
						<option value="0">--Seleccionar--</option>
					</select>
				</div>
				<div class="col-6 text-end">
					<b class="txt">Eliminar movimientos de almacen que no sean de la sucursal:</b>	
				</div>
				<div class="col-6 text-center">
					<input type="checkbox" id="elimina_mov" class="check" disabled>
				</div>
			</div>
			<div class="col-sm-6"></div>
			<div class="col-sm-6 row">
				<div class="col-6 text-end">
					<b class="txt">Eliminar Ventas que no sean de la sucursal:</b>	
				</div>
				<div class="col-6 text-center">
					<input type="checkbox" id="elimina_vtas" class="check" disabled>
				</div>
			</div>
			<div class="col-6 text-center" colspan="1">
				<hr><hr>
				<h3 class="text-primary">Fecha de generación del respaldo:</h3>		
				<input type="text" id="fecha_respaldo" class="form-control" 
					onfocus="" 
					placeholder="año-mes-dia horas:minutos:segundos"
					onclick="alert('Se debe de ser precios en la hora y fecha de generación del respaldo\nFormato : año-mes-dia horas:minutos:segundos');
					calendario(this);" value="<?php //echo $tiempo_respaldo;?>">
				<br>
				<span style="color: blue;">Formato : año-mes-dia horas:minutos:segundos, ejemplo : 2022-11-15 14:50:20</span>
			</div>
			<div class="col-6 text-center">		
				<hr><hr>
				<h3 class="text-primary">Folio unico de restauracion : </h3>
				<input type="text" id="user_unique_folio" class="form-control" 
					onfocus="" 
					placeholder=""
				>	
				<input type="hidden" value="<?php echo $initial_data['folio_unico'];?>" id="unique_folio">
				<input type="hidden" value="<?php echo "{$initial_data['fecha']} {$initial_data['hora']}";?>" id="datetime">
			</div>

			<div class="row text-center">
				<h5 style="color: red; font-size : 150%;">Importante : </h5>
				<ul>
					<li style="color: red; font-size : 100%;">Debes de ser MUY exacto al momento de poner la hora y fecha en la que se descargó la Base de Datos</li>
					<li style="color: red; font-size : 100%;">No debe de haber servidores sincronizando, de preferencia realizar este proceso en una hora en la que no haya operación</li>
				</ul>	
				
			</div>

			<div class="row text-center">
				<b class="txt" style="font-size:25px;">Ingresa usuario y contraseña para la restauración:</b>
				<div class="row text-center">
					<div class="col-2"></div>
					<div class="col-8">
						<input type="text" class="form-control" placeholder="Usuario" id="usuario"><br>
						<input type="password" class="form-control" placeholder="***Password***" id="contrasena"><br>
				
						<button class="btn btn-success form-control" onclick="verificar();">
							<i class="icon-spin3">Restaurar</i>
						</button>
						<br>
						<br>
						<button class="btn btn-warning form-control" onfocus="carga_archivo();">
							<i class="icon-tools">Mantenimiento</i>
						</button>
					</div>
				</div>
			</div>

		</div>
	</div>	
</body>
</html>