<?php

    include("../../conectMin.php");
    extract($_GET);
    
    
    $sql="SELECT
          r.id_alerta_registro,
          a.nombre,
          a.fecha,
          a.hora,
          a.tipo,
          a.id_autorizacion 
          FROM ec_alerta a
          JOIN ec_alerta_registro r ON a.id_alerta = r.id_alerta 
          LEFT JOIN ec_autorizacion e ON e.id_autorizacion = a.id_autorizacion AND NOT e.autorizado
          WHERE r.id_usuario=$user_id
          AND r.visto=0";
          
    $res=mysql_query($sql) or die(mysql_error());
    
    $num=mysql_num_rows($res);
    
    if($num > 0)
    {
        $row=mysql_fetch_row($res);
        
        echo "SI|$row[0]|$row[1]|$row[2]|$row[3]|$row[4]|$row[5]";
        
        die();
    }
    
    die("NO");          
    

?>