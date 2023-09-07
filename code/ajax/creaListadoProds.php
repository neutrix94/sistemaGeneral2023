<?php

    include("../../conectMin.php");
    extract($_GET);
    
    
    
    $sql="SELECT
          'NO',
          '".'$'."LLAVE',
          id_productos,
          nombre,
          (
            SELECT
            IF(SUM(d.cantidad_surtida*tm.afecta) IS NULL, 0, SUM(d.cantidad_surtida*tm.afecta))
            FROM ec_movimiento_detalle d
            JOIN ec_movimiento_almacen aa ON aa.id_movimiento_almacen = d.id_movimiento
            JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento = aa.id_tipo_movimiento
            WHERE aa.id_sucursal = $user_sucursal
            AND aa.id_almacen=$id_almacen
            AND d.id_producto=ec_productos.id_productos
          ),
          0
          FROM ec_productos
          WHERE id_productos > 0
          ORDER BY orden_lista";
          
          
    $res=mysql_query($sql) or die("$sql\n\n".mysql_error());
    
    $num=mysql_num_rows($res);
    
    echo "exito";
    
    for($i=0;$i<$num;$i++)
    {
        $row=mysql_fetch_row($res);
        echo "|";
        
        for($j=0;$j<sizeof($row);$j++)
        {
            if($j > 0)
                echo "~";
            echo utf8_encode($row[$j]);
        }
        
    }          


?>