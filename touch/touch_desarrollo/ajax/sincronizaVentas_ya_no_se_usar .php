<?php
//incluimos librería de conexión
	if(!include('../../conexionDoble.php')){
		die('No se encontró el archivo de conexión!!!...');
	}
	if($user_tipo_sistema=='linea'){
		die("No se puede sincronizar porque el sistema es Línea!!!!");
	}

	$libera="UPDATE ec_sincronizacion SET en_proceso=0 WHERE id_sincronizacion=3";

//verificamos que el servidor este libre...
	$verif="SELECT en_proceso FROM ec_sincronizacion WHERE id_sincronizacion=3";
	$v=mysql_query($verif,$local) or die("Error al checar status de servidor de ventas Local!!!\n\n".$v."\n\n".mysql_error($local));
	$estado_servidor=mysql_fetch_row($v);

	if($estado_servidor[0]==1){
		die('1.-Servidor Ocupado!!!');
	}
//si el servidor esta libre lo ocupamos 
	$ocupa="UPDATE ec_sincronizacion SET en_proceso=1 WHERE id_sincronizacion=3";
	$ocupar=mysql_query($ocupa,$local)or die("Error al desocupar servidor de Ventas!!!\n\n".$ocupa."\n\n".mysql_error($local));
//echo "\n\n1.-Ocupa Servidor\n\n";
//declaramos contadores
	$contSub=0;
	$contBaj=0;
	$pagosNo="";
	$impresiones="";//variable para guardar ids que generan impresiones


	for($i=0;$i<=1;$i++){
	//declaramos BD de la que se consulta info
		$extraer=$linea;
		$nom_extraer=' linea ';
	//declaramos BD de la que se inseta info
		$insertar=$local;
		$nom_insertar=' local ';
		if($i==1){//invertimos valores de conexión
		//declaramos BD de la que se consulta info
			$extraer=$local;
			$nom_extraer=' local ';
		//declaramos BD de la que se inseta info
			$insertar=$linea;
			$nom_insertar=' linea ';
		}
	//extraemos los registros de sincronización
		$sql="SELECT /*0*/id_registro_sincronizacion,/*1*/tabla,/*2*/id_registro,/*3*/tipo,/*4*/id_modulo_sincronizacion,/*5*/now(),/*6*/id_sucursal,/*7*/descripcion,/*8*/sucursal_de_cambio 
				FROM ec_sincronizacion_registros WHERE id_equivalente=0";
				
		if($i==0){
			$sql.=" AND id_sucursal=".$user_sucursal;
		}else if($i==1){
			$sql.=" AND id_sucursal=-1";
		}
		//echo $sql;
		$eje=mysql_query($sql,$extraer);
		if(!$eje){
			liberaServidor($local);
			die("Error al consultar registros de sincronización de sucursales en ".$nom_extraer."!!!!\n\n".$sql."\n\n".mysql_error($extraer));
		}
	//	echo mysql_num_rows($eje);
		while($res=mysql_fetch_row($eje)){
		//marcamos el inicio de transacciones
			mysql_query("BEGIN",$insertar);
			mysql_query("BEGIN",$extraer);
		//insertamos el registro de sincronización en la BD 
			$sql="INSERT INTO ec_sincronizacion_registros VALUES(null,'$res[8]','$res[6]','$res[1]','$res[2]','$res[3]','$res[4]',\"$res[7]\",'$res[5]',$res[0],0)";
			$eje_reg=mysql_query($sql,$insertar);
			if(!$eje_reg){
				$error=mysql_error($insertar);
				mysql_query("ROLLBACK",$insertar);//cancelamos transacción
				mysql_query("ROLLBACK",$extraer);//cancelamos transacción
				liberaServidor($local);
				die("Error al insertar el registro de sincronización!!!\n\n".$sql."\n\n".$error);
			}
	//		echo $sql.'<br>';
		//capturamos el id del registro
			$id_equiv_sinc=mysql_insert_id($insertar);

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Instrucciones de restauración														  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_respaldos'){//restauración desde línea
				$sql=$res[7];
				$eje=mysql_query($sql,$insertar);
				if(!$eje_reg){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar el registro de restauración!!!\n\n".$sql."\n\n".$error);
				}		
			}//fin de si es restauración

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos Sucursales															  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_sucursales'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/s.id_sucursal,/*1*/s.nombre,/*2*/s.telefono,/*3*/s.direccion,/*4*/s.descripcion,/*5*/s.id_razon_social,/*6*/u.id_equivalente,
						  /*7*/s.activo,/*8*/s.logo,/*9*/s.multifacturacion,/*10*/s.id_precio,/*11*/s.descuento,/*12*/s.prefijo,/*13*/s.usa_oferta,
						  /*14*/s.alertas_resurtimiento,/*15*/s.id_estacionalidad,/*16*/s.alta,/*17*/s.acceso,/*18*/s.min_apart,/*19*/s.dias_resurt,
						  /*20*/s.factor_estacionalidad_minimo,/*21*/s.factor_estacionalidad_medio,/*22*/s.factor_estacionalidad_final,/*23*/0,
						  /*24*/s.lista_precios_externa,/*25*/s.sufijo_externo,/*26*/s.almacen_externo,/*27*/s.mostrar_ubicacion,/*28*/s.verificar_inventario,
						  /*29*/s.verificar_inventario_externo,/*30*/s.requiere_info_cliente,/*31*/s.ticket_venta,/*32*/s.ticket_reimpresion,
						  /*33*/s.ticket_apartado,/*34*/s.permite_transferencias,/*35*/s.descripcion_sistema,/*36*/s.intervalo_sinc,/*37*/s.mostrar_alfanumericos 
						  FROM sys_sucursales s
						  LEFT JOIN sys_users u ON s.id_encargado=u.id_usuario
						  WHERE s.id_sucursal=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nueva sucursal en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){//si es nueva insersión
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM sys_sucursales WHERE id_sucursal='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe la nueva sucursal en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}

						}//fin de comprobación
					//concatenamos el campo de id//si es nueva inserión
						/*0*/$consulta.="id_sucursal='".$row[0]."',";
					}			
					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="telefono='".$row[2]."',";
					/*3*/$consulta.="direccion='".$row[3]."',";
					/*4*/$consulta.="descripcion='".$row[4]."',";
					/*5*/$consulta.="id_razon_social='".$row[5]."',";
					/*6*/$consulta.="id_encargado='".$row[6]."',";
					/*7*/$consulta.="activo='".$row[7]."',";
					/*8*/$consulta.="logo='".$row[8]."',";
					/*9*/$consulta.="multifacturacion='".$row[9]."',";
					/*10*/$consulta.="id_precio='".$row[10]."',";
					/*11*/$consulta.="descuento='".$row[11]."',";
					/*12*/$consulta.="prefijo='".$row[12]."',";
					/*13*/$consulta.="usa_oferta='".$row[13]."',";
					/*14*/$consulta.="alertas_resurtimiento='".$row[14]."',";
					/*15*/$consulta.="id_estacionalidad='".$row[15]."',";
					/*16*/$consulta.="alta='".$row[16]."',";
					/*if($res[3]==1){//si es incersión (se aparta para que no se cambien los accesos cuando se toman los valores desde otra sucursal)
					/*17*$consulta.="acceso='".$row[17]."',";
					}*/			
					/*18*/$consulta.="min_apart='".$row[18]."',";
					/*19*/$consulta.="dias_resurt='".$row[19]."',";
					/*20*/$consulta.="factor_estacionalidad_minimo='".$row[20]."',";
					/*21*/$consulta.="factor_estacionalidad_medio='".$row[21]."',";
					/*22*/$consulta.="factor_estacionalidad_final='".$row[22]."',";
					/*23*/$consulta.="sincronizar=".$row[23].",";
					/*24*/$consulta.="lista_precios_externa='".$row[24]."',";
					/*25*/$consulta.="sufijo_externo='".$row[25]."',";
					/*26*/$consulta.="almacen_externo='".$row[26]."',";
					/*27*/$consulta.="mostrar_ubicacion='".$row[27]."',";
					/*28*/$consulta.="verificar_inventario='".$row[28]."',";
					/*29*/$consulta.="verificar_inventario_externo='".$row[29]."',";
					/*30*/$consulta.="requiere_info_cliente='".$row[30]."',";
					/*31*/$consulta.="ticket_venta='".$row[31]."',";
					/*32*/$consulta.="ticket_reimpresion='".$row[32]."',";
					/*33*/$consulta.="ticket_apartado='".$row[33]."',";
					/*34*/$consulta.="permite_transferencias='".$row[34]."',";
					/*35*$consulta.="descripcion_sistema='".$row[35]."',";*/
					/*36*/$consulta.="intervalo_sinc='".$row[36]."',";
					/*36*/$consulta.="mostrar_alfanumericos='".$row[37]."'";
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_sucursal='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar sucursal en: ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es actualizacion o eliminar
						$consulta="UPDATE sys_sucursales SET id_sucursal=$row[0],sincronizar=0 WHERE id_sucursal=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de la nueva sucursal en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".mysql_error($insertar));
						}
					}
				}
			}//fin de si tabla es sucursales

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos perfiles de usuarios 													  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_users_perfiles'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_perfil,/*1*/nombre,/*2*/admin,/*3*/observaciones,/*4*/0 FROM sys_users_perfiles WHERE id_perfil=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nuevo perfil de usuario en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						/*0*/$consulta.="id_perfil=".$row[0].",";
						//si es nueva insersión
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM sys_users_perfiles WHERE id_perfil='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe la nueva sucursal en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}

						}//fin de comprobación
					}
					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="admin='".$row[2]."',";
					/*3*/$consulta.="observaciones='".$row[3]."',";
					/*4*/$consulta.="sincronizar=".$row[4];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_perfil='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
					die("Error al insertar perfil de usuario en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es insertar
						$consulta="UPDATE sys_users_perfiles SET id_perfil=$row[0],sincronizar=0 WHERE id_perfil=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de perfil de usuario en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es perfiles de usuario

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos usuarios   															  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_users'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_usuario,/*1*/nombre,/*2*/apellido_paterno,/*3*/apellido_materno,/*4*/login,/*5*/telefono,/*6*/correo,
						  /*7*/contrasena,/*8*/edad,/*9*/fecha_nacimiento,/*10*/puesto,/*11*/administrador,/*12*/id_sucursal,/*13*/autorizar_req,/*14*/sincroniza,
						  /*15*/recibe_correo,/*16*/vende_mayoreo,/*17*/pin_descuento,/*18*/pago_dia,/*19*/minimo_horas,/*20*/pago_hora,
						  /*21*/sexo,/*22*/tipo_perfil,/*23*/id_equivalente,/*24*/0 FROM sys_users WHERE id_usuario=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nuevo usuario en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						/*0*/$consulta.="id_usuario=null,";
					}			
					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="apellido_paterno='".$row[2]."',";
					/*3*/$consulta.="apellido_materno='".$row[3]."',";
					/*4*/$consulta.="login='".$row[4]."',";
					/*5*/$consulta.="telefono='".$row[5]."',";
					/*6*/$consulta.="correo='".$row[6]."',";
					/*7*/$consulta.="contrasena='".$row[7]."',";
					/*8*/$consulta.="edad='".$row[8]."',";
					/*9*/$consulta.="fecha_nacimiento='".$row[9]."',";
					/*10*/$consulta.="puesto='".$row[10]."',";
					/*11*/$consulta.="administrador='".$row[11]."',";
					/*12*/$consulta.="id_sucursal='".$row[12]."',";
					/*13*/$consulta.="autorizar_req='".$row[13]."',";
					/*14*/$consulta.="sincroniza='".$row[14]."',";
					/*15*/$consulta.="recibe_correo='".$row[15]."',";
					/*16*/$consulta.="vende_mayoreo='".$row[16]."',";
					/*17*/$consulta.="pin_descuento='".$row[17]."',";
					/*18*/$consulta.="pago_dia='".$row[18]."',";
					/*19*/$consulta.="minimo_horas='".$row[19]."',";
					/*20*/$consulta.="pago_hora='".$row[20]."',";
					/*21*/$consulta.="sexo='".$row[21]."',";
					/*22*/$consulta.="tipo_perfil='".$row[22]."',";//id_equivalente
					/*23*/$consulta.="id_equivalente=".$row[0].",";
					/*24*/$consulta.="sincronizar=".$row[24];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_equivalente='".$res[2]."'";
					if($row[12]==-1){
						$consulta.=" AND id_sucursal='-1'";
					}else{
						$consulta.=" AND id_sucursal='".$user_sucursal."'";
					}
				}
				$ejecuta=mysql_query($consulta,$insertar);

				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar usuario en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
					if($res[3]==1){
					//actualizamos el id_equivalente del usuario y a la vez preparamos registro para la sigueine sincronización
						$equivalente=mysql_insert_id($insertar);
						$sql_2="UPDATE sys_users set id_equivalente=$equivalente,sincronizar=0 WHERE id_usuario=$row[0]";
						$eje_2=mysql_query($sql_2,$extraer);
						if(!$eje_2){
							$error=mysql_error($extraer);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al asignar el id equivalente de usuario en ".$nom_extraer."!!!!\n\n".$sql_2."\n\n".$error);
						}
						$consulta="UPDATE sys_users SET id_usuario=$row[0],sincronizar=0 WHERE id_usuario=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro del nuevo usuario en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si la tabla es usuarios

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos permisos    															  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_permisos'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_permiso,/*1*/id_perfil,/*2*/id_menu,/*3*/ver,/*4*/modificar,/*5*/eliminar,/*6*/nuevo,
						  /*7*/imprimir,/*8*/generar,/*9*/0 FROM sys_permisos WHERE id_permiso=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nuevo permiso en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						/*0*/$consulta.="id_permiso=null,";
					}			
					/*1*/$consulta.="id_perfil='".$row[1]."',";
					/*2*/$consulta.="id_menu='".$row[2]."',";
					/*3*/$consulta.="ver='".$row[3]."',";
					/*4*/$consulta.="modificar='".$row[4]."',";
					/*5*/$consulta.="eliminar='".$row[5]."',";
					/*6*/$consulta.="nuevo='".$row[6]."',";
					/*7*/$consulta.="imprimir='".$row[7]."',";
					/*8*/$consulta.="generar='".$row[8]."',";
					/*9*/$consulta.="sincronizar=".$row[9];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_perfil='".$row[1]."' AND id_menu='".$row[2]."'";//no se eliminarán permisos
				}/*
				liberaServidor($local);
				die($consulta);*/
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar permiso en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE sys_permisos SET id_perfil=$row[1],id_menu=$row[2],sincronizar=0 WHERE id_perfil=$row[1] AND id_menu=$row[2]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de la nueva sucursal en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos categorías (familias)													  *
			*																																						  *
			**********************************************************************************************************************************************************/

			if($res[1]=='ec_categoria'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar		
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_categoria,/*1*/nombre,/*2*/imagen,/*3*/0 FROM ec_categoria WHERE id_categoria=$res[2]";	
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){		
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nueva categoría en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_categoria WHERE id_categoria='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){			
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe la categoría en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización		
								$consulta="UPDATE ".$res[1]." SET ";
							}

						}//fin de comprobación
						/*0*/$consulta.="id_categoria='".$row[0]."',";
					}			

					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="imagen='".$row[2]."',";
					/*3*/$consulta.="sincronizar=".$row[3];

				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_categoria='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);

				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar categoría en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es actualizacion o eliminar
						$consulta="UPDATE ec_categoria SET id_categoria=$row[0],sincronizar=0 WHERE id_categoria=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de categoría en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es categorías (familias)

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos subcategorías 														  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_subcategoria'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_subcategoria,/*1*/nombre,/*2*/id_categoria,/*3*/imagen,/*4*/surtir_presentacion,/*5*/0 FROM ec_subcategoria WHERE id_subcategoria=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nueva subcategoría en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_subcategoria WHERE id_subcategoria='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe la subcategoría en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_subcategoria='".$row[0]."',";
					}			

					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="id_categoria='".$row[2]."',";
					/*3*/$consulta.="imagen='".$row[3]."',";
					/*4*/$consulta.="surtir_presentacion='".$row[4]."',";
					/*5*/$consulta.="sincronizar=".$row[5];

				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_subcategoria='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar subcategoría en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es actualizacion o eliminar
						$consulta="UPDATE ec_subcategoria SET id_subcategoria=$row[0],sincronizar=0 WHERE id_subcategoria=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de la nueva subcategoría en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es subcategoría

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos subtipos		 														  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_subtipos'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_subtipos,/*1*/nombre,/*2*/id_tipo,/*3*/0 FROM ec_subtipos WHERE id_subtipos=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nuevo subtipo en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_subtipos WHERE id_subtipos='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el subtipo en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación		
						/*0*/$consulta.="id_subtipos='".$row[0]."',";
					}			

					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="id_tipo='".$row[2]."',";
					/*3*/$consulta.="sincronizar=".$row[3];

				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_subtipos='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar subtipo en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es actualizacion o eliminar
						$consulta="UPDATE ec_subtipos SET id_subtipos=$row[0],sincronizar=0 WHERE id_subtipos=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro del nuevo subtipo en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es subtipos

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos tamaños		 														  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_tamanos'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar		
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminará	
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){	
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_tamanos,/*1*/nombre,/*2*/id_categoria,/*3*/0 FROM ec_tamanos WHERE id_tamanos=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nuevo tamaño en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_tamanos WHERE id_tamanos='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el tamaño en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_tamanos='".$row[0]."',";
					}			

					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="id_categoria='".$row[2]."',";
					/*3*/$consulta.="sincronizar=".$row[3];

				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_tamanos='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar tamaño en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es insercion
						$consulta="UPDATE ec_tamanos SET id_tamanos=$row[0],sincronizar=0 WHERE id_tamanos=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción	
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro del nuevo tamaño en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es tamaños

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos colores		 														  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_colores'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_colores,/*1*/nombre,/*2*/id_categoria,/*3*/0 FROM ec_colores WHERE id_colores=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nuevo color en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_colores WHERE id_colores='$res[2]'";		
							$verificar=mysql_query($verifica_anterior,$insertar);				
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el color en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_colores='".$row[0]."',";
					}			

					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="id_categoria='".$row[2]."',";
					/*3*/$consulta.="sincronizar=".$row[3];

				}//	fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_colores='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al sincronizar color en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
					//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_colores SET id_colores=$row[0],sincronizar=0 WHERE id_colores=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);	
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro del nuevo color en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es colores

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos número de luces 														  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_numero_luces'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_numero_luces,/*1*/nombre,/*2*/id_categoria,/*3*/0 FROM ec_numero_luces WHERE id_numero_luces=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nuevo numero de luces en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_numero_luces WHERE id_numero_luces='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el numero de luces en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_numero_luces='".$row[0]."',";
					}			

					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="id_categoria='".$row[2]."',";
					/*3*/$consulta.="sincronizar=".$row[3];
					
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_numero_luces='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al sincronizar numero de luces en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
					//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_numero_luces SET id_numero_luces=$row[0],sincronizar=0 WHERE id_numero_luces=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de numero de luces en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es número de luces

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos productos																  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_productos'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_productos,/*1*/clave,/*2*/nombre,/*3*/id_categoria,/*4*/id_subcategoria,/*5*/precio_venta_mayoreo,/*6*/precio_compra,/*7*/marca,/*8*/min_existencia,
					/*9*/imagen,/*10*/observaciones,/*11*/inventariado,/*12*/es_maquilado,/*13*/genera_iva,/*14*/genera_ieps,/*15*/porc_iva,/*16*/porc_ieps,/*17*/desc_gral,/*18*/nombre_etiqueta,
					/*19*/orden_lista,/*20*/ubicacion_almacen,/*21*/codigo_barras_1,/*22*/codigo_barras_2,/*23*/codigo_barras_3,/*24*/codigo_barras_4,/*25*/id_subtipo,/*26*/maximo_existencia,
					/*27*/id_numero_luces,/*28*/id_color,/*29*/id_tamano,/*30*/habilitado,/*31*/omitir_alertas,/*32*/existencia_media,/*33*/muestra_paleta,/*34*/es_resurtido,/*35*/alta,
					/*36*/ultima_modificacion,/*37*/0 FROM ec_productos WHERE id_productos=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de nuevo producto en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_productos WHERE id_productos='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el producto en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_productos='".$row[0]."',";
					}			
					/*1*/$consulta.="clave='".$row[1]."',";
					/*2*/$consulta.="nombre='".$row[2]."',";			
					/*3*/$consulta.="id_categoria='".$row[3]."',";
					/*4*/$consulta.="id_subcategoria='".$row[4]."',";			
					/*5*/$consulta.="precio_venta_mayoreo='".$row[5]."',";
					/*6*/$consulta.="precio_compra='".$row[6]."',";			
					/*7*/$consulta.="marca='".$row[7]."',";
					/*8*/$consulta.="min_existencia='".$row[8]."',";			
					/*9*/$consulta.="imagen='".$row[9]."',";
					/*10*/$consulta.="observaciones='".$row[10]."',";			
					/*11*/$consulta.="inventariado='".$row[11]."',";
					/*12*/$consulta.="es_maquilado='".$row[12]."',";			
					/*13*/$consulta.="genera_iva='".$row[13]."',";
					/*14*/$consulta.="genera_ieps='".$row[14]."',";			
					/*15*/$consulta.="porc_iva='".$row[15]."',";
					/*16*/$consulta.="porc_ieps='".$row[16]."',";			
					/*17*/$consulta.="desc_gral='".$row[17]."',";
					/*18*/$consulta.="nombre_etiqueta='".$row[18]."',";			
					/*19*/$consulta.="orden_lista='".$row[19]."',";
					/*20*/$consulta.="ubicacion_almacen='".$row[20]."',";			
					/*21*/$consulta.="codigo_barras_1='".$row[21]."',";
					/*22*/$consulta.="codigo_barras_2='".$row[22]."',";			
					/*23*/$consulta.="codigo_barras_3='".$row[23]."',";
					/*24*/$consulta.="codigo_barras_4='".$row[24]."',";			
					/*25*/$consulta.="id_subtipo='".$row[25]."',";
					/*26*/$consulta.="maximo_existencia='".$row[26]."',";			
					/*27*/$consulta.="id_numero_luces='".$row[27]."',";
					/*28*/$consulta.="id_color='".$row[28]."',";			
					/*29*/$consulta.="id_tamano='".$row[29]."',";
					/*30*/$consulta.="habilitado='".$row[30]."',";			
					/*31*/$consulta.="omitir_alertas='".$row[31]."',";
					/*32*/$consulta.="existencia_media='".$row[32]."',";			
					/*33*/$consulta.="muestra_paleta='".$row[33]."',";
					/*34*/$consulta.="es_resurtido='".$row[34]."',";			
					/*35*/$consulta.="alta='".$row[35]."',";
					/*36*/$consulta.="ultima_modificacion='".$row[36]."',";
					/*37*/$consulta.="sincronizar=".$row[37];					
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_productos='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al sincronizar producto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
					//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_productos SET id_productos=$row[0],sincronizar=0 WHERE id_productos=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro del nuevo producto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}		
			}//fin de si tabla es productos

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos detalles de productos													  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_productos_detalle'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_producto_detalle,/*1*/id_producto,/*2*/id_producto_ordigen,/*3*/cantidad,/*4*/alta,/*5*/ultima_modificacion,/*6*/0 
							FROM ec_productos_detalle WHERE id_producto_detalle=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de detalle producto en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_productos_detalle WHERE id_producto_detalle='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el detalle del producto en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_producto_detalle='".$row[0]."',";
					}			
					/*1*/$consulta.="id_producto='".$row[1]."',";
					/*2*/$consulta.="id_producto_ordigen='".$row[2]."',";			
					/*3*/$consulta.="cantidad='".$row[3]."',";
					/*4*/$consulta.="alta='".$row[4]."',";			
					/*5*/$consulta.="ultima_modificacion='".$row[5]."',";
					/*6*/$consulta.="sincronizar=".$row[6];
					}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
						//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_producto_detalle='".$res[2]."'";
				}	
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar el detalle del producto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
					//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_productos_detalle SET id_producto_detalle=$row[0],sincronizar=0 WHERE id_producto_detalle=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de detalle de producto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es detalle de productos

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos maquila																  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_maquila'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/maq.id_maquila,/*1*/maq.folio,/*2*/maq.fecha,/*3*/u.id_equivalente,/*4*/maq.id_producto,/*5*/maq.cantidad,/*6*/maq.id_sucursal,/*7*/maq.activa,
								/*8*/maq.id_equivalente,/*9*/0 
							FROM ec_maquila maq
							LEFT JOIN sys_users u ON maq.id_usuario=u.id_usuario /*AND maq.id_sucursal=u.id_sucursal */
							WHERE id_maquila=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de maquila en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						/*0*/$consulta.="id_maquila=null,";//
					}			
					/*1*/$consulta.="folio='".$row[1]."',";
					/*2*/$consulta.="fecha='".$row[2]."',";			
					/*3*/$consulta.="id_usuario='".$row[3]."',";
					/*4*/$consulta.="id_producto='".$row[4]."',";
					/*5*/$consulta.="cantidad='".$row[5]."',";
					/*6*/$consulta.="id_sucursal='".$row[6]."',";
					/*7*/$consulta.="activa='".$row[7]."',";
					/*8*/$consulta.="id_equivalente=".$res[2].",";
					/*9*/$consulta.="sincronizar=".$row[9];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_equivalente='".$res[2]."' AND id_sucursal=".$user_sucursal;
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al sincronizar la maquila en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
					if($res[3]==1){
					//actualizamos el id_equivalenete de la maquila
						$equivalente=mysql_insert_id($insertar);
						$sql_2="UPDATE ec_maquila set id_equivalente=$equivalente,sincronizar=0 WHERE id_maquila=$row[0]";
						$eje_2=mysql_query($sql_2,$extraer);
						if(!$eje_2){
							$error=mysql_error($extraer);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al asignar el id equivalente de la maquila en ".$nom_extraer."!!!!\n\n".$sql_2."\n\n".$error);
						}
					/*}
					//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción*/
						$consulta="UPDATE ec_maquila SET id_maquila=$row[0],sincronizar=0 WHERE id_maquila=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de la nueva maquila en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}	
			}//fin de si tabla es mquila	


			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos presentaciones de productos											  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_productos_presentaciones'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_producto_presentacion,/*1*/id_producto,/*2*/nombre,/*3*/cantidad,/*4*/0 
							FROM ec_productos_presentaciones WHERE id_producto_presentacion=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de presentación de producto en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_productos_presentaciones WHERE id_producto_presentacion='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe la presentación del producto en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_producto_presentacion='".$row[0]."',";
					}			
					/*1*/$consulta.="id_producto='".$row[1]."',";
					/*2*/$consulta.="nombre='".$row[2]."',";			
					/*3*/$consulta.="cantidad='".$row[3]."',";
					/*4*/$consulta.="sincronizar=".$row[4];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_producto_presentacion='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar la presentación del producto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
					//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_productos_presentaciones SET id_producto_presentacion=$row[0],sincronizar=0 WHERE id_producto_presentacion=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de la nueva presentación de producto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de dsi tabla es presentacines de productos

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos sucursales por producto											  	  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_sucursales_producto'){
				$consulta="";
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==2){/*$res[3]==1||   no se ocupa inserción por sinc*/
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id,/*1*/id_sucursal,/*2*/id_producto,/*3*/estado_presentacion,/*4*/num_presentacion,/*5*/minimo_surtir,/*6*/estado_suc,
					/*7*/nombre_presentacion,/*8*/ubicacion_almacen_sucursal,/*9*/ultima_modificacion,/*10*/0,/*11*/es_externo FROM sys_sucursales_producto WHERE id=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de sucursal-producto en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);	
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					
					/*1*/$consulta.="id_sucursal='".$row[1]."',";
					/*2*/$consulta.="id_producto='".$row[2]."',";			
					/*3*/$consulta.="estado_presentacion='".$row[3]."',";
					/*4*/$consulta.="num_presentacion='".$row[4]."',";
					/*5*/$consulta.="minimo_surtir='".$row[5]."',";
					/*6*/$consulta.="estado_suc=".$row[6].",";
					/*7*/$consulta.="nombre_presentacion='".$row[7]."',";
					/*8*/$consulta.="ubicacion_almacen_sucursal='".$row[8]."',";
					/*9*/$consulta.="ultima_modificacion='".$row[9]."',";
					/*10*/$consulta.="sincronizar=".$row[10].",";
					/*11*/$consulta.="es_externo=".$row[11];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_producto='".$row[2]."' AND id_sucursal='".$row[1]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar la sucursal-producto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}
			}//fin de si tabla es sucursal producto

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos listas de precios														  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_precios'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/pr.id_precio,/*1*/pr.fecha,/*2*/pr.nombre,/*3*/pr.id_equivalente,/*4*/u.id_equivalente,/*5*/pr.ultima_modificacion,/*6*/pr.ultima_actualizacion,/*7*/0,/*8*/pr.es_externo 
							FROM ec_precios pr 
							LEFT JOIN sys_users u ON pr.id_usuario=u.id_usuario
							WHERE pr.id_precio=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos del precio en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_precios WHERE id_precio='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe la lista de precios en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_precio='".$row[0]."',";
					}			
					/*1*/$consulta.="fecha='".$row[1]."',";
					/*2*/$consulta.="nombre='".$row[2]."',";			
					/*3*/$consulta.="id_usuario='".$row[4]."',";		
					/*4*/$consulta.="id_equivalente='".$row[3]."',";
					/*5*/$consulta.="es_externo='".$row[8]."',";				
					/*6*/$consulta.="ultima_modificacion='".$row[5]."',";		
					/*7*/$consulta.="ultima_actualizacion='".$row[6]."',";
					/*8*/$consulta.="sincronizar=".$row[7];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
				//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_precio='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);	
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar el precio en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_precios SET id_precio=$row[0],sincronizar=0 WHERE id_precio=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de precio en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin se si tabla es precios 
			
			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos precios 																  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_precios_detalle'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_precio_detalle,/*1*/id_precio,/*2*/de_valor,/*3*/a_valor,/*4*/precio_venta,/*5*/precio_etiqueta,/*6*/id_producto,/*7*/es_oferta,
									/*8*/alta,/*9*/ultima_actualizacion,/*10*/0 
							FROM ec_precios_detalle 
							WHERE id_precio_detalle=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar detalles de precios en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);		
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_precios_detalle WHERE id_precio_detalle='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el detalle de precios en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_precio_detalle='".$row[0]."',";//'".."'
					}			
					/*1*/$consulta.="id_precio='".$row[1]."',";
					/*2*/$consulta.="de_valor='".$row[2]."',";			
					/*3*/$consulta.="a_valor='".$row[3]."',";		
					/*4*/$consulta.="precio_venta='".$row[4]."',";		
					/*5*/$consulta.="precio_etiqueta='".$row[5]."',";	
					/*6*/$consulta.="id_producto='".$row[6]."',";	
					/*7*/$consulta.="es_oferta='".$row[7]."',";	
					/*8*/$consulta.="alta='".$row[8]."',";		
					/*9*/$consulta.="ultima_actualizacion='".$row[9]."',";
					/*10*/$consulta.="sincronizar=".$row[10];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_precio_detalle='".$res[2]."'";//cambiado por Oscar 24.10.2018
				}

				//die($consulta);
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar detalle del precio en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".mysql_error($insertar));
				}else{
					//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_precios_detalle SET sincronizar=0 WHERE id_precio_detalle=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de detalle de precio en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es detalle de precios
//die('no entró');

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos almacenes																  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_almacen'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_almacen,/*1*/nombre,/*2*/es_almacen,/*3*/prioridad,/*4*/id_sucursal,/*5*/ultima_sincronizacion,/*6*/ultima_actualizacion,/*7*/0,/*8*/es_externo 
							FROM ec_almacen 
							WHERE id_almacen=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar almacenes en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_almacen WHERE id_almacen='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el almacen en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_almacen='".$row[0]."',";
					}			
					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="es_almacen='".$row[2]."',";			
					/*3*/$consulta.="prioridad='".$row[3]."',";		
					/*4*/$consulta.="id_sucursal='".$row[4]."',";
					/*8*/$consulta.="es_externo=".$row[8].",";		
					/*5*/$consulta.="ultima_sincronizacion='".$row[5]."',";	
					/*6*/$consulta.="ultima_actualizacion='".$row[6]."',";	
					/*7*/$consulta.="sincronizar=".$row[7];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_almacen='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al sincronizar el almacen en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_almacen SET id_almacen=$row[0],sincronizar=0 WHERE id_almacen=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro del nuevo almacen en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es almacen

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos conceptos-gastos														  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_conceptos_gastos'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
	//si es insertar o actualizar
		if($res[3]==1||$res[3]==2){
		//consultamos los datos en la BD que se insertó/actualizó
			$sql_1="SELECT /*0*/id_concepto,/*1*/nombre,/*2*/0 
					FROM ec_conceptos_gastos 
					WHERE id_concepto=$res[2]";
			$eje_1=mysql_query($sql_1,$extraer);
			if(!$eje_1){
				$error=mysql_error($extraer);
				mysql_query("ROLLBACK",$insertar);//cancelamos transacción
				mysql_query("ROLLBACK",$extraer);//cancelamos transacción
				liberaServidor($local);
				die("Error al consultar conceptos gastos en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
			}
			$row=mysql_fetch_row($eje_1);
		//armamos los datos
			$consulta.="SET ";
			if($res[3]==1){
				if($i==0){//si la inserción es desde línea
				//comprobamos si el registro ya existe
					$verifica_anterior="SELECT count(*) FROM ec_conceptos_gastos WHERE id_concepto='$res[2]'";
					$verificar=mysql_query($verifica_anterior,$insertar);
					if(!$verificar){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al verificar si ya existe el concepto de gasto en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
					}
					$existe=mysql_fetch_row($verificar);
					if($existe[0]==1){
						$res[3]=2;//cambiamos a actualización
						$consulta="UPDATE ".$res[1]." SET ";
					}
				}//fin de comprobación
				/*0*/$consulta.="id_concepto='".$row[0]."',";
			}			
			/*1*/$consulta.="nombre='".$row[1]."',";	
			/*2*/$consulta.="sincronizar=".$row[2];
		}//fin de if==1||2
		if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
			//$consulta.=" WHERE id_permiso='".$res[2]."'";
			$consulta.=" WHERE id_concepto='".$res[2]."'";
		}
		$ejecuta=mysql_query($consulta,$insertar);
		if(!$ejecuta){
			$error=mysql_error($insertar);
			mysql_query("ROLLBACK",$insertar);//cancelamos transacción
			mysql_query("ROLLBACK",$extraer);//cancelamos transacción
			liberaServidor($local);
			die("Error al insertar concepto gasto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
		}else{
		//actualizamos el registro para activar la siguiente sincronización
			if($res[3]==1){//si es inserción
				$consulta="UPDATE ec_conceptos_gastos SET id_concepto=$row[0],sincronizar=0 WHERE id_concepto=$row[0]";
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al preparar sincronizacion para el registro del nuevo concepto de gasto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}
			}
		}
			}//fin de si tabla es conceptos gastos


			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos gastos  																  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_gastos'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/g.id_gastos,/*1*/u.id_equivalente,/*2*/g.id_sucursal,/*3*/g.fecha,/*4*/g.hora,/*5*/g.id_concepto,/*6*/g.monto,/*7*/g.observaciones,/*8*/0 
							FROM ec_gastos g
							LEFT JOIN sys_users u ON g.id_usuario=u.id_usuario
							WHERE g.id_gastos=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar gastos en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
					/*0*/$consulta.="id_gastos=null,";//'".$row[0]."'
					}			
			/*1*/$consulta.="id_usuario='".$row[1]."',";	
			/*2*/$consulta.="id_sucursal='".$row[2]."',";	
			/*3*/$consulta.="fecha='".$row[3]."',";	
			/*4*/$consulta.="hora='".$row[4]."',";	
			/*5*/$consulta.="id_concepto='".$row[5]."',";
			/*6*/$consulta.="monto='".$row[6]."',";	
			/*7*/$consulta.="observaciones='".$row[7]."',";		
			/*8*/$consulta.="id_equivalente='".$row[0]."',";//id_equivalente		
			/*9*/$consulta.="sincronizar=".$row[8];
		}//fin de if==1||2
		if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
			//$consulta.=" WHERE id_permiso='".$res[2]."'";
			$consulta.=" WHERE id_equivalente='".$res[2]."' AND id_sucursal=".$user_sucursal;
		}
		$ejecuta=mysql_query($consulta,$insertar);
		if(!$ejecuta){
			$error=mysql_error($insertar);
			mysql_query("ROLLBACK",$insertar);//cancelamos transacción
			mysql_query("ROLLBACK",$extraer);//cancelamos transacción
			liberaServidor($local);
			die("Error al insertar gasto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
		}else{
			if($res[3]==1){
			//actualizamos el id_equivalenete del usuario
				$equivalente=mysql_insert_id($insertar);
				$sql_2="UPDATE ec_gastos set id_equivalente=$equivalente,sincronizar=0 WHERE id_gastos=$row[0]";
				$eje_2=mysql_query($sql_2,$extraer);
				if(!$eje_2){
					$error=mysql_error($extraer);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al asignar el id equivalente de gasto en ".$nom_extraer."!!!!\n\n".$sql_2."\n\n".$error);
				}
			//actualizamos el registro para activar la siguiente sincronización
		
				$consulta="UPDATE ec_gastos SET id_gastos=$row[0],sincronizar=0 WHERE id_gastos=$row[0]";
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al preparar sincronizacion para el registro del nuevo gasto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}
			}
		}
		}//fin de si tabla es gastos	

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               						Sincronizamos estacionalidades(solo actualización)												  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_estacionalidad'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
			$sql_1="SELECT /*0*/id_estacionalidad,/*1*/nombre,/*2*/id_periodo,/*3*/observaciones,/*4*/id_sucursal,/*5*/es_alta,/*6*/0 FROM ec_estacionalidad WHERE id_estacionalidad=$res[2]";
			$eje_1=mysql_query($sql_1,$extraer);
			if(!$eje_1){
				$error=mysql_error($extraer);
				mysql_query("ROLLBACK",$insertar);//cancelamos transacción
				mysql_query("ROLLBACK",$extraer);//cancelamos transacción
				liberaServidor($local);
				die("Error al consultar estacionalidad en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
			}
			$row=mysql_fetch_row($eje_1);
		//armamos los datos
			$consulta.="SET ";
			if($res[3]==1){
				if($i==0){//si la inserción es desde línea
				//comprobamos si el registro ya existe
					$verifica_anterior="SELECT count(*) FROM ec_estacionalidad WHERE id_estacionalidad='$res[2]'";
					$verificar=mysql_query($verifica_anterior,$insertar);
					if(!$verificar){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al verificar si ya existe la estacionalidad en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
					}
					$existe=mysql_fetch_row($verificar);
					if($existe[0]==1){
						$res[3]=2;//cambiamos a actualización
						$consulta="UPDATE ".$res[1]." SET ";
					}
				}//fin de comprobación
				/*0*/$consulta.="id_estacionalidad='".$row[0]."',";//null
			}			
			/*1*/$consulta.="nombre='".$row[1]."',";	
			/*2*/$consulta.="id_periodo='".$row[2]."',";	
			/*3*/$consulta.="observaciones='".$row[3]."',";	
			/*4*/$consulta.="id_sucursal='".$row[4]."',";	
			/*5*/$consulta.="es_alta='".$row[5]."',";
			/*6*/$consulta.="sincronizar=".$row[6];
		}//fin de if==1||2
		if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
			//$consulta.=" WHERE id_permiso='".$res[2]."'";
			$consulta.=" WHERE id_estacionalidad='".$res[2]."'";
		}
		$ejecuta=mysql_query($consulta,$insertar);
		if(!$ejecuta){
			$error=mysql_error($insertar);
			mysql_query("ROLLBACK",$insertar);//cancelamos transacción
			mysql_query("ROLLBACK",$extraer);//cancelamos transacción
			liberaServidor($local);
			die("Error al insertar estacionalidad en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
		}else{
		//actualizamos el registro para activar la siguiente sincronización
			if($res[3]==1){//si es inserción
				$consulta="UPDATE ec_estacionalidad SET id_estacionalidad=$row[0],sincronizar=0 WHERE id_estacionalidad=$row[0]";
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al preparar sincronizacion para el registro de la nueva estacionalidad en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}
			}
		}
			}//fin de si tabla se estacionalidad

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               						Sincronizamos estacionalidad Producto (solo actualización)										  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_estacionalidad_producto'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_estacionalidad_producto,/*1*/id_estacionalidad,/*2*/id_producto,/*3*/minimo,/*4*/medio,/*5*/maximo,/*6*/alta,/*7*/ultima_modificacion,/*8*/0 
						FROM ec_estacionalidad_producto WHERE id_estacionalidad_producto=$res[2]";				
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar detalle de estacionalidad en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						/*0*/$consulta.="id_estacionalidad_producto=null,";//'".$row[0]."'
					}			
					/*1*/$consulta.="id_estacionalidad='".$row[1]."',";	
					/*2*/$consulta.="id_producto='".$row[2]."',";	
					/*3*/$consulta.="minimo='".$row[3]."',";	
					/*4*/$consulta.="medio='".$row[4]."',";	
					/*5*/$consulta.="maximo='".$row[5]."',";
					/*6*/$consulta.="alta='".$row[6]."',";
					/*7*/$consulta.="ultima_modificacion='".$row[7]."',";
					/*8*/$consulta.="sincronizar=".$row[8];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_estacionalidad='".$row[1]."' AND id_producto='".$row[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al sincronizar estacionalidad_producto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_estacionalidad_producto SET id_estacionalidad_producto=$row[0],sincronizar=0 WHERE id_estacionalidad_producto=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de la nueva estacionalidad producto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es estacionalidad producto	

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos configuracion de correo												  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_conf_correo'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_configuracion,/*1*/smtp_server,/*2*/puerto,/*3*/smtp_user,/*4*/smtp_pass,/*5*/correo_envios,/*6*/nombre_correo,/*7*/iva,/*8*/ieps,
						/*9*/acceso_de,/*10*/acceso_a,/*11*/0 FROM ec_conf_correo WHERE id_configuracion=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						liberaServidor($local);
						die("Error al consultar configuración de correo en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".mysql_error($extraer));
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_conf_correo WHERE id_configuracion='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe la configuración de correo en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_configuracion='".$row[0]."',";//null
					}			
					/*1*/$consulta.="smtp_server='".$row[1]."',";			
					/*2*/$consulta.="puerto='".$row[2]."',";	
					/*3*/$consulta.="smtp_user='".$row[3]."',";	
					/*4*/$consulta.="smtp_pass='".$row[4]."',";	
					/*5*/$consulta.="correo_envios='".$row[5]."',";
					/*6*/$consulta.="nombre_correo='".$row[6]."',";	
					/*7*/$consulta.="iva='".$row[7]."',";		
					/*8*/$consulta.="ieps='".$row[8]."',";		
					/*9*/$consulta.="acceso_de='".$row[9]."',";		
					/*10*/$consulta.="acceso_a='".$row[10]."',";		
					/*11*/$consulta.="sincronizar=".$row[11];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_configuracion='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción		
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar configuración de correo en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
					//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_conf_correo SET id_configuracion=$row[0],sincronizar=0 WHERE id_configuracion=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de la nueva configuración de correo en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es configuración de correo

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               									Sincronizamos registros  de nómina													  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='ec_registro_nomina'){		
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/rn.id_registro_nomina,/*1*/rn.fecha,/*2*/rn.hora_entrada,/*3*/rn.hora_salida,/*4*/u.id_equivalente AS usuario,/*5*/rn.id_sucursal,/*6*/rn.id_equivalente,/*7*/0 
							FROM ec_registro_nomina rn
							LEFT JOIN sys_users u ON rn.id_empleado=u.id_usuario
							WHERE rn.id_registro_nomina=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar registro de nomina en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";			
					if($res[3]==1){
						/*0*/$consulta.="id_registro_nomina=null,";//'".$row[0]."'
					}			
					/*1*/$consulta.="fecha='".$row[1]."',";	
					/*2*/$consulta.="hora_entrada='".$row[2]."',";	
					/*3*/$consulta.="hora_salida='".$row[3]."',";	
					/*4*/$consulta.="id_empleado='".$row[4]."',";	
					/*5*/$consulta.="id_sucursal='".$row[5]."',";
					/*6*/$consulta.="id_equivalente='".$row[0]."',";//id_equivalente
					/*7*/$consulta.="sincronizar=".$row[7];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_equivalente='".$res[2]."' AND id_sucursal=".$user_sucursal;
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar registro de nomina en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
					if($res[3]==1){
					//actualizamos el id_equivalenete del registro de nomina
						$equivalente=mysql_insert_id($insertar);
						$sql_2="UPDATE ec_registro_nomina set id_equivalente=$equivalente,sincronizar=0 WHERE id_registro_nomina=$res[2]";
						$eje_2=mysql_query($sql_2,$extraer);
						if(!$eje_2){
							$error=mysql_error($extraer);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción		
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al asignar el id equivalente de registros de nomina en ".$nom_extraer."!!!!\n\n".$sql_2."\n\n".$error);
						}
					}
					//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_registro_nomina SET sincronizar=0 WHERE id_registro_nomina=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro del nuevo registro de nómina ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}//fin de else
			}//fin de si es registro de nómina

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               					Sincronizamos registros de exclusiones de Transferencias											  *
			*																																						  *
			**********************************************************************************************************************************************************/
			if($res[1]=='ec_exclusiones_transferencia'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";//concatenamos nombre de la tabla
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_exclusion_transferencia,/*1*/id_producto,/*2*/id_sucursal,/*3*/observaciones,/*4*/fecha,/*5*/hora,/*6*/0 
							FROM ec_exclusiones_transferencia
							WHERE id_exclusion_transferencia=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar registro de nomina en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM ec_exclusiones_transferencia WHERE id_exclusion_transferencia='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe la exclusión de transferencia en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						/*0*/$consulta.="id_exclusion_transferencia='".$row[0]."',";
					}			
					/*1*/$consulta.="id_producto='".$row[1]."',";	
					/*2*/$consulta.="id_sucursal='".$row[2]."',";	
					/*3*/$consulta.="observaciones='".$row[3]."',";	
					/*4*/$consulta.="fecha='".$row[4]."',";	
					/*5*/$consulta.="hora='".$row[5]."',";
					/*6*/$consulta.="sincronizar=".$row[6];				
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					//$consulta.=" WHERE id_permiso='".$res[2]."'";
					$consulta.=" WHERE id_exclusion_transferencia='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar exclusión de producto en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
					//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es inserción
						$consulta="UPDATE ec_exclusiones_transferencia SET id_exclusion_transferencia=$row[0],sincronizar=0 WHERE id_exclusion_transferencia=$row[0]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de nueva exclusión en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}//fin de else
			}//fin de si tabla es exclusiones en transferencia

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               										Sincronizamos Grids																  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_grid'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_grid,/*1*/nombre,/*2*/display,/*3*/max_width,/*4*/funcion_final,/*5*/tabla_relacionada,/*6*/funcion_nuevo,/*7*/funcion_eliminar, 
								/*8*/scroll,/*9*/alto,/*10*/datosGrid,/*11*/fileGrid,/*12*/footer,/*13*/listado,/*14*/tabla_padre,/*15*/no_tabla,/*16*/orden,/*17*/campo_llave,
								/*18*/query,/*19*/filas_inicial,/*20*/funcion_despues_eliminar,/*21*/buscador,/*22*/campo_coinc,/*23*/campo_enfoque,/*24*/consulta_coinc,/*25*/0
							FROM sys_grid WHERE id_grid=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de cabecera de grid en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						//si es nueva insersión
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM sys_grid WHERE id_grid='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el grid en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						
						/*0*/$consulta.="id_grid=".$row[0].",";
					}
					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="display='".$row[2]."',";
					/*3*/$consulta.="max_width='".$row[3]."',";
					/*4*/$consulta.="funcion_final='".$row[4]."',";
					/*5*/$consulta.="tabla_relacionada='".$row[5]."',";
					/*6*/$consulta.="funcion_nuevo='".$row[6]."',";
					/*7*/$consulta.="funcion_eliminar='".$row[7]."',"; 
					/*8*/$consulta.="scroll='".$row[8]."',";
					/*9*/$consulta.="alto='".$row[9]."',";
					/*10*/$consulta.="datosGrid=\"".$row[10]."\",";
					/*11*/$consulta.="fileGrid=\"".$row[11]."\",";
					/*12*/$consulta.="footer='".$row[12]."',";
					/*13*/$consulta.="listado='".$row[13]."',";
					/*14*/$consulta.="tabla_padre='".$row[14]."',";
					/*15*/$consulta.="no_tabla='".$row[15]."',";
					/*16*/$consulta.="orden='".$row[16]."',";
					/*17*/$consulta.="campo_llave='".$row[17]."',";
					/*18*/$consulta.="query=\"".$row[18]."\",";
					/*19*/$consulta.="filas_inicial='".$row[19]."',";
					/*20*/$consulta.="funcion_despues_eliminar='".$row[20]."',";
					/*21*/$consulta.="buscador=".$row[21].",";
					/*22*/$consulta.="campo_coinc='".$row[22]."',";
					/*23*/$consulta.="campo_enfoque='".$row[23]."',";
					/*24*/$consulta.="consulta_coinc='".$row[24]."',";
					/*25*/$consulta.="sincronizar=".$row[25];
				}//fin de if==1||2
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_grid='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
					die("Error al insertar grid en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es insertar
						$consulta="UPDATE sys_grid SET id_grid=$row[0],sincronizar=0 WHERE id_grid=$res[2]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de grid en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es Grids

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               								Sincronizamos detalle de Grid															  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_grid_detalle'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_grid_detalle,/*1*/id_grid,/*2*/display,/*3*/campo_tabla,/*4*/tipo,/*5*/modificable,/*6*/mascara,/*7*/alineacion,/*8*/formula,/*9*/datosDB,
								/*10*/depende,/*11*/on_change,/*12*/largo_combo,/*13*/sumatoria,/*14*/funcion_valida,/*15*/on_key,/*16*/valor_inicial,/*17*/requerido,/*18*/ancho,
								/*19*/html_value,/*20*/on_click,/*21*/orden,/*22*/multiseleccion,/*23*/0
							FROM sys_grid_detalle WHERE id_grid_detalle=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de detalle de grid en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						//si es nueva insersión
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM sys_grid_detalle WHERE id_grid_detalle='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el detalle de grid en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación
						
						/*0*/$consulta.="id_grid_detalle=".$row[0].",";
					}
					/*1*/$consulta.="id_grid='".$row[1]."',";
					/*2*/$consulta.="display='".$row[2]."',";
					/*3*/$consulta.="campo_tabla='".$row[3]."',";
					/*4*/$consulta.="tipo='".$row[4]."',";
					/*5*/$consulta.="modificable='".$row[5]."',";
					/*6*/$consulta.="mascara='".$row[6]."',";
					/*7*/$consulta.="alineacion='".$row[7]."',"; 
					/*8*/$consulta.="formula='".$row[8]."',";
					/*9*/$consulta.="datosDB='".$row[9]."',";
					/*10*/$consulta.="depende='".$row[10]."',";
					/*11*/$consulta.="on_change='".$row[11]."',";
					/*12*/$consulta.="largo_combo='".$row[12]."',";
					/*13*/$consulta.="sumatoria='".$row[13]."',";
					/*14*/$consulta.="funcion_valida='".$row[14]."',";
					/*15*/$consulta.="on_key='".$row[15]."',";
					/*16*/$consulta.="valor_inicial='".$row[16]."',";
					/*17*/$consulta.="requerido='".$row[17]."',";
					/*18*/$consulta.="ancho='".$row[18]."',";
					/*19*/$consulta.="html_value='".$row[19]."',";
					/*20*/$consulta.="on_click='".$row[20]."',";
					/*21*/$consulta.="orden=".$row[21].",";
					/*22*/$consulta.="multiseleccion='".$row[22]."',";
					/*23*/$consulta.="sincronizar=".$row[23];
				}//fin de if($res[3]==1||$res[3]==2)
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_grid_detalle='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
					die("Error al insertar detalle de grid en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es insertar
						$consulta="UPDATE sys_grid_detalle SET id_grid_detalle=$row[0],sincronizar=0 WHERE id_grid_detalle=$res[2]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de detalle de grid en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es Grids

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               										Sincronizamos Listados															  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_listados'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_listado,/*1*/titulo,/*2*/tabla,/*3*/no_tabla,/*4*/consulta,/*5*/anchos,/*6*/alineacion,/*7*/campos,/*8*/ver,/*9*/modificar,
								/*10*/eliminar,/*11*/condicion,/*12*/nuevo,/*13*/buscador,/*14*/consulta_buscador,/*15*/condiciones_buscador,/*16*/0
							FROM sys_listados WHERE id_listado=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos del listado en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						//si es nueva insersión
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM sys_listados WHERE id_listado='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el listado en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación

						/*0*/$consulta.="id_listado=".$row[0].",";
					}

					/*1*/$consulta.="titulo='".$row[1]."',";
					/*2*/$consulta.="tabla='".$row[2]."',";
					/*3*/$consulta.="no_tabla='".$row[3]."',";
					/*4*/$consulta.="consulta=\"".$row[4]."\",";
					/*5*/$consulta.="anchos='".$row[5]."',";
					/*6*/$consulta.="alineacion='".$row[6]."',";
					/*7*/$consulta.="campos=\"".$row[7]."\","; 
					/*8*/$consulta.="ver='".$row[8]."',";
					/*9*/$consulta.="modificar='".$row[9]."',";
					/*10*/$consulta.="eliminar='".$row[10]."',";
					/*11*/$consulta.="condicion=\"".$row[11]."\",";
					/*12*/$consulta.="nuevo='".$row[12]."',";
					/*13*/$consulta.="buscador=".$row[13].",";
					/*14*/$consulta.="consulta_buscador=\"".$row[14]."\",";
					/*15*/$consulta.="condiciones_buscador=\"".$row[15]."\",";
					/*16*/$consulta.="sincronizar=".$row[16];

				}//fin de if($res[3]==1||$res[3]==2)
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_listado='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
					die("Error al insertar el listado en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es insertar
						$consulta="UPDATE sys_listados SET id_listado=$row[0],sincronizar=0 WHERE id_listado=$res[2]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de Listado en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es listados

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               										Sincronizamos Catálogos															  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_catalogos'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_catalogo,/*1*/tabla,/*2*/no_tabla,/*3*/tab,/*4*/campo,/*5*/display,/*6*/orden,/*7*/tipo,/*8*/es_llave,/*9*/visible,
								/*10*/modificable,/*11*/requerido,/*12*/valor_inicial,/*13*/clase,/*14*/longitud,/*15*/sql_combo,/*16*/where_combo,/*17*/order_combo,
								/*18*/on_focus,/*19*/on_blur,/*20*/on_click,/*21*/on_change,/*22*/on_keypress,/*23*/on_keydown,/*24*/on_keyup,/*25*/max_length,
								/*26*/extensiones,/*27*/especificacion,/*28*/clase_esp,/*29*/depende,/*30*/0
							FROM sys_catalogos WHERE id_catalogo=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos del listado en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						//si es nueva insersión
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM sys_catalogos WHERE id_catalogo='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el listado en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación

						/*0*/$consulta.="id_catalogo=".$row[0].",";
					}
					/*1*/$consulta.="tabla='".$row[1]."',";
					/*2*/$consulta.="no_tabla='".$row[2]."',";
					/*3*/$consulta.="tab='".$row[3]."',";
					/*4*/$consulta.="campo='".$row[4]."',";
					/*5*/$consulta.="display='".$row[5]."',";
					/*6*/$consulta.="orden='".$row[6]."',";
					/*7*/$consulta.="tipo='".$row[7]."',"; 
					/*8*/$consulta.="es_llave='".$row[8]."',";
					/*9*/$consulta.="visible='".$row[9]."',";
					/*10*/$consulta.="modificable='".$row[10]."',";
					/*11*/$consulta.="requerido='".$row[11]."',";
					/*12*/$consulta.="valor_inicial='".$row[12]."',";
					/*13*/$consulta.="clase='".$row[13]."',";
					/*14*/$consulta.="longitud='".$row[14]."',";
					/*15*/$consulta.="sql_combo=\"".$row[15]."\",";
					/*16*/$consulta.="where_combo=\"".$row[16]."\",";
					/*17*/$consulta.="order_combo='".$row[17]."',";
					/*18*/$consulta.="on_focus='".$row[18]."',";
					/*19*/$consulta.="on_blur='".$row[19]."',";
					/*20*/$consulta.="on_click='".$row[20]."',";
					/*21*/$consulta.="on_change='".$row[21]."',";
					/*22*/$consulta.="on_keypress='".$row[22]."',";
					/*23*/$consulta.="on_keydown='".$row[23]."',";
					/*24*/$consulta.="on_keyup='".$row[24]."',";
					/*25*/$consulta.="max_length='".$row[25]."',";
					/*26*/$consulta.="extensiones='".$row[26]."',";
					/*27*/$consulta.="especificacion='".$row[27]."',";
					/*28*/$consulta.="clase_esp='".$row[28]."',";
					/*29*/$consulta.="depende='".$row[29]."',";
					/*30*/$consulta.="sincronizar=".$row[30];
				}//fin de if($res[3]==1||$res[3]==2)
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_catalogo='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
					die("Error al insertar el catalogo en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es insertar
						$consulta="UPDATE sys_catalogos SET id_catalogo=$row[0],sincronizar=0 WHERE id_catalogo=$res[2]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro del catalogo en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es Catálogos

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               										Sincronizamos Reportes															  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_reportes'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_reporte,/*1*/titulo,/*2*/consulta,/*3*/campo_fecha,/*4*/ver_sumatorias,/*5*/consulta_sum,/*6*/consulta_vacia,/*7*/campoSucursal,/*8*/0
							FROM sys_reportes WHERE id_reporte=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos del Reporte en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						//si es nueva insersión
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM sys_reportes WHERE id_reporte='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el Reporte en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación

						/*0*/$consulta.="id_reporte=".$row[0].",";
					}

					/*1*/$consulta.="titulo='".$row[1]."',";
					/*2*/$consulta.="consulta=\"".$row[2]."\",";
					/*3*/$consulta.="campo_fecha='".$row[3]."',";
					/*4*/$consulta.="ver_sumatorias='".$row[4]."',";
					/*5*/$consulta.="consulta_sum=\"".$row[5]."\",";
					/*6*/$consulta.="consulta_vacia=\"".$row[6]."\",";
					/*7*/$consulta.="campoSucursal='".$row[7]."',"; 
					/*8*/$consulta.="sincronizar=".$row[8];
				}//fin de if($res[3]==1||$res[3]==2)
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_reporte='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
					die("Error al insertar el Reporte en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es insertar
						$consulta="UPDATE sys_reportes SET id_reporte=$row[0],sincronizar=0 WHERE id_reporte=$res[2]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro del reporte en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es reportes

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               							Sincronizamos Columnas de Reportes															  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_reportes_columnas'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_reporte_columna,/*1*/id_reporte,/*2*/campo,/*3*/display,/*4*/sumatoria,/*5*/orden,/*6*/0,/*7*/tipo
							FROM sys_reportes_columnas WHERE id_reporte_columna=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos de la columna de Reporte en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						//si es nueva insersión
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM sys_reportes_columnas WHERE id_reporte_columna='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el Reporte en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación

						/*0*/$consulta.="id_reporte_columna=".$row[0].",";
					}
					/*1*/$consulta.="id_reporte='".$row[1]."',";
					/*2*/$consulta.="campo=\"".$row[2]."\",";
					/*3*/$consulta.="display='".$row[3]."',";
					/*4*/$consulta.="sumatoria='".$row[4]."',";
					/*5*/$consulta.="orden='".$row[5]."',";
					/*6*/$consulta.="sincronizar=".$row[6].",";
					/*7*/$consulta.="tipo='".$row[7]."'"; 
				}//fin de if($res[3]==1||$res[3]==2)
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_reporte_columna='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
					die("Error al insertar columna de Reporte en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es insertar
						$consulta="UPDATE sys_reportes_columnas SET id_reporte_columna=$row[0],sincronizar=0 WHERE id_reporte_columna=$res[2]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el registro de la columna de reporte en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es columnas de reportes

			/**********************************************************************************************************************************************************
			*																																						  *
			*                               										Sincronizamos menus																  *
			*																																						  *
			**********************************************************************************************************************************************************/	
			if($res[1]=='sys_menus'){
				$consulta="";
				if($res[3]==1){$consulta.="INSERT INTO ";}//si es insertar
				if($res[3]==2){$consulta.="UPDATE ";}//si es actualizar
				if($res[3]==3){$consulta.="DELETE FROM ";}//si es eliminar
				$consulta.=$res[1]." ";
			//si es insertar o actualizar
				if($res[3]==1||$res[3]==2){
				//consultamos los datos en la BD que se insertó/actualizó
					$sql_1="SELECT /*0*/id_menu,/*1*/nombre,/*2*/es_listado,/*3*/tabla_relacionada,/*4*/liga,/*5*/menu_padre,/*6*/en_permisos,/*7*/icono,
								/*8*/orden,/*9*/no_tabla,/*10*/habilitado,/*11*/0,/*12*/0
							FROM sys_menus WHERE id_menu=$res[2]";
					$eje_1=mysql_query($sql_1,$extraer);
					if(!$eje_1){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al consultar datos del menu en ".$nom_extraer."!!!!\n\n".$sql_1."\n\n".$error);
					}
					$row=mysql_fetch_row($eje_1);
				//armamos los datos
					$consulta.="SET ";
					if($res[3]==1){
						//si es nueva insersión
						if($i==0){//si la inserción es desde línea
						//comprobamos si el registro ya existe
							$verifica_anterior="SELECT count(*) FROM sys_menus WHERE id_menu='$res[2]'";
							$verificar=mysql_query($verifica_anterior,$insertar);
							if(!$verificar){
								$error=mysql_error($insertar);
								mysql_query("ROLLBACK",$insertar);//cancelamos transacción
								mysql_query("ROLLBACK",$extraer);//cancelamos transacción
								liberaServidor($local);
								die("Error al verificar si ya existe el Menu en ".$nom_extraer."!!!!\n\n".$verifica_anterior."\n\n".$error);
							}
							$existe=mysql_fetch_row($verificar);
							if($existe[0]==1){
								$res[3]=2;//cambiamos a actualización
								$consulta="UPDATE ".$res[1]." SET ";
							}
						}//fin de comprobación

						/*0*/$consulta.="id_menu=".$row[0].",";
					}
					/*1*/$consulta.="nombre='".$row[1]."',";
					/*2*/$consulta.="es_listado='".$row[2]."',";
					/*3*/$consulta.="tabla_relacionada='".$row[3]."',";
					/*4*/$consulta.="liga=\"".$row[4]."\",";
					/*5*/$consulta.="menu_padre='".$row[5]."',";
					/*6*/$consulta.="en_permisos=".$row[6].",";
					/*7*/$consulta.="icono='".$row[7]."',"; 
					/*8*/$consulta.="orden='".$row[8]."',"; 
					/*9*/$consulta.="no_tabla='".$row[9]."',"; 
					/*10*/$consulta.="habilitado=".$row[10].","; 
					/*11*/$consulta.="en_uso='".$row[11]."',"; 
					/*12*/$consulta.="sincronizar=".$row[12]; 
				}//fin de if($res[3]==1||$res[3]==2)
				if($res[3]==2||$res[3]==3){//si es actualizacion o eliminar
					$consulta.=" WHERE id_menu='".$res[2]."'";
				}
				$ejecuta=mysql_query($consulta,$insertar);
				if(!$ejecuta){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
					die("Error al insertar Menú en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}else{
				//actualizamos el registro para activar la siguiente sincronización
					if($res[3]==1){//si es insertar
						$consulta="UPDATE sys_menus SET id_menu=$row[0],sincronizar=0 WHERE id_menu=$res[2]";
						$ejecuta=mysql_query($consulta,$insertar);
						if(!$ejecuta){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al preparar sincronizacion para el Menú en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
						}
					}
				}
			}//fin de si tabla es menús

		//actualizamos id_equivalente del registro de sincronización
			$eje_ok=mysql_query("UPDATE ec_sincronizacion_registros SET id_equivalente=$id_equiv_sinc WHERE id_registro_sincronizacion=$res[0]",$extraer)
			or die("Error al subir id equivalente de restauración en línea\n\n!!!".$sql."\n\n".mysql_error($extraer));
				mysql_query("COMMIT",$insertar);
				mysql_query("COMMIT",$extraer);

		}//fin de while
	}//fin de for $i



