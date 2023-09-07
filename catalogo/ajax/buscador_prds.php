<?php
	
	if(!include('../conexion.php')){
		die('no');
	}

	$clave=$_POST['dato'];
	$arr_clave=explode(" ", $clave);

	$sql="SELECT
		IF(p.imagen IS NULL OR p.imagen='','SIN FOTO',p.imagen),
		p.nombre,
		p.id_productos,
		p.id_categoria, 
		GROUP_CONCAT(CONCAT(pd.de_valor,' x ','$',FORMAT(pd.precio_venta,4)) SEPARATOR ' | '),
		IF(pd.es_oferta=1,'off',''),
		CONCAT('(',p.orden_lista,') ',p.nombre)
		FROM ec_productos p
		LEFT JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
		LEFT JOIN ec_precios_detalle pd ON p.id_productos=pd.id_producto
		AND pd.id_precio IN(SELECT id_precio FROM sys_sucursales WHERE id_sucursal=-1)
		WHERE
		p.habilitado=1
		AND sp.estado_suc=1
		AND sp.id_sucursal=1/*$suc_activa*/
		AND sp.estado_suc=1 
		AND (p.id_productos like '%$clave%'
		OR p.orden_lista like '%$clave%'
		OR p.clave like '%$clave%'
		OR (";
	
	for($i=0;$i<sizeof($arr_clave);$i++){
		if($i>0){
			$sql.=" AND ";
		}
		$sql.="p.nombre LIKE '%".$arr_clave[$i]."%'";	
	}

	$sql.=")) GROUP BY p.id_productos";
//die($sql);
/*si existen categorias
	if(isset($_POST['cats'])){

	}
*/
	$eje=mysql_query($sql)or die("Error al consultar  coincidencias entre productos!!!<br>".mysql_error().$sql);
	$c=0;
	echo '<table width="100%" border="0">';

	while($r=mysql_fetch_row($eje)){
		$c++;
		echo '<tr id="opc_res_'.$c.'" tabindex="'.$c.'" onclick="carga_prd_busc(1,\''.base64_encode($r[2]).'\');" onfocus="resalta(1,this);" onblur="resalta(0,this);" onkeyup="valida_tca_opc(event,'.$c.');">';
			echo '<td style="padding:10px;">'.$r[6].'</td>';
		echo '</tr>';
	}
	echo '</table>|||';
	
	$productos=mysql_query($sql)or die("Error al consultar  coincidencias entre productos!!!<br>".mysql_error().$sql);
	
	echo '<h3>ARTICULOS</h3>';
	$cont=0;
	while ($muestra = mysql_fetch_row($productos)) {
 		$cont++;
 		if ($muestra[3] == 28 || $muestra[3]==29) {
 			$formato_img='class="galeria posicion"';
 		}else{
 			$formato_img='class="galeria1 posicion1"';
 		}
		echo '<div id="prd_'.$cont.'" '.$formato_img.' value="'.$muestra[2].'" onclick="despliega_zoom('.$muestra[2].');">';
		if($muestra[0]=='SIN FOTO'){
			echo '<img src="img/logo-casa.png">';
			echo '<p style="color:red; padding-top:-15px; font-size: 15px; font-weight:bold; position:relative; top:-17px;">SIN FOTO</p>';
		}else{
			echo '<img src="'.$muestra[0].'">';
		}
		echo '<p style="position:absolute;">'.$muestra[1].'</p>';
		echo '<p style="position:absolute;bottom:-5%;">'.$muestra[4]."    ".$muestra[5].'</p>';
		echo '</div>';
	}

?>