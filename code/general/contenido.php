<?php
/*version 30.10.2019*/
include( '../especiales/plugins/inventory.php' );//implementación Oscar 2022
	extract($_POST);
	extract($_GET);
	if(!isset($fcha_filtros)){
		$fcha_filtros="|";
	}

	require("../../conect.php");
	if($tabla == '')
		$tabla=base64_decode($aab9e1de16f38176f86d7a92ba337a8d);

	if($no_tabla == '')
		$no_tabla=base64_decode($bnVtZXJvX3RhYmxh);

//	echo 'NO TABLA:'.$no_tabla." tabla=".$tabla;


	$tipo=base64_decode($a1de185b82326ad96dec8ced6dad5fbbd);

	if($llave == '')
		$llave=base64_decode($a01773a8a11c5f7314901bdae5825a190);

//die($llave."\n\n".$tabla."\n\n".$no_tabla."\n\ntipo:".$tipo);

	mysql_query("BEGIN");

	/*
	 *
	 *  SECCIÓN DE PRUEBA
	 *
	 * */

	#echo "<!-- cod_tabla = $no_tabla -->";


	//buscamos los permisos
	$sql="SELECT id_menu FROM sys_menus WHERE tabla_relacionada = '$tabla' AND no_tabla='$no_tabla'";
//die($sql);
	$res=mysql_query($sql);
	if(!$res)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php");
	}
	$num=mysql_num_rows($res);

	if($num <= 0)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "1", "", "No aplica_1", "contenido.php");
	}

	$row=mysql_fetch_row($res);

	$sql="SELECT nuevo, modificar, eliminar, imprimir, generar FROM sys_permisos WHERE id_menu=".$row[0]." AND id_perfil=".$perfil_usuario;//cambio 22-03-2018 (antes id_usuario ahora perfil_usuario)

	$res=mysql_query($sql);
	if(!$res)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php");
	}
	$num=mysql_num_rows($res);

	if($num <= 0)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "1", "", "No aplica_2", "contenido.php");
	}

	$row=mysql_fetch_row($res);

	$mostrar_nuevo=$row[0];
	$mostrar_mod=$row[1];
	$mostrar_eli=$row[2];
	$mostrar_imp=$row[3];
	$mostrar_gen=$row[4];


	//Permisos listado

	$sql="SELECT
		  modificar,
		  eliminar,
		  nuevo
		  FROM sys_listados
		  WHERE tabla = '$tabla'
		  AND no_tabla = '$no_tabla'";

	//echo $sql;

	$res=mysql_query($sql);
	if(!$res)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php");
	}
	$num=mysql_num_rows($res);

	if($num <= 0 && $tabla!='ec_oc_recepcion')//modif Oscar 20.07.2018 para poder aacceder a otro catálogo desde el listado de recepción oc	 && $tabla!='ec_oc_recepcion'
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "1", "", "No aplica_3", "contenido.php");
	}

	$row=mysql_fetch_row($res);

	//print_r($row);

	if($row[0] != '1')
		$mostrar_mod=0;
	if($row[1] != '1')
		$mostrar_eli=0;
	if($row[2] != '1')
		$mostrar_nuevo=0;

	//echo "Dato: ".$mostrar_nuevo;


	$smarty->assign("mostrar_nuevo", $mostrar_nuevo);
	$smarty->assign("mostrar_mod", $mostrar_mod);
	$smarty->assign("mostrar_eli", $mostrar_eli);
	$smarty->assign("mostrar_imp", $mostrar_imp);
	$smarty->assign("mostrar_gen", $mostrar_gen);


	//buscamos el periodo activo
	/*$sql="SELECT id_periodo FROM eye_periodo WHERE activo=1";
	$res=mysql_query($sql);
	if(!$res)
	{
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php");
	}
	if(mysql_num_rows($res) > 0)
	{
		$row=mysql_fetch_row($res);
		$smarty->assign("periodo", $row[0]);
		$periodo=$row[0];
	}*/

//conseguimos el titulo del catalogo
	$sql="SELECT titulo FROM sys_listados WHERE tabla='$tabla' AND no_tabla='$no_tabla'";
	$res=mysql_query($sql);
	if(!$res)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php");
	}
	$row=mysql_fetch_row($res);
	$smarty->assign("titulo", $row[0]);
	mysql_free_result($res);


	//Buscamos el número de tabs
	$sql="SELECT DISTINCT tab FROM sys_catalogos WHERE tabla='$tabla' AND no_tabla='$no_tabla'";

	$res=mysql_query($sql);
	if(!$res)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php");
	}
	$num=mysql_num_rows($res);
	$smarty->assign("no_tabs", $num);
	mysql_free_result($res);



	//Buscamos los campos de la tabla
	$sql="SELECT
	      /*0*/id_catalogo,
		  /*1*/tab,
		  /*2*/campo,
		  /*3*/display,
		  /*4*/orden,
		  /*5*/tipo,
		  /*6*/es_llave,
		  /*7*/visible,
		  /*8*/modificable,
		  /*9*/valor_inicial,
		  /*10*/'',
		  /*11*/clase,
		  /*12*/longitud,
		  /*13*/sql_combo,
		  /*14*/on_focus,
		  /*15*/on_blur,
		  /*16*/on_click,
		  /*17*/on_change,
		  /*18*/on_keypress,
		  /*19*/on_keydown,
		  /*20*/on_keyup,
		  /*21*/max_length,
		  /*22*/requerido,
		  /*23*/where_combo,
		  /*24*/order_combo,
		  /*25*/'',
		  /*26*/extensiones,
		  /*27*/especificacion,
		  /*28*/clase_esp,
		  /*29*/depende
		  FROM sys_catalogos
		  WHERE tabla='$tabla'
		  AND no_tabla='$no_tabla'";