/**********************************************************************************************************************************************************
*																																						  *
*                               							Sincronizamos registros de Transferencias													  *
*																																						  *
**********************************************************************************************************************************************************/	
for($i=1;$i<=1;$i++){
//declaramos BD de la que se consulta info
	//$extraer=$linea;
	//$nom_extraer=' linea ';
//declaramos BD de la que se inseta info
	//$insertar=$local;
	//$nom_insertar=' local ';
	if($i==1){//invertimos valores de conexión
//declaramos BD de la que se consulta info
		$extraer=$local;
		$nom_extraer=' local ';
//declaramos BD de la que se inseta info
		$insertar=$linea;
		$nom_insertar=' linea ';
	}
//extraemos los registros de las sucursales
	$sql="SELECT /*0*/t.id_transferencia,/*1*/t.id_global,/*2*/u.id_equivalente,/*3*/t.folio,/*4*/t.fecha,/*5*/t.hora,/*6*/t.id_sucursal_origen,
				/*7*/t.id_sucursal_destino,/*8*/t.observaciones,/*9*/-1,/*10*/1,/*11*/0,/*12*/0,/*13*/t.id_almacen_origen,/*14*/t.id_almacen_destino,/*15*/t.id_tipo,
				/*16*/t.id_estado,/*17*/t.id_sucursal,/*18*/t.es_resolucion,/*19*/t.impresa,/*20*/t.ultima_sincronizacion,/*21*/t.ultima_actualizacion
				FROM ec_transferencias t
				LEFT JOIN sys_users u ON t.id_usuario=u.id_usuario
				WHERE (t.id_estado=1 OR t.id_estado=6 OR t.es_resolucion=1) AND (t.id_sucursal_origen=$user_sucursal OR t.id_sucursal_destino=$user_sucursal)
				AND t.id_global=0";

	$eje=mysql_query($sql,$extraer);
	if(!$eje){
		mysql_query("ROLLBACK",$insertar);//cancelamos transacción
		liberaServidor($local);
		die("Error al consultar registros de transferencias no autorizadas en ".$nom_extraer."!!!!\n\n".$sql."\n\n".mysql_error($insertar));
	}
	while($res=mysql_fetch_row($eje)){
	//marcamos el inicio de transacciones
		mysql_query("BEGIN",$insertar);
		mysql_query("BEGIN",$extraer);
	//insertamos el registro en la BD
		$sql="INSERT INTO ec_transferencias SET
				/*0*/id_transferencia=null,
				/*1*/id_global='0',
				/*2*/id_usuario='$res[2]',
				/*3*/folio='$res[3]',
				/*4*/fecha='$res[4]',
				/*5*/hora='$res[5]',
				/*6*/id_sucursal_origen='$res[6]',
				/*7*/id_sucursal_destino='$res[7]',
				/*8*/observaciones='$res[8]',
				/*9*/id_razon_social_venta='$res[9]',
				/*10*/id_razon_social_compra='$res[10]',
				/*11*/facturable='$res[11]',
				/*12*/porc_ganancia='$res[12]',
				/*13*/id_almacen_origen='$res[13]',
				/*14*/id_almacen_destino='$res[14]',
				/*15*/id_tipo='$res[15]',
				/*16*/id_estado='$res[16]',
				/*17*/id_sucursal='$res[17]',
				/*18*/es_resolucion=$res[18],
				/*19*/impresa=$res[19],
				/*20*/ultima_sincronizacion='$res[20]',
				/*21*/ultima_actualizacion='$res[21]'";
		$eje_reg=mysql_query($sql,$insertar);
		if(!$eje_reg){
			liberaServidor($local);
			die("Error al insertar la cabecera de transferencias!!!\n\n".$sql."\n\n".mysql_error($insertar));
		}
	//capturamos el id del registro de transferencia
		$id_equiv_sinc=mysql_insert_id($insertar);
	//sacamos el detalle de la transferancia 
		$sql="SELECT /*0*/null,/*1*/$id_equiv_sinc,/*2*/id_producto_or,/*3*/cantidad,/*4*/id_presentacion,/*5*/cantidad_presentacion,
					/*6*/cantidad_salida,/*7*/cantidad_salida_pres,/*8*/cantidad_entrada,/*9*/cantidad_entrada_pres,/*10*/resolucion,/*11*/referencia_resolucion
			FROM ec_transferencia_productos WHERE id_transferencia=$res[0]";
		$ejecuta=mysql_query($sql,$extraer);
		/*echo $sql;
		liberaServidor($local);
		die('');*/
		if(!$ejecuta){
			$error=mysql_error($extraer);
			mysql_query("ROLLBACK",$insertar);//cancelamos transacción
			mysql_query("ROLLBACK",$extraer);//cancelamos transacción
			liberaServidor($local);
			die("Error al extraer el detalle de la transferencia en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
		}else{
			while($detalle=mysql_fetch_row($ejecuta)){
		//insertamos el detalle de la transferencia
				$consulta="INSERT INTO ec_transferencia_productos SET 
							/*0*/id_transferencia_producto=null,
							/*1*/id_transferencia='$detalle[1]',
							/*2*/id_producto_or='$detalle[2]',
							/*3*/id_producto_de='$detalle[2]',
							/*4*/cantidad='$detalle[3]',
							/*5*/id_presentacion='$detalle[4]',
							/*6*/cantidad_presentacion='$detalle[5]',
							/*7*/cantidad_salida='$detalle[6]',
							/*8*/cantidad_salida_pres='$detalle[7]',
							/*9*/cantidad_entrada='$detalle[8]',
							/*10*/cantidad_entrada_pres='$detalle[9]',
							/*11*/resolucion=$detalle[10],
							/*12*/referencia_resolucion=$detalle[11]";
				$ejecuta_1=mysql_query($consulta,$insertar);
				if(!$ejecuta_1){
					$error=mysql_error($insertar);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al insertar el detalle de la transferencia en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
				}
			}//fin de while $detalle
		//si la transferencia ya fue terminada
			if($res[16]==6){
				$sql="SELECT /*0*/ma.id_movimiento_almacen,/*1*/ma.id_tipo_movimiento,/*2*/u.id_equivalente,/*3*/ma.id_sucursal,/*4*/ma.fecha,/*5*/ma.hora,
				/*6*/ma.observaciones,/*7*/ma.id_pedido,/*8*/ma.id_orden_compra,/*9*/ma.lote,/*10*/ma.id_maquila,/*11*/$id_equiv_sinc,/*12*/ma.id_almacen,
				/*13*/0,/*14*/0,/*15*/ma.ultima_sincronizacion,/*16*/ma.ultima_actualizacion
				FROM ec_movimiento_almacen ma
				LEFT JOIN sys_users u ON ma.id_usuario=u.id_usuario
				WHERE ma.id_transferencia='$res[0]'";
				//echo $sql.'<br>';
				$eje_ma=mysql_query($sql,$extraer);
				if(!$eje_ma){
					$error=mysql_error($extraer);
					mysql_query("ROLLBACK",$insertar);//cancelamos transacción
					mysql_query("ROLLBACK",$extraer);//cancelamos transacción
					liberaServidor($local);
					die("Error al consultar los movimientos de almacen por transferencia en ".$nom_extraer."!!!!\n\n".$consulta."\n\n".$error);
				}
			//insertamos los movimientos
				while($res_ma=mysql_fetch_row($eje_ma)){
					$sql="INSERT INTO ec_movimiento_almacen SET
							/**/id_movimiento_almacen=null,
							/*1*/id_tipo_movimiento='$res_ma[1]',
							/*2*/id_usuario='$res_ma[2]',
							/*3*/id_sucursal='$res_ma[3]',
							/*4*/fecha='$res_ma[4]',
							/*5*/hora='$res_ma[5]',
							/*6*/observaciones='$res_ma[6]',
							/*7*/id_pedido='$res_ma[7]',
							/*8*/id_orden_compra='$res_ma[8]',
							/*9*/lote='$res_ma[9]',
							/*10*/id_maquila='$res_ma[10]',
							/*11*/id_transferencia='$res_ma[11]',
							/*12*/id_almacen='$res_ma[12]',
							/*13*/sincronizar='$res_ma[13]',
							/*14*/id_equivalente='$res_ma[14]',
							/*15*/ultima_sincronizacion='$res_ma[15]',
							/*16*/ultima_actualizacion='$res_ma[16]'";
					echo $sql.'<br>';
					$ins_ma=mysql_query($sql,$insertar);
					if(!$ins_ma){
						$error=mysql_error($insertar);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al insertar la cabecera del movimiento de almacen en ".$nom_insertar." 1!!!!\n\n".$sql."\n\n".$error);
					}
				//capturamos el id del movimiento
					$id_mov_alm_nvo=mysql_insert_id($insertar);
				//extraemos el detalle del movimiento de almacen
					$sql="SELECT /*0*/null,/*1*/$id_mov_alm_nvo,/*2*/id_producto,/*3*/cantidad,/*4*/cantidad_surtida,/*5*/id_pedido_detalle,/*6*/id_oc_detalle 
					FROM ec_movimiento_detalle WHERE id_movimiento=$res_ma[0]";
					$mov_de=mysql_query($sql,$extraer);
					if(!$mov_de){
						$error=mysql_error($extraer);
						mysql_query("ROLLBACK",$insertar);//cancelamos transacción
						mysql_query("ROLLBACK",$extraer);//cancelamos transacción
						liberaServidor($local);
						die("Error al extraer el detalle del movimiento de almacen en ".$nom_extraer." 2!!!!\n\n".$sql."\n\n".$error);
					}
					while($res_de=mysql_fetch_row($mov_de)){
						$sql="INSERT INTO ec_movimiento_detalle SET
								/*0*/id_movimiento_almacen_detalle=null,
								/*1*/id_movimiento='$id_mov_alm_nvo',
								/*2*/id_producto='$res_de[2]',
								/*3*/cantidad='$res_de[3]',
								/*4*/cantidad_surtida='$res_de[4]',
								/*5*/id_pedido_detalle='$res_de[5]',
								/*6*/id_oc_detalle='$res_de[6]'";
						$ins_md=mysql_query($sql,$insertar);
						if(!$ins_md){
							$error=mysql_error($insertar);
							mysql_query("ROLLBACK",$insertar);//cancelamos transacción
							mysql_query("ROLLBACK",$extraer);//cancelamos transacción
							liberaServidor($local);
							die("Error al insertar el detalle del movimiento de almacen en ".$nom_insertar."!!!!\n\n".$sql."\n\n".$error);
						}
					}
				}	
			}//fin de si es transferencia terminada

		//eliminamos la transferencia en local
			$sql="DELETE FROM ec_transferencias WHERE id_transferencia='$res[0]'";
			$ejecuta=mysql_query($sql,$extraer);
			if(!$ejecuta){
				$error=mysql_error($extraer);
				mysql_query("ROLLBACK",$insertar);//cancelamos transacción
				mysql_query("ROLLBACK",$extraer);//cancelamos transacción
				liberaServidor($local);
				die("Error al eliminar la transferencia en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".$error);
			}

		//autorizamos transacciones
			mysql_query("COMMIT",$insertar);
			mysql_query("COMMIT",$extraer);
		}//fin de else
	}//fin de while
}//fin de for i


//liberaServidor($local);
//die('ok|');


/**********************************************************************************************************************************************************
*																																						  *
*                               									Sincronizamos clientes																  *
*																																						  *
**********************************************************************************************************************************************************/	
for($i=0;$i<=1;$i++){
//declaramos BD de la que se consulta info
	$extraer=$linea;
	$nom_extraer=' linea';
//declaramos BD de la que se inseta info
	$insertar=$local;
	$nom_insertar=' local ';
	if($i==1){//invertimos valores de conexión
//declaramos BD de la que se consulta info
		$extraer=$local;
		$nom_extraer=' local ';
//declaramos BD de la que se inseta info
		$insertar=$linea;
		$nom_insertar=' linea ';
	}

//echo "2.-Selecciona clientes\n\n";
//buscamos cientes pendientes 
	$sql="SELECT * FROM ec_clientes WHERE id_equivalente=0 AND id_cliente>1 AND id_sucursal=$user_sucursal";
	$eje=mysql_query($sql,$extraer);
	if(!$eje){
		liberaServidor($local);
		die("Error Al consultar clientes por subir en ".$nom_extraer."!!!\n\n".$sql."\n\n".mysql_error($extraer));
	}

//insertamos clientes
	while($res=mysql_fetch_row($eje)){
		mysql_query("BEGIN",$insertar);//marcamos inicio de transacción para insertar clientes
		$consulta="INSERT INTO ec_clientes VALUES(null,'$res[0]','$res[2]','$res[3]','$res[4]','$res[5]','$res[6]','$res[7]','$res[8]','$res[9]','$res[10]','$res[11]',
			'$res[12]','$res[13]','$res[14]','$res[15]')";
		$inserta=mysql_query($consulta,$insertar);
		if(!$inserta){
			mysql_query("ROLLBACK",$insertar);//cancelamos transacción
			liberaServidor($local);
			die("Error al insertar Clientes en ".$insertar."!!!!\n\n".$consulta."\n\n".mysql_error($insertar));
		}

		$id_temp=mysql_insert_id($insertar);
	
//echo "3.-Inserta Clientes\n\n";
	//actualizamos el id equivalente
		mysql_query("BEGIN",$extraer);//abrimos transacción para actualizar id equivalente
		$up="UPDATE ec_clientes SET id_equivalente='$id_temp' WHERE id_cliente='$res[0]'";
		$actualiza=mysql_query($up,$extraer);
		if(!$actualiza){
			mysql_query("ROLLBACK",$insertar);
			mysql_query("ROLLBACK",$extraer);
			liberaServidor($local);
			die("Error al actualizar el id equivalente del cliente en ".$nom_insertar."!!!!\n\n".$consulta."\n\n".mysql_error($extraer));			
		}
	//autorizamos transacciones
		mysql_query("COMMIT",$insertar);
		mysql_query("COMMIT",$extraer);
	}//fin de while $res
}//fin de for i

