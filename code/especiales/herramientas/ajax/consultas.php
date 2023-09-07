<?php
/*********************************************Sacar orden_lista,nombre,estado del producto, e inventario de productos Filtros por familia**********************************/

$sql="SELECT 
p.id_productos,
p.orden_lista,
p.nombre,
IF(p.habilitado=1,'HABILITADO','DESHABILITADO'),
SUM(IF(ma.id_movimiento_almacen IS NULL,0,(md.cantidad*tm.afecta))) as Inventario
FROM ec_productos p
LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
WHERE p.id_categoria=26 AND p.id_subcategoria=10
GROUP BY p.id_productos";

$sql="SELECT /*solo almacen de matriz*/
p.id_productos,
p.orden_lista,
p.nombre,
IF(p.habilitado=1,'HABILITADO','DESHABILITADO'),
SUM(IF(ma.id_movimiento_almacen IS NULL OR ma.id_almacen!=1,0,(md.cantidad*tm.afecta))) as Inventario
FROM ec_productos p
LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
WHERE p.id_categoria=26 AND p.id_subcategoria=10
GROUP BY p.id_productos";

/**************************************************************/

/**************Sacar el inventario en matriz, precio compra filtros:mostrar todos, por familia,tipo,productos en específico, a una fechas en específico, **************/
$flag=$_POST['fl'];
if($flag==1){
	$sql="SELECT
		ax.id_productos,
    	ax.orden_lista,
    	ax.nombre,
    	ax.inventarioMatriz,
    	ax.precio_compra
	FROM(
	    SELECT
			p.id_productos,
	    	p.orden_lista,
	    	p.nombre,
	    	SUM(IF(ma.id_movimiento_almacen is NULL or ma.id_sucursal!=1,0,(tm.afecta*md.cantidad))) as inventarioMatriz,
	    	p.precio_compra
	    FROM ec_productos p
	    LEFT JOIN ec_movimiento_detalle md on p.id_productos=md.id_producto
	    LEFT JOIN ec_movimiento_almacen ma on md.id_movimiento=ma.id_movimiento_almacen
	    LEFT JOIN ec_tipos_movimiento tm on ma.id_tipo_movimiento=tm.id_tipo_movimiento
	    WHERE p.id_productos>1
	    GROUP BY p.id_productos
	)ax
	WHERE ax.inventarioMatriz!=0
	GROUP BY ax.id_productos
	ORDER BY ax.orden_lista,ax.nombre ASC";
}


/****************************************Sacar precio de casa con inv en matriz, filtro de sucursal, que con un check se seleccione si se desea ver el inv en matriz***********************************************/
SELECT
ax.id_productos,
ax.orden_lista,
ax.nombre,
ax.precio_compra,
ax.de_valor,
ax.a_valor,
ax.precio_venta,
ax.oferta,
SUM(IF(ma.id_movimiento_almacen IS NULL OR ma.id_sucursal!=1,0,(md.cantidad*tm.afecta))) as invMatriz,
ax.precio_venta_mayoreo	
FROM(
	SELECT
    	p.id_productos,
	    p.orden_lista,
	    p.nombre,
	    p.precio_compra,
	    pd.de_valor,
	    pd.a_valor,
	    pd.precio_venta,
	    IF(pd.es_oferta=1,'oferta','') as oferta,
	    p.precio_venta_mayoreo,
    	pd.id_precio_detalle
    FROM ec_precios_detalle pd
    LEFT JOIN ec_productos p ON pd.id_producto=p.id_productos
    WHERE pd.id_precio=7
    GROUP BY pd.id_precio_detalle
    ORDER BY p.orden_lista,pd.de_valor ASC
)ax
LEFT JOIN ec_movimiento_detalle md ON ax.id_productos=md.id_producto
LEFT JOIN ec_movimiento_almacen ma On md.id_movimiento=ma.id_movimiento_almacen
LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
GROUP BY ax.id_precio_detalle