/*Cambio de Oscar para no mostrar el precio de compra si no se tiene el permiso en el perfil*/
	//$perfil_usuario!=1&&
	if($tabla=='ec_productos'){
		$sql_per_esp="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_perfil=$perfil_usuario AND id_menu=194";
		$eje=mysql_query($sql_per_esp)or die("Error al consultar el permiso especial para ver el precio de proveedor en la pantalla de Productos!!!<br>".mysql_error());
		$r_p_esp=mysql_fetch_row($eje);
		if($r_p_esp[0]==0){
			$sql.=" AND id_catalogo!=74";
		}
	//	die($sql);
	}
/*fin de cambio*/

/*Cambio de Oscar 06.09.2019 para no mostrar el combo de estatus de recepcion si no se tiene el permiso en el perfil*/
	if($tabla=='ec_oc_recepcion' && $no_tabla=="0"){
		$sql_per_esp="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_perfil=$perfil_usuario AND id_menu=198";
		$eje=mysql_query($sql_per_esp)or die("Error al consultar el permiso especial para ver el precio de proveedor en la pantalla de Productos!!!<br>".mysql_error());
		$r_p_esp=mysql_fetch_row($eje);
		if($r_p_esp[0]==0){
			$sql.=" AND id_catalogo!=903";
		}
		//die($sql);
	}
/*Fin de cambio Oscar 06.09.2019*/
	$res=mysql_query($sql);
	if(!$res)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php");
	}
	$num=mysql_num_rows($res);

	$sqlCommand="";

/*Buscamos los datos del grid*/
	$sqlGrid="SELECT
	          /*0*/id_grid,
			  /*1*/nombre,
			  /*2*/display,
			  /*3*/max_width,
			  /*4*/funcion_final,
			  /*5*/tabla_relacionada,
			  /*6*/funcion_nuevo,
			  /*7*/funcion_eliminar,
			  /*8*/scroll,
			  /*9*/alto,
			  /*10*/datosGrid,
			  /*11*/fileGrid,
			  /*12*/footer,
			  /*13*/listado,
			  /*14*/tabla_padre,
			  /*15*/orden,
			  /*16*/campo_llave,
			  /*17*/query,
			  /*18*/filas_inicial,
			  /*19*/funcion_despues_eliminar,
			  /*20*/'' as columnas,
			  /*21*/buscador/*implementación Oscar 13/02/2017*/
			  FROM sys_grid
			  WHERE tabla_padre='$tabla'
			  AND no_tabla='$no_tabla'";

/*implementación Oscar 19.02.2019 para mostrar/ocultar el grid de proveedor producto*/
	if($tabla=='ec_productos'){
		$sql_per_esp="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_perfil=$perfil_usuario AND id_menu=194";
		$eje=mysql_query($sql_per_esp)or die("Error al consultar el permiso especial para ver el precio de proveedor en la pantalla de Productos!!!<br>".mysql_error());
		$r_p_esp=mysql_fetch_row($eje);
		if($r_p_esp[0]==0){
			$sqlGrid.=" AND id_grid!=48";
		}
	}
/*fin de Cambio Oscar 19.02.2019*/

/*implementación Oscar 09.08.2019 para mostrar/ocultar el grid de movimientos de caja o cuenta*/
	if($tabla=='ec_caja_o_cuenta'){
		$sql_per_esp="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_perfil=$perfil_usuario AND id_menu=195";
		$eje=mysql_query($sql_per_esp)or die("Error al consultar el permiso especial para ver el precio de proveedor en la pantalla de Productos!!!<br>".mysql_error());
		$r_p_esp=mysql_fetch_row($eje);
		if($r_p_esp[0]==0){
			$sqlGrid.=" AND id_grid!=54";
		}
	}
/*fin de Cambio Oscar 09.08.2019*/

		$sqlGrid.=" ORDER BY orden";//ordenamiento

	$resGrid=mysql_query($sqlGrid);
	if(!$resGrid)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "2", mysql_error(), $sqlGrid, "contenido.php");
	}

	$numGrid=mysql_num_rows($resGrid);
	$gridArray=array();

	for($i=0;$i<$numGrid;$i++)
	{
		$rowGrid=mysql_fetch_row($resGrid);

		//Buscamos las columnas
		$sq1="SELECT
		      /*0*/id_grid_detalle,
		      /*1*/display,
		      /*2*/campo_tabla,
		      /*3*/tipo,
		      /*4*/modificable,
		      /*5*/mascara,
		      /*6*/alineacion,
		      /*7*/formula,
		      /*8*/REPLACE(datosDB,'$SUCURSAL','$user_sucursal') AS datosDB,
		      /*9*/depende,
		      /*10*/on_change,
		      /*11*/largo_combo,
		      /*12*/sumatoria,
		      /*13*/funcion_valida,
		      /*14*/on_key,
		      /*15*/valor_inicial,
		      /*16*/requerido,
		      /*17*/ancho,
		      /*18*/html_value,
		      /*19*/on_click,
		      /*20*/multiseleccion
		      FROM sys_grid_detalle
		      WHERE id_grid=".$rowGrid[0]."
		      ORDER BY orden";

		$re1=mysql_query($sq1);
		if(!$re1)
		{
			mysql_query("ROLLBACK");
			Muestraerror($smarty, "", "2", mysql_error(), $sq1, "contenido.php");
		}
		$arrGridDat=array();
		for($j=0;$j<mysql_num_rows($re1);$j++)
		{
			$ro1=mysql_fetch_row($re1);


			//Remplazamos valores por default
			$ro1[15]=str_replace('$SUCURSAL', $user_sucursal, $ro1[15]);
			$ro1[15]=str_replace('$USUARIO', $user_id, $ro1[15]);
			$ro1[15]=str_replace('$DATE', date('Y-m-d'), $ro1[15]);
			$ro1[15]=str_replace('$TIME', date('h:i:s'), $ro1[15]);
		/*Implementación Oscar 10.06.2019 para reemplazar valores de fecha y hora*/
			$ro1[15]=str_replace('$FECHA_HORA', date('Y-m-d').' '. date('h:i:s'), $ro1[15]);
		/*Fin de cambio Oscar 10.06.2019*/


			//echo $ro1[15]."<br>";

			array_push($arrGridDat, $ro1);
		}

		$rowGrid[20]=$arrGridDat;

		array_push($gridArray, $rowGrid);
	}