/**********************************************************************************************************************************************************
*																																						  *
*                               									Sincronizamos Ventas																  *
*																																						  *
**********************************************************************************************************************************************************/	
for($i=0;$i<=1;$i++){
//declaramos BD de la que se consulta info
	$extraer=$linea;
	$nom_extraer=' linea';
//declaramos BD de la que se inseta info
	$insertar=$local;
	$nom_insertar=' local ';
	if($i==1){//invertimos valores de conexión
//declaramos BD de la que se consulta info
		$extraer=$local;
		$nom_extraer=' local ';
//declaramos BD de la que se inseta info
		$insertar=$linea;
		$nom_insertar=' linea ';
	}

//echo "4.-Busca Ventas\n\n";
//buscamos ventas pendientes de sincronizar
	$sql="SELECT p.id_pedido,pa.id_pedido_pago 
			FROM ec_pedidos p
			LEFT JOIN ec_pedido_pagos pa on pa.id_pedido=p.id_pedido
			WHERE p.id_equivalente=0 AND p.id_sucursal=$user_sucursal";//is NULL
	$ejecuta=mysql_query($sql,$extraer);
	if(!$ejecuta){
		liberaServidor($local);
		die("Error al consultar ventas en ".$nom_extraer."!!!\n\n".mysql_error($extraer)."\n\n".$sql);
	}

//sincronizamos ventas
	while($row=mysql_fetch_row($ejecuta)){
	//checamos si ya fue sincronizado en otra BD
		$verifica="SELECT id_equivalente FROM ec_pedidos WHERE id_equivalente='$row[0]' AND id_sucursal='$user_sucursal'";
		$ej=mysql_query($verifica,$insertar);
		if(!$ej){
			liberaServidor($local);
			die("Error al validar que la venta no exsta en ".$nom_insertar."!!!\n\n".mysql_error($insertar)."\n\n".$verifica);
		}

//echo "5.-Selecciona los datos del pedido(id_pedido,id_pago)\n\n";
		if(mysql_num_rows($ej)<1){//si no ha sido sincronizado
	//reconsultamos datos del pedido localmente
			$data="SELECT * FROM ec_pedidos WHERE id_pedido=$row[0]";
			$eje=mysql_query($data,$extraer);
			if(!$eje){
				liberaServidor($local);
				die("Error al extraer los datos de la venta en ".$nom_extraer."!!\n\n".mysql_error($extraer)."\n\n".$data);
			}
			$rw=mysql_fetch_row($eje);

//echo "6.-Selecciona los datos del pedido(cabecera)\n\n";
		//extraemos id equivalente del cliente
			if($rw[5]>1){
				$sqAux="SELECT id_cliente FROM ec_clientes WHERE id_equivalente='$rw[5]' AND id_sucursal=$user_sucursal";/*aqui se agrega la condición de que pertenezca a la misma sucursal Oscar 21.11.2018*/
				$ejAux=mysql_query($sqAux,$insertar);
				if(!$ejAux){
					liberaServidor($local);
					die("Error al consular el id equivalente del cliente para insertar cabecera de pedido en ".$nom_insertar."!\n\n".mysql_error($extraer)."\n\n".$sqAux);	
				}
			//igualamos id_equivalente
				$auxCli=mysql_fetch_row($ejAux);
				$rw[5]=$auxCli[0];
			}

//echo "7.-Inserta cabecera del pedido en Línea\n\n";
	//insertamos cabecera de pedido
		mysql_query("BEGIN",$insertar);//abrimos transaccion para insertar
		$sqLinea="INSERT INTO ec_pedidos VALUES(null,'$rw[1]','$rw[2]','$rw[3]','$rw[4]','$rw[5]','$rw[6]','$rw[7]','$rw[8]','$rw[9]','$rw[10]','$rw[11]',
					'$rw[12]','$rw[13]','$rw[14]','$rw[15]','$rw[16]','$rw[17]','$rw[18]','$rw[19]','$rw[20]','$rw[21]','2','$rw[23]','$rw[24]','$rw[25]',
					'$rw[26]','-1','$rw[28]','$rw[29]','$rw[30]','$row[0]',$rw[32],'$rw[33]','$rw[34]')";//$rw[22] cambiar cuando esten los usuarios
		$ejeLin=mysql_query($sqLinea,$insertar);
		if(!$ejeLin){
			mysql_query("ROLLBACK",$insertar);
			liberaServidor($local);
			die("Error en insertar cabecera de venta en ".$nom_insertar."!!!\n\n".mysql_error($insertar)."\n\n".$sqLinea);
		}
	//obtenemos el ID del pedido en el servidor en linea
		$id_p_l=mysql_insert_id($insertar);
	//variable para guardar ids de ec_pedido_detalle
		$ids="";

//echo "8.-Selecciona el detalle del pedido \n\n";
	//buscamos detalle de pedido
		$data="SELECT * FROM ec_pedidos_detalle WHERE id_pedido=$row[0]";
		$ejeLo=mysql_query($data,$extraer);
		if(!$ejeLo){	
			mysql_query("ROLLBACK",$insertar);
			liberaServidor($local);
			die("Error al extraer el detalle del pedido en ".$nom_extraer."!!!");
		}

//echo "9.-Inserta el detalle de los pedidos\n\n";
	//insertamos detalle de pedido
		while($rw=mysql_fetch_row($ejeLo)){
			$sqLinea1="INSERT INTO ec_pedidos_detalle VALUES(null,'$id_p_l','$rw[2]','$rw[3]','$rw[4]','$rw[5]','$rw[6]','$rw[7]','$rw[8]','$rw[9]','$rw[10]','$rw[11]')";
			$insLinea=mysql_query($sqLinea1,$insertar);
			if(!$insLinea){
				mysql_query("ROLLBACK",$insertar);
				liberaServidor($local);
				die("Error al insertar detalle de venta en ".$nom_insertar."!!!\n\n".mysql_error($insertar)."\n\n".$sqLinea1);
			}//$ids.=mysql_insert_id($linea)."|";
		}//fin de while

//echo "16.-Actualiza el id equivalente de pedido\n\n";
		//actualizamos el id equivalenete en cabecera del pedido localmente
			mysql_query("BEGIN",$extraer);//marcamos inicio de transacción de bd origen
			$sql="UPDATE ec_pedidos SET id_equivalente=$id_p_l WHERE id_pedido='$row[0]'";
			$eje=mysql_query($sql,$extraer);
			if(!$eje){				
				mysql_query("ROLLBACK",$insertar);
				mysql_query("ROLLBACK",$extraer);
				die("Error al actualizar el id equivalenete del pedido en ".$nom_extraer."!!!");
			}
				$pagosNo.=$row[1]."|";
			//}
	//autorizamos transacciones
		mysql_query("COMMIT",$extraer);
		mysql_query("COMMIT",$insertar);
		if($i==1){
			$impresiones.=$id_p_l."~";//guardamos ids locales de ventas para generar ticket 
		}
		/*if($i==1){
		//generamos el ticket
				$id_pedido=$id_p_l;
			echo '<script type="text/javascript">window.open("../../../touch/ajax/ticket-php-head.inc?id_pedido='.$id_pedido.'", "nombre de la ventana", "width=300, height=200");</script>';
		//echo 'id de pedido local: '.$id_p_l;
				/*if(!include('ticket-php-head.inc')){
					liberaServidor($local);
					die('no se encuentra el generador de tickets!!!');
				}
			echo "imprime: 1<br>";
		}*/
	}//fin de if !existe
	
	}//fin de while principal
}//fin de for i

