<?php

	include("../../conectMin.php");
	
	header("Content-Type: text/plain;charset=utf-8");
	if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
	mysql_set_charset("utf8");
	
	$codigo_barras = $_GET["cb"];
	$id_producto = null;
	
	$cs = "SELECT
	       id_productos AS id_producto
	       FROM ec_productos
	       WHERE codigo_barras_1 = '{$codigo_barras}'
	       OR codigo_barras_2 = '{$codigo_barras}'
	       OR codigo_barras_3 = '{$codigo_barras}'
	       OR codigo_barras_4 = '{$codigo_barras}'
	       OR id_productos = '{$codigo_barras}'";
	#die ($cs);
	if ($rs = mysql_query($cs)) {
		if ($dr = mysql_fetch_assoc($rs)) {
			$id_producto = $dr["id_producto"];
		} mysql_free_result($rs);
	}
	
	if (!is_null($id_producto)) die ("OK|IDP:{$id_producto}");
	else die ("ERR|Folio inválido.");
	
?>