//die('here');
	require("preexcepcion.php");

	//Se procesa la accion "insertar"
	if($accion == 'insertar')
	{
		$sqlHead="INSERT INTO $tabla(";
		$sqlVals=" VALUE(";

		mysql_data_seek($res, 0);
		$row=mysql_fetch_row($res);


		if($row[8] == '1')
		{
			$iniForNew=0;
		}
		else{
			$iniForNew=1;
		}

		for($i=$iniForNew;$i<$num;$i++)
		{
			mysql_data_seek($res, $i);
			$row=mysql_fetch_row($res);
			$ax=$row[2];
			if($tabla == 'sys_users' && $row[2] == 'contrasena'){
				//$row[2].=",codigo_barras_usuario";
				//$row[2]=
			}

			if($i > $iniForNew)
			{
				$sqlHead.=",";
				$sqlVals.=",";
			}

			$sqlHead.=$row[2];

			if($row[5] == 'BINARY' && $$ax == '')
				$$ax="0";

			if($row[5] == 'FILE')
			{
				//echo "?";
				//print_r($_FILES);

				if($_FILES[$ax]['tmp_name'])
				{

				    $arrAx=explode('.', $_FILES[$ax]['name']);

					$url_final=$rootpath."/files/".$tabla."_".$ax."_".rand(1, 10000)."_".rand(1, 10000).".".$arrAx[sizeof($arrAx)-1];

					//echo "<br>$url_final";

					if(copy($_FILES[$ax]['tmp_name'], $url_final))
						$$ax=str_replace($rootpath, $rooturl, $url_final);
					/*else
						echo "??";
					die();*/
				}
				else
					$$ax="";
			}
		//aqui se aplica el md5 de la contraseña de usuario	y clave única
			if($tabla == 'sys_users' && $row[2] == 'contrasena'){/*,codigo_barras_usuario*/
				$sqlVals.="md5('".$$ax."')";
				//$sqlVals.=",md5('".$$ax."'DATE_FORMAT(NOW(), '%Y%m%d%h%i%s'))";
			}else{
				$sqlVals.="'".$$ax."'";
			}
		}

		$sqlCommand=$sqlHead.")".$sqlVals.")";


//		die($sqlCommand);

		if(!mysql_query($sqlCommand))
		{
			mysql_query("ROLLBACK");
			Muestraerror($smarty, "", "3", mysql_error(), $sqlCommand, "contenido.php");
		}

		$llave=mysql_insert_id();

		//ejecutamos las funciones de grid

		for($i=0;$i<$numGrid;$i++)
		{
			$aux="file".$gridArray[$i][1];

			if(isset($$aux))
			{
				if(file_exists($$aux))
				{
					$ar=fopen($$aux, "rt");

					if($ar)
					{
						$sqGrid="";
						while(!feof($ar))
							$sqGrid.=fgets($ar, 10000);

						$sqGrids=explode('|', $sqGrid);

						array_pop($sqGrids);

						for($j=0;$j<sizeof($sqGrids);$j++)
						{
							$sqGrids[$j]=str_replace('$LLAVE', $llave, $sqGrids[$j]);
							$sqGrids[$j]=str_replace('$periodo', $periodo, $sqGrids[$j]);
							/*if(!mysql_query($sqGrids[$j]))
							{
								mysql_query("ROLLBACK");
								Muestraerror($smarty, "", "3", mysql_error(), $sqGrids[$j], "contenido.php");
							}*/
						//implementación Oscar 2022 para insertar proveedor producto del detalle de movimiento de almacen
							/*if( $tabla = 'ec_movimiento_almacen' ){
								$subArray_madpp = explode('~~~', $sqGrids[$j]);
								$sqGrids[$j] = $subArray_madpp[0];
							}*/
							if(!mysql_query($sqGrids[$j]))
							{
								mysql_query("ROLLBACK");
								Muestraerror($smarty, "", "3", mysql_error(), $sqGrids[$j], "contenido.php");
							}/*else if( $subArray_madpp[1] != '' && $subArray_madpp[1] != null ){
								if ( $subArray_madpp[1] == 'insert' ){
									$eje_det_mov_alm_det = mysql_query( "SELECT last_insert_id()" );
									$det_mov_alm_det = mysql_fetch_row( $eje_det_mov_alm_det );
									$insert_madpp = insert_madpp( $det_mov_alm_det[0] );
									if( $insert_madpp != 'success' ){
										mysql_query("ROLLBACK");
										Muestraerror($smarty, "", "3", $insert_madpp, $insert_madpp, "contenido.php");
									}
								}else{
									if( !mysql_query($subArray_madpp[1]) ){
										mysql_query("ROLLBACK");
										Muestraerror($smarty, "", "3", mysql_error(), $subArray_madpp[1], "contenido.php");
									}
								}
							}*/
						//fin de cambio Oscar 2022
						}
					}
					fclose($ar);

					unlink($$aux);
				}
			}
		}

		//die("Creado");

	}

	if($accion == 'actualizar')
	{
		$sqlCommand="UPDATE $tabla SET ";
		mysql_data_seek($res, 0);
		$row=mysql_fetch_row($res);

		$campoLlave=$row[2];

		for($i=1;$i<$num;$i++)
		{
			mysql_data_seek($res, $i);
			$row=mysql_fetch_row($res);
			$ax=$row[2];


			if($i > 1)
				$sqlCommand.=",";

			if($row[5] == 'BINARY' && $$ax == '')
				$$ax="0";

			if($row[5] == 'FILE')
			{
				/*echo "?";
				print_r($_FILES);*/

				if($_FILES[$ax]['tmp_name'])
				{

                    $arrAx=explode('.', $_FILES[$ax]['name']);

					//$url_final=$rootpath."/files/".$tabla."_".$ax."_".rand(1, 10000)."_".$llave.".".$arrAx[sizeof($arrAx)-1];
					$url_final="../../../img_productos/".$tabla."_".$ax."_".rand(1, 10000)."_".$llave.".".$arrAx[sizeof($arrAx)-1];

					//echo $url_final;

					//Buscamos el archivo anterior para eliminarlo
					$sqTmp="SELECT $ax FROM $tabla WHERE $campoLlave = '$llave'";
					$reTmp=mysql_query($sqTmp);

					if(!$reTmp)
					{
						mysql_query("ROLLBACK");
						Muestraerror($smarty, "", "3", mysql_error(), $sqTmp, "contenido.php");
					}

					$roTmp=mysql_fetch_row($reTmp);
					$fileTmp=str_replace($rooturl, $rootpath, $roTmp[0]);
					if(file_exists($fileTmp))
						unlink($fileTmp);


					if(copy($_FILES[$ax]['tmp_name'], $url_final))
						$$ax=str_replace($rootpath, $rooturl, $url_final);
					/*else
						echo "??";
					die();*/
				}
				else
					$$ax="";
			}

			if($tabla == 'sys_users' && $row[2] == 'contrasena' && $$ax != '')
				$sqlCommand.=$row[2]."=md5('".$$ax."')";
			elseif($tabla == 'sys_users' && $row[2] == 'contrasena')
				$sqlCommand.=$row[2]."=".$row[2];
			else if($row[5] == 'FILE' && $$ax == '')
				$sqlCommand.=$row[2]."=".$row[2];
			else
				$sqlCommand.=$row[2]."='".$$ax."'";
		}

		$sqlCommand.=" WHERE $campoLlave = '$llave'";

		if(!mysql_query($sqlCommand))
		{
			mysql_query("ROLLBACK");
			Muestraerror($smarty, "", "3", mysql_error(), $sqlCommand, "contenido.php");
		}


		//ejecutamos las funciones de grid

		for($i=0;$i<$numGrid;$i++)
		{
			$aux="file".$gridArray[$i][1];

			if(isset($$aux))
			{
				if(file_exists($$aux))
				{
					$ar=fopen($$aux, "rt");

					if($ar)
					{
						$sqGrid="";
						while(!feof($ar))
							$sqGrid.=fgets($ar, 10000);

						$sqGrids=explode('|', $sqGrid);

						array_pop($sqGrids);

						for($j=0;$j<sizeof($sqGrids);$j++)
						{
							$sqGrids[$j]=str_replace('$LLAVE', $llave, $sqGrids[$j]);
							$sqGrids[$j]=str_replace('$periodo', $periodo, $sqGrids[$j]);
						/*implementación Oscar 2022 para insertar proveedor producto del detalle de movimiento de almacen
							if( $tabla = 'ec_movimiento_almacen' ){
								$subArray_madpp = explode('~~~', $sqGrids[$j]);
								$sqGrids[$j] = $subArray_madpp[0];
							}*/
							if(!mysql_query($sqGrids[$j]))
							{
								mysql_query("ROLLBACK");
								Muestraerror($smarty, "", "3", mysql_error(), $sqGrids[$j], "contenido.php");
							}else if( $gridArray[$i][0] == 91 && $j > 0 ){
								if(strpos( $sqGrids[$j], "INSERT" ) === 0 || strpos( $sqGrids[$j], "insert" ) === 0){//insercion
									$id_modulo_usuario = mysql_insert_id();
									$sql_procedure = "CALL SincronizacionSysModulosImpresionUsuarios( 'insert', $id_modulo_usuario, -1)";
									mysql_query( $sql_procedure ) or die( "Error al insertar registro de sincronizacion por insercion : " . mysql_error() );
								}else if(strpos( $sqGrids[$j], "UPDATE" ) === 0 || strpos( $sqGrids[$j], "update" ) === 0){//actualizacion
									$tmp_upd_proc = explode( 'WHERE id_modulo_impresion_usuario=', $sqGrids[$j] );
									$id_modulo_usuario = $tmp_upd_proc[1];
									$sql_procedure = "CALL SincronizacionSysModulosImpresionUsuarios( 'update', $id_modulo_usuario, -1)";
									mysql_query( $sql_procedure ) or die( "Error al insertar registro de sincronizacion por actualizacion : " . mysql_error() );
								}
							}else if( $gridArray[$i][0] == 92 && $j > 0 ){
								if(strpos( $sqGrids[$j], "INSERT" ) === 0 || strpos( $sqGrids[$j], "insert" ) === 0){//insercion
									$id_modulo_sucursal = mysql_insert_id();
									$sql_procedure = "CALL SincronizacionSysModulosImpresionSucursales( 'insert', $id_modulo_sucursal, -1)";
									mysql_query( $sql_procedure ) or die( "Error al insertar registro de sincronizacion por insercion : " . mysql_error() );
								}else if(strpos( $sqGrids[$j], "UPDATE" ) === 0 || strpos( $sqGrids[$j], "update" ) === 0){//actualizacion
								//	die("here");
									$tmp_upd_proc = explode( 'WHERE id_modulo_impresion_sucursal=', $sqGrids[$j] );
									$id_modulo_sucursal = $tmp_upd_proc[1];
									$sql_procedure = "CALL SincronizacionSysModulosImpresionSucursales( 'update', $id_modulo_sucursal, -1)";
									mysql_query( $sql_procedure ) or die( "Error al insertar registro de sincronizacion por actualizacion : " . mysql_error() );
								}

							}else if( ( $gridArray[$i][0] == 91 || $gridArray[$i][0] == 92 ) && $j == 0 ){
								$sqGrid_delete = str_replace( "'", "\'", $sqGrids[$j] );
								$sql_sync_2024 = "INSERT INTO sys_sincronizacion_registros ( sucursal_de_cambio, id_sucursal_destino, datos_json, 
									fecha, tipo, status_sincronizacion )
									SELECT
										IF( s.id_sucursal = -1, '{$user_sucursal}', '-1' ),
										s.id_sucursal,
										CONCAT('{',
											'\"action_type\" : \"sql_instruction\",',
											'\"sql\" : \"{$sqGrid_delete}\"',
											'}'
										),
										NOW(),
										'contenido.php',
										1
									FROM sys_sucursales s
									WHERE s.acceso = '1'";
								$stm_sync_2024 = mysql_query( $sql_sync_2024 ) or die( "Error al insertar instruccion de eliminacion : " );
								//die( $sqGrids[$j] );
							}
							
							/*else if( $subArray_madpp[1] != '' && $subArray_madpp[1] != null ){
								if ( $subArray_madpp[1] == 'insert' ){
									$eje_det_mov_alm_det = mysql_query( "SELECT last_insert_id()" );
									$det_mov_alm_det = mysql_fetch_row( $eje_det_mov_alm_det );
									$insert_madpp = insert_madpp( $det_mov_alm_det[0] );
									if( $insert_madpp != 'success' ){
										mysql_query("ROLLBACK");
										Muestraerror($smarty, "", "3", $insert_madpp, $insert_madpp, "contenido.php");
									}
								}else{
									if( !mysql_query($subArray_madpp[1]) ){
										mysql_query("ROLLBACK");
										Muestraerror($smarty, "", "3", mysql_error(), $subArray_madpp[1], "contenido.php");
									}
								}
							}*/
						//fin de cambio Oscar 2022
						}
					}
					fclose($ar);

					unlink($$aux);
				}
			}
		}

	}

	if($accion == 'eliminar')
	{
		mysql_data_seek($res, 0);
		$row=mysql_fetch_row($res);

		//Buscamos los archivos para eliminarlos
		$sqTmp="SELECT
		        campo
				FROM sys_catalogos
				WHERE tabla='$tabla'
				AND no_tabla='$no_tabla'
				AND tipo='FILE'";
		//echo $sqTmp;
		$reTmp=mysql_query($sqTmp);
		if(!$reTmp)
		{
			mysql_query("ROLLBACK");
			Muestraerror($smarty, "", "3", mysql_error(), $sqTmp, "contenido.php");
		}
		$nuTmp=mysql_num_rows($reTmp);
		if($nuTmp > 0)
		{
			$sqTmp="SELECT ";
			for($i=0;$i<$nuTmp;$i++)
			{
				$roTmp=mysql_fetch_row($reTmp);
				if($i > 0)
					$sqTmp.=",";
				$sqTmp.=$roTmp[0];
			}
			$sqTmp.=" FROM $tabla WHERE ".$row[2]." = '$llave'";
			$reTmp=mysql_query($sqTmp);
			if(!$reTmp)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sqTmp, "contenido.php");
			}
			$nuTmp=mysql_num_rows($reTmp);
			for($i=0;$i<$nuTmp;$i++)
			{
				$roTmp=mysql_fetch_row($reTmp);
				$fileTmp=str_replace($rooturl, $rootpath, $roTmp[0]);
				if(file_exists($fileTmp))
					unlink($fileTmp);
			}
		}

		$campoLlave=$row[2];
		$sqlCommand="DELETE FROM $tabla WHERE $campoLlave = '$llave'";
		if(!mysql_query($sqlCommand))
		{
			mysql_query("ROLLBACK");
			Muestraerror($smarty, "", "3", mysql_error(), $sqlCommand, "contenido.php");
		}


	}

	require("excepcion.php");

    if($accion == 'eliminar')
    {
    	mysql_query("COMMIT");
        header("location: ".$rooturl."code/general/listados.php?tabla=".base64_encode($tabla)."&no_tabla=".base64_encode($no_tabla));
        die();
    }


	//Buscamos los campos visibles de la tabla
	$sql="SELECT
	      /*0*/id_catalogo,
		  /*1*/tab,
		  /*2*/campo,
		  /*3*/display,
		  /*4*/orden,
		  /*5*/tipo,
		  /*6*/es_llave,
		  /*7*/visible,
		  /*8*/modificable,
		  /*9*/valor_inicial,
		  /*10*/'',
		  /*11*/clase,
		  /*12*/longitud,
		  /*13*/sql_combo,
		  /*14*/on_focus,
		  /*15*/on_blur,
		  /*16*/on_click,
		  /*17*/on_change,
		  /*18*/on_keypress,
		  /*19*/on_keydown,
		  /*20*/on_keyup,
		  /*21*/max_length,
		  /*22*/requerido,
		  /*23*/where_combo,
		  /*24*/order_combo,
		  /*25*/'',
		  /*26*/extensiones,
		  /*27*/especificacion,
          /*28*/clase_esp,
          /*29*/(SELECT GROUP_CONCAT(c.id_catalogo SEPARATOR ',') FROM sys_catalogos c WHERE c.depende = sys_catalogos.id_catalogo) AS dependencia,
          /*30*/(SELECT GROUP_CONCAT(c.campo SEPARATOR ',') FROM sys_catalogos c WHERE c.depende = sys_catalogos.id_catalogo) AS dependencia_nombre,
          /*31*/depende
		  FROM sys_catalogos
		  WHERE tabla='$tabla'
		  AND no_tabla='$no_tabla'
		  AND visible=1";

