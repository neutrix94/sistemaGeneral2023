<?php
    include('../../../../conectMin.php');
    $fecha_del=$_POST['fcha_del'];
    $fecha_al=$_POST['fcha_al'];
    $id_producto=$_POST['id_prodcto'];

    $sql="SELECT
            ax1.fechas,
            ax1.Ventas,
            ax1.Inventario,
            max(ax1.Inventario)
        FROM(
        SELECT
            ax.fechas,
            IF(ax.ventas IS NULL,0,ax.ventas)as Ventas,
            SUM(IF(ma.id_movimiento_almacen IS NULL OR alm.es_externo=1,0,IF(ma.fecha<=ax.fechas,md.cantidad*tm.afecta,0 ) ) ) as Inventario
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
    for($i=0;$i<=$numero_reg;$i++){
        //if($i==0){
            $maximo_inventario=$r[3];
        //}
        $r=mysql_fetch_row($eje);
        $fechas.=$r[0];
        $ventas.=$r[1];
        $inventario.=$r[2];        
        if($i<$numero_reg-1){
            $fechas.=",";
            $ventas.=",";
            $inventario.=",";
        }
    }
//die('max_inv:'.$maximo_inventario);
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Gráfica</title>

		<style type="text/css">

		</style>
	</head>
	<body>
<script src="../../../js/jquery-1.10.2.min.js"></script>
<script src="../../../js/Highcharts-7.0.3/code/highcharts.js"></script>
<script src="../../../js/Highcharts-7.0.3/code/modules/data.js"></script>
<script src="../../../js/Highcharts-7.0.3/code/modules/series-label.js"></script>
<!--<script src="../../../../js/Highcharts-7.0.3/code/modules/exporting.js"></script>
<script src="../../../../js/Highcharts-7.0.3/code/modules/export-data.js"></script>-->

<!-- Additional files for the Highslide popup effect 
<script src="https://www.highcharts.com/media/com_demo/js/highslide-full.min.js"></script>
<script src="https://www.highcharts.com/media/com_demo/js/highslide.config.js" charset="utf-8"></script>
<!--<link rel="stylesheet" type="text/css" href="https://www.highcharts.com/media/com_demo/css/highslide.css" />-->
<button style="position:absolute;top:10px;right:10px;font-size:20px;z-index:300;background:red;color:white;"
onclick="document.getElementById('simula_tooltip_grafica').innerHTML;document.getElementById('simula_tooltip_grafica').style.display='none';">
    <b>X</b>
</button>
<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

<script type="text/javascript">

Highcharts.chart('container', {

    chart: {
        scrollablePlotArea: {
            minWidth: 700
        }
    },

    data: {
        //csvURL: 'https://cdn.jsdelivr.net/gh/highcharts/highcharts@v7.0.0/samples/data/analytics.csv',
        //beforeParse: function (csv) {
          //  return csv.replace(/\n\n/g, '\n');
        //}
    },

    title:{text: 'Ventas e Inventario'},

    subtitle: {
        text: 'Periodo:'
    },

    xAxis: {
       /* type:'datetime',*/
        categories: [<?php echo $fechas;?>]/*,
        tickWidth: 0,
        gridLineWidth: 1,
        labels: {
            align: 'left',
            x: 3,
            y: -3
        }*/
    },

    yAxis: [{ // left y axis
        tickInterval:<?php echo $maximo_inventario;?>,
        title: {
            text: null
        },
        labels: {
            align: 'left',
            x: 3,
            y: 16,
            format: '{value:.,0f}'
        },
        showFirstLabel: false
    }, { // right y axis
        linkedTo: 0,
        gridLineWidth: 0,
        opposite: true,
        title: {
            text: null
        },
        labels: {
            align: 'right',
            x: -3,
            y: 16,
            format: '{value:.,0f}'
        },
        showFirstLabel: false
    }],

    legend: {
        align: 'left',
        verticalAlign: 'top',
        borderWidth: 0
    },

    tooltip: {
        shared: true,
        crosshairs: true
    },

    plotOptions: {
        series: {
            cursor: 'pointer',
            point: {
                events: {
                    click: function (e) {
                        hs.htmlExpand(null, {
                            pageOrigin: {
                                x: e.pageX || e.clientX,
                                y: e.pageY || e.clientY
                            },
                            headingText: this.series.name,
                            maincontentText: Highcharts.dateFormat('%A, %b %e, %Y', this.x) + ':<br/> ' +
                                this.y + ' inventario',
                            width: 200
                        });
                    }
                }
            },
            marker: {
                lineWidth: 1
            }
        }
    },

    series: [{
        name: 'Ventas',
        data:[<?php echo $ventas;?>],
        lineWidth: 4,
        marker: {
            radius: 4
        }
    }, {
        name: 'Inventario',
        data:[<?php echo $inventario;?>]
    }]
});

		</script>
	</body>
</html>
