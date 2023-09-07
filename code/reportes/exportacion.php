<?php
	php_track_vars;

	extract($_GET);
	extract($_POST);
	include("../../conect.php");
	include("../../code/general/funciones.php");
	require "../../include/xls_generator/cl_xls_generator.php";
	if($tipo==0)
	{
		$strConsulta="SELECT DISTINCT
						sias_asistentes.id_asistente AS 'ID SIAS',
						CONCAT(sias_asistentes.apellido_paterno,' ',sias_asistentes.apellido_materno,' ',sias_asistentes.nombre) AS 'NOMBRE',
						sias_asistentes.email AS 'Email',
						sias_asistentes.email2 AS 'Email 2',
						sias_asistentes.telefono_casa AS 'Telefono de Casa',
						sias_asistentes.telefono_celular AS 'Telefono Celular',
						sias_asistentes.nextel AS 'Nextel',
						sias_asistentes.telefono_oficina AS 'Telefono de Oficina'
				FROM sias_registros_asistencias 
				LEFT JOIN sias_asistentes ON sias_registros_asistencias.id_asistente=sias_asistentes.id_asistente
				LEFT JOIN sias_eventos_agenda ON sias_registros_asistencias.id_evento=sias_eventos_agenda.id_evento_agenda
				LEFT JOIN sias_eventos ON sias_eventos_agenda.id_evento=sias_eventos.id_evento
				WHERE (sias_eventos.id_evento IN (SELECT id_evento_relacionado FROM sias_eventos_detalles WHERE id_evento='".$evento."') AND sias_registros_asistencias.id_estatus_registro='2'";
		if($solocorreo==1)
			$strConsulta.="AND recibir_correos='1'";
		$strConsulta.=" ) ORDER BY sias_asistentes.apellido_paterno, sias_asistentes.apellido_materno, sias_asistentes.nombre";
		$res=mysql_query($strConsulta) or die("Error en:\n$strConsulta\n\nDescripcion:".mysql_error());
		$num=mysql_num_rows($res);
		$xml_template="<?xml version=\"1.0\"?>";
		$xml_template.="<Workbook>";
		$xml_template.="<Styles>";
		$xml_template.="<style name=\"heading\" bold=\"1\" valign=\"middle\" size=\"9\" align=\"center\" bg_color=\"navy\" color=\"white\" border=\"medium\" />";
		$xml_template.="<style name=\"id\" bold=\"1\" valign=\"middle\" size=\"9\" align=\"center\" bg_color=\"navy\" color=\"white\" border=\"medium\" />";
		$xml_template.="</Styles>";
		$xml_template.="<Worksheet name=\"Listado de Alumnos\">";
		$xml_template.="<Table>";
		$xml_template.="<Row>";
		$xml_template.="<Cell style=\"heading\" width=\"10\">ID SIAS</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"30\">Nombre</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Email</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Email 2</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Telefono de Casa</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Telefono Celular</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Nextel</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Telefono de Oficina</Cell>";
		$xml_template.="</Row>";
		while($row=mysql_fetch_row($res))
		{
			$xml_template.="<Row>";
			$xml_template.="<Cell>".$row[0]."</Cell>";
			for($i=1;$i<count($row);$i++)
			{
				$xml_template.="<Cell>".$row[$i]."</Cell>";
			}
			$xml_template.="</Row>";
		}
		$xml_template.="</Table>";
		$xml_template.="</Worksheet>";
		$xml_template.="</Workbook>";		
	}
	else if($tipo==1)
	{
		$filtros=base64_decode($filtro);
		if($filtros!="")
			$filtros.=" )";
				
		$posicionaux=0;
		$filtrosaux=$filtros;		
		while(($posicionIni=strpos($filtros, 'sias_empresas',$posicionaux))!==FALSE)
		{
			$posicionaux=$posicionIni+1;			
			$posicionand=strpos($filtros, 'AND',$posicionIni);
			$posicionor=strpos($filtros, 'OR',$posicionIni);
			if($posicionand!==FALSE||$posicionor!==FALSE)
			{
				$posicionand=($posicionand===FALSE)?0:$posicionand;
				$posicionor=($posicionor===FALSE)?0:$posicionor;
				if(($posicionand<$posicionor)||$posicionor==0)
					$longFinal=$posicionand-$posicionIni;
				else
					$longFinal=$posicionor-$posicionIni;
			}
			else
				$longFinal=strlen($filtros)-$posicionIni-1;
			$subcadena=substr($filtros,$posicionIni,$longFinal);
			$arrcadenas=explode("sias_empresas",$subcadena);
			$complemento=$arrcadenas[1];
			$cadenaSustitucion="(sias_empresas".$complemento." OR sias_empresas_2".$complemento.") ";
			$filtrosaux=str_replace($subcadena,$cadenaSustitucion,$filtrosaux);			
		}
		$filtros=$filtrosaux;
		
		$posicionaux=0;
		$filtrosaux=$filtros;		
		while(($posicionIni=strpos($filtros, 'sias_estados',$posicionaux))!==FALSE)
		{
			$posicionaux=$posicionIni+1;			
			$posicionand=strpos($filtros, 'AND',$posicionIni);
			$posicionor=strpos($filtros, 'OR',$posicionIni);
			if($posicionand!==FALSE||$posicionor!==FALSE)
			{
				$posicionand=($posicionand===FALSE)?0:$posicionand;
				$posicionor=($posicionor===FALSE)?0:$posicionor;
				if(($posicionand<$posicionor)||$posicionor==0)
					$longFinal=$posicionand-$posicionIni;
				else
					$longFinal=$posicionor-$posicionIni;
			}
			else
				$longFinal=strlen($filtros)-$posicionIni-1;
			$subcadena=substr($filtros,$posicionIni,$longFinal);
			$arrcadenas=explode("sias_estados",$subcadena);
			$complemento=$arrcadenas[1];
			$cadenaSustitucion="(sias_estados".$complemento." OR sias_estados_2".$complemento.") ";
			$filtrosaux=str_replace($subcadena,$cadenaSustitucion,$filtrosaux);			
		}
		$filtros=$filtrosaux;

		$posicionaux=0;
		$filtrosaux=$filtros;		
		while(($posicionIni=strpos($filtros, 'sias_ciudades',$posicionaux))!==FALSE)
		{
			$posicionaux=$posicionIni+1;			
			$posicionand=strpos($filtros, 'AND',$posicionIni);
			$posicionor=strpos($filtros, 'OR',$posicionIni);
			if($posicionand!==FALSE||$posicionor!==FALSE)
			{
				$posicionand=($posicionand===FALSE)?0:$posicionand;
				$posicionor=($posicionor===FALSE)?0:$posicionor;
				if(($posicionand<$posicionor)||$posicionor==0)
					$longFinal=$posicionand-$posicionIni;
				else
					$longFinal=$posicionor-$posicionIni;
			}
			else
				$longFinal=strlen($filtros)-$posicionIni-1;
			$subcadena=substr($filtros,$posicionIni,$longFinal);
			$arrcadenas=explode("sias_ciudades",$subcadena);
			$complemento=$arrcadenas[1];
			$cadenaSustitucion="(sias_ciudades".$complemento." OR sias_ciudades_2".$complemento.") ";
			$filtrosaux=str_replace($subcadena,$cadenaSustitucion,$filtrosaux);			
		}
		$filtros=$filtrosaux;
		$strConsulta="SELECT DISTINCT
						sias_asistentes.id_asistente AS 'ID SIAS',
						CONCAT(sias_asistentes.apellido_paterno,' ',sias_asistentes.apellido_materno,' ',sias_asistentes.nombre) AS 'NOMBRE',
						sias_asistentes.email AS 'Email',
						sias_asistentes.email2 AS 'Email 2',
						sias_asistentes.telefono_casa AS 'Telefono de Casa',
						sias_asistentes.telefono_celular AS 'Telefono Celular',
						sias_asistentes.nextel AS 'Nextel',
						sias_asistentes.telefono_oficina AS 'Telefono de Oficina'
				FROM sias_registros_asistencias 
				LEFT JOIN sias_asistentes ON sias_registros_asistencias.id_asistente=sias_asistentes.id_asistente
				LEFT JOIN sias_eventos_agenda ON sias_registros_asistencias.id_evento=sias_eventos_agenda.id_evento_agenda
				LEFT JOIN sias_eventos ON sias_eventos_agenda.id_evento=sias_eventos.id_evento						
				WHERE sias_asistentes.id_asistente IN (
		
					SELECT 
							DISTINCT sias_asistentes.id_asistente
					FROM sias_asistentes
					LEFT JOIN sias_registros_asistencias ON sias_registros_asistencias.id_asistente=sias_asistentes.id_asistente
					LEFT JOIN sias_eventos_agenda ON sias_eventos_agenda.id_evento_agenda=sias_registros_asistencias.id_evento
					LEFT JOIN sias_eventos ON sias_eventos_agenda.id_evento=sias_eventos.id_evento
					LEFT  JOIN sys_sexo  ON sias_asistentes.id_sexo=sys_sexo.id_sexo
					LEFT  JOIN sias_estados  ON sias_asistentes.id_estado=sias_estados.id_estado
					LEFT  JOIN sias_profesiones  ON sias_asistentes.id_profesion=sias_profesiones.id_profesion
					LEFT  JOIN sias_ocupaciones ON sias_asistentes.id_ocupacion=sias_ocupaciones.id_ocupacion
					LEFT  JOIN sias_escolaridades  ON sias_asistentes.id_escolaridad=sias_escolaridades.id_escolaridad
					LEFT  JOIN sias_motivos_desintereses  ON sias_asistentes.id_motivo_desinteres=sias_motivos_desintereses.id_motivo_desinteres
					LEFT  JOIN sias_asociaciones_becas  ON sias_asistentes.id_asociacion=sias_asociaciones_becas.id_asociacion
					LEFT  JOIN sias_empresas  ON sias_asistentes.id_empresa=sias_empresas.id_empresa
					LEFT  JOIN sias_ciudades  ON sias_ciudades.id_ciudad=sias_asistentes.id_ciudad
					LEFT  JOIN sias_asistentes_empresas ON sias_asistentes.id_asistente=sias_asistentes_empresas.id_asistente
					LEFT  JOIN sias_empresas as sias_empresas_2 ON sias_asistentes_empresas.id_empresa_relacionada=sias_empresas_2.id_empresa
					LEFT  JOIN sias_numero_empleados  ON sias_numero_empleados.id_numero_empleados=sias_empresas_2.id_numero_empleados
					LEFT  JOIN sias_estados as sias_estados_2 ON sias_empresas_2.id_estado=sias_estados_2.id_estado
					LEFT  JOIN sias_ciudades as sias_ciudades_2  ON sias_ciudades_2.id_ciudad=sias_empresas_2.id_ciudad
					LEFT JOIN sias_estatus_registros ON sias_estatus_registros.id_estatus_registro=sias_registros_asistencias.id_estatus_registro
					WHERE 1 XXX   ) ORDER BY sias_asistentes.apellido_paterno, sias_asistentes.apellido_materno, sias_asistentes.nombre";
		$strConsulta=str_replace("XXX",$filtros,$strConsulta);
		$strConsulta=str_replace("*",'%',$strConsulta);
		$res=mysql_query($strConsulta) or die("Error en:\n$strConsulta\n\nDescripcion:".mysql_error());
		$num=mysql_num_rows($res);
		//echo $strConsulta;
		$xml_template="<?xml version=\"1.0\"?>";
		$xml_template.="<Workbook>";
		$xml_template.="<Styles>";
		$xml_template.="<style name=\"heading\" bold=\"1\" valign=\"middle\" size=\"9\" align=\"center\" bg_color=\"navy\" color=\"white\" border=\"medium\" />";
		$xml_template.="<style name=\"id\" bold=\"1\" valign=\"middle\" size=\"9\" align=\"center\" bg_color=\"navy\" color=\"white\" border=\"medium\" />";
		$xml_template.="</Styles>";
		$xml_template.="<Worksheet name=\"Listado de Alumnos\">";
		$xml_template.="<Table>";
		$xml_template.="<Row>";
		$xml_template.="<Cell style=\"heading\" width=\"10\">ID SIAS</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"30\">Nombre</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Email</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Email 2</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Telefono de Casa</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Telefono Celular</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Nextel</Cell>";
		$xml_template.="<Cell style=\"heading\" width=\"20\">Telefono de Oficina</Cell>";
		$xml_template.="</Row>";
		while($row=mysql_fetch_row($res))
		{
			$xml_template.="<Row>";
			$xml_template.="<Cell>".$row[0]."</Cell>";
			for($i=1;$i<count($row);$i++)
			{
				$xml_template.="<Cell>".utf8_decode(htmlentities($row[$i]))."</Cell>";
			}
			$xml_template.="</Row>";
		}
		$xml_template.="</Table>";
		$xml_template.="</Worksheet>";
		$xml_template.="</Workbook>";	
	}

// TEMP DIRECTORY PATH - !!! YOU SHOULD HAVE THE WRITING PERMISSIONS TO IT
$temp_dir = "../../cache/Excel";

// RESULT FILE NAME
$file_name = "exportacion_correos.xls";

// MAIN CLASS CALL
$xls = new xls($xml_template,$file_name,"xls_config.inc",$temp_dir);


// PASSING THE GENERATED FILE TO THE USER
header("Content-Type: application/X-MS-Excel; name=\"$file_name\"");
header("Content-Disposition: attachment; filename=\"$file_name\"");

$fh=fopen($file_name, "rb");
fpassthru($fh);
unlink($file_name);/**/
?>