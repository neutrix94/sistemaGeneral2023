<?php

extract($_GET);
extract($_POST);

#echo "<h2>{$reporte}</h2>";


function retornaListaIdsNombres($strSQL, $id_origen = 'NO', $depende = 0, $val = 0) {
    $htmltable = array();
    unset($ids);
    unset($names);
    $ids = array();
    $names = array();


    if ($depende != 0) {
        $strSQL = str_replace('$IDD', $val, $strSQL);
    }

    if ($id_origen != 'NO')
        $strSQL = str_replace('$ID', $id_origen, $strSQL);
    //die($strSQL);
    if (!($resource0 = mysql_query($strSQL)))
        die("Error en:<br><i>$strSQL</i><br><br>Descripcion:<br><b>" . mysql_error());
    while ($row0 = mysql_fetch_row($resource0)) {
        array_push($ids, $row0[0]);
        array_push($names, $row0[1]);
    }
    mysql_free_result($resource0);
    $htmltable[0] = $ids;
    $htmltable[1] = $names;
    return $htmltable;
}

require("../../conect.php");



$reporte = str_replace('-', '', $reporte);


$strTrans = "AUTOCOMMIT=0";
mysql_query($strTrans);
mysql_query("BEGIN");
$error = 0;

//echo "2-";
//	$instrucciones = "Seleccione los filtros deseados para lanzar su reporte y de clic al bot&oacute;n 'Generar reporte' para generar el reporte en una ventana independiente o a 'Exportar a Excel' para enviar su reporte a un archivo de Excel.";
$instrucciones = "A continuaci&oacute;n seleccione los filtros deseados y de clic a 'Generar reporte' para verlo en pantalla o bien a 'Exportar a Excel' para guardar el archivo como un libro de Microsoft Excel.";
$carpetaRep = $rootpath . '/templates/reportes';

$smarty->assign('raiz', $rootpath . 'modules/');
$smarty->assign('rutaTPL', $rootpath . '/templates/');
//$smarty->assign('carpetaImagenes',ROOTURL.'modules/GEG/templates/default/media/');
$smarty->assign("contentheader", "M&oacute;dulo de reportes");
$smarty->assign("carpeta", $carpetaRep);

$smarty->assign('instrucciones', $instrucciones);
$smarty->assign('reporte', $reporte);

$smarty->assign('accion', 'reportes');



// Primero vamos a hacer el arreglo de tablas relacionadas al reporte para pasarselo al template
require('../ajax/relacionReportesTablas.php');

$smarty->assign('arrayTablas', $arrayTablas);

//Buscaremos los campos por los cuales se ordena el reporte
$tablas = array_keys($arrayTablas);
$tablas = "'" . implode("','", $tablas) . "'";




//AQUI NO ENTIENDO
//Busca en los catalogos los campos de las tablas que se obtuvieros en la anterior
$strConsulta = "SELECT CONCAT(tabla,'.',campo) AS id, display FROM sys_catalogos WHERE tabla IN(" . $tablas . ")";
//echo "3.3-";
$arrOrden = retornaListaIdsNombres($strConsulta);
//echo "3.4-";
$smarty->assign('arrOrden', $arrOrden);

//echo "3.5-";
//	Estos arreglos se van para la seccion de contabilidad
$smarty->assign('valuesTipo', $valuesTipo);
$smarty->assign('outputsTipo', $outputsTipo);

$smarty->assign('valuesCC', $valuesCC);
$smarty->assign('outputsCC', $outputsCC);

$smarty->assign('valuesTiposClientes', $valuesTiposClientes);
$smarty->assign('outputsTiposClientes', $outputsTiposClientes);

$smarty->assign('valuesTiposProveedores', $valuesTiposProveedores);
$smarty->assign('outputsTiposProveedores', $outputsTiposProveedores);
$reporte = str_replace('-', '', $reporte);



//ASIGNA EL TITULO DEL REPORTE
switch ($reporte) {
    // Catalogos
    case 1:
        $titulo = 'Reporte de Ventas por Cliente.';
        break;
    case 2:
        $titulo = 'Reporte de Ventas por Pedido.';
        break;
    case 3:
        $titulo = 'Reporte de Ventas por Producto';
        break;
    case 5:
        $titulo = 'Reporte de Ventas Facturadas';
        break;
    case 6:
        $titulo = 'Reporte de Ventas Tipo de Pago';
        break;
}

//OBTENEMOS LOS AÑOS MAXIMOS Y MINIMOS DE LOS REPORTES

if ($reporte < 4 || ($reporte > 4 && $reporte < 10)) {
    $sql = "SELECT MIN(fecha) FROM `fl_pedidos`";
    $res = mysql_query($sql);
    $row = mysql_fetch_row($res);
    $fecha1 = $row[0];

    $sql = "SELECT MAX(fecha) FROM `fl_pedidos`";
    $res = mysql_query($sql);
    $row = mysql_fetch_row($res);
    $fecha2 = $row[0];
    
} else if ($reporte == 4) {
    $sql = "SELECT MIN(fecha) FROM `fl_pedidos` WHERE facturado=1";
    $res = mysql_query($sql);
    $row = mysql_fetch_row($res);
    $fecha1 = $row[0];

    $sql = "SELECT MAX(fecha) FROM `fl_pedidos`  WHERE facturado=1";
    $res = mysql_query($sql);
    $row = mysql_fetch_row($res);
    $fecha2 = $row[0];
} else {
    $fecha1 = date("Y-m-d");
    $fecha2 = date("Y-m-d");
}

if ($reporte == 4) {
    $anios = array();
    for ($i = date("Y"); $i >= 2007; $i--) {
        array_push($anios, $i);
    }
    
    $smarty->assign('anios', $anios);
}


    //Buscamos si es admin el usuario
    $sql="SELECT
          id_sucursal
          FROM sys_users
          WHERE id_usuario=$user_id";
      
    $res = mysql_query($sql);
    $row=mysql_fetch_row($res);
    
    $smarty->assign('sucUsuario', $row[0]);    


//Seleccionar Delegaciones
$sql = 'SELECT nombre FROM sys_delegacion';
$res = mysql_query($sql);        
$delegaciones = array();

while($row = mysql_fetch_row($res)){
    array_push($delegaciones,$row[0]);
}

$smarty->assign('delegaciones',$delegaciones);

$smarty->assign('fecha1', $fecha1);
$smarty->assign('fecha2', $fecha2);
$smarty->assign('reporte', $reporte);

$smarty->assign('titulo', $titulo);

$smarty->display('reportes.tpl')
;?>