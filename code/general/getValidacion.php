<?php

     header("Content-Type: text/plain;charset=utf-8");
    
    include("../../conectMin.php");
    
    
    extract($_GET);
    
    
    $sql="SELECT autorizado FROM ec_autorizacion WHERE id_autorizacion=$id";
    
    $res=mysql_query($sql);
    
    $row=mysql_fetch_row($res);
    
    if($row[0] == '0')
        echo "SI";
    
    echo "NO";

?>