<?php

    header("Content-Type: text/plain;charset=utf-8");
    
    include("../../conectMin.php");
    
    
    extract($_GET);
    
    $sql="DELETE FROM ec_autorizacion WHERE id_autorizacion=$id";
    
    mysql_query($sql);

?>