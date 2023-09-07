<?php
	include('../../conectMin.php');//incluimos el archivo de conexión
	
	$arr_prods=explode(",",$_GET['arreglo']);
	print_r("resp:".$arr_prods);
	
	if( isset($_GET['id_lista_precio']) ){
		$precio="pd.id_precio=".$_GET['id_lista_precio'];
	}else{
		$precio="pd.id_precio=s.id_precio";
	}

	$productos_no_mayoreo="";
	$productos_si_mayoreo="";
	for($i=0;$i<sizeof($arr_prods);$i++){
		$aux=explode("~",$arr_prods[$i]);
		$sql="SELECT
				p.orden_lista,
				$aux[1],
				IF(pd.id_precio IS NULL,0,1) as entra_mayoreo
			FROM ec_productos p 
			LEFT JOIN ec_precios_detalle pd ON $precio ";
		$eje=mysql_query($sql)or die("Error al buscar coincidencia de precios\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		if($r[2]==0){
			$productos_no_mayoreo+=$r[2]."~".$r[2].",";
		}else{
			$productos_si_mayoreo+=$r[2]."~".$r[2].",";
		}
	}//fin de for i

	die('ok|'.$productos_no_mayoreo.'|');
//consultamos los precios de los productos que no entraron en el mayoreo
		$btn_1='<button class="btn_emgr" onclick="agregaFila(null,';
            if(can.value==''){btn_1+=cant;}else{btn_1+=can.value;}
            btn_1+=',-1,'+aux[2]+',1);document.getElementById(\'emergePermisos\').style.display=\'none\';';
            if(enlistando_paquete!=0){
                    btn_1+='enlista_paquete();';
            }
            btn_1+='">Aceptar</button>';/*agregaFila(null,'+can.value+',0,'+id_prod_selec+');*/
            var btn_2='<button class="btn_emgr" onclick="document.getElementById(\'cantidad2\').value=\'\';document.getElementById(\'buscadorLabel\').value=\'\';';
            btn_2+='document.getElementById(\'emergePermisos\').style.display=\'none\';document.getElementById(\'buscadorLabel\').focus();';
            if(enlistando_paquete!=0){
                    btn_2+='enlista_paquete();';
            }
            btn_2+='">Cancelar</button>';
?>