/*Cambio de Oscar para no mostrar el precio de compra si no se tiene el permiso en el perfil*/
	//$perfil_usuario!=1&&
	if($tabla=='ec_productos'){
		$sql_per_esp="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_perfil=$perfil_usuario AND id_menu=194";
		$eje=mysql_query($sql_per_esp)or die("Error al consultar el permiso especial para ver el precio de proveedor en la pantalla de Productos!!!<br>".mysql_error());
		$r_p_esp=mysql_fetch_row($eje);
		if($r_p_esp[0]==0){
			$sql.=" AND id_catalogo!=74";
		}
	//	die($sql);
	}

/*fin de cambio*/

/*Cambio de Oscar 06.09.2019 para no mostrar el combo de estatus de recepcion si no se tiene el permiso en el perfil*/
	if($tabla=='ec_oc_recepcion' && $no_tabla=="0"){
		$sql_per_esp="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_perfil=$perfil_usuario AND id_menu=198";
		$eje=mysql_query($sql_per_esp)or die("Error al consultar el permiso especial para ver el precio de proveedor en la pantalla de Productos!!!<br>".mysql_error());
		$r_p_esp=mysql_fetch_row($eje);
		if($r_p_esp[0]==0){
			$sql.=" AND id_catalogo!=903";
		}
		//die($sql);
	}
