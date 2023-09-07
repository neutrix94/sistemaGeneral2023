<?php
	extract($_POST);//recibimos variables por post
	extract($_GET);
	$muestraStatus=1;
	$status=0;
	$folio='';
	$c=0;
	$validos=0;

/*
	include('ajax/extras.php');
*/
	
/*implementación Oscar 15.08.2018 para filtrar productos externos*/
	if($muestra_prod_ext==1){
		$cond_externos=" AND sucP.es_externo=1";
	}else{
		$cond_externos=" AND sucP.es_externo=0";		
	}
/*fin de cambio 15.08.2018*/
	$cantPre="IF(aux.faltante< sucP.minimo_surtir,0,TRUNCATE((aux.faltante)/aux.cantPres,0)) AS CantidadPresentacion,";
	$cantSurt="(IF(TRUNCATE((aux.faltante)/aux.cantPres,0)*aux.cantPres < sucP.minimo_surtir,0,
						TRUNCATE((aux.faltante)/aux.cantPres,0)*aux.cantPres)) AS cantidadSurtir,";
	$otroFiltro="";
//en caso de recibir id de tranferencias
if(isset($idTransfer)){	
//checamos estatus de transferencia
	$sql1="SELECT id_estado,id_sucursal_origen,id_sucursal_destino,id_almacen_origen,id_almacen_destino,folio from ec_transferencias
				WHERE id_transferencia=$idTransfer";
	$ejecuta=mysql_query($sql1) or die(mysql_error());
	$resultado=mysql_fetch_row($ejecuta);
	$status=$resultado[0];
	$origen=$resultado[1];
	$destino=$resultado[2];
	$al_origen=$resultado[3];
	$al_destino=$resultado[4];
	$folio=$resultado[5];
//seleccionamos los almacenes
	$sql="SELECT id_almacen_origen,id_almacen_destino,id_sucursal_origen,id_sucursal_destino FROM ec_transferencias WHERE id_transferencia=$idTransfer";
	$eje=mysql_query($sql);
	if(!$eje){die("Error al consultar datos generales de la transferencia!!!\n\n".$sql."\n\n".mysql_error());}
	$row=mysql_fetch_row($eje);
//asignamos valores
	$al_origen=$row[0];
	$al_destino=$row[1];
	$origen=$row[2];
	$destino=$row[3];

	$sql="SELECT
	 		aux.ID,
	 		aux.ordenLista,
	 		aux.Nombre,
	 		aux.CantidadPresentacion CantidadPresentacion,
	 		1 AS Presentacion,
	 		SUM(IF(m.id_almacen=$al_origen,IF(md.cantidad IS NULL, 0, md.cantidad*tm.afecta),0)) AS InvOr,
			SUM(IF(m.id_almacen=$al_destino,IF(md.cantidad IS NULL, 0, md.cantidad*tm.afecta),0)) AS InvDes,
			aux.maximo,
			aux.idEstProd,
		/*Implementación Oscar 26.02.2019 para incluir el alfanumérico*/
			aux.clave,
		/*Fin de cambio Oscar 26.02.2019*/
			aux.ubicacion_almacen as ubicacion_matriz,
			aux.ubicacion_almacen_sucursal

	 	FROM(
			SELECT
				tp.id_producto_or as ID,
				p.orden_lista AS ordenLista,
		/*Implementación Oscar 26.02.2019 para incluir el alfanumérico*/
				REPLACE(p.clave,',','*') as clave,
		/*Fin de cambio Oscar 26.02.2019*/
				p.nombre AS Nombre,
				tp.cantidad AS CantidadPresentacion,
				tp.cantidad AS cantidadSurtir,
				ep.maximo,
				ep.id_estacionalidad_producto as idEstProd,
				p.ubicacion_almacen,
				sucP.ubicacion_almacen_sucursal
			FROM ec_transferencia_productos tp
			LEFT JOIN ec_productos p ON p.id_productos=tp.id_producto_or
			LEFT JOIN sys_sucursales_producto sucP ON sucP.id_producto=tp.id_producto_or AND sucP.id_sucursal=$destino
			LEFT JOIN ec_estacionalidad_producto ep ON tp.id_producto_or=ep.id_producto  
			WHERE id_transferencia=$idTransfer AND ep.id_estacionalidad=(SELECT id_estacionalidad FROM sys_sucursales WHERE id_sucursal=$destino)
			GROUP BY tp.id_producto_or
		)aux
		LEFT JOIN ec_movimiento_detalle md ON aux.ID=md.id_producto
		LEFT JOIN ec_movimiento_almacen m ON md.id_movimiento = m.id_movimiento_almacen AND m.id_sucursal IN($origen,$destino)
		LEFT JOIN ec_tipos_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento
		GROUP BY aux.ID
		ORDER BY aux.ordenLista ASC";
	//die($sql);
	$res=mysql_query($sql)or die($sql.mysql_error());
	echo '<input type="hidden" id="id_trans" value="'.$idTransfer.'">';

}else{//de lo contrario(nuevaTransferencia)
	$orden="ORDER BY  ordenLista,Diferencia, Equivalencia DESC";
	$FILTRO_CATEGORIAS=$filtroFam;//asignamos filtro por categoria
	$res="";
//	echo 'sucursal origen: '.$origen.', sucursal destino: '.$destino.'almacen origen: '.$al_origen.', almacen destino: '.$al_destino.', FILTRAR POR: '.$filtrarPor;
//condicionamos de acuerdo a los datos que extraemos	
	if($filtrarPor=='full' || $id_tipo==6){//si es completo
		//echo'0-0';
		if($filtrarPor=='full'){
			//$orden="ORDER BY  valido DESC";
			
			/*$otroFiltro="IF(
							IF(
								sucP.estado_presentacion=0,(aux.faltante),
								TRUNCATE((aux.faltante)/aux.cantPre,0)
							)>0,1,0) as valido, ";*/
	
			$otroFiltro="IF(TRUNCATE(aux.faltante/aux.cantPre,0)>0,1,0) as valido, ";
			
			if($id_tipo==4){
				$WHERETIPO="";	
				//$otroFiltro1="IF(aux.InvDes<aux.maximo AND cantidadSurtir=0,2,0) as valido,";		 
			}
			if($id_tipo == 1){//ESTE TIPO CORRESPONDE A OPCIÓN URGENTE.	
				//echo'aqui esta entrando';
				$WHERETIPO=" AND InvDes <= minimo";	
				//$otroFiltro="IF(cantidadSurtir=0,0,2) as valido, ";
				//IF(cantidadSurtir=-1,0,0)as valido,";
			}
	
			if($id_tipo == 3){//ESTE TIPO CORRESPONDE A OPCIÓN MEDIO.
				$WHERETIPO=" AND InvDes <= medio";
				//$otroFiltro="IF(aux.InvDes <aux.medio AND cantidadSurtir>0,1,0)as valido,";
			}
			$filtrado="";//filtro vacio
			$limitador="";
		}
	}else{//de lo contrario
	//creamos condiciones de filtrado
//echo'<br><font color=red>0-0</font>';
		$limitador="WHERE InvDes < maximo";
		
		$s="AND IF(aux.cantPres=1,
						IF((aux.faltante)>=sucP.minimo_surtir,(aux.faltante),0),
						IF((aux.faltante)>=aux.cantPres,aux.faltante,0)
					) >=IF(aux.cantPres=1,sucP.minimo_surtir,aux.cantPres)";
		
	}	
	if($id_tipo == 1 || $id_tipo == 3 || $id_tipo == 4 || $id_tipo == 6 || $id_tipo ==2){//EN CASO DE;
			//die("exito");//RETORNA EXITO
		if($id_tipo==2){
			//echo '<br>here</br>';
			$limitador="";
			$s="";
			$WHERETIPO="";
		}
		if($id_tipo==4){
			$WHERETIPO="";			 
		}
		if($id_tipo == 1){//ESTE TIPO CORRESPONDE A OPCIÓN URGENTE.	
			$WHERETIPO=" AND InvDes <= minimo";	
		}
		if($id_tipo == 3){//ESTE TIPO CORRESPONDE A OPCIÓN MEDIO.
			$WHERETIPO=" AND InvDes <= medio";
		}
		if($id_tipo==2){
			$cantPre="0 AS CantidadPresentacion,";
			$cantSurt="0 AS cantidadSurtir,";
			$limitador="";
		}
//condcionamos la manera de consultar los inventarios
	$sqI="SELECT es_almacen FROM ec_almacen WHERE id_almacen=$al_origen";
	$eje=mysql_query($sqI);
	if(!$eje){
		die("Error al identificficar si es almacen primario o secundario\n".mysql_error()."\n".$sqI);
	}
	$rw=mysql_fetch_row($eje);
	//die('res:'.$rw[0]);
	if($rw[0]==0){
		//die('sec');
		$campo="alm2";
	}else if($rw[0]==1){
		//die('prim');
		$campo="existencias";
	}	
//$wendy="LEFT JOIN sys_sucursales_producto ssp ON p.id_productos=ssp.id_productos and ssp.id_sucursales=".$destino;
//$wendyWhere=" and ssp.estado_suc=1";
/*implementación Oscar 08.05.2019 para sacra el año actual*/
	$sql="SELECT YEAR(CURRENT_DATE)";
	$eje_fcha=mysql_query($sql)or die("Error al consultar año actual!!!\n\n".mysql_error());
	$year_act=mysql_fetch_row($eje_fcha);
	$act_year=$year_act[0];
/*Fin de cambio Oscar 08.05.2019*/
$sql="SELECT 
			au.maximo,
			au.ID,
			au.ordenLista,
			au.Nombre,
			au.InvOr,
			au.InvDes,
			au.Presentacion,
			au.nombrePresentacion,
			IF(au.stock_bajo=1 AND au.racionar_transferencias_productos=1,(au.racion_3/au.Presentacion),au.CantidadPresentacion)AS CantidadPresentacion,
			au.cantidadSurtir,
			au.Equivalencia,
			au.Diferencia,
			au.idEstProd,
			au.clave,
			IF(au.stock_bajo=0 AND $origen=1 AND au.InvOr < au.total_pedir AND au.racionar_transferencias_productos=1 AND 
			au.ventasTodasSucs>0, 1, 0 ) AS raciona,
			au.ubicacion_matriz,
			au.ubicacion_almacen_sucursal,
			au.total_pedir,
			au.inventarioAlmacenesPrincipales,
			au.InvOr,
			au.total_llenar_maximos_activos,
			au.stock_bajo,
			au.racion_3
	FROM(
	 	SELECT 
			aux.maximo,
			aux.ID,
			aux.ordenLista,
			IF(aux.cantPres=1,aux.Nombre,CONCAT(aux.Nombre,' (',aux.nomPres,' de ',aux.cantPres,')'))AS Nombre,
			aux.InvOr,
			aux.InvDes,
			aux.cantPres AS Presentacion,
			IF(aux.cantPres=1,'PIEZA',aux.nomPres) AS nombrePresentacion,
			$cantPre
			$cantSurt
			aux.cantPres AS Equivalencia,
			$otroFiltro
			ABS((aux.faltante)-TRUNCATE((aux.faltante)/aux.cantPres, 0)*aux.cantPres)/*)*/AS Diferencia,
			aux.idEstProd as idEstProd,/*guardamos el id del registro de la estacionalidad*/
		/*Implementación Oscar 26.02.2019 para incluir el alfanumérico*/
			aux.clave,
		/*Fin de cambio Oscar 26.02.2019*/
			aux.total_llenar_maximos_activos-(aux.inventarioAlmacenesPrincipales) AS total_pedir,
			aux.ubicacion_matriz,
			sucP.ubicacion_almacen_sucursal,
			sucP.stock_bajo,
			sucP.racion_3,
			aux.inventarioAlmacenesPrincipales,
			aux.total_llenar_maximos_activos,
			aux.racionar_transferencias_productos,
			aux.ventasTodasSucs
/*Implementación Oscar 19.03.2019 para la transferencia complementaria*/
		FROM(
			SELECT
				aux2.ID,
				aux2.ordenLista,
				aux2.Nombre,
				aux2.InvOr,
				aux2.InvDes,
				aux2.idEstProd,
				aux2.maximo,
				aux2.medio,
				aux2.minimo,
				IF('$filtrarPor'='complemento',(aux2.faltante-SUM(IF(trc.id_transferencia IS NULL,0,trp.cantidad))),aux2.faltante) as faltante,
				aux2.cantPres,
				aux2.nomPres,
				aux2.clave,
				aux2.total_llenar_maximos_activos,
				aux2.ubicacion_matriz,
				(aux2.inventarioAlmacenesPrincipales+aux2.productos_en_transferencias_pendientes) AS inventarioAlmacenesPrincipales,
				aux2.racionar_transferencias_productos,
				aux2.ventasTodasSucs	
					FROM(
/*Fin de cambio Oscar 19.03.2019*/
						SELECT
						aux1.ID,
						aux1.ordenLista,
						aux1.Nombre,
						aux1.InvOr,
						aux1.InvDes,
						aux1.idEstProd,
						aux1.maximo,
						aux1.medio,
						aux1.minimo,
						TRUNCATE((aux1.maximo-aux1.InvDes),0) AS faltante,
						IF(pres.id_producto_presentacion IS NULL,1,pres.cantidad) AS cantPres,
						IF(pres.id_producto_presentacion IS NULL,'PIEZA',pres.nombre) AS nomPres,
				/*Implementación Oscar 26.02.2019 para incluir el alfanumérico*/
						aux1.clave,
				/*Fin de cambio Oscar 26.02.2019*/
						(SELECT
							IF(
								SUM(IF(trans.id_transferencia IS NULL,0,trans_prd.cantidad)) IS NULL,
								0,
								SUM(IF(trans.id_transferencia IS NULL,0,trans_prd.cantidad))
							)
							FROM ec_transferencia_productos trans_prd
							LEFT JOIN ec_transferencias trans ON trans_prd.id_transferencia=trans.id_transferencia
							LEFT JOIN sys_sucursales s_1_1 ON trans.id_sucursal_destino=s_1_1.id_sucursal
							AND s_1_1.activo=1
							RIGHT JOIN sys_sucursales_producto sp_1_1 ON s_1_1.id_sucursal=sp_1_1.id_sucursal
							AND sp_1_1.id_sucursal>1 AND sp_1_1.estado_suc=1
							WHERE trans_prd.id_producto_or=aux1.ID
							AND sp_1_1.id_producto=aux1.ID
							AND trans.id_estado BETWEEN '2' AND '5'
						)AS productos_en_transferencias_pendientes,
						aux1.ubicacion_matriz,
				/*Implementación Oscar 24.03.2019 para sumar los máximos de las estacionalidades*/
						(SELECT
								SUM(IF(ep_ax.id_estacionalidad_producto IS NULL,0,ep_ax.maximo))
								FROM ec_estacionalidad_producto ep_ax
								LEFT JOIN ec_estacionalidad estac ON ep_ax.id_estacionalidad=estac.id_estacionalidad
								RIGHT JOIN sys_sucursales suc ON estac.id_estacionalidad=suc.id_estacionalidad
								AND suc.activo=1 AND suc.id_sucursal>1
								RIGHT JOIN sys_sucursales_producto sp_1 
								ON suc.id_sucursal=sp_1.id_sucursal
								AND sp_1.estado_suc=1
								WHERE ep_ax.id_producto=aux1.ID
								AND sp_1.id_producto=aux1.ID
						)as total_llenar_maximos_activos,
				/*Fin de cambio Oscar 24.03.2019*/
						aux1.inventarioAlmacenesPrincipales,
						aux1.racionar_transferencias_productos,
						aux1.ventasTodasSucs
						FROM(
							SELECT
							p.id_productos AS ID,
							p.orden_lista as ordenLista,
					/*Implementación Oscar 26.02.2019 para incluir el alfanumérico*/
							REPLACE(p.clave,',','*') as clave,
					/*Fin de cambio Oscar 26.02.2019*/
							p.nombre AS Nombre,
							SUM(IF(m.id_almacen=$al_origen,IF(md.cantidad IS NULL, 0, md.cantidad*tm.afecta),0)) AS InvOr,
							SUM(IF(m.id_almacen=$al_destino,IF(md.cantidad IS NULL, 0, md.cantidad*tm.afecta),0)) AS InvDes,
							IF(ep.id_estacionalidad_producto is null,0,ep.id_estacionalidad_producto)AS idEstProd,
							IF(ep.id_estacionalidad IS NULL,p.maximo_existencia,ep.maximo) AS maximo,
							IF(ep.id_estacionalidad IS NULL,p.existencia_media,ep.medio) AS medio,
							IF(ep.id_estacionalidad IS NULL,p.min_existencia,ep.minimo) AS minimo,
						/*Implementación Oscar 19.09.2019 para calcular el inventario total de almaccens principales*/
							SUM(IF(alm.es_almacen=1 AND m.id_sucursal!=1,(md.cantidad*tm.afecta),0)) AS inventarioAlmacenesPrincipales,
						/*Fin de cambio Oscar 19.09.2019*/
							REPLACE(p.ubicacion_almacen, ',' , '*') AS ubicacion_matriz,
						/**/
						/*implementación Oscar 08.05.2019 para tomar si se raciona o no se raciona desde la tabla de configuración del sistema*/
							cfg.racionar_transferencias_productos,
						/*fin de cambio Oscar 08.05.2019*/
						/*Implementación Oscar 08.05.2019 para sacar las ventas (por medio de los movimientos de almacen)*/
							SUM(IF(m.id_movimiento_almacen IS NOT NULL AND alm.es_almacen=1 AND m.id_almacen!=1 AND tm.id_tipo_movimiento=2 AND alm.es_externo=0 AND m.fecha like '%$act_year%',md.cantidad,0)) AS ventasTodasSucs
						/*fin de cambio Oscar 08.05.2019*/
							FROM ec_productos p
							LEFT JOIN sys_sucursales_producto SP ON SP.id_producto=p.id_productos
							LEFT JOIN ec_movimiento_detalle md ON SP.id_producto = md.id_producto
							LEFT JOIN ec_movimiento_almacen m ON md.id_movimiento = m.id_movimiento_almacen AND m.id_sucursal IN($origen,$destino)
							LEFT JOIN ec_tipos_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento
							LEFT JOIN ec_almacen alm ON m.id_almacen=alm.id_almacen
							JOIN sys_sucursales s ON s.id_sucursal=$destino
							LEFT JOIN ec_estacionalidad_producto ep ON p.id_productos = ep.id_producto
							AND ep.id_estacionalidad IN(s.id_estacionalidad)
						/*implementación Oscar 08.05.2019 para tomar si se raciona o no se raciona desde la tabla de configuración del sistema*/
							JOIN sys_configuracion_sistema cfg ON cfg.id_configuracion_sistema=1
						/*fin de cambio Oscar 08.05.2019*/
							WHERE p.habilitado=1 AND p.id_productos!=-1 AND p.es_maquilado=0 AND p.muestra_paleta=0/*Modificación para no mostrar productos con muestra_paleta Oscar 23-05-2018*/
							AND p.id_productos!=1808
							$FILTRO_CATEGORIAS
							AND SP.id_sucursal=1 AND SP.estado_suc=1
							GROUP BY p.id_productos
						)aux1
						LEFT JOIN ec_productos_presentaciones pres ON aux1.ID=pres.id_producto
						GROUP BY aux1.ID
		/*Implementación Oscar 19.03.2019 para la transferencia complementaria*/
					)aux2
					LEFT JOIN ec_productos ej_p ON aux2.ID=ej_p.id_productos
					LEFT JOIN ec_transferencia_productos trp ON ej_p.id_productos=trp.id_producto_or
					LEFT JOIN ec_transferencias trc ON trp.id_transferencia=trc.id_transferencia 
					AND(trc.id_estado=1 OR trc.id_estado=2 OR trc.id_estado=3 OR trc.id_estado=4 OR trc.id_estado=5 ) AND trc.id_sucursal_destino IN($destino)
					GROUP BY aux2.ID
		/*Fin de Cambio Oscar 19.03.2019*/
				)aux
				LEFT JOIN sys_sucursales_producto sucP ON aux.ID = sucP.id_producto AND sucP.id_sucursal=$destino AND sucP.estado_suc=1
				LEFT JOIN ec_exclusiones_transferencia et ON aux.ID=et.id_producto 
				$limitador
				$s
				$WHERETIPO
				$cond_externos/*filtro implementado por Oscar 15.08.2018 para prods internos,externos*/
				AND et.id_producto IS NULL
				ORDER BY ordenLista ASC
			)au
		ORDER BY au.ordenLista ASC";
//die($sql);
	$res=mysql_query($sql) or die($sql."\n\nError:".mysql_error());
//	echo $sql;
	//echo '<br><font color="black">'.$sql.'</font>';
	}
}//finaliza else
/*Implementación Oscar 27.02.2019 para modificar los datos del encabezado de Transferencias*/
	$sql="SELECT
			CONCAT('$folio',' De ',a_1.nombre,' a ',a_2.nombre)
		FROM ec_almacen a_1
		LEFT JOIN ec_almacen a_2 ON a_2.id_almacen='$al_destino'
		WHERE a_1.id_almacen IN('$al_origen')";
		//die($sql);
	$eje_1=mysql_query($sql)or die($sql."br>".mysql_error());
	$datos_encabezado=mysql_fetch_row($eje_1);
/*Fin de Cambio Oscar 27.02.2019*/
?>