$impresiones.="|";//separamos ids

/**********************************************************************************************************************************************************
*																																						  *
*                               										Sincronización de pagos															  *
*																																						  *
**********************************************************************************************************************************************************/	
for($i=0;$i<=1;$i++){
//declaramos BD de la que se consulta info
	$extraer=$linea;
	$nom_extraer=' linea';
//declaramos BD de la que se inseta info
	$insertar=$local;
	$nom_insertar=' local ';
	if($i==1){//invertimos valores de conexión
//declaramos BD de la que se consulta info
		$extraer=$local;
		$nom_extraer=' local ';
//declaramos BD de la que se inseta info
		$insertar=$linea;
		$nom_insertar=' linea ';
	}
//echo 'Selecciona pagos pendienetes<br>';
//buscamos pagos pendientes de subir
	$sp="SELECT pp.* 
			FROM ec_pedido_pagos pp
			LEFT JOIN ec_pedidos p ON pp.id_pedido=p.id_pedido
			WHERE pp.id_equivalente=0 AND p.id_sucursal=$user_sucursal";
//implementacion para evitar duplicidad de pagos
	$excluye=explode("|",$pagosNo);
	/*for($j=0;$j<sizeof($excluye)-1;$j++){
		$sp.=" AND id_pedido_pago!=".$excluye[$j];
	}*/
//echo 'Sp: '.$sp;
	$ejeSp=mysql_query($sp,$extraer);
	if(!$ejeSp){
		liberaServidor($local);
		die("Error al consultar pagos pendienetes por subir!!!\n\n".$sp."\n\n".mysql_error($local));
	}
	
	while($re=mysql_fetch_row($ejeSp)){
//echo 'Selecciona id equivalenete del pedido<br>';
	//sacamos id de pedido en linea
		$id_li="SELECT id_pedido FROM ec_pedidos WHERE id_equivalente='$re[2]' AND id_sucursal=$user_sucursal";
		$ejeId=mysql_query($id_li,$insertar);
		if(!$ejeId){
			liberaServidor($local);
			die("Error al consultar el id de pedido en linea para insertar en pago de ".$nom_insertar."!!!\n\n".$id_li."\n\n".mysql_error($insertar));
		}
		$ax1=mysql_fetch_row($ejeId);
//echo 'id_equivalente:'.$ax1[0]."<br>";
		//die('iukberf; '.$ax1[0]);
//echo "inserta el pago<br>";
	//insertamos el pago en linea
		mysql_query("BEGIN",$insertar);//marcamos inicio de transacción
		$insP="INSERT INTO ec_pedido_pagos VALUES(null,'$re[0]','$ax1[0]','$re[3]','$re[4]','$re[5]','$re[6]','$re[7]','$re[8]','$re[9]','$re[10]','$re[11]','$re[12]',$re[13])";
		$ins=mysql_query($insP,$insertar);
		if(!$ins){
			mysql_query("ROLLBACK",$insertar);
			liberaServidor($local);
			die("Error al insertar pagos Pendientes en ".$nom_insertar."!!!\n\n".$insP.mysql_error($insertar));
		}
//echo "actualizamos el id equivalente del pago localmente<br>";
	//actualizamos el id equivalente del pago localmente
		$id_aux_pag=mysql_insert_id($insertar);
		mysql_query("BEGIN",$extraer);
		$act_loc="UPDATE ec_pedido_pagos SET id_equivalente='$id_aux_pag' WHERE id_pedido_pago='$re[0]'";
		$actLo=mysql_query($act_loc,$extraer);
		if(!$actLo){
			mysql_query("ROLLBACK",$insertar);
			mysql_query("ROLLBACK",$extraer);
			liberaServidor($local);
			die("Error al actualizar el id global de pagos en ".$nom_extraer."!!!\n\n".$act_loc."\n\n".mysql_error($extraer));
		}
//echo "actualizamos el pedido si la cantidad total ya fue cubierta por los pagos<br>";
	//actualizamos el pedido si la cantidad total ya fue cubierta por los pagos
		$act_ped="UPDATE ec_pedidos SET pagado=IF((SELECT ROUND(SUM(monto)) FROM ec_pedido_pagos WHERE id_pedido='$ax1[0]' AND (referencia='' OR referencia is null))>=total,1,0)
					WHERE id_pedido=$ax1[0]";
		$a_p=mysql_query($act_ped,$insertar);
		if(!$a_p){
			mysql_query("ROLLBACK",$insertar);
			mysql_query("ROLLBACK",$extraer);
			liberaServidor($local);
			die("Error al actualizar el status del pedido despues de registrar los pagos en ".$nom_insertar."!!!\n\n".$act_loc."\n\n".mysql_error($insertar));		
		}
	//autorizamos transacciones
		mysql_query("commit",$insertar);
		mysql_query("commit",$extraer);
		
		if($i==1){
			$impresiones.=$id_aux_pag."~";
		}

	}//fin de while para insertar pagos
}//fin de for i

