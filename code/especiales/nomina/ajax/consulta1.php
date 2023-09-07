<?php
	<?php
	$sql = "SELECT 
				rn.id_registro_nomina,/*0*/
				CONCAT( u.nombre, ' ', u.apellido_paterno ) AS empleado,/*1*/
				a.Date,/*2*/
				GROUP_CONCAT( 
					CONCAT(
						rn.hora_entrada, '~' ,
						rn.hora_salida, '~',
						rn.id_registro_nomina, '~',
						TIMEDIFF( rn.hora_salida, rn.hora_entrada)
						/*SUM(ROUND(TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%H') +(TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%i')/60),2))*/
					) 
					SEPARATOR '|'
				) AS asistencias,/*3*/ 
			 	/*TIMEDIFF( rn.hora_salida, rn.hora_entrada) AS */'TiempoLaborado',/*4*/
			 	u.horario_entrada,/*5*/
			 	u.tiempo_tolerancia,/*6*/
			 	u.descuento_por_retardo,/*7*/
			 	u.descuento_no_registrarse,/*8*/
			 	u.descuento_por_faltar/*9*/
			FROM (
				SELECT 
					curdate() - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a) ) DAY AS Date
				FROM (
					SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
				) AS a
				CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
				) AS b
				CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
				) AS c
			    CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
		    	) AS d
			)a
			LEFT JOIN sys_users u ON {$usuario} = u.id_usuario
			LEFT JOIN sys_sucursales s ON u.id_sucursal = s.id_sucursal
			LEFT JOIN ec_registro_nomina rn ON rn.fecha = a.Date AND rn.id_empleado = u.id_usuario
			WHERE a.Date BETWEEN '{$_POST['initial_date']}' AND '{$_POST['final_date']}'
			GROUP BY u.id_usuario, a.Date
			ORDER BY u.id_usuario,a.Date ASC";
	
?>
?>