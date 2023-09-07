<?php
	
	include("../../conectMin.php");
	
	header("Content-Type: text/plain;charset=utf-8");
	
	if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
	
	mysql_set_charset("utf8");
	
	$cs = "SELECT id_productos, nombre, codigo_barras_1, codigo_barras_2, codigo_barras_3, codigo_barras_4 FROM ec_productos " .
		"ORDER BY nombre ASC ";
	
	echo "{\n";
	
	$ix = 0;
	
	if ($rs = mysql_query($cs)) {
		while ($dr = mysql_fetch_assoc($rs)) {
			
			$arrprod = array();
			
			if (!empty($dr["codigo_barras_1"])) array_push($arrprod, $dr["codigo_barras_1"]);
			if (!empty($dr["codigo_barras_2"])) array_push($arrprod, $dr["codigo_barras_2"]);
			if (!empty($dr["codigo_barras_3"])) array_push($arrprod, $dr["codigo_barras_3"]);
			if (!empty($dr["codigo_barras_4"])) array_push($arrprod, $dr["codigo_barras_4"]);
			
			if (!count($arrprod)) array_push($arrprod, 0);
			
			$arrprod = array_unique($arrprod, SORT_STRING);
			
			for ($jx=0; $jx<count($arrprod); ++$jx) {
				echo "\t\"{$dr["id_productos"]}~{$arrprod[$jx]}\":\"{$arrprod[$jx]}~{$dr["nombre"]}\"" . ($ix+1 == mysql_num_rows($rs) && $jx+1 == count($arrprod) ? "" : ","). "\n";
			}
			++$ix;
		} mysql_free_result($rs);
	}
	
	echo "}";
?>