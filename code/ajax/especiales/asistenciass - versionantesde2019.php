<?php
  include("../../../conectMin.php");
//fl:'actualiza_salidas',verifica_pass:password,password_encargado:pass_enc,salidas:por_guardar
  $flag=$_POST['fl'];
  if($flag=='actualiza_salidas'){
    $verificar_pss=$_POST['verifica_pass'];
    $pss=md5($_POST['password_encargado']);
    $arr_salidas=explode("|",$_POST['salidas']);
  //verificamos el password si es el caso
    if($verificar_pss==1){
      $sql="SELECT 
              u.id_usuario 
            FROM sys_users u
            LEFT JOIN sys_sucursales s ON s.id_encargado=u.id_usuario
            WHERE s.id_sucursal=$user_sucursal
            AND contrasena='$pss'";
      $eje=mysql_query($sql)or die("Error al verificar el password del encargado!!!\n".mysql_error());
      if(mysql_num_rows($eje)!=1){
        die("Contraseña de encargado incorrecta!!!");
      }
    }
    
    $id_usuario=0;
    
    for($i=0;$i<(sizeof($arr_salidas)-1);$i++){
      $tmp=explode("~", $arr_salidas[$i]);
  
  //extraemos el id del usuario
      if($i==0){
        $sql="SELECT id_empleado FROM ec_registro_nomina WHERE id_registro_nomina=$tmp[0]";
        $eje=mysql_query($sql)or die("Error al consultar el id de usuario!!!<br>".mysql_error()."<br>".$sql);
        $r=mysql_fetch_row($eje);
        $id_usuario=$r[0];
      }

    //validamos que no haya una entrada menor al registro de salida
      $sql="SELECT IF(hora_entrada<'$tmp[1]',0,1) FROM ec_registro_nomina WHERE id_empleado=$id_usuario AND fecha='$tmp[2]' AND id_registro_nomina>$tmp[0]";
      $eje=mysql_query($sql)or die("Error al validar hora de salida de registro de nomina!!!".mysql_error());
      $r1=mysql_fetch_row($eje);
      //die($sql);
      if($r1[0]==0 && $r1[0]!=''){
        die("No se puede poner una hora mayor a la ultima entrada!!! verifique sus horas y vuelva a intentar");
      }

    //validamos que no haya una entrada menor al registro de salida
      $sql="SELECT IF(hora_entrada>'$tmp[1]',0,1) FROM ec_registro_nomina WHERE id_registro_nomina=$tmp[0]";
      $eje=mysql_query($sql)or die("Error al validar hora de salida de registro de nomina contra la entrada!!!".mysql_error());
      $r1=mysql_fetch_row($eje);
      //die($sql);
      if($r1[0]==0){
        die("No se puede poner una hora de salida menor a la entrada!!! verifique sus horas y vuelva a intentar");
      }

      $sql="UPDATE ec_registro_nomina SET hora_salida='$tmp[1]' WHERE id_registro_nomina='$tmp[0]'";
    //  die($sql);
      $eje=mysql_query($sql)or die("Error al modificar las salidas!!!\n".mysql_error());
    }
    die('ok');
  }

?>

<button style="position:absolute;background:rgba(225,0,0,.6);padding:20px;font-size:40px;top:1%;right:1%;color:white;"
onclick="location.reload();"><!--document.getElementById('emer_asist').style.display='none'-->
  <b>X</b>
</button>
<?php
	//include("../../conect.php");
	
	//print_r($_GET);
	//extract($_GET);
//tipo=3&clave="+$("#clave_as").val()+"&tipo_asistencia="+tipo_de_reg+"&="+login
//inicializamos variables	
  $llave=$_GET['clave'];
  $login=$_GET['incluir_login'];
  $tipo_registro=$_GET['tipo_asistencia'];

	if(!isset($sucur)){
		$sucur=-1;
  }
  
