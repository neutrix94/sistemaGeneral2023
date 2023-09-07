<?php
	//include("../../conect.php");
	include("../../../conectMin.php");
	
	//print_r($_GET);
	extract($_GET);
	
	if(!isset($sucur)){
		$sucur=-1;
  }
  
 //die('suc:'.$user_sucursal);
    
 /* 
Modificación de Oscar 26.02.2018
*/
//consultamos que el login coincida
    $sql="SELECT 
        id_usuario,
        CONCAT(nombre,' ',apellido_paterno,' ',apellido_materno)
        FROM sys_users
        WHERE login='$clave' AND (id_sucursal='$user_sucursal' OR id_sucursal=-1)";
/*fin de modificacion 26.02.2018*/

   $res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
    $num=mysql_num_rows($res);
  //si no coincide regresamos error
    if($num <= 0){
        die("exito|NO|¡Código no válido!");
    }
  //guardamos datos de consulta en variables
    $row=mysql_fetch_row($res);
    $id_empleado=$row[0]; 
    $empleado=$row[1];

/*Implementación de Oscar 27.02.2018*/
  //verificamos que no tenga un logueo atrasado

/*implementación Oscar 18.11.2018 para que las asistencias salgan con la fecha correcta en línea*/
  //sacamos la fecha desde el mysql
    $sql_fecha=mysql_query("SELECT current_date,current_time")or die("Error al consultar la fecha desde mysql!!!\n\n".mysql_error());
    $fecha_array=mysql_fetch_row($sql_fecha);
    $fcha=$fecha_array[0];
    $hora_reg=$fecha_array[1];
/*Fin de cambio Oscar 18.11.2018*/

    $sql="SELECT
    /*0*/ r_n.id_registro_nomina,
    /*1*/ CONCAT(u.nombre,' ',u.apellido_materno,' ',u.apellido_materno) as Nombre,
    /*2*/ r_n.fecha,
    /*3*/ r_n.hora_entrada,
    /*4*/ r_n.hora_salida
          FROM ec_registro_nomina r_n
          LEFT JOIN sys_users u ON u.id_usuario=r_n.id_empleado
          WHERE u.login='$clave'
          AND (u.id_sucursal='$user_sucursal' OR u.id_sucursal=-1)
          AND (r_n.hora_salida='' OR r_n.hora_salida='00:00:00')
          AND r_n.fecha<'$fcha'";
        //  die($sql);
    $eje=mysql_query($sql)or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
    
    if(mysql_num_rows($eje)>=1){
    //recolectamos datos
      echo "exito|Salida Pendiente|";
      $r=mysql_fetch_row($eje);
      die($r[0].'~'.$r[1].'~'.$r[2].'~'.$r[3].'~'.$r[4]);
    }
/**/        
    
//Buscamos si tiene registro del dia de hoy    
    $sql="SELECT
          id_registro_nomina,
          hora_salida
          FROM ec_registro_nomina
          WHERE id_empleado='$id_empleado'
          AND fecha=DATE_FORMAT(NOW(), '%Y-%m-%d')
        /*implementación de Oscar 26.02.2018*/
          ORDER BY id_registro_nomina DESC";
        //fin de impl  
          
    $res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
    $num=mysql_num_rows($res);          
  //si no tiene registros insertamos nuevo registro
    if($num == 0)
    {
        $sql="INSERT INTO ec_registro_nomina(fecha, hora_entrada, id_empleado, id_sucursal,id_equivalente,sincronizar)
                                      VALUES('$fcha', '$hora_reg', $id_empleado, $user_sucursal,0,1)";
        mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());                                          
        
        die("exito|SI|$empleado|Entrada|".$hora_reg);
    }
    else
    {
        //Buscamos si tiene hora de salida
        $row=mysql_fetch_row($res);
        
        if($row[1] == '' || $row[1] == '00:00:00')
        {
            $sql="UPDATE ec_registro_nomina SET hora_salida='$hora_reg',sincronizar=1 WHERE id_registro_nomina=".$row[0];
            
            mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());                                          
        
            die("exito|SI|$empleado|Salida|".$hora_reg);
        } 
        else{
        /*implementación de Oscar 26.02.2018*/
            //die("exito|NO|Ya ha realizado registro de entrada y salida por hoy");
        
            $sql="INSERT INTO ec_registro_nomina(fecha, hora_entrada, id_empleado, id_sucursal)
                                      VALUES('$fcha', '$hora_reg', $id_empleado, $user_sucursal)";
            mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());                                          
        
            die("exito|SI|$empleado|Entrada|".$hora_reg);
          }
    }
?>
