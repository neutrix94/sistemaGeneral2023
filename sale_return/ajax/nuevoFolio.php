<?php

	header("Content-Type: text/plain;charset=utf-8");
	
	include("../../conectMin.php");
	
	$prefijo = "";
	$folio = "";
	
	$cs = "SELECT prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
	if ($rs = mysql_query($cs)) {
		if ($dr = mysql_fetch_assoc($rs)) {
			$prefijo = $dr["prefijo"];
		} mysql_free_result($rs);
	}
	
	$campo_segun_tipo = $_GET["tipo"] == "P" ? "folio_pedido" : "folio_nv";
	
	$cs = "SELECT IF(ISNULL(MAX(CAST(REPLACE({$campo_segun_tipo}, '{$prefijo}', '') AS SIGNED INT))), 1, MAX(CAST(REPLACE({$campo_segun_tipo}, '{$prefijo}', '') AS SIGNED INT))+1) AS folio " .
		"FROM ec_pedidos " .
		"WHERE REPLACE({$campo_segun_tipo}, '{$prefijo}', '') REGEXP ('[0-9]') " .
		"AND id_sucursal='{$user_sucursal}' " .
		"AND id_pedido <> '{$_GET["idp"]}' ";
	#die ($cs);
	if ($rs = mysql_query($cs)) {
		if ($dr = mysql_fetch_assoc($rs)) {
			$folio = $prefijo . sprintf("%05d", $dr["folio"]);
			die ("OK|FOLIO:{$folio}");
		} mysql_free_result($rs);
	}
	
	echo "ERR|Folio inválido.";
?>