//consultamos que el login coincida
  $sql="SELECT 
        id_usuario,
        CONCAT(nombre,' ',apellido_paterno,' ',apellido_materno),
        current_date,
        current_time
      FROM sys_users
      WHERE (codigo_barras_usuario='$llave'"; 
/*Fin de cambio Oscar 18.11.2018*/
//agregamos coincidencia por login si es el caso
  if($login==1){
    $sql.=" OR login='".$llave."'";
   }
   $sql.=") AND (id_sucursal='$user_sucursal' OR id_sucursal=-1)";
  
  $res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
  $num=mysql_num_rows($res);
//si no coincide regresamos error
    if($num <= 0){
      //echo "exito|NO|¡Código no válido!";
       die('<p align="center" style="position:absolute:top:35%;width:100%;color:white;font-size:50px;">'.
        '<b>Código inválido!!!</b></p>');
    }
//guardamos datos de consulta en variables
    $row=mysql_fetch_row($res);
    $id_empleado=$row[0]; 
    $empleado=$row[1];
    $fcha=$row[2];
    $hora_reg=$row[3];
  $id_de_registro=0;
  
  if($tipo_registro==1){//entrada
    $tipo='<b style="color:green;">Entrada</b>';
  //insertamos la entrada
    $sql="INSERT INTO ec_registro_nomina(fecha, hora_entrada, id_empleado, id_sucursal,id_equivalente,sincronizar)
                                      VALUES('$fcha', '$hora_reg', $id_empleado, $user_sucursal,0,1)";
    mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());                                          
    //echo "exito|SI|$empleado|Entrada|".$hora_reg;
  //id insertado
    $id_de_registro=mysql_insert_id();
  }elseif ($tipo_registro==2){//salida
    $tipo='<b style="color:red;">Salida</b>';
  //buscamos la ultima entrada pendiente de salida
    $sql="SELECT id_registro_nomina FROM ec_registro_nomina WHERE id_empleado=$id_empleado 
    AND (hora_salida='' OR hora_salida='00:00:00') AND fecha='$fcha'
    ORDER BY hora_entrada DESC LIMIT 1";
    $eje=mysql_query($sql)or die("Error al consultar la ultima entrada regitrada!!!".mysql_error());
    if(mysql_num_rows($eje)<=0){
      die('<p align="center" style="position:absolute:top:35%;width:100%;color:white;font-size:50px;">'.
        '<b>No hay registros de entrada el dia de hoy para el usuario<br>'.$empleado.'!!!</b></p>');
    }
    $r=mysql_fetch_row($eje);
  //actualizamos las salidas
    $sql="UPDATE ec_registro_nomina SET hora_salida='$hora_reg',sincronizar=1 WHERE id_registro_nomina=".$r[0];
    mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());                                          
    echo "exito|SI|$empleado|Salida|".$hora_reg.'|';
  }  
 //die('suc:'.$user_sucursal);
?>
<div>
    <p align="center" style="color:white;font-size:40px;"><b>Registro Exitoso!</b></p>
    <table align="center" width="60%" style="position:abolute;top:230px;left:20%;">
        <tr>
                <td align="center">
        <tr>
                <td align="center">
          <table width="600" id="ec_registro_nomina" cellpadding="0" cellspacing="0" Alto="255" conScroll="S" validaNuevo="false" AltoCelda="25"
          auxiliar="0" validaElimina="false" Datos="../ajax/especiales/asistenciass.php?tipo=1&sucur={$clave_acceso}"
          verFooter="S" guardaEn="False" listado="S" class="tabla_Grid_RC" paginador="S" datosxPag="30" pagMetodo='php'
          ordenaPHP="S" title="Listado de Registros">

            <tr style="border-bottom:1px solid #f60;">
              <td style="border-bottom:1px solid #f60; border-right:1px solid #f60;"><p style="font-size:19px; padding:12px; font-weight:bold;">Nombre:</p></td>
                            <td style="border-bottom:1px solid #f60;">
                              <p style="font-size:19px; padding:12px; font-weight:bold; color:#9EC76B;" id="nombre">
                                <?php echo $empleado;?>
                              </p>
                            </td>
            </tr>
            <tr style="border-bottom:1px solid #f60;">
              <td style="border-bottom:1px solid #f60;border-right:1px solid #f60;"><p style="font-size:19px; padding:12px; font-weight:bold;">
                Tipo:</p>
              </td>
              <td style="border-bottom:1px solid #f60;">
                <p style="font-size:19px;  padding:12px;font-weight:bold;" id="tipoM">
                  <?php echo $tipo;?>
                </p>
            </td>
            </tr>
            <tr>
              <td style="border-right:1px solid #f60;">
                <p style="font-size:19px; padding:12px; font-weight:bold;">Hora:</p>
              </td>
              <td>
                <p style="font-size:19px; padding:12px; font-weight:bold;" id="hora">
                  <?php echo $hora_reg;?>
                </p>
              </td>
            </tr>
          </table>
          
        </td> 
      </tr>
    </table>
    <button id="btn_ok" style="position:absolute;top:160px;padding:20px;right:10%;font-size:40px;" onclick="guarda_cambios_salidas();"><!--onclick="location.reload();"-->
      <b>Aceptar</b>
    </button>
  </div>