/*********************************Comparación de Precios entre lista externa contra una lista seleccionada;solo productos que tenga lista de precios externa (seleción lista1, lista2) **************************/
SELECT
ax.id_productos,
ax.orden_lista,
ax.clave,
ax.nombre,
ax.externo,
pde.precio_venta
FROM(
SELECT
p.id_productos,
p.orden_lista,
p.clave,
p.nombre,
pd.precio_venta as externo
FROM ec_productos p
LEFT Join ec_precios_detalle pd ON p.id_productos=pd.id_producto 
WHERE pd.id_precio=8
)ax
LEFT JOIN ec_precios_detalle pde ON ax.id_productos=pde.id_producto
WHERE pde.id_precio=7
ORDER BY ax.orden_lista ASC



///////////******************************Inventario de picks por colores sin filtros*********************************\\\\\\\\\\\\\\
SELECT
	ax.id_productos,
    ax.orden_lista,
    cl.nombre,
    ax.invMatriz
FROM(
    SELECT
		p.id_productos,
    	p.orden_lista,
    	p.nombre,
    	p.id_color,
    	SUM(IF(ma.id_movimiento_almacen IS NULL OR ma.id_sucursal!=1,0,(md.cantidad*tm.afecta))) as invMatriz
    	FROM ec_productos p
    	LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
    	LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
    	LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
    	WHERE p.id_productos>0
    	AND p.id_categoria=26
    	AND p.id_subcategoria=10
    	GROUP BY p.id_color
    	ORDER BY p.orden_lista ASC
)ax
LEFT JOIN ec_colores cl ON ax.id_color=cl.id_colores
ORDER BY ax.id_color ASC



///////////////////////********************Productos habilitados en sucursales filtro por sucursal,familia,tipo,almacen (uno o los 2 almacenes)**********************\\\\\\\\\\\\\\\\\\\\
SELECT
p.id_productos,
p.orden_lista,
p.clave,
p.nombre,
'agregar el inventario en la sucursal almacén',
'agregar el inventario en la sucursal exhibición'
'dos columnas vacías al final'
FROM ec_productos p
LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto
WHERE sp.estado_suc=1
AND sp.id_sucursal=
order by p.orden_lista ASC;




/******************consulta de pagos filtrable por sucursal (por rango de fechas)************************/

(SELECT SUM(dp.monto)
                FROM ec_devolucion_pagos dp
                LEFT JOIN ec_devolucion d ON dp.id_devolucion=d.id_devolucion
            WHERE (dp.fecha BETWEEN '2018-11-16'AnD '2018-11-16') AND d.id_sucursal=2
)
UNION
(SELECT SUM(pp.monto)
				FROM ec_pedido_pagos pp
				LEFT JOIN ec_pedidos pe ON pe.id_pedido=pp.id_pedido
			/*WHERE (pe.fecha_alta>= '2018-10-01 00:00:01' and pe.fecha_alta<='2018-11-21 23:59:59')*/
 			 WHERE (CONCAT(pp.fecha,' ',pp.hora) BETWEEN '2018-11-16 00:00:01' AND '2018-11-16 23:59:59') AND pe.id_sucursal=2)


/***************Pagos externos******************/
(SELECT 'id_pedido','monto','fecha','hora','es_externo')
UNION
(SELECT pp.id_pedido,pp.monto,pp.fecha,pp.hora,pp.es_externo 
                FROM ec_pedido_pagos pp
                LEFT JOIN ec_pedidos pe ON pe.id_pedido=pp.id_pedido
            
            WHERE (pp.fecha BETWEEN '2018-11-26' AND '2018-12-02') AND pe.id_sucursal=4 AND PP.es_externo=1)


/****************Pagos de Devoluciones externas******************/
(SELECT 'id_devlucion','monto','fecha','hora','es_externo')
UNION
(SELECT 
pd.id_devolucion,
pd.monto,
pd.fecha,
pd.hora,
pd.es_externo
from ec_devolucion_pagos pd
LEFT JOIN ec_devolucion d on pd.id_devolucion=d.id_devolucion
WHERE d.id_sucursal=4 
and pd.es_externo=1
and pd.fecha BETWEEN '2018-10-01' and '2018-12-02');

?>