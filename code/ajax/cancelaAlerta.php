<?php

    include("../../conectMin.php");
    extract($_GET);
  
    $sql="UPDATE ec_alerta_registro SET visto=1 WHERE id_alerta_registro=$id";
    
    mysql_query($sql) or die(mysql_error());
    
    echo "exito";  
    
?>