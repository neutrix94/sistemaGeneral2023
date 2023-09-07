<?php

include("../../conect.php");


extract($_GET);
extract($_POST);

global $cadenaWheres;
global $strWheresEspecialesProductosYYY;

$totalMon = array();

//variable para el filtro de cuentas por fecha de registro o de pago	
$opPagoRegistro = is_null($_GET['opPagoRegistro']) ? 0 : $_GET['opPagoRegistro'];

$parametros = is_null($_GET['parametros']) ? 0 : str_replace("*", "%", $_GET['parametros']);
if ($parametros != 0 || $parametros != '') {
    $arrWheres = explode('~', $parametros);
    array_pop($arrWheres);
    $cadenaWheres = implode(" ", $arrWheres);
    if($delegacion ){
        $cadenaWheres = $cadenaWheres." AND sys_delegacion.nombre =".$delegacion; 
    }
    //$cadenaWheres = $cadenaWheres;
    //$cadenaWheres = str_replace('AND', ' AND (', $cadenaWheres, 1);
    $cadenaWheres = $cadenaWheres . ')';
}
else
    $cadenaWheres = " AND 1";

//Agrego el Filtro de Delegacion


$opcionales = is_null($_GET['opcionales']) ? 0 : $_GET['opcionales'];
if ($opcionales != '|') {
    $arropc = explode('|', $opcionales);
    $fechaCorte = "'" . $arropc[0] . "'";
    //	Reportes que necesitan versi�n, es decir, detallada o no detallada y fecha
    if ($idRep == '2') {
        $version = $arropc[0];
        $fechaCorte = "'" . $arropc[1] . "'";
        $fecha2 = "'" . $arropc[2] . "'";
    }
    //	Reportes que necesitan rango de fechas
    else if ($idRep == '3')
        $fecha2 = "'" . $arropc[1] . "'";

    if ($idRep == '54') {
        $fecha3 = "'" . $arropc[2] . "'";
        $fecha4 = "'" . $arropc[3] . "'";
    }
    //	Reportes que necesitan solo versi�n
    else
        $version = $arropc[0];
}else {
    $fechaCorte = " NOW() ";
    $fecha2 = " NOW() ";
}

include $rootpath . "/include/phpreports/PHPReportMaker.php";
//echo $_GET['idRep'];


