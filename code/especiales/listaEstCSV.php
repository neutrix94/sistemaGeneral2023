<?php
    //php_track_vars;
    
    extract($_GET);
    extract($_POST);
    
//CONECCION Y PERMISOS A LA BASE DE DATOS
    include("../../conect.php");
   
  /*implementacion Oscar 10.09.2018 para concatenar nombre de estacionaldiad en la descarga*/ 
    $sql="SELECT REPLACE(nombre,' ','_'),nombre FROM ec_estacionalidad WHERE id_estacionalidad=$id_estacionalidad";
    $res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error()); 
    $row=mysql_fetch_row($res);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        //header("Content-type: atachment-download");
        //header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        //header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        //header("Content-type: atachment/vnd.ms-excel");
        header("Content-type: application/csv");
        
        
        header("Content-Disposition: atachment; filename=\"".$row[0]."_".date('Y-m-d').".csv\";");
        header("Content-transfer-encoding: binary\n"); 

	//nombre de la estacionalidad
		echo $row[1]."\n";		

    
    
        echo "ID Detalle,ID Estacionalidad,ID Producto,Nombre,Maximo, Orden Lista (eliminar columna antes de subir archivo)\n";
        
        
        $sql="SELECT
              ep.id_estacionalidad_producto,
              ep.id_estacionalidad,
              ep.id_producto,
              p.nombre,/* deshabilitado por Oscar 2.09.2018
              ep.minimo,
              ep.medio,*/
              ep.maximo,
              p.orden_lista
              FROM ec_estacionalidad_producto ep
              JOIN ec_productos p ON ep.id_producto = p.id_productos
              WHERE ep.id_estacionalidad=$id_estacionalidad";
              
      $res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
       
      $num=mysql_num_rows($res);        
    
      for($i=0;$i<$num;$i++)
      {
          $row=mysql_fetch_row($res);
          
          for($j=0;$j<sizeof($row);$j++)
          {
              if($j > 0)
                echo ",";
              echo $row[$j];
          }
          echo "\n";
          
      }
      
      
     
    
    
?>