/*Fin de cambio Oscar 06.09.2019*/
	$sql.=" ORDER BY orden";
	//echo $sql;


	$res=mysql_query($sql);
	if(!$res)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php");
	}
	$num=mysql_num_rows($res);

//Calculamos el numero de filas del formulario
	if($num == 4 || $num == 3)
		$no_filas=3;
	elseif($num < 3)
		$no_filas=$num;
	elseif($num > 4)
		$no_filas=ceil($num/2);

	$campos=array();
	$campos2=array();

	//variable de validaciones
	$validacion_form="";



	for($i=0;$i<$num;$i++){
		mysql_data_seek($res, $i);
		$row=mysql_fetch_row($res);

		$sqlTime="SELECT DATE_FORMAT(NOW(), '%Y-%m-%d'), TIME_FORMAT(NOW(), '%H:%i:%s')";
		$rtime=mysql_query($sqlTime);
		$roTime=mysql_fetch_row($rtime);

	//valores predeterminados
		if($row[9] == '$DATE')
			$row[9]=$roTime[0];
		if($row[9] == '$TIME')
			$row[9]=$roTime[1];
		if($row[9] == '$USUARIO')
			$row[9]=$user_id;
		if($row[9] == '$SUCURSAL'){
			$row[9]=$user_sucursal;
		}

/*implementación Oscar 07.09.2018 para sustituir el id de sucursal*/
		$row[13]=str_replace('$SUCURSAL', $user_sucursal, $row[13]);
/*fin de cambio*/


/*implementación de Oscar 26.10.2018 para */
		$row[13]=str_replace('$TIPO_PERFIL', $perfil_usuario,$row[13]);
/*fin de cambio Oscar 26.10.2018*/

/*implementación de Oscar 26.10.2018 para */
		$row[13]=str_replace('$LLAVE', $llave,$row[13]);
/*fin de cambio Oscar 26.10.2018*/


/*implementación de Oscar 14.08.2018 para hacer editable el campo de sucursal cuando se trata de línea en la pantalla de gastos*/
		if($tabla=='ec_gastos' && $no_tabla==0 && $user_sucursal==-1 || $tabla=='ec_almacen' && $no_tabla==0 && $user_sucursal==-1){
			if($row[0]==443||$row[0]==652){
				//die($row[13]);
				$row[8]=1;
				$row[11]='barra_dos';
				$row[13]="(SELECT '0','--Seleccionar Sucursal--') UNION (".$row[13];
				$row[13]=str_replace('$SUCURSAL', $user_sucursal, $row[13]);
				//die($row[13]);
			}
		}
/*Fin de cambio*/

	/*agregado por Oscar 20.07.2018*/
		if($row[9] == '$LLAVE')
			$row[9]=$llave;
		/**/

		//Armamos consulta para obtener el valor
		if($llave != '' && $i == 0)
		{
			$sqlVar="SELECT ";
			for($j=0;$j<$num;$j++)
			{
				mysql_data_seek($res, $j);
				$ro=mysql_fetch_row($res);

				if($j > 0)
					$sqlVar.=",";
				$sqlVar.=$ro[2];
			}
			$campoLlave=$row[2];
			$sqlVar.=" FROM $tabla WHERE ".$row[2]." = '$llave'";

			$re=mysql_query($sqlVar);
			if(!$re)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "2", mysql_error(), $sqlVar, "contenido.php");
			}


			$ro=mysql_fetch_row($re);
			$valores=$ro;

			mysql_free_result($re);

		}
