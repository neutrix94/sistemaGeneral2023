<?php 
//session_start();
	include('conexion.php');
		
		$condicion_id="";
		$familia = mysql_query("SELECT id_categoria,nombre from ec_categoria where id_categoria != '1' AND id_categoria != '35'")or die("error".mysql_error());
		/*if(!isset($user_sucursal)){
			$user_sucursal=1;
		}*/
		/**/
		if(isset($_GET['cHJvZHVjdG8'])){
			$condicion_id=" AND id_productos=".base64_decode($_GET['cHJvZHVjdG8']);
		}
	//validamos si existe la variable de sesion de cliente
	/*	if(isset($SESSION['id_lista'])){
			$id_lista_precio=$SESSION[id_lista]
		}	*/
		
		/**/
		$sql="SELECT IF(p.imagen IS NULL OR p.imagen='','SIN FOTO',p.imagen),
				p.nombre,
				p.id_productos,
				p.id_categoria,
				GROUP_CONCAT(CONCAT(de_valor,' x ','$',FORMAT(pd.precio_venta,4)) SEPARATOR ' | '),
				IF(pd.es_oferta=1,'off','')
				FROM ec_productos p
				LEFT JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
				LEFT JOIN ec_precios_detalle pd ON p.id_productos=pd.id_producto
				AND pd.id_precio IN(SELECT id_precio FROM sys_sucursales WHERE id_sucursal=-1)
				/*AND IF('$user_sucursal'='',sp.id_sucursal=1,sp.id_sucursal='$user_sucursal') LEFT JOIN ec_precios pr ON pr.id_precio=pd.id_precio
				*/				
				/*AND sp.id_sucursal=$user_sucursal*/
				WHERE p.habilitado=1
				AND sp.estado_suc=1
				AND sp.id_sucursal=1/*$suc_activa*/
				AND sp.estado_suc=1
				$condicion_familias
				$condicion_subcategorias
				$condicion_id
			GROUP BY p.id_productos
			ORDER BY p.id_categoria,p.orden_lista";
			//GROUP BY p.id_productos";
	//echo $sql;


		$productos = mysql_query($sql)or die("error".mysql_error().'<br>'.$sql);
		
		$subcategoria = mysql_query("SELECT id_subcategoria,nombre from ec_subcategoria where id_categoria != '1' AND id_categoria != '35' $condicion_familias order by nombre asc")or die("error".mysql_error());

?>