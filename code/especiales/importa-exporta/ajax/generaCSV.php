<?php
	include('../../../../conectMin.php');
	$sql="SELECT prefijo FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
	$eje=mysql_query($sql)or die("Error\n\n");
	$rw=mysql_fetch_row($eje);
	extract($_POST);
	$nombre="reporteDeVentas.csv";
	$aux1=explode("~",$datos);
	$ordenados=ordena($aux1,sizeof($aux1));
	$aux1=$ordenados;
	$aux2="";
	$dato="ID,Fecha,Hora,Monto,Folio,Estatus,nombre,Exportado,Forma\n";
//recorremos arreglos
	//sort($aux1,2);
	for($i=0;$i<sizeof($aux1);$i++){
		//$aux2=explode("|",$aux1[$i]);
		for($j=0;$j<sizeof($aux2);$j++){
			if($j==4){
				$dato.=$rw[0].$aux2[$j];
			}else{
				$dato.=$aux2[$j];
			}
			if($j<sizeof($aux2)-1){
				$dato.=",";
			}else{
				$dato.="\n";
			}
		}
	}
	header('Content-Type: application/octet-stream');
	header('Content-Transfer-Encoding:Binary');
	header('Content-disposition: attachment; filename="'.$nombre.'"');
	echo $dato;

	function ordena($A,$n){
		for($i=1;$i<$n;$i++){
            for($j=0;$j<$n-$i;$j++){
            	$uno=explode("|",$a[$j]);
            	$dos=explode("|",$a[$j+1]);
                if($uno[2]>$dos[2]){
                	$k=$a[$j+1]; 
                	$A[$j+1]=$a[$j]; 
                	$a[$j]=$k;
                }
            }
        }
      return $A;
	}
?>