//die($sqlVar);
		if($llave != '')
		{

			if($tabla == 'sys_users' && $row[2] == 'contrasena'){
				$row[10] = '';
			}
			else{
				$row[10]=$valores[$i];
			}
		}

		//valores de combo
		if($row[5] == 'COMBO')
		{
			//echo "$row[2]";
			//echo $tipo."<br>";
			$sqlCombo=$row[13]." ";
			if($tipo == 2 || $tipo == 3 || ($row[8] == '0' && $tipo != 0)){
				$sqlCombo.=$row[23]." '".$valores[$i]."' ";
			}
			$sqlCombo.=$row[24];
/*			if($row[0]==886){
			//	die('here');
				//$sqlCombo=str_replace(" AND sc.fecha=DATE_FORMAT(now(),'%Y-%m-%d') AND sc.hora_fin='00:00:00'","", $sqlCombo);
				$sqlCombo=str_replace(' AND sc.fecha=DATE_FORMAT(now(),\'%Y-%m-%d\') AND sc.hora_fin=\'00:00:00\'', $user_sucursal, $sqlCombo);
				die($sqlCombo);
			}
//			echo $sqlCombo.'<br>'.$tipo.'<br>';*/

			$sqlCombo=str_replace('$SUCURSAL', $user_sucursal, $sqlCombo);
            $sqlCombo=str_replace('$USUARIO', $user_id, $sqlCombo);
			//echo "COMBO_1: $sqlCombo<br>";

			if($row[31] != '')
			{

				for($cac=0;$cac<=sizeof($campos);$cac++)
				{
					if($campos[$cac][0] == $row[31])
					{
						if($campos[$cac][10] != '')
							$sqlCombo=str_replace('$llaveDep', $campos[$cac][10], $sqlCombo);
						elseif(isset($campos[$cac][25][0][0]))
							$sqlCombo=str_replace('$llaveDep', $campos[$cac][25][0][0], $sqlCombo);
						else
							$sqlCombo=str_replace('$llaveDep', $campos[$cac][9], $sqlCombo);
					}
				}

				for($cac=0;$cac<=sizeof($campos2);$cac++)
				{
					if($campos2[$cac][0] == $row[31])
					{
						if($campos2[$cac][10] != ''){
							$sqlCombo=str_replace('$llaveDep', $campos2[$cac][10], $sqlCombo);
						}
						elseif(isset($campos2[$cac][25][0][0])){
							$sqlCombo=str_replace('$llaveDep', $campos2[$cac][25][0][0], $sqlCombo);
						}
						else{
							$sqlCombo=str_replace('$llaveDep', $campos2[$cac][9], $sqlCombo);
						}
					}
				}

				$sqlCombo=str_replace('$llaveDep', '', $sqlCombo);
			}

		/*implementación de Oscar 15.09.2018 para concatenar la opción seleccionar sucursal en gastos*/
			if($tabla=='ec_gastos' && $no_tabla==0 && $user_sucursal==-1 || $tabla=='ec_almacen' && $no_tabla==0 && $user_sucursal==-1){
				if($row[0]==443||$row[0]==652){
					//die('aqui');
					$sqlCombo.=')';//cerramos el paréntesis
				}
			}
		/*fin de cambio Oscar 15.09.2018*/

			//echo "COMBO_2:$sqlCombo<br>";

				//if($row[0]==646){
	//	echo $sqlCombo;//com este se muestra consulta de combos
				//}
			$arr=getCombo($sqlCombo);

			//print_r($arr);
			//echo "<br><br>";



			$row[25]=$arr;
			//print_r($row[25]);
		}

		//BUSCADOR
		if($row[5] == 'BUSCADOR')
		{
			if($row[10] != '')
			{
				$sqlValorBusc=$row[23]."'".$row[10]."'";

				//echo $sqlValorBusc;

				$re=mysql_query($sqlValorBusc);
				if(!$re)
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "2", mysql_error(), $sqlValorBusc, "contenido.php");
				}
				$ro=mysql_fetch_row($re);
				$row[25]=$ro[1];
			}
		}


		if($num <= 3)
		{
			array_push($campos, $row);
		}
		elseif($num == 4)
		{
			if($i < 3)
				array_push($campos, $row);
			else
				array_push($campos2, $row);
		}
		else
		{
			if($i < $no_filas)
				array_push($campos, $row);
			else
				array_push($campos2, $row);
		}

