<?php
/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de Sucursales													   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/

$sucInsert=0;
$sucAct=0;
$sql="SELECT /*1*/id_sucursal,/*2*/nombre,/*3*/telefono,/*4*/direccion,/*5*/descripcion,/*6*/id_razon_social,/*7*/id_encargado,/*8*/activo,/*9*/logo,
			/*10*/multifacturacion,/*11*/id_precio,/*12*/descuento,/*13*/prefijo,/*14*/usa_oferta,/*15*/alertas_resurtimiento,/*16*/id_estacionalidad
		FROM sys_sucursales WHERE alta>'$ultimaSinc'";
$eje=mysql_query($sql,$linea);
if(!$eje){
	mysql_query('rollback',$local);
	mysql_query('rollback',$linea);
	die("Error en la consulta!!!\n".mysql_error($linea)."\n".$sql);
}
$sB=mysql_num_rows($eje);
if($sB>0){
	while($row=mysql_fetch_row($eje)){
		$aux="INSERT INTO sys_sucursales(/*1*/id_sucursal,/*2*/nombre,/*3*/telefono,/*4*/direccion,/*5*/descripcion,/*6*/id_razon_social,/*7*/id_encargado,
						/*8*/activo,/*9*/logo,/*10*/multifacturacion,/*11*/id_precio,/*12*/descuento,/*13*/prefijo,/*14*/usa_oferta,/*15*/alertas_resurtimiento,
						/*16*/id_estacionalidad)
				VALUES('$row[0]','$row[1]','$row[2]','$row[3]','$row[4]','$row[5]','$row[6]','$row[7]','$row[8]','$row[9]','$row[10]','$row[11]','$row[12]',
						'$row[13]','$row[14]','1')";
		$eje2=mysql_query($aux,$local);
		if(!$eje2){
			$comp="SELECT id_sucursal FROM sys_sucursales WHERE id_sucursal=$row[0]";
			$ej=mysql_query($comp,$local);
			if(!$ej){
				die('error!!!'.mysql_error($local));
			}
			$nS=mysql_num_rows($ej);
			if($nS==1){
		//remplazamos la sucursal
				$subcons="UPDATE sys_sucursales SET

							/*2*/nombre='$row[1]',
							/*3*/telefono='$row[2]',
							/*4*/direccion='$row[3]',
							/*5*/descripcion='$row[4]',
							/*6*/id_razon_social='$row[5]',
							/*7*/id_encargado='$row[6]',
							/*8*/activo='$row[7]',
							/*9*/logo='$row[8]',
							/*10*/multifacturacion='$row[9]',
							/*11*/id_precio='$row[10]',
							/*12*/descuento='$row[11]',
							/*13*/prefijo='$row[12]',
							/*14*/usa_oferta='$row[13]',
							/*15*/alertas_resurtimiento='$row[14]',
							/*16*/id_estacionalidad='$row[15]'
						WHERE id_sucursal=$row[0]";
				$rempla=mysql_query($subcons,$local);
				if(!$rempla){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die('no se pudo rempzar la sucursal'.mysql_error($local));
				}
				$sucAct++;
			}else{
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				die('error diferente'.mysql_error($local));	
			}
		}
		$sucInsert++;//incrementamos contador
	}
}
echo '<tr style="background:rgba(0,0,225,.5);"><td colspan="4"><font color="white">Sucursales</font></td></tr>';
echo '<tr><td>Sucursales por insertar:</td><td>'.$sB.'</td>';
echo '<td>Sucursales bajadas:</td><td>'.$sucInsert.'</td>';
echo '<tr><td colspan="4">Sucursales actualizadas:</td><td>'.$sucAct.'</td></tr>';

?>