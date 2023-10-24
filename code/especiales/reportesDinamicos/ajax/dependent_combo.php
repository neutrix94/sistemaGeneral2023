<?php
//libreria de conexion
	include("../../../../conectMin.php");
//consulta la dependencia del combo
	$sql = "{$_POST['consulta']} {$_POST['condicion']} '{$_POST['valor']}'";
	//die( $sql );
	$res = mysql_query( $sql ) or die( "Error al consultar datos de combo dependiente : " . mysql_error() );
	echo 'ok|';
	$cont = 0;
	while ( $valores = mysql_fetch_row( $res )) {
		echo ( $cont > 0 ? '|' : null ) . $valores[0] . "~" . $valores[1] ;
		$cont ++;
	}
?>