<?php
	extract($_GET);
	extract($_POST);	
	include("../../conectMin.php");
	$ip = sprintf("%u",ip2long($_SERVER['REMOTE_ADDR']));
	$namef=$rootpath."/cache/".$tabla."_".$llave."_".$ip.".dat";
	$fileant="";

	if($iteracion > 0){
		$file=fopen($namef,"rt");
		if($file){
			while(!feof($file)){
				$cadaux=fread($file,1000);
				$fileant.=$cadaux;
			}
		}		
		fclose($file);	
	}
	$file = fopen($namef,"wt");
	if($file){			
		$myQuery=array();
					
		if($iteracion > 0){	
			$myQuery=explode("|",$fileant);
			array_pop($myQuery);			
		}else{
			if($id_grid == 26){
				array_push($myQuery,"DELETE FROM $tabla WHERE $campoid = (SELECT id_oc FROM ec_cuentas_por_pagar WHERE id_cxp='$llave')");
			}else if($id_grid == 27){
				array_push($myQuery,"DELETE FROM $tabla WHERE $campoid = (SELECT id_pedido FROM ec_cuentas_por_cobrar WHERE id_cxc='$llave')");
			}elseif($id_grid==2){
				array_push($myQuery,"DELETE FROM $tabla WHERE $campoid = -20");				
			}else{			
				array_push($myQuery,"DELETE FROM $tabla WHERE $campoid = '$llave'");
			}
		}				
			
		$sql="SELECT campo_tabla FROM `sys_grid_detalle` WHERE id_grid=$id_grid ORDER BY orden";
		$res=mysql_query($sql);
		if(!$res){
			die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
		}
		$num=mysql_num_rows($res);
		if($num <= 0){
			die("No se encontraron campos para la tabla [$tabla]");
		}
		$campos=array();
		for($i=0;$i<$num;$i++){
			$row=mysql_fetch_row($res);
			array_push($campos,$row[0]);
		}
		
		for($i=0;$i<$numdatos;$i++){
			$sql="SELECT ".$campos[0]." FROM $tabla WHERE ".$campos[0]."='".$dato1[$i]."'";		
			$res=mysql_query($sql);
			if(!$res)
				die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
			$num=mysql_num_rows($res);	
			if($num <= 0){
				$sql = "";
				if( $id_grid == 9 ){//implementacion Oscar 2024 para insertar detalles de movimientos de almacen con Procedure
					$sql = "CALL spMovimientoAlmacenDetalle_inserta( '\$LLAVE', {$dato5[$i]}, {$dato7[$i]}, {$dato8[$i]}, {$dato3[$i]}, {$dato4[$i]}, {$dato9[$i]}, 3, NULL )";
				}else{
					$sql="INSERT INTO $tabla(";
					for($j=0;$j<sizeof($campos);$j++){
						if($j > 0 && $campos[$j] != "NO"){
							if($j > 1){
								$sql.=",";
							}
							$sql.=$campos[$j];	
						}	
					}				
					$sql.=") VALUES(";
					for($j=0;$j<sizeof($campos);$j++){
						if($j == 1 && ($id_grid == 26 || $id_grid == 27)){
							$aux="dato".($j+1);
							$ax=$$aux;
							$sql.=$ax[$i];
						}else if($j > 0 && $campos[$j] != "NO"){
							if($j > 1){
								$sql.=",";
							}
							$aux="dato".($j+1);
							$ax=$$aux;//die("here : " . $aux[$i]);		
							if($ax[$i] == '$id_usuario'){
								$sql.=$_SESSION["USR"]->userid;
							}else{	
								$sql.="'".$ax[$i]."'";						
							}
						}		
					}
					$sql.=")";//die($sql);
				}
				array_push($myQuery, $sql);
			}else{
				$sql="UPDATE $tabla SET ";
				for($j=0;$j<sizeof($campos);$j++){
					if($campos[$j] != "NO"){
						if($j > 0){
							$sql.=",";
						}
						$sql.=$campos[$j]."='";	
						$aux="dato".($j+1);
						$ax=$$aux;
						$sql.=$ax[$i]."'";
					}	
				}				
				$sql.=" WHERE ".$campos[0]."=".$dato1[$i];

				array_push($myQuery, $sql);	

				$myQuery[0].=" AND ".$campos[0]." <> ".$dato1[$i];
				
			}//fin de else
		}		
		
		for($i=0;$i<sizeof($myQuery);$i++){
			fwrite($file,$myQuery[$i]."|");
		}	
	}
	fclose($file);
	echo "exito|".$namef;
?>