<?php

include("../../conect.php");


extract($_GET);




$strSQL = "SELECT
                fl_pedidos.id_pedido as id,
		fl_pedidos.no_pedido as no_pedido,
		fl_clientes.nombre as cliente,
		DATE_FORMAT(fl_pedidos.fecha, '%d/%m/%Y') as fecha,
		DATE_FORMAT(fl_pedidos.fecha_hora_entrega, '%d/%m/%Y') as fecha_envio,
		'' as Estado,
		'' as tipo_envio,
		IF(fl_pedidos.id_estatus>=3, 'SI', 'NO') as pagado,
		IF(fl_pedidos.id_estatus=5, 'SI', 'NO') as entregado,
		fl_pedidos.total,			      
		CONCAT('$', FORMAT(fl_pedidos.total, 2)) as montof   				  
				   FROM fl_pedidos
				   JOIN fl_clientes ON fl_pedidos.id_cliente = fl_clientes.id_cliente
				   WHERE fl_pedidos.id_pedido=".$id;


//Numero de registros		 
$sql = "SELECT
		           COUNT(1)
				   FROM fl_pedidos
				   JOIN fl_clientes ON fl_pedidos.id_cliente = fl_clientes.id_cliente				   
				   WHERE fl_pedidos.id_pedido=".$id;			   				   
				   

$res = mysql_query($sql) or die("Error en: $sql");
$row = mysql_fetch_row($res);
$totalReg = $row[0];


//sumatorias
$sql = "
				   SELECT
		           CONCAT('$', FORMAT(SUM(fl_pedidos.total), 2))		    
				   FROM fl_pedidos
                                   WHERE
				   fl_pedidos.id_pedido=".$id;;

//echo $strSQL;		

$res = mysql_query($sql) or die("Error en: $sql");
$totalMon = mysql_fetch_row($res);






$strSQL = str_replace("\\", "", $strSQL);


$reporte = $version == 'd' ? "rep4d.xml" : "rep4.xml";

$strConsulta = $strSQL;


$titulo = "VENTAS PEDIDO";


$filtro = 'Ninguno';



function insert_date() {
return "Generado el : " . date('d/m/Y   H:i:s');
}



$oRpt = new PHPReportMaker();

if (!$oRpt)
echo "Error al crear objeto reporte";



$parametros = array("filtro" => $filtro, "titulo" => $titulo, "rango_fechas" => $rango_fechas);

$parametros["sum_subtotal"] = $total_Mon;
$parametros["sum_iva"] = $sum_iva;
$parametros["sum_total"] = $total_Mon;


//enviamos parametros

if ($totalReg) {
//die($totalReg);
$parametros["totalReg"] = $totalReg;
}

if (sizeof($totalMon) > 0) {
for ($i = 0; $i < sizeof($totalMon); $i++) {
$parametros["total" . ($i + 1)] = $totalMon[$i];
}
}




$oRpt->setParameters($parametros);




$matrizDatos = array();


$oRpt->setConnection($dbHost);
$oRpt->setDatabase($dbName);
$oRpt->setDatabaseInterface("mysql");
$oRpt->setUser($dbUser);

//abajo
$oRpt->setPassword($dbPassword);
//arriba
//$oRpt->setPassword("PTpg81xi");
//echo $strConsulta;

$oRpt->setSQL(iconv("ISO-8859-1", "UTF-8", $strConsulta));

//echo "Reporte: $reporte";

$oRpt->setXML($reporte);
//require_once("../inc/datosBaseReportes.php");

if ($opcion == 2)
ob_start();

//echo "No sablotron";
//$oRpt->setPageSize(20);
$oRpt->run();

//echo "Si sablotron ?";

if ($opcion == 2) {
//session_cache_limiter('none'); //*Use before session_start()
//session_start();
$out = ob_get_clean();
#  
#       header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
#       header("Cache-Control: private",false);
#       //header ( "Content-Type: $filedatatype" );
#       header("Content-Disposition: attachment; filename=\"".$file1."\";");
#       header("Content-Transfer-Encoding:* binary");
#       header("Content-Length: ".filesize($file));
//header("Expires: 0");
//header("Cache-Control: private",false);
//header("Cache-Control: max-age=60, must-revalidate");
//header("Pragma: no-cache");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//header("Content-type: atachment-download");
//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
//header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
//header("Content-type: atachment/vnd.ms-excel");
header("Content-type: application/xls");


header("Content-Disposition: atachment; filename=\"$titulo.xls\";");
header("Content-transfer-encoding: binary\n");
echo $out;
}


die();
?>
