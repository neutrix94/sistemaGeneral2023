<?php
    //php_track_vars;
    
    extract($_GET);
    extract($_POST);
    
	//CONECCION Y PERMISOS A LA BASE DE DATOS
	include("../../conect.php");
	
	
	$llave=base64_decode($a01773a8a11c5f7314901bdae5825a190);
	
	
	$sql="	SELECT
			t.folio AS Folio,
			CONCAT(t.fecha, ' ', t.hora) AS Fecha,
			s.nombre AS 'Sucursal origen',
			a.nombre AS 'Almacén origen',
			s2.nombre AS 'Sucursal destino',
			a2.nombre AS 'Almacén destino',
			CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno)
			FROM ec_transferencias t
			JOIN sys_sucursales s ON t.id_sucursal_origen = s.id_sucursal
			JOIN sys_sucursales s2 ON t.id_sucursal_destino = s2.id_sucursal
			JOIN ec_almacen a ON t.id_almacen_origen = a.id_almacen
			JOIN ec_almacen a2 ON t.id_almacen_destino = a2.id_almacen
			JOIN sys_users u ON t.id_usuario = u.id_usuario
			WHERE t.id_transferencia=$llave";
			
			
	$res=mysql_query($sql);
	if(!$res)	  
	{
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "resolucionTransferencias.php"); 
	}
	
	if(mysql_num_rows($res) <= 0)
	{
		Muestraerror($smarty, "", "2", "No se encontro el dato buscado", "No aplica", "resolucionTransferencias.php"); 
	}
	
	$row=mysql_fetch_row($res);
	
	$smarty->assign("let1", utf8_decode("Este cambio será irreversible y afectará la transferencia y el inventario.¿desea continuar?"));
	$smarty->assign("let2", utf8_decode("No aplica este tipo de resolución"));
	
			
	$smarty->assign("folio", $row[0]);
	$smarty->assign("fechahora", $row[1]);
	$smarty->assign("sucursal_or", $row[2]);
	$smarty->assign("alma_or", $row[3]);
	$smarty->assign("sucursal_des", $row[4]);
	$smarty->assign("alma_des", $row[5]);
	$smarty->assign("creadapor", $row[6]);
	$smarty->assign("llave", $llave);
	
	
	$smarty->display("especiales/resolucionTransferencias.tpl");
    
?>