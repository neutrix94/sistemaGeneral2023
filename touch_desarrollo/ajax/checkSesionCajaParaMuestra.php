<?php	
	header("Content-Type: text/plain;charset=utf-8");
	include("../../conectMin.php");

    try{

        $sql="SELECT DATE_FORMAT(now(),'%Y-%m-%d')";
		$eje=mysql_query($sql)or die("Error al consultar la fecha actual!!!");
		$fecha_actual=mysql_fetch_row($eje);
	    //comprobamos que haya una sesion abierta en el dia actual
		$sql="SELECT count(*) FROM ec_sesion_caja WHERE fecha='$fecha_actual[0]' AND id_sucursal=$user_sucursal AND hora_fin='00:00:00'";
		$eje=mysql_query($sql)or die("Error al verificar que haya sesión de caja abierta!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
		if($r[0]<1){
			die("sin_sesion_caja");
		}

    }catch (Exception $e){
		echo "ERR|" . $e->getMessage();
		mysql_query("ROLLBACK");//cancelamos transacción
		mysql_close();
		exit ();
	}