switch($_GET['idRep']){
		/**
		  *	Ventas por Cliente
		  */
		case 1:
            
            $filSuc="";
            
            if($sucUsuario != -1)
            {
                $filSuc=" AND fl_pedidos.id_sucursal=$sucUsuario";
            }
            
            
            //sucUsuario
            
            
			$strSQL = "SELECT 
			           fl_clientes.id_cliente as id,
			           fl_clientes.nombre AS cliente,
			           SUM(fl_pedido_detalle.cantidad) as cantidad,  
			           CONCAT('$', FORMAT(fl_pedidos.total,2)) as totalf
			           FROM fl_pedidos
			           JOIN fl_pedido_detalle ON fl_pedidos.id_pedido = fl_pedido_detalle.id_pedido
                       JOIN fl_clientes ON fl_pedidos.id_cliente = fl_clientes.id_cliente
                       WHERE fl_pedidos.id_estatus >= 3
                       AND fl_pedidos.fecha >= '".$arropc[0]."'
                       AND fl_pedidos.fecha <= '".$arropc[1]."'
                       $filSuc
                       GROUP BY fl_clientes.id_cliente
                       ORDER BY fl_pedidos.total DESC";

		//Numero de registros		 
			$sql="
				   SELECT
				   COUNT(1)
				   FROM
				   ( 
					   SELECT
					   COUNT(1)
					   FROM fl_pedidos
					   JOIN fl_pedido_detalle ON fl_pedidos.id_pedido = fl_pedido_detalle.id_pedido
					   JOIN fl_clientes ON fl_pedidos.id_cliente = fl_clientes.id_cliente					   
					   WHERE fl_pedidos.id_estatus >=3
					   AND fl_pedidos.fecha >= '".$arropc[0]."'
					   AND fl_pedidos.fecha <= '".$arropc[1]."'
					   $filSuc				  
					   GROUP BY fl_clientes.id_cliente
				   ) aux";		 
					 
			$res=mysql_query($sql) or die("Error en: $sql");
			$row=mysql_fetch_row($res);
			$totalReg=$row[0];
			
			//sumatorias
			$sql="SELECT				   
				   IF(SUM(fl_pedido_detalle.cantidad) IS NULL, '0', FORMAT(SUM(fl_pedido_detalle.cantidad), 0)) as cantidad,
				   IF(SUM(fl_pedido_detalle.cantidad*fl_pedido_detalle.precio) IS NULL, '$0.00', CONCAT('$', FORMAT(SUM(fl_pedido_detalle.cantidad*fl_pedido_detalle.precio), 2))) as total
				   FROM fl_pedidos
				   JOIN fl_pedido_detalle ON fl_pedidos.id_pedido = fl_pedido_detalle.id_pedido
				   JOIN fl_clientes ON fl_pedidos.id_cliente = fl_clientes.id_cliente					   
				   WHERE fl_pedidos.id_estatus >=3
				   AND fl_pedidos.fecha >= '".$arropc[0]."'
				   AND fl_pedidos.fecha <= '".$arropc[1]."'
				   $filSuc";
					 
			//echo $strSQL;		
			
			$res=mysql_query($sql) or die("Error en: $sql");
			$totalMon=mysql_fetch_row($res);
			$rango_fechas="Del ".$arropc[0]."  al ".$arropc[1];

		break;
		/**
		*	Ventas por pedido
		*/
		case 2:
            
        $filSuc="";
            
        if($sucUsuario != -1)
        {
            $filSuc=" AND fl_pedidos.id_sucursal=$sucUsuario";
        }    
		
		$strSQL = "
		SELECT 
		    fl_pedidos.id_pedido as id,
		    fl_pedidos.no_pedido as no_pedido,
		    fl_clientes.nombre as cliente,
		    DATE_FORMAT(fl_pedidos.fecha, '%d/%m/%Y') as fecha,
		    CONCAT('$', FORMAT(fl_pedidos.total, 2)) as totalf
		FROM
		    fl_pedidos
		        JOIN
		    fl_clientes ON fl_pedidos.id_cliente = fl_clientes.id_cliente
		WHERE
				fl_pedidos.id_estatus >=3
				AND fl_pedidos.fecha >= '".$arropc[0]."'
				AND fl_pedidos.fecha <= '".$arropc[1]."'
				$filSuc
		ORDER BY fl_pedidos.fecha DESC";
		
		#echo $strSQL."<br/><br/>";
				   
				   
			//Numero de registros		 
			$sql="
				  SELECT 
					    COUNT(1)
					FROM
					    fl_pedidos
					        JOIN
					    fl_clientes ON fl_pedidos.id_cliente = fl_clientes.id_cliente
					WHERE
						fl_pedidos.id_estatus >=3
					    AND fl_pedidos.fecha >= '".$arropc[0]."'
					        AND fl_pedidos.fecha <= '".$arropc[1]."'
					        $filSuc
					        ";		 
			#echo $sql;
					 
			$res=mysql_query($sql) or die("Error en: $sql");
			$row=mysql_fetch_row($res);
			$totalReg=$row[0];		
			
			
			//sumatorias
			$sql="
				   SELECT 
					    CONCAT('$', FORMAT(SUM(fl_pedidos.total), 2))
					FROM
					    fl_pedidos
					WHERE
					fl_pedidos.id_estatus >=3
					AND fl_pedidos.fecha >= '".$arropc[0]."'
					AND fl_pedidos.fecha <= '".$arropc[1]."'
					$filSuc
			    	";
			#echo $sql;
					 
			//echo $strSQL;		
			
			$res=mysql_query($sql) or die("Error en: $sql");
			$totalMon=mysql_fetch_row($res);

			$rango_fechas="Del ".$arropc[0]."  al ".$arropc[1];	   
				   
		
		break;
		/**
		 * Ventas por Producto
		 */
		case 3:
		
		
		$filSuc="";
            
        if($sucUsuario != -1)
        {
            $filSuc=" AND fl_pedidos.id_sucursal=$sucUsuario";
        }

		$strSQL = "
		SELECT 
		    fl_productos.id_producto, 
			fl_productos.codigo, 
			fl_productos.descripcion as codigo_productof,
		    SUM(fl_pedido_detalle.cantidad) as cantidadf,
			FORMAT(SUM(fl_pedido_detalle.cantidad * fl_pedido_detalle.precio), 2) as totalf
		FROM
		    fl_pedido_detalle
		        JOIN
		    fl_pedidos ON fl_pedido_detalle.id_pedido = fl_pedidos.id_pedido
		        JOIN
		    fl_productos ON fl_pedido_detalle.id_producto = fl_productos.id_producto
		WHERE
			fl_pedidos.id_estatus >=3
		    AND fl_pedidos.fecha >= '".$arropc[0]."'
					AND fl_pedidos.fecha <= '".$arropc[1]."'
			$filSuc		
		GROUP BY fl_productos.codigo";
		
				   
			//Numero de registros		
			$sql = "
			SELECT 
			    COUNT(DISTINCT fl_productos.codigo)
			FROM
			    fl_pedido_detalle
			        JOIN
			    fl_pedidos ON fl_pedido_detalle.id_pedido = fl_pedidos.id_pedido
			        JOIN
			    fl_productos ON fl_pedido_detalle.id_producto = fl_productos.id_producto
			WHERE
				fl_pedidos.id_estatus >=3
			    AND fl_pedidos.fecha >= '".$arropc[0]."'
					AND fl_pedidos.fecha <= '".$arropc[1]."'
					$filSuc";

					 
			$res=mysql_query($sql) or die("Error en: $sql");
			$row=mysql_fetch_row($res);
			$totalReg=$row[0];			
			
			
			$sql = "
			SELECT 
			    CONCAT('$',
			            FORMAT(SUM(fl_pedido_detalle.cantidad * fl_pedido_detalle.precio), 2)) as total_vendidof
			FROM
			    fl_pedido_detalle
			        JOIN
			    fl_pedidos ON fl_pedido_detalle.id_pedido = fl_pedidos.id_pedido
			        JOIN
			    fl_productos ON fl_pedido_detalle.id_producto = fl_productos.id_producto
			WHERE
			fl_pedidos.id_estatus >=3
			AND fl_pedidos.fecha >= '".$arropc[0]."'
			AND fl_pedidos.fecha <= '".$arropc[1]."'
			$filSuc";
					 
			//echo $strSQL;		
			
			$res=mysql_query($sql) or die("Error en: $sql");
			$totalMon=mysql_fetch_row($res);
			
			$rango_fechas="Del ".$arropc[0]."  al ".$arropc[1];		   
				   
		break;
		/**
		  * Ventas Facturadas organizadas por folio
		  */
		case 5:
		
		    $filSuc="";
            
            if($sucUsuario != -1)
            {
                $filSuc=" AND fl_pedidos.id_sucursal=$sucUsuario";
            }
		
			$strSQL = "SELECT 
					    fl_pedidos.folio_factura as folio,
					    fl_pedidos.fecha as fecha,
					    fl_pedidos.total as total,
					    CONCAT('$', FORMAT(fl_pedidos.total, 2)) as totalf
					FROM
					    fl_pedidos
					WHERE
					    fl_pedidos.facturado = 1
					        AND fl_pedidos.id_estatus >= 3
					        AND fl_pedidos.fecha >= '".$arropc[0]."'
					        AND fl_pedidos.fecha <= '".$arropc[1]."'
					        $filSuc
					ORDER BY fl_pedidos.id_pedido ";
			#echo $strSQL;
					   
			//Numero de registros		 
			$sql="SELECT			           					   
				   COUNT(1)
				   FROM fl_pedidos					   					   
				   WHERE fl_pedidos.facturado=1
				   AND fl_pedidos.id_estatus >= 3
				   AND fl_pedidos.fecha >= '".$arropc[0]."'
				   AND fl_pedidos.fecha <= '".$arropc[1]."'
				   $filSuc";		 
					 
			$res=mysql_query($sql) or die("Error en: $sql");
			$row=mysql_fetch_row($res);
			$totalReg=$row[0];
			
			//sumatorias
			$sql="SELECT 
					    IF(fl_pedidos.total IS NULL,
					        0,
					        CONCAT('$', FORMAT(SUM(fl_pedidos.total), 2)))
					FROM
					    fl_pedidos
					WHERE
					    fl_pedidos.facturado = 1
					        AND fl_pedidos.id_estatus >= 3 
						   AND fl_pedidos.fecha >= '".$arropc[0]."'
						   AND fl_pedidos.fecha <= '".$arropc[1]."'
						   $filSuc";
					 
			//echo $strSQL;		
			
			$res=mysql_query($sql) or die("Error en: $sql");
			$totalMon=mysql_fetch_row($res);
			$rango_fechas="Del ".$arropc[0]."  al ".$arropc[1];		   
					   
				//mysql_query($strSQL) or die("Error en:<br><i>$strSQL</i><br><br>Descripcion:<br><b>".mysql_error());			   
		break;
		/**
		 * Tipo de pago
		 * */
		case 6:
		
		   $filSuc="";
            
            if($sucUsuario != -1)
            {
                $filSuc=" AND fl_pedidos.id_sucursal=$sucUsuario";
            }
		
		
			$strSQL = "
			SELECT 
			    fl_formas_pago.id_forma_pago as ID,
			    fl_formas_pago.nombre as tipo_pago,
					COUNT(1) as cantidad,
			    CONCAT('$', FORMAT(SUM(fl_pedidos.total), 2)) as montof
			FROM
			    fl_pedidos
			        JOIN
			    fl_formas_pago ON fl_pedidos.id_forma_pago = fl_formas_pago.id_forma_pago
			WHERE
			fl_pedidos.id_estatus >= 3
			AND fl_pedidos.fecha >= '".$arropc[0]."'
			AND fl_pedidos.fecha <= '".$arropc[1]."'
			$filSuc
			GROUP BY fl_formas_pago.id_forma_pago
			ORDER BY fl_pedidos.total DESC";
			
			//Numero de registros		 
		
				  $sql = "
				  SELECT 
						COUNT(1)
					FROM
					    fl_pedidos
					        JOIN
					    fl_formas_pago ON fl_pedidos.id_forma_pago = fl_formas_pago.id_forma_pago
					WHERE
				  		fl_pedidos.id_estatus >= 3
					    $filSuc
					ORDER BY fl_pedidos.total DESC ";
					 
			$res=mysql_query($sql) or die("Error en: $sql");
			$row=mysql_fetch_row($res);
			$totalReg=$row[0];
			
			//sumatorias

				$sql= "
				SELECT 
				    CONCAT('$', FORMAT(SUM(fl_pedidos.total), 2))
				FROM
				    fl_pedidos
				        JOIN
				    fl_formas_pago ON fl_pedidos.id_forma_pago = fl_formas_pago.id_forma_pago
				WHERE
					fl_pedidos.id_estatus >= 3
						AND fl_pedidos.fecha >= '".$arropc[0]."'
					  AND fl_pedidos.fecha <= '".$arropc[1]."'
					  $filSuc
				ORDER BY fl_pedidos.total DESC ";
			
			//echo $strSQL;		
			
			$res=mysql_query($sql) or die("Error en: $sql");
			$totalMon=mysql_fetch_row($res);
			
			$rango_fechas="Del ".$arropc[0]."  al ".$arropc[1];			 
					 
		break;
		case '100':
		//------------------------------
			$arr = explode('|',$parametros);
			$strSQL = "SELECT DISTINCT sys_acciones.id_accion,
					   sys_acciones.nombre as operacion,
					   sys_bitacora.tabla,
					   sys_bitacora.id_campo,
					   id_relacionado,
					   sys_bitacora.id_usuario,
					   concat(apellidos,' ', nombres) as usuario,
					   fecha,
					   hora,
					   sys_bitacora.id_menu,
					   if(otro is null or otro ='0' or otro ='','-',otro ) as valor,
					   observaciones,
					  if(sys_config_encabezados.nombre is null,'-',sys_config_encabezados.nombre) as nombre_campo,
					  if(sys_bitacora.tabla is null or sys_bitacora.tabla='','-',if(sys_menus.nombre is null,sys_bitacora.tabla,sys_menus.nombre)) as nombre_tabla
				 FROM sys_bitacora
				 left join sys_usuarios on sys_bitacora.id_usuario=sys_usuarios.id_usuario
				 left join sys_acciones on sys_acciones.id_accion=sys_bitacora.id_accion
				 left join sys_config_encabezados on sys_config_encabezados.tabla=sys_bitacora.tabla and
				                                     sys_bitacora.id_campo=sys_config_encabezados.campo
				 left join sys_menus on sys_menus.tabla_asociada=sys_bitacora.tabla";
			
			//anexamos los where
			
			$strSQL.=" WHERE  fecha>='".$fechaIni."' and fecha<='".$fechaFin."' and sys_bitacora.id_usuario in (".$usu.") ";
				//window.open("reportes/procesaReportes.php?parametros=&opcion=&idRep=100&opcionales=&titulo=Bit�cora&opPagoRegistro=&"+fecha_Ini+"=&fechaFin="+fecha_Fin+"&us"+strusu+"=&operacion="+stroperaciones+"","_blank",especificaciones);
	
			// where fecha>='' and fecha<='' and sys_bitacora.id_usuario in () and sys_acciones.id_accion in ();
			// where fecha>='' and fecha<='' and sys_bitacora.id_usuario in () and sys_acciones.id_accion in ();
			/*if($arr[0]==$arr[1])
				$strSQL.=" WHERE Fecha>='".$arr[0]."' AND id!=1";
			else
				$strSQL .= " WHERE Fecha BETWEEN '".$arr[0]."' AND '".$arr[1]."' AND id!=1";
			*/			
			if($operacion!='')
				$strSQL.=" AND sys_acciones.id_accion in (".$operacion.")";
			//$strSQL.=" ORDER BY usuario, Fecha";
			
		//-------------------------------------------------------
		break;
		default:
			echo "No existe alg�n reporte con este ID. Cierre la ventana e intente nuevamente.";
			die();
	}

