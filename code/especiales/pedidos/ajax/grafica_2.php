<?php
    include('../../../../conectMin.php');
    $fecha_del=$_POST['fcha_del'];
    $fecha_al=$_POST['fcha_al'];
    $id_producto=$_POST['id_prodcto'];
    $periodo=' Periodo del "'.$fecha_del.'" al "'.$fecha_al.'"';

//sacamos el nombre del producto
    $sql="SELECT CONCAT('Ventas e Inventario <br>(<b>',orden_lista,'</b>) ',nombre) FROM ec_productos WHERE id_productos=$id_producto";
    $eje=mysql_query($sql)or die("Error al consultar datos del producto!!!\n".mysql_error()."\n".$sql);
    $r=mysql_fetch_row($eje);
    $titulo_grafica=$r[0];

    $sql="SELECT
            ax1.fechas,
            ax1.Ventas,
            ax1.Inventario,
            max(ax1.Inventario),
            ax1.InventarioAjustes
        FROM(
        SELECT
            ax.fechas,
            IF(ax.ventas IS NULL,0,ax.ventas)as Ventas,
            SUM(IF(ma.id_movimiento_almacen IS NULL OR alm.es_externo=1,0,IF(ma.fecha<=ax.fechas,md.cantidad*tm.afecta,0 ) ) ) as Inventario,
            SUM(IF(ma.id_movimiento_almacen IS NULL OR alm.es_externo=1 OR (ma.id_tipo_movimiento!=8 AND ma.id_tipo_movimiento!=9),
                0,IF(ma.fecha<=ax.fechas,md.cantidad*tm.afecta,0 ) ) ) as InventarioAjustes            
        FROM(
            SELECT 
            '$id_producto' as id_prd,
            SUM(IF(p.id_pedido IS NULL OR pd.es_externo=1,0,pd.cantidad))as Ventas,
            DATE_FORMAT(p.fecha_alta, '%Y-%m-%d') as fechas
        FROM ec_pedidos p
        LEFT JOIN ec_pedidos_detalle pd ON p.id_pedido=pd.id_pedido
        WHERE p.fecha_alta BETWEEN '$fecha_del 00:00:01' AND '$fecha_al 23:59:59'
        AND pd.id_producto='$id_producto'
        GROUP BY DATE_FORMAT(p.fecha_alta, '%Y-%m-%d')
        )ax
        LEFT JOIN ec_movimiento_detalle md ON md.id_producto=ax.id_prd
        LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
        LEFT JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento=ma.id_tipo_movimiento
        LEFT JOIN ec_almacen alm ON alm.id_almacen=ma.id_almacen
        GROUP BY ax.fechas
        ORDER BY ax.fechas ASC
        )ax1
        GROUP BY ax1.fechas
        ORDER BY ax1.fechas ASC";
//die($sql);
    $eje=mysql_query($sql)or die("Error al consultar datos para graficar!!!<br>".mysql_error()."<br>".$sql);
//guardamos los datos
    $numero_reg=mysql_num_rows($eje);
    $fechas="";
    $ventas="";
    $inventario="";
    $maximo_inventario="";
    $ajustes="";
    for($i=0;$i<=$numero_reg;$i++){
        //if($i==0){
            $maximo_inventario=$r[3];
        //}
        $r=mysql_fetch_row($eje);
        $fechas.="'".$r[0]."'";-
        $ventas.=$r[1];
        $inventario.=$r[2];  
        $ajustes.=$r[4];      
        if($i<$numero_reg-1){
            $fechas.=",";
            $ventas.=",";
            $inventario.=",";  
            $ajustes.=",";
        }
    }
    $fechas=str_replace("'''", "'", $fechas);
   // $ventas=str_replace("'''", "'", $ventas);
 //   die($fechas);
//die('max_inv:'.$maximo_inventario);
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Highcharts Example</title>

		<style type="text/css">

		</style>
	</head>
	<body>
<!--<script src="../../../js/jquery-1.10.2.min.js"></script>
<script src="../../../js/Highcharts-7.0.3/code/highcharts.js"></script>-->
<!--<script src="../../../js/Highcharts-7.0.3/code/modules/series-label.js"></script>
<script src="../../../js/Highcharts-7.0.3/code/modules/exporting.js"></script>-->
<button style="position:absolute;top:10px;right:10px;font-size:20px;z-index:300;background:red;color:white;"
onclick="document.getElementById('simula_tooltip_grafica').innerHTML;document.getElementById('simula_tooltip_grafica').style.display='none';">
    <b>X</b>
</button>
<div id="linea" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

<script type="text/javascript">

$('#linea').highcharts({
    chart: {
        type: 'line',  // tipo de gráfica
        borderWidth: 0 // ancho del borde de la gráfica
    },
    title: {
        text: '<?php echo $titulo_grafica;?>', // título
        x: -20 
    },
    subtitle: {
        text: '<?php echo $periodo;?>', // subtítulo
        x: -20
    },
    xAxis: {
        categories: [<?php echo $fechas;?>] // categorías
    },
    yAxis: {
        title: {
            text: 'Piezas' // nombre del eje de Y
        },
        plotLines: [{
            color: '#808080'
        }]
    },
    tooltip: {
        valueSuffix: ' Piezas' // el sufijo de la información presente en el "tooltip"
    },
    legend: { // configuración de la leyenda
        layout: 'horizontal',
        align: 'center',
        verticalAlign: 'bottom',
        borderWidth: 1
    },
    series: [{ // configuración de las series
        name: 'Inventario',
        data: [<?php echo $inventario;?>]
    }, {
        name: 'Ventas',
        data: [<?php echo $ventas;?>]
    }, {
        name: 'Ajustes',
        data: [<?php echo $ajustes;?>]
    }
    ]
});
		</script>
	</body>
</html>