$impresiones.="|";


/**********************************************************************************************************************************************************
*																																						  *
*                               									Subimos modificaciones  en pedidos													  *
*																																						  *
**********************************************************************************************************************************************************/	
for($i=0;$i<=1;$i++){
//declaramos BD de la que se consulta info
	$extraer=$linea;
	$nom_extraer=' linea';
//declaramos BD de la que se inseta info
	$insertar=$local;
	$nom_insertar=' local ';
	if($i==1){//invertimos valores de conexión
//declaramos BD de la que se consulta info
		$extraer=$local;
		$nom_extraer=' local ';
//declaramos BD de la que se inseta info
		$insertar=$linea;
		$nom_insertar=' linea ';
	}
//consultamos pedidos modificados localmente
	$sql="SELECT id_pedido,id_equivalente,subtotal,total,descuento,ultima_modificacion 
			FROM ec_pedidos 
			WHERE id_equivalente>0 
			AND modificado=1 
			AND id_sucursal=$user_sucursal";
	$eje1=mysql_query($sql,$extraer);
	if(!$eje1){
		liberaServidor($local);
		die("Error al consultar pedidos modificados en ".$nom_extraer."!!!\n\n".$sql."\n\n".mysql_error($extraer));
	}
	while($r=mysql_fetch_row($eje1)){
	mysql_query("BEGIN",$insertar);//iniciamos transaccion en BD destino
	//eliminamos el detalle que esta en el destino
		$sql_1="DELETE FROM ec_pedidos_detalle WHERE id_pedido=$r[1]";	
		$eje_1=mysql_query($sql_1,$insertar);
		if(!$eje_1){
			$error=mysql_error($insertar);
			liberaServidor($local);
			die("Error al eliminar para reemplazar el detalle del pedido modificado en ".$nom_insertar."!!!\n\n".$sql_1."\n\n".$error);				
		}
	//consultamos el detalle del pedido para sobreescribir
		$sql2="SELECT p.id_equivalente,pd.id_producto,pd.cantidad,pd.precio,pd.monto,pd.iva,pd.ieps,pd.cantidad_surtida,pd.descuento,0,pd.es_externo 
		FROM ec_pedidos_detalle pd 
		LEFT JOIN ec_pedidos p ON pd.id_pedido=p.id_pedido
		WHERE pd.id_pedido=$r[0] AND p.id_sucursal=$user_sucursal";

		$eje2=mysql_query($sql2,$extraer);
		if(!$eje2){
			liberaServidor($local);
			die("Error al consultar detalle de pedido modificado en ".$nom_extraer."!!!\n\n".$sql2."\n\n".mysql_error());
		}
	//actualizamos los detalles
		while($ro=mysql_fetch_row($eje2)){
			$act1="INSERT INTO ec_pedidos_detalle SET 
						id_pedido_detalle=null,
						id_pedido='$ro[0]',
						id_producto='$ro[1]',
						cantidad=$ro[2],
						precio='$ro[3]',
						monto=$ro[4],
						iva='$ro[5]',
						ieps='$ro[6]',
						cantidad_surtida='$ro[7]',
						descuento='$ro[8]',
						modificado=$ro[9],
						es_externo='$ro[10]'";
			$eje3=mysql_query($act1,$insertar);
			if(!$eje3){
				mysql_query("ROLLBACK",$insertar);
				liberaServidor($local);
				die("Error al reinsertar el detalle de pedido modificado en ".$nom_insertar."\n\n".$act1."\n\n".mysql_error($insertar));
			}
		}//termina while de actualización de detalle_pedido
//actualizamos los pagos
	//eliminamos los pagos del pedido donde se inserta la modificación por devolucion
		$elim_pag="DELETE FROM ec_pedido_pagos WHERE id_pedido=$r[1]";
		$eje_elim=mysql_query($elim_pag,$insertar);
		if(!$eje_elim){
			$error=mysql_error($insertar);
			mysql_query("ROLLBACK",$insertar);
			liberaServidor($local);
			die("Error al extraer detalles de pago en: ".$nom_extraer."\n\n".$elim_pago."\n\n".$error);
		}
	//extraemos los pagos
		$sql_pagos="SELECT /*0*/null,/*1*/pp.id_pedido_pago,/*2*/p.id_equivalente,/*3*/pp.id_tipo_pago,/*4*/pp.fecha,/*5*/pp.hora,/*6*/pp.monto,
						/*7*/pp.referencia,/*8*/pp.id_moneda,/*9*/pp.es_externo
					FROM ec_pedido_pagos pp
					LEFT JOIN ec_pedidos p ON pp.id_pedido=p.id_pedido
					WHERE p.id_pedido=$r[0]";
		$eje_pagos=mysql_query($sql_pagos,$extraer);
		if(!$eje_pagos){
			$error=mysql_error($extraer);
			mysql_query("ROLLBACK",$insertar);
			liberaServidor($local);
			die("Error al extraer detalles de pago en: ".$nom_extraer."\n\n".$sql_pagos."\n\n".$error);
		}
	//actualizamos los pagos del pedido modificado
		while($r_pgs=mysql_fetch_row($eje_pagos)){
			$act_pgo="INSERT INTO ec_pedido_pagos SET 
							id_pedido_pago=null,
							id_equivalente='$r_pgs[1]',
							id_pedido='$r_pgs[2]',
							id_tipo_pago='$r_pgs[3]',
							fecha='$r_pgs[4]',
							hora='$r_pgs[5]',
							monto='$r_pgs[6]',
							referencia='$r_pgs[7]',
							id_moneda='$r_pgs[8]',
							tipo_cambio=1,
							id_nota_credito=-1,
							id_cxc=-1,
							exportado=0,
							es_externo='$r_pgs[9]'";
			$actualizar_pago=mysql_query($act_pgo,$insertar);
			if(!$actualizar_pago){
				$error=mysql_error($insertar);
				mysql_query("ROLLBACK",$insertar);
				liberaServidor($local);
				die("Error al reescribir detalles de pago en: ".$nom_insertar."\n\n".$act_pgo."\n\n".$error);
			}
		//ccapturamos el id del pago para actualizar el nuevo equivalente del registro id_sucursal_origen
			$id_eq=mysql_insert_id($insertar);
			$c_a_p="UPDATE ec_pedido_pagos SET id_equivalente=$id_eq WHERE id_pedido_pago='$r_pgs[1]'";
			$act_eq=mysql_query($c_a_p);
			if(!$act_eq){
				$error=mysql_error($insertar);
				mysql_query("ROLLBACK",$insertar);
				liberaServidor($local);
				die("Error al actualizar id equivalente de pago(s) en: ".$nom_insertar."\n\n".$c_a_p."\n\n".$error);
			}

		}//fin de while que reinserta pagos

	//actualizamos cabecera del pedido en BD destino
		$sql4="UPDATE ec_pedidos SET subtotal=$r[2],total=$r[3],descuento=$r[4],ultima_modificacion='$r[5]' WHERE id_pedido=$r[1]";
		$eje4=mysql_query($sql4,$insertar);
//echo "SQL: \n\n".$sql4;
		if(!$eje4){
			mysql_query("ROLLBACK",$insertar);
			liberaServidor($local);
			die("Error al actualizar el encabezado del pedido en ".$nom_insertar."\n\n".$sql4."\n\n".mysql_error($insertar));
		}

	//actualizamos el status del encabezado localmente
		mysql_query("BEGIN",$extraer);//iniciamos transaccion local
		$sql4="UPDATE ec_pedidos SET modificado=0 WHERE id_pedido=$r[0]";
		$eje4=mysql_query($sql4,$extraer);
		if(!$eje4){
			mysql_query("ROLLBACK",$insertar);
			mysql_query("ROLLBACK",$extraer);
			liberaServidor($local);
			die("Error al actualizar el pedido como no modificado en ".$nom_extraer."\n\n".$sql4."\n\n".mysql_error($extraer));
		}
	/*actualizamos los detalles  no mofdificado (se cambia el modificado por valor 0)
		$sql4="UPDATE ec_pedidos_detalle SET modificado=0 WHERE id_pedido=$r[0]";
		$eje4=mysql_query($sql4,$extraer);
		if(!$eje4){
			mysql_query("ROLLBACK",$insertar);
			mysql_query("ROLLBACK",$extraer);
			liberaServidor($local);
			die("Error al actualizar en ceros el detalle de pedido por devolución en ".$nom_extraer."\n\n".$sql4."\n\n".mysql_error($extraer));
		}*/
		mysql_query("COMMIT",$insertar);//autorizamos transaccion de modificaciones en pedido,detalle del pedido y movimiento de almacen
		mysql_query("COMMIT",$extraer);//autorizamos transaccion de modificaciones locales
		
		if($i==1){
			$impresiones.=$r[1]."~";
		}

	}//termina while de pedidos modificados

}//fin de for i

