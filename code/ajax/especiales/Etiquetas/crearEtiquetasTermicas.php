<?php
	include("../../../../conectMin.php");
	include("../../../../conexionMysqli.php");
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
					CONCAT( ax1.nombre_etiqueta, ' (', ax1.orden_lista,')' ) AS tag_name,
					ax1.precio AS price,
					ax1.orden_lista AS list_order,
					ax1.id_productos AS product_id,
					ax1.de_valor AS from_value,
					ax1.es_externo AS is_extrernal,
					ax1.oferta AS is_promotion
				FROM(
				SELECT
					ax.nombre_etiqueta,
					IF(ax.es_externo=0,ax.precio,CONCAT(pd_1.de_valor,'X',ROUND(pd_1.precio_venta*pd_1.de_valor))) as precio,
					ax.orden_lista,
					ax.id_productos,
					ax.de_valor,
					ax.es_externo,
					IF(ax.es_externo=0,ax.es_oferta,pd_1.es_oferta) as oferta
				FROM(
				SELECT 
					p.nombre_etiqueta,
					CONCAT(pd.de_valor,'X',ROUND(pd.precio_venta*pd.de_valor)) as precio,
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
					CONCAT( ax1.nombre_etiqueta, ' (', ax1.orden_lista,')' ) AS tag_name,
					ax1.precio AS price,
					ax1.orden_lista AS list_order,
					ax1.id_productos AS product_id,
					ax1.es_externo AS is_extrernal,
					ax1.oferta AS is_promotion
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

	//die( "HERE : " . $sql );
	//print_r($datos);
	//creaPdf($datos, $canProds, $arr2[1], $user_sucursal);
	//die( "Pantilla : {$arr2[1]}" );
	$template = $arr2[1];
	$TermalPrinter = new TermalPrinter( $link );
	switch( $template ){
		case '1': 
			$TermalPrinter->makeNormalTags($datos, $canProds, $arr2[1], $user_sucursal);
		break;
		case '3': 
			$TermalPrinter->makeSeveralTags($datos, $canProds, $arr2[1], $user_sucursal );
		break;
		case '4': 
			$TermalPrinter->makeMoreThanOnePriceTags($datos, $canProds, $arr2[1], $user_sucursal);
		break;
		/*case '4': 
			makeSeveralTags($datos, $canProds, $arr2[1], $user_sucursal);
		break;*/
	}
	//secho $query;
 }else{
 	echo 'fail|No hay datos para generar tus etiquetas'.$query;
 }	

 	class TermalPrinter{
 		private $link;
 		function __construct( $connection ){
 			$this->link = $connection;
 		}

	 	function makeNormalTags( $datos, $prods, $plantilla, $user_sucursal ) {
	 		//die( 'here' );
	 		//var_dump( $datos['result']);
	 		$epl_code = "";
	 		while ( $row = mysql_fetch_assoc( $datos['result'] ) ) {
	 			$price_size = 4;

				$row['tag_name'] = strtoupper( $row['tag_name'] );
				$row['tag_name'] = str_replace( "Ñ", "N", $row['tag_name'] );
				$row['tag_name'] = str_replace( "ñ", "n", $row['tag_name'] );
				$parts = $this->part_word( $row['tag_name'] );
				$part_1 = $parts[0];
				$part_2 = $parts[1];
	 			
	 			$epl_code .= "\nI8,A,001\n\n";
				$epl_code .= "Q408,024\n";
				$epl_code .= "q448\n";
				$epl_code .= "rN\n";
				$epl_code .= "S1\n";
				$epl_code .= "D10\n";
				$epl_code .= "ZT\n";
				$epl_code .= "JF\n";
				$epl_code .= "O\n";
				$epl_code .= "R112,0\n";
				$epl_code .= "f100\n";
				$epl_code .= "N\n";
//A590,280,2,4,4,4,N,"$"
				$epl_code .= "A590,280,2,4,4,4,N,\"$\"\n";
				if( $row['price'] > 999 ){
					$price_size = 3;
//A400,255,2,5,2,2,N,","
					$epl_code .= "A400,255,2,5,2,2,N,\",\"\n";
				}
				$epl_code .= "b500,290,Q,m2,s5,\"{$row['list_order']}\"\n";//QR
				$epl_code .= "A486,380,2,5,{$price_size},4,N,\"{$row['price']}\"\n";
				$epl_code .= "A612,150,2,3,2,3,N,\"{$part_1}\"\n";
				$epl_code .= "A612,80,2,3,2,3,N,\"{$part_2}\"\n";
				$epl_code .= "P1\n";
	 		//die( "Code" . $epl_code );
	 		}
	 		$file_name = date("Y_m_d_H_i_s");
	 	//creacion de archivo
	 		$file = fopen("../../../../cache/ticket/tag_{$file_name}.txt", "a");
			fwrite($file, $epl_code );
			fclose($file);
			die( 'ok' );
	 	}

	 	function makeSeveralTags( $datos, $prods, $plantilla, $store_id ) {
	 		//var_dump( $datos['result']);
	 		$epl_code = "";
	 		while ( $row = mysql_fetch_assoc( $datos['result'] ) ) {
	 		//consulta los diferentes precios
				$sql = "SELECT 
							CONCAT( pd.de_valor, 'x', ROUND(pd.precio_venta * pd.de_valor )) as price
						FROM sys_sucursales_producto sp
						JOIN sys_sucursales s 
						ON s.id_sucursal=sp.id_sucursal
						JOIN ec_precios pr 
						ON s.id_precio = pr.id_precio
						JOIN ec_precios_detalle pd 
						ON sp.id_producto = pd.id_producto
						AND pd.id_precio = pr.id_precio
						WHERE sp.id_producto = {$row['product_id']}
						AND sp.id_sucursal = {$store_id} 
						AND sp.estado_suc=1";
				$stm = $this->link->query( $sql ) or die( "Error al consultar los precios del producto : {$this->link->error}" );
	 			$price_size = 4;
				$row['tag_name'] = strtoupper( $row['tag_name'] );
				$row['tag_name'] = str_replace( "Ñ", "N", $row['tag_name'] );
				$row['tag_name'] = str_replace( "ñ", "n", $row['tag_name'] );
				$parts = $this->part_word( $row['tag_name'] );
				$part_1 = $parts[0];
				$part_2 = $parts[1];

				$epl_code .= "\nI8,A,001\n\n";
				$epl_code .= "Q408,024\n";
				$epl_code .= "q448\n";
				$epl_code .= "rN\n";
				$epl_code .= "S1\n";
				$epl_code .= "D10\n";
				$epl_code .= "ZT\n";
				$epl_code .= "JF\n";
				$epl_code .= "O\n";
				$epl_code .= "R112,0\n";
				$epl_code .= "f100\n";
				$epl_code .= "N\n";
/*

"1x22"
"6x120"
,"12x216"
,"88888888888888888888888"
,"ROJO M21124R (27093)888"

*/
				$price = $stm->fetch_assoc();
				$epl_code .= "A610,10,1,4,5,5,N,\"{$price['price']}\"\n";//precio 1
				$price = $stm->fetch_assoc();
				$epl_code .= "A490,10,1,4,5,5,N,\"{$price['price']}\"\n";//precio 2
				$price = $stm->fetch_assoc();
				$epl_code .= "A370,10,1,4,5,4,N,\"{$price['price']}\"\n";//precio 3
				$epl_code .= "A250,20,1,4,2,1,N,\"{$parts[0]}\"\n";
				$epl_code .= "A200,20,1,4,2,1,N,\"{$parts[1]}\"\n";
				$epl_code .= "b40,150,Q,m2,s5,\"{$row['list_order']}\"\n";			
				$epl_code .= "P1\n";
	 		//die( "Code" . $epl_code );
	 		}
	 		$file_name = date("Y_m_d_H_i_s");
	 	//creacion de archivo
	 		$file = fopen("../../../../cache/ticket/tag_{$file_name}.txt", "a");
			fwrite($file, $epl_code );
			fclose($file);
			die( 'ok' );
	 	}

	 	function makeMoreThanOnePriceTags( $datos, $prods, $plantilla, $store_id ) {
	 		//die( 'here' );
	 		//var_dump( $datos['result']);
	 		$epl_code = "";
	 		while ( $row = mysql_fetch_assoc( $datos['result'] ) ) {
	 			$price_size = 4;
				$row['tag_name'] = strtoupper( $row['tag_name'] );
				$row['tag_name'] = str_replace( "Ñ", "N", $row['tag_name'] );
				$row['tag_name'] = str_replace( "ñ", "n", $row['tag_name'] );
				$parts = $this->part_word( $row['tag_name'] );
				$part_1 = $parts[0];
				$part_2 = $parts[1];
				$epl_code .= "\nI8,A,001\n\n";
				$epl_code .= "Q408,024\n";
				$epl_code .= "q448\n";
				$epl_code .= "rN\n";
				$epl_code .= "S1\n";
				$epl_code .= "D10\n";
				$epl_code .= "ZT\n";
				$epl_code .= "JF\n";
				$epl_code .= "O\n";
				$epl_code .= "R112,0\n";
				$epl_code .= "f100\n";
				$epl_code .= "N\n";
				$epl_code .= "A600,380,2,5,3,4,N,\"{$row['price']}\"\n";//precio
				$epl_code .= "A615,150,2,3,2,3,N,\"{$parts[0]}\"\n";
				$epl_code .= "A615,80,2,3,2,3,N,\"{$parts[1]}\"\n";			
				$epl_code .= "P1\n";
	 		//die( "Code" . $epl_code );
	 		}
	 		$file_name = date("Y_m_d_H_i_s");
	 	//creacion de archivo
	 		$file = fopen("../../../../cache/ticket/tag_{$file_name}.txt", "a");
			fwrite($file, $epl_code );
			fclose($file);
			die( 'ok' );
	 	}

/*

I8,A,001

Q408,024
q448
rN
S1
D10
ZT
JF
O
R112,0
f100
N
A600,380,2,5,3,4,N,"6X230"
A623,150,2,3,2,3,N,"RENO EN CUERDA ROJO A"
A623,80,2,3,2,3,N,"CUADROS ( 31377)"
P1

*/

	/*partir nombre de etiqueta*/
		function part_word( $txt ){
			$size = strlen( $txt );
			$half = round( $size / 2 );
			$words = explode(' ', $txt );
			$resp = array( '','');
			$chars_counter = 0;
			$middle_word = "";
			foreach ($words as $key => $word) {
				$is_middle = 0;
				if( $key > 0 ){
					$chars_counter ++;//espacio
					if( $chars_counter == $half ){
						$is_middle = 1;
					}
				}
				for( $i = 0; $i < strlen( $word ); $i ++ ){
					$chars_counter ++;//palabras
					if( $chars_counter == $half || $is_middle == 1){
						$middle_word = $word;
						$is_middle = 1;
					}
				}
				if( $middle_word == '' ){
					$resp[0] .= ( $resp[0] != '' ? ' ' : '' );
					$resp[0] .= $word;
				}else if( $middle_word != '' && $is_middle == 0 ){
					$resp[1] .= ( $resp[1] != '' ? ' ' : '' );
					$resp[1] .= $word;
				}
				$is_middle = 0;
			}
			if( strlen( "{$resp[0]} {$middle_word}" ) < strlen( "{$middle_word} {$resp[1]}" )  ){//asigna palabra intermedia a primera parte
				$resp[0] = "{$resp[0]} {$middle_word}";
			}else{//asigna palabra intermedia a segunda parte
				$resp[1] = "{$middle_word} {$resp[1]}";
			}
			return $resp;
		}

 	}
		
?>