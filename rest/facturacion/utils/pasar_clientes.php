<?php
	if( isset( $_POST['fl'] ) ){
		include( '../../../conexionMysqli.php' );
		$data = explode('|~|', $_POST['datos'] );
	//obtiene caracteres de reemplazo
		$sql = "SELECT caracter, codigo_reemplazo FROM vf_caracteres_especiales";
		$stm = $link->query( $sql ) or die( "Error al consultar los caracteres especiales : {$link->error}" );
		$replace = array();
		while ( $row = $stm->fetch_assoc() ) {
			$replace[] = $row;
		}

		$link->autocommit( false );
		foreach ( $data as $key => $value ) {
			$row = explode( '~', $value );
			foreach ($replace as $key => $rep) {
				$row[22] = str_replace( "{$rep['codigo_reemplazo']}", "{$rep['caracter']}", $row[22] );//nombre razon social
				$row[23] = str_replace( "{$rep['codigo_reemplazo']}", "{$rep['caracter']}", $row[23] );//calle
				$row[26] = str_replace( "{$rep['codigo_reemplazo']}", "{$rep['caracter']}", $row[26] );//colonia
				$row[27] = str_replace( "{$rep['codigo_reemplazo']}", "{$rep['caracter']}", $row[27] );//del_municipio
			}
		//inserta cliente temporal
			$sql = "INSERT INTO vf_clientes_razones_sociales_tmp  
							SET rfc = '{$row[1]}', 
							razon_social = '{$row[22]}', 
							id_tipo_persona = '{$row[15]}', 
							entrega_cedula_fiscal = '{$row[16]}', 
							url_cedula_fiscal = '',
							calle = '{$row[23]}',
							no_int = '{$row[24]}',
							no_ext = '{$row[25]}',
							colonia = '{$row[26]}',
							del_municipio = '{$row[27]}',
							cp = '{$row[28]}',
							estado = '{$row[31]}',
							pais = '{$row[32]}',
							regimen_fiscal = '{$row[18]}',
							id_cliente_facturacion = '0'";
			$stm_costumer = $link->query( $sql ) or die( "Error al insertar cabecera de cliente temporal : {$link->error}" );
			$costumer_id = $link->insert_id;

		//inserta contacto del cliente
			$sql = "INSERT INTO vf_clientes_contacto_tmp
						SET id_cliente_facturacion_tmp = '{$costumer_id}',
						nombre = '{$row[22]}',
						telefono = '',
						celular = IF( '{$row[2]}' = '', '{$row[4]}', '{$row[2]}' ),
						correo = '{$row[6]}',
						uso_cfdi = 'G03',
						fecha_alta = NOW()";
			$stm_costumer = $link->query( $sql ) or die( "Error al insertar contacto de cliente temporal : {$link->error}" );
		}
		$link->autocommit( true );
		$local_path = "";
			$archivo_path = "../../../conexion_inicial.txt";
			if(file_exists($archivo_path) ){
				$file = fopen($archivo_path,"r");
				$line=fgets($file);
				fclose($file);
				$config=explode("<>",$line);
				$tmp=explode("~",$config[0]);
				$local_path = "localhost/" . base64_decode( $tmp[1] ) . "/rest/facturacion/envia_cliente";
			}else{
				die("No hay archivo de configuración!!!");
			}
			//die( $local_path );
			$crl = curl_init( $local_path );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			//curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
$resp = curl_exec($crl);//envia peticion 
			curl_close($crl);
			//die( "{$resp}" );
			if( $resp != "ok" ){
				var_dump( $resp );
				die( "Error!" );
			}
		die( 'ok' );
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../css/icons/css/fontello.css">
	<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
	<script language="JavaScript" src="../../../js/papaparse.min.js"></script>
	<title>Importacion de Clientes</title>
</head>
<body>
	<div class="row text-center">
		<form>
			<br>
			<br>
			<br>
			<!--label for="file">Seleccionar Archivo : </label-->
			<input type="file" id="file" name="">
			<br>
			<div class="row">
				<button
					type="button"
					class="btn btn-success"
					id="submit-file"
					style="display : none;"
				>	
					<i class="icon-ok-circle">Procesar clientes</i>
				</button>
			</div>
		</form>
	</div>
	<div class="row" style="padding : 30px !important; max-width: 100%; overflow: auto; max-height: 450px;">
		<table class="table table-striped table-bordered">
			<thead style="position: sticky;top : 0; background-color : white;">
				<tr>
					<th class="text-center">#</th>
					<th class="text-center">RFC</th>
					<th class="text-center">clientes_telefono</th>
					<th class="text-center">clientes_telefono_2</th>
					<th class="text-center">clientes_movil</th>
					<th class="text-center">clientes_contacto</th>
					<th class="text-center">clientes_email</th>
					<th class="text-center">clientes_dias_credito</th>
					<th class="text-center">clientes_maximo_adeudo</th>
					<th class="text-center">clientes_es_cliente</th>
					<th class="text-center">clientes_id_sucursal</th>
					<th class="text-center">clientes_monto_desc</th>
					<th class="text-center">clientes_porc_desc</th>
					<th class="text-center">clientes_min_compra_desc</th>
					<th class="text-center">clientes_id_equivalente</th>
					<th class="text-center">clientes_idTipoPersona</th>
					<th class="text-center">clientes_EntregaConsSitFiscal</th>
					<th class="text-center">clientes_UltimaActualizacion</th>
					<th class="text-center">clientes_regimenFiscal</th>
					<th class="text-center">rs_id_cliente_rs</th>
					<th class="text-center">rs_id_cliente</th>
					<th class="text-center">rs_rfc</th>
					<th class="text-center">rs_razon_social</th>
					<th class="text-center">rs_calle</th>
					<th class="text-center">rs_no_int</th>
					<th class="text-center">rs_no_ext</th>
					<th class="text-center">rs_colonia</th>
					<th class="text-center">rs_del_municipio</th>
					<th class="text-center">rs_cp</th>
					<th class="text-center">rs_referencia</th>
					<th class="text-center">rs_estado</th>
					<th class="text-center">rs_pais</th>
				</tr>
			</thead>
			<tbody id="rows_previous">
				
			</tbody>
		</table>
	</div>
	<div class="row text-center">
		<button
			type="button"
			class="text-center btn btn-success"
			onclick="getCostumers();"
		>
			<i class="icon-import">Importar</i>
		</button>
	</div>

	<form id="TheForm" method="post" action="pasar_clientes.php" target="TheWindow">
			<input type="hidden" id="fl" name="fl" value="import_costumers" />
			<input type="hidden" id="datos" name="datos" value=""/>
	</form>

</body>
</html>

<script type="text/javascript">
	
//
	$('#submit-file').on("click",function(e){
		e.preventDefault();
		$('#file').parse({
			config: {
				delimiter:"auto",
				complete: importaClientes,
			},
		 		before: function(file, inputElem){
		 			$("#espacio_importa").css("display","none");//ocultamos el botón de búsqueda
			//console.log("Parsing file...", file);
			},
				error: function(err, file){
		   			console.log("ERROR:", err, file);
				alert("Error!!!:\n"+err+"\n"+file);
			},
		 		complete: function(){
				console.log("Done with all files");
			}
		});
	});

//detectamos archivo cargado
	$("#file").change(function(){
		var fichero_seleccionado = $(this).val();
			var nombre_fichero_seleccionado = fichero_seleccionado.replace(/.*[\/\\]/, '');
			if(nombre_fichero_seleccionado!=""){
			$("#bot_imp_estac").css("display","none");//ocultamos botón de importación
			$("#submit-file").css("display","block");//mostramos botón de inserción
			$("#txt_info_detalle_oc_csv").val(nombre_fichero_seleccionado);//asignamos nombre del archivo seleccionado
			$("#txt_info_detalle_oc_csv").css("display","block");//volvemos visible el nombre del archivo seleccionado
			//$("#importa_csv_icon").css("display","none");
		}else{
			alert("No se seleccionó ningun Archivo CSV!!!");
			return false;
		}
	});

/*	function importaClientes(){
		alert();
	}*/
	function importaClientes(results){
		var resp = ``;
	//lanzamos la emergente
		$("#mensaje_pres").html('<p align="center" style="color:white;font-size:30px;">Cargando datos<br><img src="../../../img/img_casadelasluces/load.gif" width="120px"></p>');
		$("#cargandoPres").css("display","block");
		
		var id_estac=$("#id_estacionalidad").val();
		var data = results.data;//guardamos en data los valores del archivo CSV
		var tam_grid=$("#estacionalidadProducto tr").length-3;
		//alert(data);
		//return true;
		var arr="";
		var orden_lista_tmp="";
		var c = 0;
		for(var i=1;i<data.length;i++){
			//console.log( data[i] );
			//data[i][0] = data[i][0].replaceAll( '"', '' );
			//alert( data[i][0] );
			var array = data[i][0].split( ',' );
			if( data[i][0].trim() != '' ){
				c ++;
				resp += `<tr><td>${c}</td>`;
				for(var j = 0;j<array.length;j++){
					array[j] = array[j].replaceAll( '"', '' );
					resp += `<td class="text-center">${array[j]}</td>`;
				}
				resp += `</tr>`;	
			}
		}//fin de for i
		$( '#rows_previous' ).html( resp );
	}

ventana_abierta = null;
	function getCostumers(){
		var data = ``;
		$( '#rows_previous tr' ).each( function( index ){
			data += ( data == '' ? '' : '|~|' );
			$( this ).children( 'td' ).each( function( index2 ){
				data += ( index2 == '' ? '' : '~' );
				data += $( this ).html().trim();
			});
		});
		if( data == `` ){
			alert( "Los datos no pueden ir vacios!" );
			return false;
		}else{
			$( '#datos' ).val( data );
			ventana_abierta=window.open('', 'TheWindow');	
			document.getElementById('TheForm').submit();
			setTimeout(cierra_pestana,15000);
		}
		console.log( data );
	}

	function cierra_pestana(){
		ventana_abierta.close();
	}



</script>
<?php
die( '' );
  include( '../../../conexionMysqli.php' );
  //include('../../conexionMysqli.php');
  $dbHost = "sistemageneralcasa.com";
  $dbUser = "wwsist_oscar23";
  $dbPassword = "wwsist_oscar23_23";
  $dbName = "wwsist_casa_luces_bazar"; 

  $linkFact = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
  if( $linkFact->connect_error ){
    die( "Error al conectar con la Base de Datos : " . $linkFact->connect_error);
  }
  $linkFact->set_charset("utf8mb4");
//bases de datos destino de facturacion
  $bd_facturacion=[];
  //Recupera bases de datos
  $sql="SELECT id, nombre_bd FROM ec_bases_facturacion WHERE active = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar las bases de datos de facturacion : {$link->error}" );

  //die( 'here8' );
  while( $row = $stm->fetch_assoc() ) {
    $bd_facturacion[]=$row['nombre_bd'];
  }

 //consulta e inserta clientes
  foreach ($bd_facturacion as $key => $dB) {
  	$sql = "SELECT
				c.nombre AS cliente_rfc,	
				c.telefono AS clientes_telefono,		
				c.telefono_2 AS clientes_telefono_2,				
				c.movil AS clientes_movil,		
				REPLACE( c.contacto, ',', 'AA_01' ) AS clientes_contacto,			
				c.email AS clientes_email,		
				c.dias_credito AS clientes_dias_credito,			
				c.maximo_adeudo AS clientes_maximo_adeudo,			
				c.es_cliente AS clientes_es_cliente,				
				c.id_sucursal AS clientes_id_sucursal,				
				c.monto_desc AS clientes_monto_desc,			
				c.porc_desc AS clientes_porc_desc,				
				c.min_compra_desc AS clientes_min_compra_desc,				
				c.id_equivalente AS clientes_id_equivalente,			
				c.idTipoPersona AS clientes_idTipoPersona,			
				c.EntregaConsSitFiscal AS clientes_EntregaConsSitFiscal,			
				c.UltimaActualizacion AS clientes_UltimaActualizacion,				
				c.regimenFiscal AS clientes_regimenFiscal,
				crs.id_cliente_rs AS rs_id_cliente_rs,
				crs.id_cliente AS rs_id_cliente,	
				crs.rfc AS rs_rfc,
				REPLACE( crs.razon_social, ',', 'AA_01' ) AS rs_razon_social,
				REPLACE( crs.calle, ',', 'AA_01' ) AS rs_calle,
				REPLACE( crs.no_int, ',', 'AA_01' ) AS rs_no_int,
				REPLACE( crs.no_ext, ',', 'AA_01' ) AS rs_no_ext,
				REPLACE( crs.colonia, ',', 'AA_01' ) AS rs_colonia,
				REPLACE( crs.del_municipio, ',', 'AA_01' ) AS rs_del_municipio,
				crs.cp AS rs_cp,
				REPLACE( crs.referencia, ',', 'AA_01' ) AS rs_referencia,	
				crs.estado AS rs_estado,	
				crs.pais AS rs_pais
			FROM ec_clientes c 
			LEFT JOIN ec_clientes_razones_sociales crs 
			ON crs.id_cliente = c.id_cliente 
			WHERE c.regimenFiscal != ''";
	$stm = $linkFact->query( $sql ) or die( "Error al consultar datos del sitema de facturacion {$dB} : {$linkFact->error}" );
	while( $row = $stm->fetch_assoc() ){
	//inserta cliente temporal
		$sql = "INSERT INTO vf_clientes_razones_sociales_tmp  
						SET rfc = '{$row['rs_rfc']}', 
						razon_social = '{$row['rs_razon_social']}', 
						id_tipo_persona = '{$row['clientes_idTipoPersona']}', 
						entrega_cedula_fiscal = '{$row['clientes_EntregaConsSitFiscal']}', 
						url_cedula_fiscal = '',
						calle = '{$row['rs_calle']}',
						no_int = '{$row['rs_no_int']}',
						no_ext = '{$row['rs_no_ext']}',
						colonia = '{$row['rs_colonia']}',
						del_municipio = '{$row['rs_del_municipio']}',
						cp = '{$row['rs_cp']}',
						estado = '{$row['rs_estado']}',
						pais = '{$row['rs_pais']}',
						regimen_fiscal = '{$row['clientes_regimenFiscal']}',
						id_cliente_facturacion = '0'";
		$stm_costumer = $link->query( $sql ) or die( "Error al insertar cabecera de cliente temporal : {$link->error}" );
		$costumer_id = $link->insert_id;

	//inserta contacto del cliente
		$sql = "INSERT INTO vf_clientes_contacto_tmp
					SET id_cliente_facturacion_tmp = '{$costumer_id}',
					nombre = '{$row['']}',
					telefono = '',
					celular = IF( '{$row['clientes_movil']}' = '', '{$row['clientes_telefono']}', '{$row['clientes_movil']}' ),
					correo = '{$row['clientes_email']}',
					uso_cfdi = 'G03',
					fecha_alta = NOW()";
		$stm_costumer = $link->query( $sql ) or die( "Error al insertar contacto de cliente temporal : {$link->error}" );
	}
  }
  die( 'ok' );
?>