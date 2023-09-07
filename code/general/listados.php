<?php

	extract($_POST);
	extract($_GET);
	require("../../conect.php");
	
	//die($filt_suc);
	//conseguimos el dato de tabla
	$tabla=base64_decode($tabla);
	$no_tabla=base64_decode($no_tabla);
	
	if($tabla == '')
		Muestraerror($smarty, "", "1", "", "No aplica", "listados.php"); 
	if($tabla == 'ec_pedidos')
	{
		$query = 'SELECT 
				id_tipo_pago,
				nombre 
				FROM 
				ec_tipos_pago 
				WHERE 1';
		$result = mysql_query($query)	or die (mysql_error());	

		$vals = array();
		$textos = array();

		while($fila = mysql_fetch_row($result))
		{
			array_push($vals,$fila[0]);
			array_push($textos,$fila[1]);
		}
	    $smarty->assign("vals", $vals);
	    $smarty->assign('textos',$textos);


	}
		
		
	//buscamos los permisos
	
	$sql="SELECT id_menu FROM sys_menus WHERE tabla_relacionada = '$tabla' AND no_tabla='$no_tabla'";
	$res=mysql_query($sql);
	if(!$res)	  
	{
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "listados.php"); 
	}	
	$num=mysql_num_rows($res);
	
	if($num <= 0)	
		Muestraerror($smarty, "", "1", "", "No aplica", "listados.php"); 
	
	$row=mysql_fetch_row($res);	
	
	$sql="SELECT nuevo, modificar, eliminar, imprimir, generar FROM sys_permisos WHERE id_menu=".$row[0]." AND id_perfil=$perfil_usuario";//id_usuario=".$user_id
	
	$res=mysql_query($sql);
	if(!$res){
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "listados.php"); 
	}	
	$num=mysql_num_rows($res);
	
	if($num <= 0)	
		Muestraerror($smarty, "", "1", "", "No aplica", "listados.php"); 
	
	$row=mysql_fetch_row($res);
	
	$mostrar_nuevo=$row[0];
	$mostrar_mod=$row[1];
	$mostrar_eli=$row[2];
	$mostrar_imp=$row[3];
	$mostrar_gen=$row[4];
	
	$smarty->assign("mostrar_nuevo", $mostrar_nuevo);
	$smarty->assign("mostrar_mod", $mostrar_mod);
	$smarty->assign("mostrar_eli", $mostrar_eli);
	$smarty->assign("mostrar_imp", $mostrar_imp);
	$smarty->assign("mostrar_gen", $mostrar_gen);
		
	//Buscamos los datos del listado
	$sql="SELECT
	      id_listado,
		  titulo,
		  anchos,
		  alineacion,
		  campos,
		  ver,
		  modificar,
		  eliminar,
		  consulta,
		  '',
		  nuevo,
		  buscador/*implementacion de Oscar 14-02-2018*/
		  FROM sys_listados
		  WHERE tabla='$tabla'
		  AND no_tabla='$no_tabla'";	
		  
	$res=mysql_query($sql);
	if(!$res){
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "listados.php"); 
	}
	$num=mysql_num_rows($res);
	
	if($num <= 0)	
		Muestraerror($smarty, "", "1", "", "No aplica", "listados.php"); 
	
	$row=mysql_fetch_row($res);	
	
	//Tratamos los datos
	$row[2]=explode('|', $row[2]);
	$row[3]=explode('|', $row[3]);
	$row[4]=explode('|', $row[4]);
	
	//obtenemos los nombres
	$res=mysql_query($row[8]);
	if(!$res){
		Muestraerror($smarty, "", "2", mysql_error(), $row[8], "listados.php"); 
	}
	
	$num=mysql_num_fields($res);
	$aux=array();
	
	for($i=0;$i<$num;$i++){
		$meta=mysql_fetch_field($res, $i);
		array_push($aux, $meta->name);
	}	
	//print_r($aux);
	$row[9]=$aux;

	$smarty->assign("letMaquila", "Este cambio será irreversible en sistema. ¿Desea continuar?");
	$smarty->assign("letAutTrans", "\n<br>¿Desea autorizar esta transferencia?");

	$smarty->assign("datos", $row);
	$smarty->assign("tabla", base64_encode($tabla));
	$smarty->assign("no_tabla", base64_encode($no_tabla));
	$smarty->assign("user_sucursal",$user_sucursal);
	$smarty->assign("tipo_sistema",$user_tipo_sistema);
/*implementacion Oscar 11.06.2019 para mandar el id del movimiento para cargar la bitácora de cambios*/
	$smarty->assign("id",$id);
/*Fin de cambio Oscar 11.06.2019*/
/**/
	$smarty->assign("perfil_id",$perfil_usuario);
/**/

/*implementacion Oscar 01.11.2019 para recargar los listados*
	if($tabla='ec_sesion_caja' && $no_tabla=='0'){
		if(isset($filt_suc)){
		//combo de sucursales
			/*$combo_suc_1='<sleect>';
			$sql="SELECT id_sucursal,nombre FROM sys_sucursales WHERE IF('$user_sucursal'=-1,id_sucursal>0,id_sucursal=$user_sucursal)";
			$eje=mysql_query($sql)or die("Error al consultar el combo de sucursales!!!<br>".mysql_error());
			$filtros_adicionales="&fltros_adic=";*
			if($filt_suc!=-1){
				$filtros_adicionales.="AND sc.id_sucursal=".$filt_suc;
			}
		//combo de tipo
			if($f_tip==2){
				$filtros_adicionales.=" AND sc.total_monto_ventas!=sc.total_monto_validacion";
			}
			if($f_tip==3){
				$filtros_adicionales.=" AND sc.total_monto_ventas=sc.total_monto_validacion";
			}
			$smarty->assign("filtros_listado_cortes",$filtros_adicionales);
		}
	}
	//die($filtros_adicionales);
/*fin de cambio Oscar 01.11.2019*/
	//die("??");
	//die('user:'.$user_sucursal);
	$smarty->display('general/listados.tpl');
?>