$impresiones.="|";

/**********************************************************************************************************************************************************
*																																						  *
*                               									Subimos modificaciones  en pedidos													  *
*																																						  *
**********************************************************************************************************************************************************	
for($i=0;$i<=1;$i++){
//declaramos BD de la que se consulta info
	$extraer=$linea;
	$nom_extraer=' linea';
//declaramos BD de la que se inseta info
	$insertar=$local;
	$nom_insertar=' local ';
	if($i==1){//invertimos valores de conexión
//declaramos BD de la que se consulta info
		$extraer=$local;
		$nom_extraer=' local ';
//declaramos BD de la que se inseta info
		$insertar=$linea;
		$nom_insertar=' linea ';
	}
//consultamos pedidos modificados localmente
	$sql="SELECT id_pedido,id_equivalente,subtotal,total,descuento,ultima_modificacion FROM ec_pedidos WHERE id_equivalente>0 AND modificado=1 AND id_sucursal=$user_sucursal";
	$eje1=mysql_query($sql,$extraer);
	if(!$eje1){
		liberaServidor($local);
		die("Error al consultar pedidos modificados en ".$nom_extraer."!!!\n\n".$sql."\n\n".mysql_error($extraer));
	}
	/*if(mysql_num_rows($eje1)>0){
		die("si hay!!");
	}else{
		die("no hay!!!");
	}*\
	while($r=mysql_fetch_row($eje1)){
	//consultamos datos de productos modificados en el detalle del pedido
		$sql2="SELECT id_producto,cantidad,monto,descuento FROM ec_pedidos_detalle WHERE id_pedido=$r[0] AND modificado=1";
		$eje2=mysql_query($sql2,$extraer);
		if(!$eje2){
			liberaServidor($local);
			die("Error al consultar detalle de pedido modificado en ".$nom_extraer."!!!\n\n".$sql2."\n\n".mysql_error());
		}

	//actualizamos los detalles en Linea
		while($ro=mysql_fetch_row($eje2)){
			mysql_query("BEGIN",$insertar);//iniciamos transaccion en BD destino
			$act1="UPDATE ec_pedidos_detalle SET cantidad=$ro[1],monto=$ro[2],descuento=$ro[3] WHERE id_pedido=$r[1] AND id_producto=$ro[0]";
			$eje3=mysql_query($act1,$insertar);
			if(!$eje3){
				mysql_query("ROLLBACK",$insertar);
				liberaServidor($local);
				die("Error al actualizar el detalle de pedido modificado en ".$nom_insertar."\n\n".$act1."\n\n".mysql_error($insertar));
			}
		}//termina while de actualización de detalle_pedido

	//actualizamos cabecera del pedido en BD destino
		$sql4="UPDATE ec_pedidos SET subtotal=$r[2],total=$r[3],descuento=$r[4],ultima_modificacion='$r[5]' WHERE id_pedido=$r[1]";
		$eje4=mysql_query($sql4,$insertar);
//echo "SQL: \n\n".$sql4;
		if(!$eje4){
			mysql_query("ROLLBACK",$insertar);
			liberaServidor($local);
			die("Error al actualizar el encabezado del pedido en ".$nom_insertar."\n\n".$sql4."\n\n".mysql_error($insertar));
		}

	//actualizamos el status del encabezado localmente
		mysql_query("BEGIN",$extraer);//iniciamos transaccion local
		$sql4="UPDATE ec_pedidos SET modificado=0 WHERE id_pedido=$r[0]";
		$eje4=mysql_query($sql4,$extraer);
		if(!$eje4){
			mysql_query("ROLLBACK",$insertar);
			mysql_query("ROLLBACK",$extraer);
			liberaServidor($local);
			die("Error al actualizar el pedido como no modificado en ".$nom_extraer."\n\n".$sql4."\n\n".mysql_error($extraer));
		}
	//actualizamos los detalles  no mofdificado (se cambia el modificado por valor 0)
		$sql4="UPDATE ec_pedidos_detalle SET modificado=0 WHERE id_pedido=$r[0]";
		$eje4=mysql_query($sql4,$extraer);
		if(!$eje4){
			mysql_query("ROLLBACK",$insertar);
			mysql_query("ROLLBACK",$extraer);
			liberaServidor($local);
			die("Error al actualizar en ceros el detalle de pedido por devolución en ".$nom_extraer."\n\n".$sql4."\n\n".mysql_error($extraer));
		}
		mysql_query("COMMIT",$insertar);//autorizamos transaccion de modificaciones en pedido,detalle del pedido y movimiento de almacen
		mysql_query("COMMIT",$extraer);//autorizamos transaccion de modificaciones locales
		
		if($i==1){
			$impresiones.=$r[1]."~";
		}

	}//termina while de pedidos modificados

}//fin de for i

$impresiones.="|";*/