$strSQL = str_replace("XXX", $cadenaWheres, $strSQL);
//para el query de totales 2
$strSQL22 = str_replace("XXX", $cadenaWheres, $strSQL22);
$strSQL22 = str_replace("\\", "", $strSQL22);

$strSQL = str_replace("\\", "", $strSQL);

if ($idRep == '2' || $idRep == '6')
    $reporte = $version == 'd' ? "rep" . $idRep . "d.xml" : "rep" . $idRep . ".xml";
else
    $reporte = "rep" . $idRep . ".xml";
$strConsulta = $strSQL;

//grabaBitacora('7','','0','0',$_SESSION["USR"]->userid,'0',$titulo.' con los filtros'.$cadenaWheres,'');
//grabaBitacora($_SESSION["MGW"]->userid,15, 0, $titulo.' con los filtros'.$cadenaWheres);


if ($strSQL22 != '') {
    $resConsulta2 = mysql_query($strSQL22) or die("Error at a $strSQL ::" . mysql_error());
    extract(mysql_fetch_assoc($resConsulta2));
}

//echo $reporte."<br><br>";
//echo $strSQL;
//echo utf8_encode($cadenaWheres);
$titulo = isset($titulo) ? $titulo : "Reporte_generico";
$titulo = strtoupper($titulo);
$filtro = $cadenaWheres;
$filtro = str_replace('\\', '', $filtro);
if ($filtro == ' AND 1')
    $filtro = 'Ninguno';

//	die();

function insert_date() {
    return "Generado el : " . date('d/m/Y   H:i:s');
}

function insert_where() {
    return ' -' . $filtro;
}

$oRpt = new PHPReportMaker();

if (!$oRpt)
    echo "Error al crear objeto reporte";



$parametros = array("filtro" => $filtro, "titulo" => $titulo, "rango_fechas" => $rango_fechas);
if ($idRep == '2' || $idRep == '6') {
    $parametros["sum_subtotal"] = $sum_subtotal;
    $parametros["sum_iva"] = $sum_iva;
    $parametros["sum_total"] = $sum_total;
}

//enviamos parametros

if ($totalReg) {
    //die($totalReg);
    $parametros["totalReg"] = $totalReg;
}

if (sizeof($totalMon) > 0) {
    for ($i = 0; $i < sizeof($totalMon); $i++) {
        $parametros["total" . ($i + 1)] = $totalMon[$i];
    }
  // print_r($parametros);
   //die();
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
//fl_peecho $strConsulta;

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
