<?php

    include("../../conectMin.php");
    
    header("Content-Type: text/plain;charset=utf-8");
    mysql_set_charset("utf8");
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    
    extract($_GET);
    
    $sql="SELECT
    	  e.id_autorizacion  
          FROM ec_autorizacion e  
          WHERE e.id_autorizacion=$id_aut ";
          
    $res=mysql_query($sql) or die(mysql_error());
    
    if(mysql_num_rows($res) > 0)
    {
        die("SI");
    }
    
    die("NO");

?>