<?php
	include("../../../../conectMin.php");
	include("../../../../include/tcpdf/tcpdf.php");

extract($_GET);
extract($_POST);
$numProd = 0;
$numProd = count($arr);
//recibimos variable $ofert
//------------ CONSULTA BASE  (Se anidan para sacar de lista de precios interna, externa Oscar 15.08.2018)-------------//
	if($arr2[1] == 4 || $arr2[1] == 5){
	//aqui seleccionamos los producto que tienen oferta y apartir de cuantas piezas entra la oferta
		$query="SELECT 
					ax1.nombre_etiqueta,
					ax1.precio,
					ax1.orden_lista,
					ax1.id_productos,
					ax1.de_valor,
					ax1.es_externo,
					ax1.oferta
				FROM(
				SELECT
					ax.nombre_etiqueta,
					IF(ax.es_externo=0,ax.precio,CONCAT(pd_1.precio_venta,' X ',pd_1.de_valor)) as precio,
					ax.orden_lista,
					ax.id_productos,
					ax.de_valor,
					ax.es_externo,
					IF(ax.es_externo=0,ax.es_oferta,pd_1.es_oferta) as oferta
				FROM(
				SELECT 
					p.nombre_etiqueta,
					CONCAT(pd.precio_venta,' X ',pd.de_valor) as precio,
					p.orden_lista,
					p.id_productos,
					pd.de_valor,
					sp.es_externo,
					pd.es_oferta
					FROM ec_productos p
					JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos AND sp.id_sucursal=$user_sucursal AND sp.estado_suc=1
					JOIN sys_sucursales s ON s.id_sucursal=sp.id_sucursal
					JOIN ec_precios pr ON s.id_precio = pr.id_precio
					JOIN ec_precios_detalle pd ON p.id_productos = pd.id_producto AND pd.de_valor > 1 AND pd.id_precio = pr.id_precio/*".$ofert."*/
					WHERE ";
	}else{//si arr2[1] es 1,2 o 3
	//aqui seleccionamos los productos y su precio normal
		$query="SELECT 
					ax1.nombre_etiqueta,
					ax1.precio,
					ax1.orden_lista,
					ax1.id_productos,
					ax1.es_externo,
					ax1.oferta
				FROM(
				SELECT
					ax.nombre_etiqueta,
					IF(ax.es_externo=0,ax.precio_venta,pd_1.precio_venta) as precio,
					ax.orden_lista,
					ax.id_productos,
					ax.es_externo,
					IF(ax.es_externo=0,ax.es_oferta,pd_1.es_oferta) as oferta
				FROM(
					SELECT 
						p.nombre_etiqueta,
						pd.precio_venta,
						p.orden_lista,
						p.id_productos,
						sp.es_externo,
						pd.es_oferta 
					FROM ec_productos p
					JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos AND sp.id_sucursal=$user_sucursal AND sp.estado_suc=1
					JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal AND s.id_sucursal IN($user_sucursal)
					JOIN ec_precios pr ON s.id_precio = pr.id_precio
					JOIN ec_precios_detalle pd ON p.id_productos = pd.id_producto AND pd.de_valor=1/*no quitar este porque si no muestra todos los precios Oscar 24.10.2018*/ 
					AND pd.id_precio = pr.id_precio /*".$ofert."*/
					WHERE ";	
	}

//filtro de categoría
	if($fil[0] != (-1)){
		$query .= " p.id_categoria ='$fil[0]'";
	}
//filtro de subcategoría
	if($fil[1] != (-1)){
			if($fil[0] != (-1)){
				$query .= " AND p.id_subcategoria = '$fil[1]'";
			}
			else{
				$query .= " p.id_subcategoria = '$fil[1]'";
			}
	}
//filtro de subtipo
	if($fil[2] != (-1) && $fil[2] != 0 ){
			if($fil[1] != (-1)){
				$query .= " AND p.id_subtipo = '$fil[2]'";
			}
			else{
				$query .= "p.id_subtipo = '$fil[2]'";
			}		
	}
//filtro de rango de precios de venta
	if($fil[3] != 0 && $fil[4] != 0){
		if($fil[0] != (-1) || $fil[1] !=(-1) || $fil[2] != (-1)){
		   $query .= " AND pd.precio_venta >= '$fil[3]' AND pd.precio_venta <= '$fil[4]'";
		}else{
			 $query .= " pd.precio_venta >= '$fil[3]' AND pd.precio_venta <= '$fil[4]'";
		}
	}
	
	$canProds=array();
	
//si es desde productos capturados manuelamente
	if($numProd > 0){
		if($arr[0] != null){
	    for($i=0;$i<$numProd;$i++){
			if(isset($canProds[$arr[$i]]))
				$canProds[$arr[$i]]++;
			else
				$canProds[$arr[$i]]=1;	
		
				if($fil[0] != (-1) || $fil[1] !=(-1) || $fil[2] != 0|| $fil[3] != 0 && $fil[4] != 0)
				{
					 $query .= " OR p.id_productos ='$arr[$i]'";
				}
				else{	
					if($i==0){
						$query .= " p.id_productos ='$arr[$i]'";
					}else{
						$query .= " OR p.id_productos ='$arr[$i]'";
					}
				} 
		}//fin de for $i	
		}
	}
/*implementación Oscar 15.08.2018 para obtener precios externos*/
	$query.=")ax LEFT JOIN sys_sucursales_producto sp_1 ON ax.id_productos=sp_1.id_producto AND sp_1.id_sucursal=$user_sucursal AND sp_1.estado_suc=1
					LEFT JOIN sys_sucursales s_1 ON sp_1.id_sucursal=s_1.id_sucursal AND s_1.id_sucursal IN($user_sucursal)
					LEFT JOIN ec_precios pr_1 ON s_1.lista_precios_externa = pr_1.id_precio
					LEFT JOIN ec_precios_detalle pd_1 ON sp_1.id_producto=pd_1.id_producto"; 
/*cambio de Oscar 24.10.2018*/

	if($arr[1]<=3){
		$query.=" AND pd_1.de_valor = 1";
	}

	$query.=" AND pd_1.id_precio = pr_1.id_precio
					)ax1".$ofert;//.$oferta_anidada
	/*fin de cambio Oscar 15.08.2018*/
/*fin de Cambio Oscar 15.08.2018*/

//die('o|'.$query);
//implementación de Ocar 22.05.2018 para impresión de paquetes
	if($paquete==1){
		//-------------- CONFIGURACION INICIAL DEL PDF --------------//
		$pdf = new TCPDF('L','mm','LETTER', true, 'UTF-8', false);//$orientacion
		$pdf->SetMargins(3,3,3);
		$pdf->SetAutoPageBreak(false,5);
		$pdf->SetDrawColor (0,-1,-1,-1, $ret=false, $name='red');
 		$pdf->setPrintHeader(false); //no imprime la cabecera ni la linea 
		$pdf->setPrintFooter(false);
		$fontname=$pdf->addTTFfont('TobagoPoster.ttf', 'TrueTypeUnicode', '', 32);
		
		$tw_cent=$pdf->addTTFfont('TwCen.ttf', 'TrueTypeUnicode', '', 32);
		$arial_black=$pdf->addTTFfont('ArialBlack.ttf', 'TrueTypeUnicode', '', 32);
		$bernard=$pdf->addTTFfont('BernardMTCondensed.ttf', 'TrueTypeUnicode', '', 32);
		$arial_narrow=$pdf->addTTFfont('ArialNarrow.ttf', 'TrueTypeUnicode', '', 32);
		$rockwell=$pdf->addTTFfont('Rockwell.ttf', 'TrueTypeUnicode', '', 32);
		$arial2=$pdf->addTTFfont('608.ttf', 'TrueTypeUnicode', '', 32);

	//--------------------- CREACION DEL PDF --------------------//	
	//listamos los paquetes
		$query="SELECT id_paquete,nombre FROM ec_paquetes";
		$eje=mysql_query($query)or die("Error al consultar paquetes!!!\n\n".$sql."\n\n".mysql_error());
		while($r=mysql_fetch_row($eje)){
			$pdf->AddPage();
			$pdf->Image('../../../../img/img_casadelasluces/marco.png',0,0,280,216,'png');
			$pdf->Image('../../../../img/img_casadelasluces/logocasadelasluces-easy.png',220,118,70,90,'png');
			$pdf->SetX(5);$pdf->SetY(10);
			$pdf->SetFont($bernard,'B',60);
			$pdf->SetTextColor(225,0,0);
			$pdf->cell(270,15,$r[1],0,1,'C');
		//consultamos detalle del paquete
			$query="SELECT
						ax.orden_lista,
						ax.nombre,
						ax.cantidad_producto,
						ax.monto_pieza_descuento,
						ax.subtotal_prods,
						ax.es_externo
					FROM(
						SELECT 
							p.orden_lista,/*0*/
							p.nombre,/*1*/
							pd.cantidad_producto,/*2*/
							pd.monto_pieza_descuento,/*3*/
							pd.cantidad_producto*pd.monto_pieza_descuento AS subtotal_prods,/*4*/
							sp.es_externo		
						FROM ec_productos p 
						LEFT JOIN ec_paquete_detalle pd ON p.id_productos=pd.id_producto
						LEFT JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
						LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto AND s.id_sucursal=sp.id_sucursal AND sp.estado_suc=1   
						WHERE pd.id_paquete=$r[0]
					)ax";
			$eje1=mysql_query($query)or die("Error al consultar detalle del paquete!!!\n\n".$query."\n\n".mysql_error());
			
			$pdf->SetFont($bernard,'B',30);
			$pdf->SetTextColor(0,225,0);
		//
			$total=0;
			while($rw=mysql_fetch_row($eje1)){
				$pdf->SetX(12);
				$pdf->Cell(100,10,$rw[0]."| ".$rw[1]."| ".$rw[2]." x ".$rw[3]."=".$rw[4],0,1,'L');
				$total+=$rw[2]*$rw[3];
			}
			$query2="SELECT CEIL($total-($total*descuento)) FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
			$eje3=mysql_query($query2)or die("|Error al calcular precio menos descuento por paquete!!!\n\n".$query2."\n\n".mysql_error());
		//ponemos total del paquete
			$pdf->SetX(5);$pdf->SetY(170);
			$pdf->SetFont($bernard,'B',50);
			$pdf->SetTextColor(225,0,0);
			$pdf->Cell(270,10,"Total: $".$total,0,0,'C');

		}//fin de while $r
		$ruta = '../../../../etiquetas/etiquetas'.date(ymd_his).'.pdf';
		if(!$pdf->Output($ruta,'F'))
		{
			echo 'ok|'.$ruta;
		}
		else
		{
			$pdf->Error('Error al generar las etiquetas,intenta nuevamente profavor');
		}
		die('');
			//die("|".$query);

}//fin de cambio 22.05.2018


	//$query.=$ofert;
	//print_r($canProds);
	
//die('ok|'.$query);		
	$result = mysql_query($query) or die ('Productos: '.mysql_error());
	$cant   = mysql_num_rows($result);
	
	if($cant > 0){
    //echo $query;
	if($arr2[1] == 1){
		$altura      = 51;
		$ancho       = 88;
		$orientacion = 'L';
		$tfuente     = 70;
		$tfuente2    = 15;
		$xx          = 3;
		$xy          = 11;
		$flag        = 5;
	}
	if($arr2[1] == 2){
		$altura      = 98;
		$ancho       = 133;
		$orientacion = 'L';
		$tfuente     = 110;
		$tfuente2    = 30;
		$xx          = 2;
		$xy          = 3;
		$flag        = 5;
	}
	if($arr2[1] == 3){
		$altura      = 130;
		$ancho       = 100;
		$orientacion = 'P';
		$tfuente     = 40;
		$tfuente2    = 25;
		$xx          = 2;
		$xy          = 3;
		$flag        = 0;
	}
	
	if($arr2[1] == 4){
		$altura      = 51;
		$ancho       = 88;
		$orientacion = 'L';
		$tfuente     = 45;
		$tfuente2    = 15;
		$xx          = 3;
		$xy          = 11;
		$flag        = 1;
	}
	if($arr2[1] == 5){
		$altura      = 100;
		$ancho       = 132;
		$orientacion = 'L';
		$tfuente     = 75;
		$tfuente2    = 20;
		$xx          = 2;
		$xy          = 3;
		$flag        = 1;
	}
//implementación de Oscar para impreion depaquees 22.05.2018
	if($paquete==1){//configuración de formato para paquete
		$altura      = 100;
		$ancho       = 132;
		$orientacion = 'L';
		$tfuente     = 75;
		$tfuente2    = 20;
		$xx          = 2;
		$xy          = 3;
		$flag        = 1;
	}
//fin de cambios
	$datos = array(
					'altura'      => $altura,
					'ancho'       => $ancho,
					'orientacion' => $orientacion,
					'result'      => $result,
					'cant'        => $arr2[0],
					'tfuente'     => $tfuente,
					'tfuente2'	  => $tfuente2,
					'xx'	      => $xx,
					'xy'          => $xy,
					'flag'		  => $flag		
				  );

	//print_r($datos);
	creaPdf($datos, $canProds, $arr2[1], $user_sucursal);
	//secho $query;
 }else{
 	echo 'fail|No hay datos para generar tus etiquetas'.$query;
 }	


	function creaPdf($datos, $prods, $plantilla, $user_sucursal){
		extract($datos);
			$canti   = mysql_num_rows($result);
	
		//echo $cant;
	//-------------- CONFICURACION INICIAL DEL PDF --------------//
		$pdf = new TCPDF($orientacion,'mm','LETTER', true, 'UTF-8', false);
		
		if($plantilla == 1 || $plantilla == 2 || $plantilla == 3 || $plantilla == 4 || $plantilla == 5)
		{
			$pdf->SetMargins(0,0,0);
			$pdf->SetAutoPageBreak(false,5);
		}
		else
		{
			$pdf->SetMargins(3,3,3);
			$pdf->SetAutoPageBreak(true,5);
		}	
		$pdf->SetDrawColor (0,-1,-1,-1, $ret=false, $name='red');
 		$pdf->setPrintHeader(false); //no imprime la cabecera ni la linea 
		$pdf->setPrintFooter(false);
		$fontname=$pdf->addTTFfont('TobagoPoster.ttf', 'TrueTypeUnicode', '', 32);
		
		$tw_cent=$pdf->addTTFfont('TwCen.ttf', 'TrueTypeUnicode', '', 32);
		$arial_black=$pdf->addTTFfont('ArialBlack.ttf', 'TrueTypeUnicode', '', 32);
		$bernard=$pdf->addTTFfont('BernardMTCondensed.ttf', 'TrueTypeUnicode', '', 32);
		$arial_narrow=$pdf->addTTFfont('ArialNarrow.ttf', 'TrueTypeUnicode', '', 32);
		$rockwell=$pdf->addTTFfont('Rockwell.ttf', 'TrueTypeUnicode', '', 32);
		$arial2=$pdf->addTTFfont('608.ttf', 'TrueTypeUnicode', '', 32);
		
		// set text shadow effect
		
		
		//$fontname="Arial Black";

	//--------------------- CREACION DEL PDF --------------------//	
		$pdf->AddPage();
			$j=0;
 			$count=0;

		$x0=$pdf->getX()+3;
			$y0=$pdf->getY()+3;
			
			$xor=$pdf->getX()+3;
			$yor=$pdf->getY()+3;
			
			if($plantilla == '1' || $plantilla == '2' || $plantilla == '3' || $plantilla == '4' || $plantilla == '5')
			{
				$j=0;
				$k=0;
			}


		for($y=0;$y<$canti;$y++)
		{
			$fila = mysql_fetch_row($result);
			
			if($flag == 1)
			{
				/*$query = "SELECT
						  DISTINCT(id_precio), 
						  pd.precio_venta*2 AS prom,
						  id_producto
						  FROM ec_precios_detalle pd
						  WHERE id_producto = $fila[3]";
						
				
				$res = mysql_query($query)	or die ('Precio2: '.mysql_error());
				$row = mysql_fetch_row($res);
				$precio = $row[1];*/
				
				$sql="	SELECT DISTINCT
						pd.de_valor,
						pd.precio_etiqueta
						FROM ec_precios_detalle pd
						JOIN sys_sucursales s ON s.id_sucursal=".$user_sucursal." AND s.id_precio = pd.id_precio
						WHERE pd.id_producto=".$fila[3]."
						AND pd.de_valor=".$fila[4]."
						ORDER BY pd.de_valor DESC
						LIMIT 1";
						
						
				$re=mysql_query($sql) or die(mysql_error());
				$nu=mysql_num_rows($re);
				
				if($nu <= 0)
				{
					$ca=2;
					$precio=$fila[1]*2;
				}		
				else
				{
					$ro=mysql_fetch_row($re);
					$ca=$ro[0];
					$precio=round($ro[0]*$ro[1]);
				}
				
				
				
				if($plantilla == 4)
				{
					$html='';
					$html2='<font face="'.$arial_narrow.'" size="110" color="black" style="letter-spacing: 0px;"><b>'.$ca.'</b></font>';
					$html3='<font face="'.$arial_narrow.'" size="70" color="black" style="letter-spacing: 0px;"><b>X</b></font>';
					$html4='<font face="'.$arial_narrow.'" size="110" color="black" ><b>'.$precio.'</b></font>';
					//$html5='<font face="'.$aial2.'" size="25" color="black" style="letter-spacing: 3px;line-height: 10px;font-stretch: extra-condensed"><strong>'.$fila[0].'</strong>(<font face="'.$arial2.'" size="25" color="green"  style="letter-spacing: 0px !important;line-height: 15px;font-stretch: condensed">'.$fila[2].'</font>)</font>';	  
					$html5='<font face="'.$arial2.'" size="25" color="black" style="letter-spacing: 3px;line-height: 10px;font-stretch: extra-condensed"><strong>'.$fila[0].'</strong>(<font face="'.$arial2.'" size="25" color="green" style="letter-spacing: 0px !important;line-height: 15px;font-stretch: condensed">'.$fila[2].'</font>)</font>';
				}
				else
				{
					$html='';
					$html2='<font face="'.$arial2.'" size="240" color="black" style="letter-spacing: 0px;font-stretch: ultra-condensed">'.$ca.'</font>';
					$html3='<font face="'.$arial2.'" size="180" color="black" style="letter-spacing: 0px;font-stretch: extra-condensed">x</font>';
					$html4='<font face="'.$arial2.'" size="240" color="black" style="letter-spacing: 0px;font-stretch: ultra-condensed">'.$precio.'</font>';
					//$html5='<font face="'.$tw_cent.'" size="35" color="black" style="line-height: 10px;font-stretch: condensed"><b>'.$fila[0].'</b>(<font face="'.$tw_cent.'" size="39" color="green">'.$fila[2].'</font>)</font>';	  
					$html5='<font face="'.$arial2.'" size="37" color="black" style="letter-spacing: 6px;line-height: 10px;font-stretch: extra-condensed"><strong>'.$fila[0].'</strong>(<font face="'.$arial2.'" size="37" color="green" style="letter-spacing: 3px !important;line-height: 15px;font-stretch: condensed">'.$fila[2].'</font>)</font></span>';
				}
			}
			if($flag == 0)
			{
				/*$query = "SELECT
						de_valor,
						precio_venta*1 AS prom,
						id_producto
						FROM ec_precios_detalle pd
						WHERE de_valor = 1
						AND id_producto = $fila[3]
						UNION
						SELECT
						de_valor,
						(pd.precio_venta) AS prom,
						id_producto
						FROM ec_precios_detalle pd
						WHERE de_valor >= 2
						AND id_producto = $fila[3]";

				$res = mysql_query($query)	or die ('Precio2: '.mysql_error());
				$nume = mysql_num_rows($res); 
				$row = mysql_fetch_row($res);
				$precios = array();
					
					for($i=0;$i<$nume;$i++)
					{
						array_push($precios,$row[1]);
					}*/
					
				$query="	SELECT DISTINCT
							pd.de_valor,
							pd.precio_etiqueta
							FROM ec_precios_detalle pd
							JOIN sys_sucursales s ON s.id_sucursal=".$user_sucursal." AND s.id_precio = pd.id_precio
							WHERE pd.id_producto=".$fila[3]."
							ORDER BY pd.de_valor
							LIMIT 3";		
						
						
				$res = mysql_query($query)	or die ("Precio2:<br>$query<br><br>".mysql_error());
				$num=mysql_num_rows($res);

				if($num < 3)
					continue;
				
				switch($num)
				{
					case 0:
						$pre1=$fila[1];
						$can1=1;
						$pre2=$fila[1]*2;
						$can2=2;
						$pre3=$fila[1]*3;
						$can3=3;
						break;
					case 1:
						$row=mysql_fetch_row($res);
						$pre1=$row[1]*$row[0];
						$can1=$row[0];
						$pre2=$row[1]*$row[0]*2;
						$can2=$row[0]*2;
						$pre3=$row[1]*$row[0]*3;
						$can3=$row[0]*3;
						break;
					case 2:
						$row=mysql_fetch_row($res);
						$pre1=$row[1]*$row[0];
						$can1=$row[0];
						$row=mysql_fetch_row($res);
						$pre2=$row[1]*$row[0];
						$can2=$row[0];
						$pre3=$row[1]*$row[0]*2;
						$can3=$row[0]*2;
						break;		
					case 3:
						$row=mysql_fetch_row($res);
						$pre1=$row[1]*$row[0];
						$can1=$row[0];
						$row=mysql_fetch_row($res);
						$pre2=$row[1]*$row[0];
						$can2=$row[0];
						$row=mysql_fetch_row($res);
						$pre3=$row[1]*$row[0];
						$can3=$row[0];
						break;			
				}	
				
				$pre1=round($pre1);
				$pre2=round($pre2);
				$pre3=round($pre3);
				
					
				
				$html='';
				$html2='<font face="'.$bernard.'" size="100" color="red"><b>'.$can1.'</b></font>';
				$html3='<font face="'.$bernard.'" size="90" color="red"><b>x</b></font>';
				$html4='<font face="'.$bernard.'" size="100" color="red"><b>'.$pre1.'</b></font>';
				
				$html5='<font face="'.$bernard.'" size="100" color="red"><b>'.$can2.'</b></font>';
				$html6='<font face="'.$bernard.'" size="90" color="red"><b>x</b></font>';
				$html7='<font face="'.$bernard.'" size="100" color="red"><b>'.$pre2.'</b></font>';
				
				$html8='<font face="'.$bernard.'" size="100" color="red"><b>'.$can3.'</b></font>';
				$html9='<font face="'.$bernard.'" size="90" color="red"><b>x</b></font>';
				$html10='<font face="'.$bernard.'" size="100" color="red"><b>'.$pre3.'</b></font>';
				
				//$html11='<font face="'.$tw_cent.'" size="32" color="black" style="line-height: 10px;font-stretch: condensed"><b>'.$fila[0].'</b>(<font face="'.$tw_cent.'" size="36" color="green">'.$fila[2].'</font>)</font>';
				$html11='<font face="'.$arial2.'" size="31" color="black" style="letter-spacing: 3px;line-height: 10px;font-stretch: extra-condensed"><strong>'.$fila[0].'</strong>(<font face="'.$arial2.'" size="31" color="green" style="letter-spacing: 0px !important;line-height: 15px;font-stretch: condensed">'.$fila[2].'</font>)</font></span>';
			}	
			if($flag != 1 && $flag != 0)
			{
				$precio = $fila[1];
				
				if($plantilla == '1')
				{
					
			//implementación de Oscar para ajuste de fuente en precios 12-02-2017
					if($precio>999||strlen($precio)>3){
						$tam_fte='75';
						$marg_arr='line-height:23px;';
					}else{
						$tam_fte='104';
						$marg_arr='';
					}
			//fin cambio
					$html="";
					
					$html2='<font face="'.$arial2.'" size="65" color="red" ><b>$</b></font>';
				//corresponde al precio
					$html3='<font face="'.$arial2.'" size="'.$tam_fte.'" color="red" style="letter-spacing: 0px;'.$marg_arr.'"><b>'.$precio.'</b></font>';
					$html4='<font face="'.$arial2.'" size="25" color="black" style="letter-spacing: 3px;line-height: 10px;font-stretch: extra-condensed"><strong>'.$fila[0].'</strong>(<font face="'.$arial2.'" size="25" color="green" style="letter-spacing: 0px !important;line-height: 15px;font-stretch: condensed">'.$fila[2].'</font>)</font></span>';
				}
				else if($plantilla == '2')
				{
			//implementación de Oscar para ajuste de fuente en precios 12-02-2017
					if($precio>999||strlen($precio)>3){
						$tam_fte='180';
						$marg_arr='line-height:20px;';
					}else{
						$tam_fte='240';
						$marg_arr='';
					}
			//fin cambio
					
					$html="";
					
					$html2='<font face="'.$arial2.'" size="150" color="red" style="font-stretch: extra-condensed;letter-spacing: 6px;"><b>$</b></font>';
					$html3='<font face="'.$arial2.'" size="'.$tam_fte.'" color="red" style="font-stretch: extra-condensed;letter-spacing: 6px;'.$marg_arr.'"><b>'.$precio.'</b></font>';
					//$html4='<font face="'.$tw_cent.'" size="40" color="black" style="line-height: 10px;font-stretch: condensed"><b>'.$fila[0].'</b>(<font face="'.$tw_cent.'" size="52" color="green">'.$fila[2].'</font>)</font></span>';
					$html4='<font face="'.$arial2.'" size="37" color="black" style="letter-spacing: 6px;line-height: 10px;font-stretch: extra-condensed"><strong>'.$fila[0].'</strong>(<font face="'.$arial2.'" size="37" color="green" style="letter-spacing: 3px !important;line-height: 15px;font-stretch: condensed">'.$fila[2].'</font>)</font></span>';
				}
				else
				{
					$html='	<span style="text-align:center;">
								<font face="'.$fontname.'" size="'.$tfuente.'" color="red">
									<b>'.$precio.'n</b>
								</font>
								<br><br>
								<font face="calibri" size="'.$tfuente2.'" color="black">
									<b>'.$fila[0].'</b>
									(<font face="calibri" size="'.$tfuente2.'" color="green">
											'.$fila[2].'
										</font>)
								</font>
							</span>';
				}			
			}


			/*echo $fila[3]."<br>";
			print_r($prods);
			echo "<br>";*/
			

			
			if(isset($prods[$fila[3]]))
				$cr=$cant*$prods[$fila[3]];
			else
				$cr=$cant;	
			
						
 			
			for($i=0;$i<$cr;$i++)
			{
				
				
				
				
				if($plantilla == '1')
				{
					
					$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.5, 'depth_h'=>2, 'color'=>array(0,0,0), 'opacity'=>1, 'blend_mode'=>'Normal'));			
					
					$pdf->writeHTMLCell	(
											18,
										 	30,
										 	$xor-0,
										 	$yor+2,
										 	$html2,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
					$pdf->writeHTMLCell	(
											80,
										 	30,
										 	$xor+12,
										 	$yor-9,
										 	$html3,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
					$pdf->setTextShadow(array('enabled'=>false, 'depth_w'=>0.8, 'depth_h'=>3.2, 'color'=>array(0,0,0), 'opacity'=>0.8, 'blend_mode'=>'Normal'));					
										
					$pdf->writeHTMLCell	(
											86,
										 	21,
										 	$xor+1,
										 	$yor+34,
										 	$html4,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);											
									
					$pdf->SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'color' => array(31, 151, 207)));					
										
										
					$pdf->writeHTMLCell	(
											$ancho,
										 	$altura,
										 	$xor,
										 	$yor,
										 	$html,
									 		$border      = 1,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = true 
										);									
				}
				else if($plantilla == '2')
				{
					
					
					//#1F97CF
					$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.6, 'depth_h'=>2, 'color'=>array(0,0,0), 'opacity'=>1, 'blend_mode'=>'Normal'));			

					
					$pdf->writeHTMLCell	(
											28,
										 	28,
										 	$xor+1,
										 	$yor+1,
										 	$html2,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
					$pdf->writeHTMLCell	(
											100,
										 	30,
										 	$xor+24,
										 	$yor-22,
										 	$html3,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
										
					$pdf->setTextShadow(array('enabled'=>false, 'depth_w'=>0.4, 'depth_h'=>1.6, 'color'=>array(0,0,0), 'opacity'=>0.8, 'blend_mode'=>'Normal'));					
										
					$pdf->writeHTMLCell	(
											131,
										 	38,
										 	$xor+1,
										 	$yor+71,
										 	$html4,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);										

					$pdf->SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'color' => array(31, 151, 207)));
					
					$pdf->writeHTMLCell	(
											$ancho,
										 	$altura,
										 	$xor,
										 	$yor,
										 	$html,
									 		$border      = 1,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = true 
										);
				}
				else if($plantilla == '3')
				{
				
					$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.4, 'depth_h'=>1.2, 'color'=>array(0,0,0), 'opacity'=>0.8, 'blend_mode'=>'Normal'));			
				
					$pdf->writeHTMLCell	(
											22,
										 	28,
										 	$xor+8,
										 	$yor-4,
										 	$html2,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
					$pdf->writeHTMLCell	(
											22,
										 	28,
										 	$xor+30,
										 	$yor-4,
										 	$html3,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);	
										
					$pdf->writeHTMLCell	(
											60,
										 	28,
										 	$xor+45,
										 	$yor-4,
										 	$html4,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);	
										
					$pdf->writeHTMLCell	(
											22,
										 	28,
										 	$xor+8,
										 	$yor+30,
										 	$html5,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
					$pdf->writeHTMLCell	(
											22,
										 	28,
										 	$xor+30,
										 	$yor+30,
										 	$html3,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);	
										
					$pdf->writeHTMLCell	(
											60,
										 	28,
										 	$xor+45,
										 	$yor+30,
										 	$html7,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);															
										
					$pdf->writeHTMLCell	(
											42,
										 	28,
										 	$xor-4,
										 	$yor+64,
										 	$html8,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
					$pdf->writeHTMLCell	(
											22,
										 	28,
										 	$xor+30,
										 	$yor+64,
										 	$html3,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
					$pdf->writeHTMLCell	(
											60,
										 	28,
										 	$xor+45,
										 	$yor+64,
										 	$html10,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
										
					$pdf->setTextShadow(array('enabled'=>false, 'depth_w'=>0.4, 'depth_h'=>1.6, 'color'=>array(0,0,0), 'opacity'=>0.8, 'blend_mode'=>'Normal'));					
										
					$pdf->writeHTMLCell	(
											98,
										 	28,
										 	$xor+1,
										 	$yor+106,
										 	$html11,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);																				
																			
				
				
					//$pdf->SetLineStyle(array('width' => 0.9, 'cap' => 'butt', 'join' => 'miter', 'color' => array(255, 0, 0)));
					$pdf->SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'color' => array(31, 151, 207)));
					
					$pdf->writeHTMLCell	(
											$ancho,
										 	$altura,
										 	$xor,
										 	$yor,
										 	$html,
									 		$border      = 1,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = true 
										);
										
										
				
				}
				else if($plantilla == '4')
				{
				
					$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.4, 'depth_h'=>1.6, 'color'=>array(228,43,78), 'opacity'=>0.8, 'blend_mode'=>'Normal'));			
					
					$pdf->writeHTMLCell	(
											30,
										 	26,
										 	$xor-5,
										 	$yor-8,
										 	$html2,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
					$pdf->writeHTMLCell	(
											28,
										 	26,
										 	$xor+14,
										 	$yor+1,
										 	$html3,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);	
										
										
					$pdf->writeHTMLCell	(
											60,
										 	26,
										 	$xor+31,
										 	$yor-8,
										 	$html4,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);										
										
					$pdf->setTextShadow(array('enabled'=>false, 'depth_w'=>0.4, 'depth_h'=>1.6, 'color'=>array(0,0,0), 'opacity'=>0.8, 'blend_mode'=>'Normal'));					
										
					$pdf->writeHTMLCell	(
											86,
										 	18,
										 	$xor+1,
										 	$yor+34,
										 	$html5,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);
				
				
				
					$pdf->SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'color' => array(229, 43, 78)));
					
					$pdf->writeHTMLCell	(
											$ancho,
										 	$altura,
										 	$xor,
										 	$yor,
										 	$html,
									 		$border      = 1,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = true 
										);
				}
				else if($plantilla == '5')
				{
					$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.4, 'depth_h'=>1.6, 'color'=>array(255,0,0), 'opacity'=>0.8, 'blend_mode'=>'Normal'));			
					$pdf->writeHTMLCell	(
											44,
										 	40,
										 	$xor-5,
										 	$yor-19,
										 	$html2,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);
					if($fila[0] < 100)					
										
						$pdf->writeHTMLCell	(
												40,
											 	40,
											 	$xor+24,
											 	$yor-13,
											 	$html3,
										 		$border      = 0,
											 	$ln          = 0,
											 	$fill        = false,
											 	$reseth      = true,
											 	$align       = 'C',
											 	$autopadding = false 
											);					
					else
					
							$pdf->writeHTMLCell	(
											40,
										 	40,
										 	$xor+16,
										 	$yor-13,
										 	$html3,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);	


					$pdf->writeHTMLCell	(
											90,
										 	40,
										 	$xor+46,
										 	$yor-19,
										 	$html4,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);	

					$pdf->setTextShadow(array('enabled'=>false, 'depth_w'=>0.4, 'depth_h'=>1.6, 'color'=>array(228,43,78), 'opacity'=>0.8, 'blend_mode'=>'Normal'));			
										
										
					$pdf->writeHTMLCell	(
											131,
										 	38,
										 	$xor+1,
										 	$yor+73,
										 	$html5,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);					

				
				
					$pdf->SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'color' => array(229, 43, 78)));
					
					$pdf->writeHTMLCell	(
											$ancho,
										 	$altura,
										 	$xor,
										 	$yor,
										 	$html,
									 		$border      = 1,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = true
										);
										
				}
				else
				{
					$pdf->writeHTMLCell	(
											$ancho,
										 	$altura,
										 	$pdf->getX()+3,
										 	'',
										 	$html,
									 		$border      = 1,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = true 
										);
				}						

				$j++;
				if($plantilla == 1)
				{
					$xor+=90;
					
					if($j > 2)
					{
						$yor+=$altura+1;
						$xor=$x0;
						$j=0;
						$k++;
					}
					if($k > 3)
					{
						$pdf->AddPage();
						$k = 0;
						
						$yor=$y0;
						$xor=$x0;
	
						$j= 0;
						
					}					
				}
				else if($plantilla == 2)
				{
					$xor+=136;
					
					if($j > 1)
					{
						$yor+=$altura+3;
						$xor=$x0;
						$j=0;
						$k++;
					}
					if($k > 1)
					{
						$pdf->AddPage();
						$k = 0;
						
						$yor=$y0;
						$xor=$x0;
	
						$j= 0;
						
					}					
				}
				else if($plantilla == 3)
				{
					$xor+=102;
					
					if($j > 1)
					{
						$yor+=$altura+3;
						$xor=$x0;
						$j=0;
						$k++;
					}
					if($k > 1)
					{
						$pdf->AddPage();
						$k = 0;
						
						$yor=$y0;
						$xor=$x0;
	
						$j= 0;
						
					}					
				}
				else if($plantilla == 4)
				{
					$xor+=90;
					
					if($j > 2)
					{
						$yor+=$altura+1;
						$xor=$x0;
						$j=0;
						$k++;
					}
					if($k > 3)
					{
						$pdf->AddPage();
						$k = 0;
						
						$yor=$y0;
						$xor=$x0;
	
						$j= 0;
						
					}					
				}
				else if($plantilla == 5)
				{
					$xor+=136;
					
					if($j > 1)
					{
						$yor+=$altura+3;
						$xor=$x0;
						$j=0;
						$k++;
					}
					if($k > 1)
					{
						$pdf->AddPage();
						$k = 0;
						
						$yor=$y0;
						$xor=$x0;
	
						$j= 0;
						
					}					
				}	
				else
				{
					if($j==$xx)
					{	
						if($count !=$xy)
						{	
							$pdf->Ln($altura+1);
							$j = 0;
						}
						else
						{
							//$pdf->AddPage();
							$count = 0;
	
							$j= 0;
							continue;
						}
					}
					$count++;

				}	

				
			}
		}

		mysql_free_result($result);
		$ruta = '../../../../etiquetas/etiquetas'.date(ymd_his).'.pdf';
		if(!$pdf->Output($ruta,'F'))
		{
			echo 'ok|'.$ruta;
		}
		else
		{
			$pdf->Error('Error al generar las etiquetas,intenta nuevamente profavor');
		}

	}
		
?>