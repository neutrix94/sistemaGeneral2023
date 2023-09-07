<?php
	include("../../../../conectMin.php");
	extract($_POST);
	//die('ok|'.$datos);
	$arr=explode("|",$datos);
//consultamos nombre del proveedor
	$sql="SELECT prov.nombre_comercial
			FROM ec_proveedor prov 
			WHERE prov.id_proveedor=$id_proveedor";
	$ejeProv=mysql_query($sql)or die("Error al buscar nombre del proveedor!!!\n\n".$sql."\n\n".mysql_error());
	$rw=mysql_fetch_row($ejeProv);
	$nom_prov=$rw[0];//aqui guardamos el nombre del proveedor

//empatamos CSV con BD
	$respuesta='<table id="contenido_pro_prod">';
	for($i=0;$i<sizeof($arr);$i++){
		//echo $arr[$i];
		$arr2=explode(",",$arr[$i]);
		$sql="SELECT 
				p.id_productos,
				p.orden_lista,
				pp.clave_proveedor,
				p.nombre,
				p.clave
				FROM ec_productos p
			/**/
				LEFT JOIN ec_proveedor_producto pp ON p.id_productos=pp.id_producto
			/**/	
				WHERE p.clave LIKE '%$arr2[0]%' OR(pp.clave_proveedor LIKE '%$arr2[0]%' AND pp.id_proveedor=$id_proveedor)"; 
				/*OR p.nombre like '%$arr2[1]%')/*AND pp.id_proveedor=$id_proveedor*/
				//die($sql);
		$eje=mysql_query($sql)or die("Error al consultar coincidencias de proveedor en BD!!!\n\n".$sql."\n\n".mysql_error());
	//
		if($i%2==0){
			$color="#E6E8AB";
		}else{
			$color="#BAD8E6";
		}
	//si no hay coincidencias o hay mas de una coincidencia
		$segunda_busqueda="";
		//if(mysql_num_rows($eje)>1){
			while($r=mysql_fetch_row($eje)){
				$arr_claves=explode(",",$r[4]);
				for($k=0;$k<sizeof($arr_claves);$k++){
					if($arr_claves[$k]==$arr2[0]){
						$sql="SELECT id_productos,orden_lista,'$arr2[0]',nombre FROM ec_productos WHERE id_productos=$r[0]";
						$eje_2=mysql_query($sql)or die("Error en la segunda busqueda!!!\n".mysql_error()."\n".$sql);
						if(mysql_num_rows($eje_2)==1){
							$r_2=mysql_fetch_row($eje_2);
							$segunda_busqueda=$r[0]."~".$r[1]."~".$r[2]."~".$r[3];
						}
					}
				}
			}
		//}
	//si hay una coincidencia
		if($segunda_busqueda!=""){
			$r=mysql_fetch_row($eje);
			$aux_cod=explode(",",$r[2]);
			$cods="";
			for($j=0;$j<sizeof($aux_cod);$j++){
				$cods.=$aux_cod[$j];
				if($j<sizeof($aux_cod)-1){
					$cods.="\n";
				}
			}
		//asignamos los valores que se encontraron el la busqueda desglozada
			if($segunda_busqueda!=""){
				$aux_seg_bsq=explode("~",$segunda_busqueda);
				$r[0]=$aux_seg_bsq[0];
				$r[1]=$aux_seg_bsq[1];
				$r[2]=$aux_seg_bsq[2];
				$r[3]=$aux_seg_bsq[3];
			}
//			echo "Arr_1:".$arr2[1];
			$respuesta.='<tr id="fila_'.$i.'" tabindex="'.$i.'" style="background:'.$color.';height: auto;">';
				/*1*/$respuesta.='<td width="10%"><input type="text" class="edita_txt" id="c_1_'.$i.'" onclick="posicion_coordenadas(this);" value="'.$r[1].
				'" onfocus="enfoca(this,1,'.$i.');" onblur="desenfoca(this,1,'.$i.');" onkeyup="buscador(event,'.$i.',2);"></td>';//orden de lista 
				/*2*/$respuesta.='<td style="display:none;" id="id_prod_'.$i.'" value="'.$r[0].'"></td>';//id de producto
				/*3*/$respuesta.='<td width="10%" id="c_2_'.$i.'">'.$arr2[0].'</td>';//codigo de proveedor//$cods
				/*4*/$respuesta.='<td width="10%">'.$nom_prov.'</td>';//nombre del proveedor
				/*5*/$respuesta.='<td width="20%" id="nom_prd_sys_'.$i.'">'.$r[3].'</td>';//nombre del producto proveedor
				/*6*/$respuesta.='<td width="20%">'.$arr2[1].'</td>';//nombre del producto en sistema
				/*7*/$respuesta.='<td width="11.5%" id="c_3_'.$i.'">'.$arr2[3].'</td>';//precio caja
				/*8*/$respuesta.='<td width="11.5%" id="c_4_'.$i.'">'.$arr2[2].'</td>';//presentacion
				/*9*/$respuesta.='<td width="7%" align="center"><img src="../../../img/especiales/del.png" width="50%" onclick="eliminar('.$i.');"></td>';//quitar
			$respuesta.='</tr>';
		}else{//de lo contrario...
			$respuesta.='<tr id="fila_'.$i.'" tabindex="'.$i.'" style="background:'.$color.';"  onfocus="enfoca(this,1,'.$i.');" onblur="desenfoca(this,1,'.$i.');">';
				/*1*/$respuesta.='<td width="10%"><input type="text" class="edita_txt" id="c_1_'.$i.'" onclick="posicion_coordenadas(this);" onkeyup="buscador(event,'.$i.',2);"'.
				'onfocus="enfoca(this,1,'.$i.');" onblur="desenfoca(this,1,'.$i.');"></td>';//onclick="edita_valor('.$i.');"
				/*2*/$respuesta.='<td style="display:none;" id="id_prod_'.$i.'" value="0"></td>';//id de producto
				/*3*/$respuesta.='<td width="10%" id="c_2_'.$i.'">'.$arr2[0].'</td>';//codigo de proveedor
				/*4*/$respuesta.='<td width="10%">'.$nom_prov.'</td>';//nombre del proveedor
				/*5*/$respuesta.='<td width="20%" id="nom_prd_sys_'.$i.'"></td>';
				/*6*/$respuesta.='<td width="20%">'.$arr2[1].'</td>';
				/*7*/$respuesta.='<td width="11.5%" id="c_3_'.$i.'">'.$arr2[3].'</td>';
				/*8*/$respuesta.='<td width="11.5%" id="c_4_'.$i.'">'.$arr2[2].'</td>';
				/*9*/$respuesta.='<td width="7%" align="center"><img src="../../../img/especiales/del.png" width="50%" onclick="eliminar('.$i.','.$r[7].');"></td>';
			$respuesta.='</tr>';
		}
	}//fin de for i
	
	$respuesta.='</table><input type="hidden" id="total_filas" value="'.(sizeof($arr)).'">';
echo 'ok|'.$respuesta;
?>
<style>
	.edita_txt{
		width: 90%;
	}
</style>