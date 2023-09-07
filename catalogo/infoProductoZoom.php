<?php
	include('conexion.php');

	//flag:despliega,id_producto:id_prd
	$fl=$_POST['flag'];
	if($fl=='despliega'){
		$id=$_POST['id_producto'];
		$sql="SELECT 
				p.id_productos,/*0*/
				p.nombre,/*1*/
				IF(p.imagen IS NULL OR p.imagen='','SIN FOTO',p.imagen),/*2*/
				p.orden_lista,/*3*/
				p.marca,/*4*/
				GROUP_CONCAT(CONCAT(de_valor,' x ','$',FORMAT(pd.precio_venta,4)) SEPARATOR ' | '),/*5*/
				IF(pd.es_oferta=1,'off','')/*6*/
			FROM ec_productos p
			LEFT JOIN ec_precios_detalle pd ON p.id_productos=pd.id_producto
			AND pd.id_precio IN(SELECT id_precio FROM sys_sucursales WHERE id_sucursal=-1)
			WHERE p.id_productos=$id";
	}else{
		$ord_lista=$_POST['orden_lista'];
		$categoria=$_POST['catego'];
		$subcat=$_POST['subc'];

		$categoria=str_replace("~", ",", $categoria);
		
		//	die($fl);
		if($fl==-1){
			$condicion_ord_lista=" orden_lista<".$ord_lista;
			$orden="DESC";
		}else if($fl==1){
			$condicion_ord_lista=" orden_lista>".$ord_lista;
			$orden="ASC";
		}
		if($categoria!=''){
			$cond_categoria=" AND id_categoria IN(".$categoria.")";
			$cond_categoria=str_replace(",)", ")", $cond_categoria);
		}

		$sql="SELECT id_productos,nombre,IF(imagen IS NULL OR imagen='','SIN FOTO',imagen),orden_lista FROM ec_productos

		LEFT JOIN sys_sucursales_producto sp ON id_producto=id_productos
				AND IF('$user_sucursal'='',id_sucursal=1,id_sucursal='$user_sucursal')

		WHERE habilitado=1
		AND estado_suc=1
		AND".$condicion_ord_lista.$cond_categoria." 
		ORDER BY id_categoria, orden_lista $orden LIMIT 1";
	}
//die($sql);
	$eje=mysql_query($sql)or die("Error al consultar la info del producto!!!".mysql_error().$sql);
	echo 'ok|';
	if(mysql_num_rows($eje)<=0){
		die('no|'.$fl);
	}

	$r=mysql_fetch_row($eje);

//carrito
	echo '<table width="100%"><tr>';
	echo '<td width="70%">';
	if($r[6]=='off'){
//		echo 'oferta';
		echo '<img src="img/icono-oferta.png" width="90px">';
	}
	echo '</td>';
	echo '<td width="20%" align="right"><input type="number" id="numero_piezas" style="width:50px;padding:8px;" value="1"></td>';
	echo '<td width="10%" align="left"><button type="button" onclick="agrega_carrito('.$id.');" style="padding:10px;border-radius:50%;"> + </button></td><tr></table>';

	if($r[2]=="SIN FOTO"){
		echo '<p style="color:red; padding-top:150px; font-size: 30px; font-weight:bold;">SIN FOTO</p>';
	}else{
		echo '<img src="'.$r[2].'" style="width:60%; padding-top:10px;">';

	}
	echo '<br>';
	echo '<p style="text-align:center;">'.$r[1].'</p>';
	echo '<p id="ord_de_lsta">'.$r[3].'</p>';//orden de lista 
?>