<?php
//enlistamos las salidas pendientes de los ultimos 7 dias
  //if($tipo_registro==1){
    $sql="SELECT
    /*0*/ r_n.id_registro_nomina,
    /*1* CONCAT(u.nombre,' ',u.apellido_materno,' ',u.apellido_materno) as Nombre,*/
    /*2*/ r_n.fecha,
    /*3*/ r_n.hora_entrada,
    /*4*/ r_n.hora_salida
          FROM ec_registro_nomina r_n
          LEFT JOIN sys_users u ON u.id_usuario=r_n.id_empleado
          WHERE u.id_usuario='$id_empleado'
          AND (u.id_sucursal='$user_sucursal' OR u.id_sucursal=-1)
          AND (r_n.hora_salida='' OR r_n.hora_salida='00:00:00')
          AND r_n.fecha>=DATE_ADD(CURDATE(), INTERVAL -7 DAY)
          AND r_n.id_registro_nomina!=$id_de_registro";
    $eje=mysql_query($sql)or die("Error al listar las salidas pendientes de los ultimos 7 días!!!");
    if(mysql_num_rows($eje)>=1){
      echo '<p align="center" style="color:red;font-size:40px;position:absolute;top:270px;width:100%;"><b>Salidas pendientes de registrar</b></p>';
      echo '<div style="position:absolute;top:370px;width:50%;left:25%;background:white;height:300px;overflow:scroll;">';
      echo '<table width="100%;">';
        echo '<tr>';
          echo '<th style="background:rgba(225,0,0,.6); color:white;padding:8px;">Fecha</th>';
          echo '<th style="background:rgba(225,0,0,.6); color:white;padding:8px;">Entrada</th>';
          echo '<th style="background:rgba(225,0,0,.6); color:white;padding:8px;">Salida</th><tr>';
      $cot_salidas=0;
      while ($r=mysql_fetch_row($eje)){
        $cont_salidas++;
        echo '<tr id="fila_sal_pend_'.$cont_salidas.'">';
          echo '<td id="col_sal_pend_1_'.$cont_salidas.'" style="display:none;">'.$r[0].'</td>';
          echo '<td id="col_sal_pend_2_'.$cont_salidas.'" align="center">'.$r[1].'</td>';
          echo '<td id="col_sal_pend_3_'.$cont_salidas.'" align="center">'.$r[2].'</td>';
          echo '<td id="col_sal_pend_4_'.$cont_salidas.'" align="center" onclick="edita_celda('.$cont_salidas.');">'.$r[3].'</td>';
        echo '<tr>';
      }
      echo '</table></div>';
    //buscamos en la consfiguracion de la sucursal si hay que ingresar el passwor para las salidas pendientes
      $sql="SELECT IF($user_sucursal=-1,0,pide_password_asistencia_login) FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
      $eje=mysql_query($sql)or die("Error al verificar si se pide password para modificar asistencias!!!!".mysql_error());
      $r=mysql_fetch_row($eje);
      echo '<table style="position:absolute;top:500px;right:5%;">';
      if($r[0]==1){
        echo '<tr><td align="center"><b style="color:white;">PASSWORD DE ENCARGADO:</b><br>';
          echo '<input type="text" id="password_assistencia_1">';
        echo '</td></tr>';
      }
      //echo '<tr><td align="center"><br><button style="padding:8px;" onclick="guarda_cambios_salidas();">Cambiar Salida(s)</button></td>';
      echo '</tr></table>'; 
    //total de salidas pendientes
      echo '<input type="hidden" value="'.$cont_salidas.'" id="total_salidas_pendientes">';
    } 
  //}
?>