/**********************************************************************************************************************************************************
*																																						  *
*                               										Sincronizamos devoluciones														  *
*																																						  *
**********************************************************************************************************************************************************/	
for($i=0;$i<=1;$i++){
//declaramos BD de la que se consulta info
	$extraer=$linea;
	$nom_extraer=' linea';
//declaramos BD de la que se inseta info
	$insertar=$local;
	$nom_insertar=' local ';
	if($i==1){//invertimos valores de conexión
//declaramos BD de la que se consulta info
		$extraer=$local;
		$nom_extraer=' local ';
//declaramos BD de la que se inseta info
		$insertar=$linea;
		$nom_insertar=' linea ';
	}
//consultamos pedidos modificados localmente
//insertamos las devoluciones en linea
		$sql5="SELECT d.id_devolucion,2/*d.id_usuario aqui se tiene que cambiar cuando los usuarios esten sincronizados*/,d.id_sucursal,d.fecha,d.hora,p.id_equivalente,d.folio,d.es_externo
				FROM ec_devolucion d
				LEFT JOIN ec_pedidos p on d.id_pedido=p.id_pedido
				WHERE d.id_equivalente=0 AND d.id_sucursal=$user_sucursal";
		$eje5=mysql_query($sql5,$extraer);
		if(!$eje5){
			liberaServidor($local);
			die("Error al consultar la devolucion en ".$nom_extraer."!!!\n\n".$sql5."\n\n".mysql_error($extraer));
		}
	//comenzamos a insertar devoluciones en linea
		while($dev=mysql_fetch_row($eje5)){
			mysql_query("BEGIN",$insertar);//abrimos transacción de bd destino
			$sql6="INSERT INTO ec_devolucion(id_equivalente,id_usuario,id_sucursal,fecha,hora,id_pedido,folio,es_externo) VALUES($dev[0],$dev[1],$dev[2],'$dev[3]','$dev[4]',$dev[5],'$dev[6]',$dev[7])";
			$eje6=mysql_query($sql6,$insertar);
			if(!$eje6){
				mysql_query("ROLLBACK",$insertar);
				liberaServidor($local);
				die("Error al insertar devolucion en ".$nom_insertar."!!!\n\n".$sql6."\n\n".mysql_error($insertar));
			}
			$dev_linea=mysql_insert_id($insertar);//capturamos id insertado
		//consultamos detalle de devolucion
			$sql6="SELECT id_producto,cantidad FROM ec_devolucion_detalle WHERE id_devolucion=$dev[0]";
			$eje6=mysql_query($sql6,$extraer);
			if(!$eje6){
				mysql_query("ROLLBACK",$extraer);
				liberaServidor($local);
				die("Error al consultar detalle de devoucion en ".$nom_extraer."!!!\n\n".$sql6."\n\n".mysql_error($extraer));
			}
		//insertamos detalle de devolucion en linea
			while($det_dev=mysql_fetch_row($eje6)){
				$sql7="INSERT INTO ec_devolucion_detalle(id_devolucion,id_producto,cantidad) VALUES($dev_linea,$det_dev[0],$det_dev[1])";
				$eje7=mysql_query($sql7,$insertar);
				if(!$eje7){
					mysql_query("ROLLBACK",$insertar);
					die("Error al insertar detalle de devolucion en ".$nom_insertar."!!!\n\n".$sql7."\n\n".mysql_error($insertar));
				}
			}//termina while para insertar detalles de devolucion
		//consultamos pago de la devolucion
			$sql7="SELECT id_tipo_pago,monto,referencia,es_externo,fecha,hora 
					FROM ec_devolucion_pagos WHERE id_devolucion=$dev[0]";
			$eje7=mysql_query($sql7,$extraer);
			if(!$eje7){
				mysql_query("ROLLBACK",$extraer);
				die("Error al consultar el pago de la devolucion\n\n".$sql7."en ".$nom_extraer."\n\n".mysql_error($extraer));
			}
			while($dev_pag=mysql_fetch_row($eje7)){
			//insertamos en línea el pagod e la devolución
				$sql8="INSERT INTO ec_devolucion_pagos(id_devolucion,id_tipo_pago,monto,referencia,es_externo,fecha,hora)
						VALUES($dev_linea,$dev_pag[0],$dev_pag[1],'$dev_pag[2]',$dev_pag[3],'$dev_pag[4]','$dev_pag[5]')";
				$eje8=mysql_query($sql8,$insertar);
				if(!$eje8){
					mysql_query("ROLLBACK",$insertar);
					die("Error al insertar el pago de la devolucion\n\n".$sql8." en ".$nom_insertar."\n\n".mysql_error($insertar));
				}
			}//fin de while $dev_pag

		//actualizamos el id quivalenete localmente
			mysql_query("BEGIN",$extraer);//abrimos transacción para bd origen
			$sql8="UPDATE ec_devolucion SET id_equivalente=$dev_linea WHERE id_devolucion=$dev[0]";
			$eje8=mysql_query($sql8,$extraer);
			if(!$eje8){
				mysql_query("ROLLBACK",$extraer);
				mysql_query("ROLLBACK",$insertar);
				die("Error al actualizar el id equivalente de la devolucion en ".$nom_extraer."!!!\n\n".$sql8."\n\n".mysql_error($extraer));
			}
		//autorizamos transacciones
			mysql_query("COMMIT",$insertar);
			mysql_query("COMMIT",$extraer);
			
			if($i==1){
				$impresiones.=$dev_linea."~";
			}
		
		}//termina while para insertar devoluciones hechas localmente
}//fin de for i