/*implementación Oscar 08.11.2018 para hacer requerido el campo de mínimo de horas por día cuando esta puesto*/
	if($tabla=='sys_users' && $row[2]=='pago_dia'){
		$validacion_form.="\n\t\t\t\tif(f.pago_dia.value>0 && f.minimo_horas.value<=0){alert('El campo de mínimo de horas es requerido cuando se ingresa pago por día!!!');f.minimo_horas.focus();f.minimo_horas.select();";
		//echo $validacion_form;
		$validacion_form.="document.getElementById('emerge').style.display='none';return false;}";
	}
/*implementación Oscar 14.10.2019 para validar que no haya comas en el nombre,observaciones y alfanumérico del producto*/
	if($tabla=='ec_productos'){
		if($row[2]=='nombre'){
			$validacion_form.="\n\t\t\t\tif(f.nombre.value.search(',')!=-1){alert('El nombre no puede llevar comas!!!');";
			$validacion_form.="f.nombre.focus();f.nombre.select();";
			$validacion_form.="document.getElementById('emerge').style.display='none';return false;}";
		}
/*		if($row[2]=='clave'){
			$validacion_form.="\n\t\t\t\tif(f.clave.value.search(',')!=-1){alert('El alfanumerico no puede llevar comas!!!');";
			$validacion_form.="f.clave.focus();f.clave.select();";
			$validacion_form.="document.getElementById('emerge').style.display='none';return false;}";
		}*/
		if($row[2]=='observaciones'){
			$validacion_form.="\n\t\t\t\tif(f.observaciones.value.search(',')!=-1){alert('Las observaciones no puede llevar comas!!!');";
			$validacion_form.="f.observaciones.focus();f.observaciones.select();";
			$validacion_form.="document.getElementById('emerge').style.display='none';return false;}";
		}

	}

/*fin de cambio Oscar 14.10.2019*/
	//validaciones de requeridos
		if($row[22] == '1'){
			if($row[5] == 'FILE' && $row[10] != ''){
				$validacion_form.="\n\t\t\t\t /*El tipo FILE tiene valor*/";
			}
			else{
				$validacion_form.="\n\t\t\t\tif(f.".$row[2].".value.length == 0){alert('El campo \"".$row[3]."\" es requerido');f.".$row[2].".focus();";
				//die($validacion_form);
			/*implemntación Oscar 21.08.2018 para ocultar emergente cuando un valor es requerido*/
				$validacion_form.="document.getElementById('emerge').style.display='none';return false;}";
			/*fin de cambio*/
			}
			if($row[5] == 'DATE'){
				$validacion_form.="\n\t\t\t\tif(validaFecha(f.".$row[2].".value) == false){alert('El campo \"".$row[3]."\" tiene un formato no valido');f.".$row[2].".focus();return false;}";
			}
			if($row[5] == 'TIME'){
				$validacion_form.="\n\t\t\t\tif(validaHora(f.".$row[2].".value, f.".$row[2].") == false){alert('El campo \"".$row[3]."\" tiene un formato no valido');f.".$row[2].".focus();return false;}";
			}
		}

	}


	mysql_free_result($res);

	//echo "6";

	if($accion == "insertar" || $accion == "actualizar")
		$tipo=2;

	//print_r($campos);

	/* Excepciones*/
	require("postexcepcion.php");




	$smarty->assign("campos", $campos);
	$smarty->assign("gridArray", $gridArray);
	$smarty->assign("campos2", $campos2);
	$smarty->assign("no_campos", $num);
	$smarty->assign("no_filas", $no_filas);
	$smarty->assign("auxNum", $auxNum);
	$smarty->assign("tipo", $tipo);
    $smarty->assign("tipo64", base64_encode($tipo));
	$smarty->assign("tabla", $tabla);
	$smarty->assign("no_tabla", $no_tabla);
	$smarty->assign("llave", $llave);
	$smarty->assign("numGrid", $numGrid);
	$smarty->assign("llave64", base64_encode($llave));
	$smarty->assign("validacion_form", $validacion_form);
	$smarty->assign("tabla64", base64_encode($tabla));
	$smarty->assign("no_tabla64", base64_encode($no_tabla));
