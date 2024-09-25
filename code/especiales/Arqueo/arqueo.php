<?php
/*version 2.0 2024-06-21*/
	include('../../../conectMin.php');
	include('../../../conexionMysqli.php');
	include( 'ajax/Arqueo.php' );
	$Arqueo = new Arqueo( $link );

	$llave='';
	if(isset($_GET['aWRfY29ydGU'])){
		$llave=base64_decode($_GET['aWRfY29ydGU']);
		$clase_1='encabezado_ant';
		$clase_2='footer_ant';
	}else{
		$llave='0';
		$clase_1='encabezado';
		$clase_2='footer';
	}

//consultamos login del cajero
	$login_cajero = $Arqueo->getLogin( $user_id );
//Consulta los datos del corte
	$r = $Arqueo->getSessionData( $user_id, $llave );
	$id_sesion_caja=$r[0];
	$info_folio=' disabled value="'.$r[1].'"';
	$fecha_sesion=$r[2];
	$hora_inicio_sesion=$r[3];
	$hora_cierre_sesion=$r[4];
	echo '<input type="hidden" id="id_sesion" value="'.$id_sesion_caja.'">';
	echo '<input type="hidden" id="fecha_del_corte" value="'.$fecha_sesion.'">';
	echo '<input type="hidden" id="hora_de_inicio" value="'.$r[3].'">';
	echo '<input type="hidden" id="hora_de_cierre" value="'.$r[4].'">';
	$info_completa_sesion='Fecha: '.$fecha_sesion.'<br> Hora de inicio: '.$hora_inicio_sesion;
	$pide_cerrar=explode("___", $r[5]);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Arqueo de caja</title>

	<link rel="stylesheet" type="text/css" href="../../../css/gridSW_l.css"/>
	<script type="text/javascript" src="../../../js/calendar.js"></script>
	<script type="text/javascript" src="../../../js/calendar-es.js"></script>
	<script type="text/javascript" src="../../../js/calendar-setup.js"></script>
	<script type="text/JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/JavaScript" src="../../../js/passteriscoByNeutrix.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>

	<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">

</head>
<body background="../../../img/img_casadelasluces/bg8.jpg" <?php if($pide_cerrar[0]==1){echo 'onload="pedir_cerrar_corte();"';}?>>
	<!--Implementación Oscar 17.06.2019 para meter pantalla emergente-->
	<div id="emergente">
		<div id="contenido_emergente">	
		</div>
	</div>
	<!--Fin de cambio Oscar 17.06.2019-->
	<div class="row">
		<div class="<?php echo $clase_1;?> col-4">
			<?php echo $info_completa_sesion;?>
		</div>
	<?php
		$sql="SELECT multicajero FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
		$eje_suc=mysql_query($sql)or die("Error al verificar si la sucursal es multicajero!!!");
		$r=mysql_fetch_row($eje_suc);
		$multicajero=$r[0];
	//	die($multicajero);
		if($multicajer0==1){
			include('encabezadoUnicajero.php');
		}else{
			include('encabezadoUnicajero.php');
		}
	?>
		<div id="reporte">

		</div>
		<div class="<?php echo $clase_2;?> text-center" style="padding : 5px;">
			<button type="button" class="btn btn-light" onclick="salir();">
				<i class="icon-home-1">Regresar al panel</i>
			</button>	
		</div>
	</div>
</body>
</html>
<!-- implementacion Oscar 2023 para que salgan o no salgan los tickets de validaciones pendientes -->
<?php
	$row = $Arqueo->getStoreConfig( $user_sucursal );
	if( $row['print_pending_validations'] == 1 ){
?>
	<script type="text/javascript">
		pending_sales_validation();
	</script>
<?php 
	}
?>
<!-- fin  de cambio Oscar 2023 -->
