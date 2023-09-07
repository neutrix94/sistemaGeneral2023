<?php
	include('../../conectMin.php');//incluimos el archivo de conexión
//recibimos la variable de sucursal
	$id_sucursal=$_GET['id_suc'];
//extraemos el detalle de la lista de precios
mysql_query("BEGIN");
$sql="SELECT 
		id_precio_detalle,
		id_precio,
		de_valor,
		a_valor,
		precio_venta,
		precio_etiqueta,
		id_producto,
		es_oferta,
		alta,
		ultima_actualizacion
	FROM ec_precios_detalle WHERE id_precio IN(SELECT id_precio FROM sys_sucursales WHERE id_sucursal=$id_sucursal)";
$eje=mysql_query($sql)or die(mysql_error()." 1 :\n\n".$sql);
while($r=mysql_fetch_row($eje)){
	$sql="INSERT INTO ec_sincronizacion_registros SELECT null,$user_sucursal,$id_sucursal,'ec_precios_detalle',$r[0],1,6,
        CONCAT(\"INSERT INTO ec_precios_detalle SET 
                id_precio_detalle='$r[0]',
                id_precio='$r[1]',
                de_valor='$r[2]',          
                a_valor='$r[3]',       
                precio_venta='$r[4]',      
                precio_etiqueta='$r[5]',   
                id_producto='$r[6]',   
                es_oferta='$r[7]', 
                alta='$r[8]',      
                ultima_actualizacion='$r[9]',
                sincronizar=0
                ___UPDATE ec_precios_detalle SET sincronizar=0 WHERE id_precio_detalle='$r[0]'\"
        ),
        1,0,CONCAT('Se agregó un precio para el producto ',(SELECT nombre FROM ec_productos WHERE id_productos=$r[6])),now(),0,0,'id_precio_detalle'";
	$eje_1=mysql_query($sql)or die(mysql_error()." 2 :\n\n".$sql);
}
mysql_query("COMMIT");
	die('ok');	
?>