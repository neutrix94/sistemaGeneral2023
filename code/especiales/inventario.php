<?php
	//php_track_vars;
	
	extract($_GET);
	extract($_POST);
	
//CONECCION Y PERMISOS A LA BASE DE DATOS
	include("../../conect.php");
	
	$sql="SELECT
	      id_almacen,
	      nombre
	      FROM ec_almacen
	      WHERE id_sucursal=$user_sucursal 
	      AND id_almacen <> -1 
	      ORDER BY nombre";
          
    $res=mysql_query($sql);
    if(!$res)     
    {
        mysql_query("ROLLBACK");
        Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php"); 
    }   
    $num=mysql_num_rows($res);
    $sucval=array(-1);
    $suctxt=array('-Todas-');
    for($i=0;$i<$num;$i++)
    {
        $row=mysql_fetch_row($res);
        array_push($sucval, $row[0]);
        array_push($suctxt, $row[1]);
    }
    $smarty->assign("sucval", $sucval);
    $smarty->assign("suctxt", $suctxt);
          
    $smarty->assign("multi", 1);      
	
	$smarty->display("especiales/inventario.tpl");
	
?>	