/*implementación Oscar 22.09.2018*/
	$smarty->assign("tipo_sistema",$user_tipo_sistema);
/*fin de cambio 22.09.2018*/


	//buscamos los campos invisibles

	$sql="SELECT
	      /*0*/id_catalogo,
		  /*1*/campo,
		  /*2*/orden,
		  /*3*/valor_inicial,
		  /*4*/''
		  FROM sys_catalogos
		  WHERE tabla='$tabla'
		  AND no_tabla='$no_tabla'
		  AND visible=0";
	//echo $sql;
	$res=mysql_query($sql);
	if(!$res)
	{
		mysql_query("ROLLBACK");
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php");
	}
	$num=mysql_num_rows($res);

	$datosInvisibles=array();

	for($i=0;$i<$num;$i++)
	{

		//Armamos consulta para obtener el valor
		if($llave != '' && $i == 0)
		{
			$sqlVar="SELECT ";
			for($j=0;$j<$num;$j++)
			{
				mysql_data_seek($res, $j);
				$ro=mysql_fetch_row($res);

				if($j > 0)
					$sqlVar.=",";
				$sqlVar.=$ro[1];
			}

			$sqlVar.=" FROM $tabla WHERE ".$campoLlave." = '$llave'";

			$re=mysql_query($sqlVar);
			if(!$re)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "2", mysql_error(), $sqlVar, "contenido.php");
			}
			$ro=mysql_fetch_row($re);
			$valores=$ro;

			mysql_free_result($re);

		}

		mysql_data_seek($res, $i);
		$row=mysql_fetch_row($res);


		//print_r($row);

		if($llave != '')
		{
			$row[4]=$valores[$i];
		}


		//valores predeterminados
		if($row[3] == '$escuela')
			$row[3]=$user_escuela;
		if($row[3] == '$DATE')
			$row[3]=date("Y-m-d");
		if($row[3] == '$TIME')
			$row[3]=date("h:i:s");
		if($row[3] == '$USUARIO')
			$row[3]=$user_id;
		if($row[3] == '$SUCURSAL')
			$row[3]=$user_sucursal;


		array_push($datosInvisibles, $row);
	}

	//print_r($datosInvisibles);


	require("post2excepciones.php");


/*implementación de Oscar 14.08.2018 para mandar el filtro*/
	if($tabla=="ec_modulos_sincronizacion"){
		$smarty->assign("filtro_fechas_1",$fcha_filtros);//enviamos la variable de rango de fechas si existe
	}
/*fin de cambio Oscar 14.08.2018*/
	$smarty->assign("datosInvisibles", $datosInvisibles);

	$smarty->assign("letSalir", "¿Desea salir de este formulario sin guardar?");

/*implementación Oscar 03.05.2018 para asignar el tipo de perfil en contenido visual*/
	$smarty->assign("tipo_perfil",$perfil_usuario);
/*fin de cambio oscar 03.05.2018*/

/*implementacion Oscar 2021 para editar */
	if (isset ($_GET['is_list_transfer']) && $_GET['is_list_transfer'] == 1 ){
		$smarty->assign("special_transfers", 1);
	}
/*Fin de cambio Oscar 2021*/
	$smarty->display('general/contenido.tpl');

	mysql_query("COMMIT");

	/*
		Creado por: AF
		Fecha: 2020-09-19
		Funcionalidad: Invocación de servicio para crear/actualizar producto en BD(s) de facturación
	*/
	if ($tabla == 'ec_productos' && $no_tabla == 0 && ($accion=='insertar' || $accion == 'actualizar') && !empty($llave)){
			error_log('CL - LOG Productos: Ejecuta acción para guardar producto en facturación, idProducto: '.$llave);
			//Recupera token
			$sql = "select token from api_token where id_user=0 and expired_in > now() limit 1;";
			$respuesta=mysql_fetch_assoc(mysql_query($sql));
			$token = $respuesta['token'];
			//echo 'here_1';
			//Valida token
			//echo 'here_2';
					//Recupera path de servicios
					$sql = "select a.value from api_config a where a.key='api' and a.name='path' limit 1;";
					$respuesta=mysql_fetch_assoc(mysql_query($sql));
					$path = $respuesta['value'];
				
			if (! $token ) {
				$token = getTokenByUser( $path, 'admin', 'oscarmendoza' ); 
			}
					//Valida path
					if ($path) {
							//Prepar petición
							$data = array(
									'productos' => array(
										array(
										'idProducto' => $llave
										)
									)
							);
							$post_data = json_encode($data);
							error_log('CL - LOG Petición: '. $post_data);
							// Inicializa curl request
							$crl = curl_init($path.'/rest/v1/productos/nuevoFact');
							curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($crl, CURLINFO_HEADER_OUT, true);
							curl_setopt($crl, CURLOPT_POST, true);
							curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
							curl_setopt($crl, CURLOPT_HTTPHEADER, array(
									'Content-Type: application/json',
									'token: ' . $token)
							);
							// Ejecuta petición
							$result = curl_exec($crl);
							error_log('CL - LOG Respuesta: '.$result);
							// Cierra curl sesión
							curl_close($crl);
					}
			/*}else{
			//	echo 'no hay token';
			}*/
	}


	function getTokenByUser( $path, $user, $pass ){
		//Prepar petición
		$data = array(
				'user' => $user,
					'password' => $pass
		);
		$post_data = json_encode($data);
		error_log('CL - LOG Petición: '. $post_data);
		// Inicializa curl request
		$crl = curl_init($path . '/rest/v1/token');
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($crl, CURLINFO_HEADER_OUT, true);
		curl_setopt($crl, CURLOPT_POST, true);
		curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($crl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'token: ' . $token)
		);
		// Ejecuta petición
		$result = curl_exec($crl);
		//error_log('CL - LOG Respuesta: '.$result);
		// Cierra curl sesión
		curl_close($crl);
		$response = json_decode($result);
		return $response->result->access_token;
	}




?>