//$impresiones.="|";

/**********************************************************************************************************************************************************
*																																						  *
*                               									Subimos movimientos hechos localmente 												  *
*																																						  *
**********************************************************************************************************************************************************/	
for($i=0;$i<=1;$i++){
//declaramos BD de la que se consulta info
	$extraer=$linea;
	$nom_extraer=' linea';
//declaramos BD de la que se inseta info
	$insertar=$local;
	$nom_insertar=' local ';
	if($i==1){//invertimos valores de conexión
//declaramos BD de la que se consulta info
		$extraer=$local;
		$nom_extraer=' local ';
//declaramos BD de la que se inseta info
		$insertar=$linea;
		$nom_insertar=' linea ';
	}
//consultamos los movimientos locales que no han sido sincronizados
	$sq="SELECT *
			FROM ec_movimiento_almacen/*/ma
			LEFT JOIN ec_pedidos ped ON ec_m*/
			WHERE id_equivalente=0 AND id_movimiento_almacen>0 AND id_sucursal='$user_sucursal'";//is NULL
	$eje=mysql_query($sq,$extraer);
	if(!$eje){
		liberaServidor($local);
		die("Error al consultar los movimientos sin sincronizar en ".$nom_extraer."!!!\n\n".$sq."\n\n".mysql_error($extraer));
	}

	while($r=mysql_fetch_row($eje)){
	//marcamos inicio de transacciones
		mysql_query("BEGIN",$insertar);
		mysql_query("BEGIN",$extraer);
//insertamos el movimieto de almacen en linea (id_pedido:7,id_ord_compra:8lote:9id_maquila:10id_trans:11)
		$sq1="INSERT INTO ec_movimiento_almacen VALUES(/*0*/null,/*1*/'$r[1]',/*2*/'2',/*3*/'$r[3]',/*4*/'$r[4]',/*5*/'$r[5]',/*6*/'$r[6]',/*7*/'-1',/*8*/'-1',/*9*/'-1',/*10*/'-1',
			/*11*/'-1',/*12*/'$r[12]',/*13*/$r[13],/*14*/'$r[0]',/*15*/'$r[15]',/*16*/'$r[16]')";//$r[7],$r[8],$r[9],$r[10],$r[11]
		$eje1=mysql_query($sq1,$insertar);
		if(!$eje1){
			mysql_query("ROLLBACK",$insertar);
			liberaServidor($local);
			die("Error al insertar el movimiento de almacen en ".$nom_insertar."!!!\n\n".$sq1."\n\n".mysql_error($insertar));
		}
	//capturamos el id insertado en linea
		$id_mov_lin=mysql_insert_id($insertar);
//seleccionamos el detalle del movimiento
		$sq1="SELECT id_producto,cantidad,cantidad_surtida,id_pedido_detalle,id_oc_detalle FROM ec_movimiento_detalle WHERE id_movimiento=$r[0]";
		$eje1=mysql_query($sq1,$extraer);
		if(!$eje1){
			mysql_query("ROLLBACK",$insertar);
			liberaServidor($local);
			die("Error al consultar los detalles del movimiento de almacen en ".$nom_extraer."!!!\n\n".$sq1."\n\n".mysql_error());
		}
//insertamos detalle de movimiento de almacen en linea
		while($rw=mysql_fetch_row($eje1)){
			$sq2="INSERT INTO ec_movimiento_detalle VALUES(null,'$id_mov_lin','$rw[0]','$rw[1]','$rw[2]','$rw[3]','$rw[4]')";/*,0 se quitó este cero porque ya no se usa*/
			$eje2=mysql_query($sq2,$insertar);
			if(!$eje2){
				mysql_query("ROLLBACK",$insertar);
				liberaServidor($local);
				die("Error al insertar el  detalle de movimiento de almacen en ".$nom_insertar."!!!\n\n".$sq2."\n\n".mysql_error($insertar));
			}
		}//finaliza while para insertar detalle de movimiento de almacen en linea

	//verificamos si otra sincronización ya actualizó el id equivalente
		$sql="SELECT id_equivalente FROM ec_movimiento_almacen WHERE id_movimiento_almacen='$r[0]'";
		$verifica_dupl=mysql_query($sql,$extraer);
		if(!$verifica_dupl){
			$error=mysql_error($extraer);
			mysql_query("ROLLBACK",$extraer);
			mysql_query("ROLLBACK",$insertar);
			liberaServidor($local);
			die("Error al verificar el id equivalente en ".$nom_extraer."!!!\n\n".$sql."\n\n".$error);
		}
		$res_verif=mysql_fetch_row($verifica_dupl);
		if($res_verif[0]!=0 && $res_verif[0]!='' && $res_verif[0]!=null){
		//deshacemos los cambios
			mysql_query("ROLLBACK",$extraer);
			mysql_query("ROLLBACK",$insertar);
		}else{
		//actualizamos el id equivalente en BD local
			$sq2="UPDATE ec_movimiento_almacen SET id_equivalente='$id_mov_lin' WHERE id_movimiento_almacen='$r[0]'";
			$eje2=mysql_query($sq2,$extraer);
			if(!$eje2){
				mysql_query("ROLLBACK",$extraer);
				mysql_query("ROLLBACK",$insertar);
				liberaServidor($local);
				die("Error al actualizar el id global en ".$nom_extraer."!!!\n\n".$sq2."\n\n".mysql_error($extraer));
			}
			mysql_query("COMMIT",$insertar);
			mysql_query("COMMIT",$extraer);
		}//fin de else

	}//finaliza while para insertar movimientos de almacen en linea
}//fin de for i


liberaServidor($local);
//
echo("ok|".$impresiones);

function liberaServidor($c_loc){
		$libera="UPDATE ec_sincronizacion SET en_proceso=0 WHERE id_sincronizacion=3";
		$eje=mysql_query($libera,$c_loc);
		if(!$eje){
			return 'no';
		}else{
			return 'ok';
		}
	}	
?>