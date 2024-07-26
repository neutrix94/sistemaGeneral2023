<?php
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header("Content-Type: application/xml; charset=utf-8");

  $sql = "SELECT 
            id_productos,
            orden_lista 
          FROM ec_productos
          WHERE es_ultimas_piezas = '1'";
  $stm = mysql_query( $sql ) or die( "Error al consultar los productos  que son ultimas  piezas : " . mysql_error() );
  $last_pieces = "";
  $last_orders = "";
  while( $row = mysql_fetch_row( $stm ) ){
    $last_pieces .= ( $last_pieces != "" ? "," : "" );
    $last_pieces .= $row[0];

    $last_orders .= ( $last_orders != "" ? "," : "" );
    $last_orders .= $row[1];
  //echo "here : {$row[0]}";
  }
  echo "<script> 
          var global_last_pieces_array = '{$last_pieces}'.split( ',' );
          var global_last_orders_array = '{$last_orders}'.split( ',' );
          //alert( global_last_pieces_array );
        </script>";
/*implementacion Oscar 2023 para indicar que es cotizacion*/
  if( isset( $_GET['is_quotation'] ) ){
    echo "<input type=\"hidden\" id=\"is_just_quotation\" value=\"{$_GET['is_quotation']}\">";
    //echo "<h3 class=\"text-center\">Cotizacion<h3>";
  }
/**/
?>

<!-- -->
<script type="text/javascript">
var productsCatalogue = new Array();
var packsCatalogue = new Array();
  function getProductsCatalogue( price_list = '' ){
//alert( 'getProductsCatalogue' );
    var url = "ajax/productsCatalogue.php?fl_db=getProductsCatalogue";
    if( price_list != '' ){
      url += "&price_list=" + price_list;
    }
//alert( url );
    var response = ajaxR( url ).split( '|' );
    if( response[0] != 'ok' ){
      alert( "Error : " + response );
      return false;
    }

    //alert( response[2] );
    productsCatalogue = JSON.parse( response[1] );
    packsCatalogue = JSON.parse( response[2] );
//console.log( productsCatalogue );     
//console.log( packsCatalogue );     
    buildProductsCatalogue();
  }

  function getProductsCatalogueByLocalStorage(){
//alert( 'getProductsCatalogueByLocalStorage' );
    productsCatalogue = JSON.parse( localStorage.getItem('productsCatalogue') );
//console.log( productsCatalogue );
    buildProductsCatalogue();
  }
//
  function buildProductsCatalogue(){
//alert( 'buildProductsCatalogue' );hidde_seeker_response();
    resp = `<button 
          class="btn rounded-circle hidde_seeker_response_btn"
          onclick="document.getElementById('resBus').style.display='none';"
        >
          <i class="icon-up-big"></i>
        </button>`;
    for (var product in productsCatalogue){
      var product_prices = productsCatalogue[product].product_prices.split( ' l ' );
      productsCatalogue[product].product_prices = '';
      for( var i = 0; i < product_prices.length; i++ ){
        productsCatalogue[product].product_prices += ( i > 0 ? ' | ' : '' ) + product_prices[i].replace( '__CLASS__', 
          ( 'class="' + ( i == 0 ? 'txtGreen' : ( i == 1 ? 'txtRed' : 'txtYellow' ) ) + '"' ) );
      }
    //  alert( productsCatalogue[product].product_prices );
      resp += `<div class="seeker_item objetoLista" id="product_item_${productsCatalogue[product].product_id}"
        onclick="ocultaBuscador(this);"//insertProductRow( ${productsCatalogue[product].product_id} );
      >${productsCatalogue[product].list_order} | ${productsCatalogue[product].product_name} | ${productsCatalogue[product].product_prices}
      </div>`;
    }

    for (var pack in packsCatalogue){
      resp += `<div class="seeker_item objetoLista" id="product_item_${productsCatalogue[product].product_id}"
        onclick="ocultaBuscador(this, 1);"//insertProductRow( ${productsCatalogue[product].product_id} );
      >${packsCatalogue[pack].pack_id} | ${packsCatalogue[pack].pack_name}
      </div>`;
    }
    //alert( resp );
   // $( '#resBus' ).html( resp );
    document.getElementById( 'resBus' ).innerHTML = resp;
    document.getElementById( 'resBus' ).style.display = 'none';
   // if( localStorage.getItem('productsCatalogue') == null ){
      localStorage.setItem('productsCatalogue', JSON.stringify( productsCatalogue ));
    //}
   // $( '#resBus' ).css( 'display', 'block' );
  }

  function seek_product(obj, e){
    //alert( obj );
    if( e.keyCode == undefined && e != 'intro'  ){
      hidde_seeker_response();
      return false;
    }
    var txt_orig = $( obj ).val().trim().toUpperCase();
    var txt = txt_orig.split(' ');
    //var tmp_txt = txt.split( ' ' );
    if( txt.length == 4 && ( txt[1].includes( 'PQ' ) || txt[1].includes( 'CJ' ) ) ){
      txt_orig = '';
      for ( var i = 0; i < (txt.length - 1 ); i++ ) {
        txt_orig += ( txt_orig != '' ? ' ' : '' );
        txt_orig += txt[i];
      }
    }
    var size = productsCatalogue.length;
    var resp;
    if( $( obj ).val().length <= 2 ){
      $( '.seeker_item' ).css( 'display', 'block' );
      return false;
    }
    var ref_comp = txt.length;
    var was_finded_by_barcode = null;
   // alert( txt );
    if( e.keyCode == 13 || e == 'intro' ){
  //busca por codigo de barras
      var tmp;
      for (var product in productsCatalogue){
  //orden de lista
        if( productsCatalogue[product].list_order != '' 
          && productsCatalogue[product].list_order == txt_orig ){
              was_finded_by_barcode = product;
              break;
        }
  /*implementacion Oscar 2023 para buscar por clave de proveedor
        if( productsCatalogue[product].clave_proveedor != '' && productsCatalogue[product].clave_proveedor != null ){
          tmp = productsCatalogue[product].clave_proveedor.split( ' __ ' );
          for( var i = 0; i < tmp.length; i++ ){
            if( tmp[i] == txt_orig ){
              was_finded_by_barcode = product;
              break;
            }
          }
          if( was_finded_by_barcode != null ){
            break;
          }
        }
      */
  //codigos de pieza
        if( productsCatalogue[product].codigo_barras_pieza_1 != '' && productsCatalogue[product].codigo_barras_pieza_1 != null ){
          tmp = productsCatalogue[product].codigo_barras_pieza_1.split( ' __ ' );
          for( var i = 0; i < tmp.length; i++ ){
            if( tmp[i] == txt_orig ){
              was_finded_by_barcode = product;
              break;
            }
          }
          if( was_finded_by_barcode != null ){
            break;
          }
        }

        if( productsCatalogue[product].codigo_barras_pieza_2 != '' && productsCatalogue[product].codigo_barras_pieza_2 != null ){
          tmp = productsCatalogue[product].codigo_barras_pieza_2.split( ' __ ' );
          for( var i = 0; i < tmp.length; i++ ){
            if( tmp[i] == txt_orig ){
              was_finded_by_barcode = product;
              break;
            }
          }
          if( was_finded_by_barcode != null ){
            break;
          }
        }

        if( productsCatalogue[product].codigo_barras_pieza_3 != '' && productsCatalogue[product].codigo_barras_pieza_1 != null ){
          tmp = productsCatalogue[product].codigo_barras_pieza_3.split( ' __ ' );
          for( var i = 0; i < tmp.length; i++ ){
            if( tmp[i] == txt_orig ){
              was_finded_by_barcode = product;
              break;
            }
          }
          if( was_finded_by_barcode != null ){
            break;
          }
        }
  //codigos de paquete
        if( productsCatalogue[product].codigo_barras_presentacion_cluces_1 != '' && productsCatalogue[product].codigo_barras_pieza_1 != null ){
          tmp = productsCatalogue[product].codigo_barras_presentacion_cluces_1.split( ' __ ' );
          for( var i = 0; i < tmp.length; i++ ){
            if( tmp[i] == txt_orig ){
              was_finded_by_barcode = product;
              break;
            }
          }
          if( was_finded_by_barcode != null ){
            break;
          }
        }

        if( productsCatalogue[product].codigo_barras_presentacion_cluces_2 != '' && productsCatalogue[product].codigo_barras_pieza_1 != null ){
          tmp = productsCatalogue[product].codigo_barras_presentacion_cluces_2.split( ' __ ' );
          for( var i = 0; i < tmp.length; i++ ){
            if( tmp[i] == txt_orig ){
              was_finded_by_barcode = product;
              break;
            }
          }
          if( was_finded_by_barcode != null ){
            break;
          }
        }
  //codigos de caja
        if( productsCatalogue[product].codigo_barras_caja_1 != '' && productsCatalogue[product].codigo_barras_pieza_1 != null ){
          tmp = productsCatalogue[product].codigo_barras_caja_1.split( ' __ ' );
          for( var i = 0; i < tmp.length; i++ ){
            if( tmp[i] == txt_orig ){
              was_finded_by_barcode = product;
              break;
            }
          }
          if( was_finded_by_barcode != null ){
            break;
          }
        }

        if( productsCatalogue[product].codigo_barras_caja_2 != '' && productsCatalogue[product].codigo_barras_pieza_1 != null ){
          tmp = productsCatalogue[product].codigo_barras_caja_2.split( ' __ ' );
          for( var i = 0; i < tmp.length; i++ ){
            if( tmp[i] == txt_orig ){
              was_finded_by_barcode = product;
              break;
            }
          }
          if( was_finded_by_barcode != null ){
            break;
          }
        }

    }
  }
    //alert( was_finded_by_barcode );
    if( was_finded_by_barcode != null ){
    //  alert( 'here' );
      $( '#product_item_' + productsCatalogue[was_finded_by_barcode].product_id ).click();
      //insertProductRow( productsCatalogue[was_finded_by_barcode].product_id );
    //  $( obj ).val( '' );
      //hidde_seeker_response( true );
      return false;
    }
    //alert();
  //seeker_response
  var were_found = 0;
    $( '#resBus' ).children( 'div' ).each( function( index ){
      var txt_comp = $( this ).html().toUpperCase().trim();
      var matches  = 0;
      for (var j = 0; j < ref_comp; j++) {//comparacion de cadena de texto
        txt_comp.includes(txt[j]) ? matches ++ : null;
      }     
      $( this ).css('display', matches == ref_comp ? 'block' : 'none');
      were_found += (  matches == ref_comp ? 1 : 0 );
    });
    $( '#resBus' ).css( 'display', 'block' );
    $( '.icon-eye-5' ).addClass( 'icon-eye-off' );
    $( '.icon-eye-5' ).removeClass( 'icon-eye-5' );

    if( was_finded_by_barcode != null ){
      hidde_seeker_response( true );
    }else if( was_finded_by_barcode == null && were_found == 0 && ( e.keyCode == 13 || e == 'intro' ) ){
      alert( "El producto no fue encontrado!" );
    }
  }
//buscador
  function hidde_seeker_response( hidde = false ){
    if( $( '#resBus' ).hasClass( 'no_visible' ) || hidde == true  ){
      $( '#resBus' ).removeClass( 'no_visible' );
      $( '.icon-eye-5' ).addClass( 'icon-eye-off' );
      $( '.icon-eye-5' ).removeClass( 'icon-eye-5' );
    }else{
      $( '#resBus' ).addClass( 'no_visible' );
      $( '.icon-eye-off' ).addClass( 'icon-eye-5' );
      $( '.icon-eye-off' ).removeClass( 'icon-eye-off' );
    }
  }

  function solicitarMuestras( ){

    var baseUrl = window.location.origin;
    var apiUrl = baseUrl + '/sistemaGeneral2023/rest/v1/surte/Muestra';

    var productosSeleccionados = getProductosMuestras();

    if( productosSeleccionados.length > 0 ){
      if (confirm("Se enviarán a solicitar " + productosSeleccionados.length + " muestras. ¿Confirmas la operación?") == true) {
          $.ajax({
          type: 'POST',
          url: apiUrl,
          headers: {
            'Token':'9aca3d54-6eae-48f4-8597-6022be714915'
          },
          data: {
              "productos": productosSeleccionados,
              "vendedor": "EL VENDEDOR",
              "pedido": "123"
          },
          datatype: 'json',
          success: function (data) {
              //var obj = JSON.parse(data);
              
              console.log(data);
              alert(JSON.stringify(data,null,true));
              
              clearAllCheckBox();

          },
          error:function(error){
            console.log("ERROR".error);
          }
        });
        } else {
          alert("Cancelaste la operación");
        }
        
    }else{
      alert("Favor de seleccionar al menos una casilla de muestra correspondiente a un producto dentro de la tabla");
    }
  }

  function selectAllMuestras(item){
    var isChecked = $(item).is(':checked'); // Verifica si el checkbox clicado está seleccionado o no
    $('.cbMuestra').prop('checked', isChecked);
  }
  /**
   * Función que recorre las filas de la tabla que tienen el checkbox seleccionado
   */
  function getProductosMuestras(){
    
    var seleccionados = [];
    var filas = $('#listaProductos tr.move');
    if( filas.length > 0 ){
      // Mostrar las filas en la consola
      filas.each(function(index, fila) {
        var $row = $(this);
        var isChecked = $row.find('input[type="checkbox"]').is(':checked');
        if (isChecked) {
          var ordenDeLista = $row.find('td:eq(0)').text().trim();
          var cantidad = $row.find('td:eq(2)').text().trim();
          seleccionados.push({
            id: ordenDeLista,
            cantidad: cantidad
          });
        }
      });
    }
    return seleccionados;
  }

  function clearAllCheckBox(){
    $(".cbSelectAllMuestras").prop('checked', false); 
    $('.cbMuestra:checked').each(function() {
        //$(this).prop('checked', false);  // Desmarcar el checkbox
        $(this).trigger('click');        // Lanzar el evento click
    });
  }

  function openDevolucionProductos(){
    var filas = $('#listaProductos tr.move');
    
    if(filas.length > 0){
      $('#modalDevolucion').show();
      //Cada que el modal se muestre, el input se limpia
      $('#inputDevolucion').val("");
      $('#inputDevolucion').focus();
    }else{
      alert("No se tienen productos en el pedido para devolver.\nFavor de agregar al menos un producto");
    }

  }

  function closeModal(){
    $('#modalDevolucion').hide();
  }

  function detectaEnter( e ){
    if( e.key ==='Enter' || e.keyCode === 13 ){
        buscarProducto();
    }
  }

  function buscarProducto(){
    
    var valCodigo = $('#inputDevolucion').val();
      if( valCodigo == "" ){
        alert('Favor de ingresar un código de producto');
      }else{

        var arrProductosTabla = buildObjetoOrdenListaCodigoBarras();

        if( arrProductosTabla.length > 0 ){
          //VALIDAR QUE EL CÓDIGO DE BARRAS SE ENCUENTRA EN EL OBJETO DE LA TABLA
          const listOrder = findListOrderByBarcode(valCodigo, arrProductosTabla);

          if( listOrder != null ){

            descontarCantidad(listOrder);
          }else{
            alert("El código ingresado no existe en el pedido.\nFavor de revisar");
            $('#inputDevolucion').val("");
          }

        }else{
          alert("No existen productos para devolver.");

        }
      }
  }

  function findListOrderByBarcode(barcode, productos) {
    for (const producto of productos) {
        if (
            producto.codigo_barras_pieza_1 && producto.codigo_barras_pieza_1.includes(barcode) ||
            producto.codigo_barras_pieza_2 && producto.codigo_barras_pieza_2.includes(barcode) ||
            producto.codigo_barras_pieza_3 && producto.codigo_barras_pieza_3.includes(barcode)
        ) {
            return producto.list_order;
        }
    }
    return null; // Devuelve null si no se encuentra el código de barras
}


  function buildObjetoOrdenListaCodigoBarras(){

    productsCatalogueLocal = JSON.parse( localStorage.getItem('productsCatalogue') );

    var filas = $('#listaProductos tr.move');   
    var idsProductos = [];
    var arrProductos = [];
    // Mostrar las filas en la consola
    filas.each(function(index, fila) {
        //console.log('Fila ' + (index + 1) + ':');
        // Obtener las celdas de la fila actual
        var celdas = $(fila).find('td');
        
        // Mostrar el contenido de cada celda
        celdas.each(function(indice, celda) {
            //console.log(' Columna ' + (indice + 1) + ': ' + $(celda).text());
            if( indice == 0 ){ //El indice 0 contiene el número de Orden de lista (Código de Producto)
              var ordenLista = $(celda).text();
              
              var objProductoLocal = findProductByListOrder( productsCatalogueLocal, ordenLista );
             
              arrProductos.push(objProductoLocal);
              //idsProductos.push($(celda).text());
            }
        });
    });
    console.log("OBJ PRODUCTOS" );
    console.log(arrProductos);

    return arrProductos;
  }

  function findProductByListOrder( arrProductosLocal, listOrder ) {
    return arrProductosLocal.find(producto => producto.list_order === listOrder);
}

  function descontarCantidad(ordenDeLista) {
    //searchOrdenListaByBarCode();
    $('#listaProductos tr.move').each(function() {
      var $row = $(this);
      var rowOrdenDeLista = $row.find('td:eq(0)').text().trim();
      if (rowOrdenDeLista === ordenDeLista) {
        var $cantidadCell = $row.find('td:eq(2)');
        var cantidad = parseInt($cantidadCell.text().trim());
        if (cantidad > 0) {
          var nuevaCantidad = cantidad - 1;
          if( nuevaCantidad == 0 ){
            //Eliminar fila, se manda a ejecutar directamente el click en el botón "quitar"
            //eq(6) - en la posición 6 de la fila se encuentra el botón para quitar
            $row.find('td:eq(6)').children().trigger('click');
            closeModal();

          }else{

            $cantidadCell.text(nuevaCantidad);
            var precio = $row.find('td:eq(3)').text().trim();
            var precioString = precio.replace(/[^0-9.-]+/g, "");
            var precio = parseFloat(precioString);

            var nuevoMonto = nuevaCantidad * precio;
            //Se actualiza la columna 5 que corresponde al monto
            $row.find('td:eq(5)').text("$" + nuevoMonto.toFixed(2));
            
            //Actualizar cantidad en sessionStorage
            updateCantidadMontoSessionStorage( ordenDeLista, nuevaCantidad, nuevoMonto );
            //Actualizar el total del pedido
            calculaTotalVenta();

            closeModal();

          }
          
        }
      }
    });
  }

  // function searchOrdenListaByBarCode(){

  // }

  function updateCantidadMontoSessionStorage( idProducto,nuevaCantidad, nuevoMonto ){

    if( sessionStorage.getItem('SALE_PRODUCTS_LIST') != null && sessionStorage.getItem('SALE_PRODUCTS_LIST') != '' ){

      var products_array = sessionStorage.getItem('SALE_PRODUCTS_LIST');
        //var products_array = localStorage.getItem('SALE_PRODUCTS_LIST');
      products_array = JSON.parse( products_array );

      var indexSessionStorage = getIndexSessionStorage( products_array, idProducto );

      if( indexSessionStorage != null ){
        //Se actualiza la cantidad que se encuentra en la posición 3
        products_array[indexSessionStorage][2] = nuevaCantidad;
        //Se actualiza el monto que se encuentra en la posicion 6
        products_array[indexSessionStorage][5] = "$"+nuevoMonto;

        sessionStorage.setItem('SALE_PRODUCTS_LIST', JSON.stringify( products_array ));
      }
    }

  }

  function getIndexSessionStorage( productsArray, idProducto ){
    var index = null;
    for (let i = 0; i < productsArray.length; i++) {
      // El id de Producto o Número de lista se encuenta en la posición 0, es por eso que se valida  productsArray[i][0]
      if( productsArray[i][0] == idProducto ){
        index = i
        //Instrucción para romper el ciclo y salir de el
        i = productsArray.length;
      } 
    }
    return index;
  }


</script>
<!-- -->

<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='expires' content='0'>
<meta http-equiv='pragma' content='no-cache'>
<link rel="stylesheet" href="../scannerBC/zxing/library/modalStyle.css">
<style>
  /*impplementado por Oscar 04-11-2017*/
  .des{
    width:95%;
    height:30px;
    border:1px solid blue;
    padding:2px;
    font-size:15px;
    border-radius: 5px;
  }
  .objetoLista{
  	padding:20px;
  }

  .autDesc{
    font-size: 25px;
    color:white;
  }
	.Pcolores{
		display:none;
		position:fixed;
		z-index:1000;
		top:0px;
		left:0px; 
		bottom:0px;
		right:0px;
		background-color: black;
		filter: alpha(opacity=100);
	}
	
	.btn_pal{
		width: 70px;
		height:30px;
	}
	
	.ctn_paleta{
		width:90%;
		height:90%;
		margin:0 auto;
		margin-top:12px;
		background:#fff;
		padding:12px;
		background: #fff;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
		margin-top:12px; 
		overflow: hidden;
		-webkit-box-shadow: -1px 0 0 0 #d2d2d2, -1px 0 0 0 #e6e6e6, 1px 0 0 0 #d2d2d2, 2px 0 0 0 #e6e6e6, 0 -1px 0 0 #e8e8e8, 0 2px 0 0 rgba(241, 241, 241, 0.3), 0 1px 0 0 #b1b1b1;
		-moz-box-shadow: -1px 0 0 0 #d2d2d2, -1px 0 0 0 #e6e6e6, 1px 0 0 0 #d2d2d2, 2px 0 0 0 #e6e6e6, 0 -1px 0 0 #e8e8e8, 0 2px 0 0 rgba(241, 241, 241, 0.3), 0 1px 0 0 #b1b1b1;
		box-shadow: -1px 0 0 0 #d2d2d2, -1px 0 0 0 #e6e6e6, 1px 0 0 0 #d2d2d2, 2px 0 0 0 #e6e6e6, 0 -1px 0 0 #e8e8e8, 0 2px 0 0 rgba(241, 241, 241, 0.3), 0 1px 0 0 #b1b1b1;
		-ie7-8-border: 1px solid #dedede;
		border: 1px solid #dedede \9;
		position: relative;
		margin-bottom: 9p
	}
		
	.ctn_paleta > td > button{
		background:#f60;
		border-radius:6px;}	
		
	.ctn_paleta table{
		border: none !important;
		width:80%px !important;
		
	}
	
	.ctn_paleta table td{border: none !important;}
	
	.tablaFuera{
		width:80% !important;
		height:70% !important;
	}	
  /*Agregado por Oscar(28-10-2017)*/
  .tablaEmergente{
    /*border:1px solid rgba(0,0,225,0.5);*/
    width:95%;
    height:200px;
    overflow:scroll;
  }
  .bot_reg_efec{
    padding: 5px;
    width: 200px;
    background:white;
    text-decoration: none;
    color:black;
    border-radius: 5px;
  }
  .bot_emerge_1{
    position:absolute;top:15px;right:15px;background:rgba(225,0,0,0.6);color:white;padding:20px;
  }
  .btn_emgr{padding: 10px; color:black;font-size:20px;margin:30px;}
  .btn_emgr:hover{background:#36FF33;}
  
  .txtGreen{
    background-color : green;
    color : white;
    padding : 5px ;
  }
  .txtRed{
    background-color : red;
    color : white;
    padding : 5px ;
  }
  .txtYellow{
    background-color : orange;
    color : white;
    padding : 5px ;
  }
  .seeker_item{
    box-shadow: 1px 1px 10px rgba( 0,0,0,.3 );
  }
  .no_visible{
    display: none;
  }
  .cabecera{
    position: sticky !important;
    top : 0;
  }
/*Oscar 2023 para marcar en rojo productos sin inventario*/
  .red{
    color : red !important;
  }
  .orange{
    color : orange !important;
  }

  .buttonMuestras{
    margin-bottom: 10px;
    margin-top: 10px;
    float: right;
    /* background-color: #2d97b2; */
  }

  .input-group {
    display: flex;
    align-items: center; /* Centra verticalmente los elementos dentro del grupo */
    margin-bottom: 10px;
  }

  .input-group span {
    margin-right: 10px; /* Espacio entre el span y el input */
  }

  .modal {
    display: none; /* Oculto por default */
    position: fixed; 
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; 
    background-color: rgb(0,0,0); 
    background-color: rgba(0,0,0,0.4);
}

/* Modal content */
.modal-content {
    background-color: #fefefe;
    margin: 15% auto; 
    padding: 20px;
    border: 1px solid #888;
    width: 40%; 
    position: relative; 
    text-align: center;
}

.close {
    color: #aaa;
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

.modal-input {
  /* width: 50%; */
  padding: 10px;
  margin: 20px 0;
}

.confirm-btn {
    padding: 10px 20px;
    background-color: #0679c8;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.confirm-btn:hover {
    background-color: #055993;
}
</style>
<?php
 // extract($_GET);
 //die($tv);
/*impleemntacion Oscar 25.06.2019 para guardar los ids de devolucion*/
  if(isset($id_dev)){
    $sql = "SELECT status FROM ec_devolucion WHERE id_devolucion = '{$id_dev}'";
    $eje = mysql_query( $sql ) or die( "Error al consultar status de la devolución : " . mysql_error() );
    $row = mysql_fetch_row( $eje );
    if( $row[0] == 3){
      echo '<script>alert("esta devolucion ya fue completada!");location.href="index.php?";</script>';
    }
    echo '<input type="hidden" id="id_de_devoluciones" value="'.$id_dev.'">';
  }

/*fin de cambio Oscar 25.06.2019*/
  
  if($tv!=""||$tv!=null){

?>
<!---->
    <input type="hidden" id="id_lista_mayoreo" value="<?php echo $aWRfcHJlY2lv;?>">
<!---->
<div>
  <p align="center" style="font-size:25px;font-family:fantasy;color:orange;">Venta Por Mayoreo</p>
</div>

<?php
  }
?>
<div class="eme" id="emergente_1" style="width:100%;height:100%;max-height:100%;position:fixed;top:0;background:rgba(0,0,0,.8);display:none;z-index:10000;overflow : auto;">
  <p id="cont_emerg" style="color:white;font-size:30px;" align="center">
    
  </p>
</div>

<!-- Ventana emergente para devolución de productos -->
<div id="modalDevolucion" class="modal">
  <div class="modal-content">
    <span class="close closeModalEscanear" onclick="closeModal()">&times;</span>
    <h3><b>Escanear código de producto</b></h3>
    <input id="inputDevolucion" type="text" placeholder="Ingresa código de producto" class="modal-input" onkeyup="detectaEnter(event)">
    <button class="confirm-btn" onclick="buscarProducto()">Confirmar</button>
  </div>
</div>

<input type="hidden" id="tipo_venta" value="<?php echo $tv;?>">
<div id="paleta_Colores" class="Pcolores"> <!--style="display:none; position:fixed; overflow:hidden; z-index:1000; top:0px; left:0px; bottom:0px; right:0px; background-color: black; opacity: 1; filter: alpha(opacity=100);"-->
    <div class="ctn_paleta">
	<form name="formaColores">
     <table width="100%" class="tablaFuera"  cellpadding="0" cellspacing="0" align="center" id="tablaPaleta">
     <tr>
     <td id="labelProductoPaleta"></td>
     <td id="cantidadProductoPaleta"></td>
     </tr>
     <tr id="">
     	<input type="hidden" name="cantidadPaleta" id="cantidadPaleta" value=""/>
      
     	<td colspan="2" width="100%">
     		<div style="height: 350px; width:100%; overflow-y: scroll; color: #333 !important;">
     		<table id="contenidoPaleta" height="300px" width="100%">
     			<tr>
       				<td style="color: #333 !important;">Rojo</td>
       				<td><input class="btn_sss" maxlength="4" type="text" name="cantidad"></td>
       			</tr>

            <tr>
        	</table>
      </div>
       </td>  


     </tr>
      
     <tr>
     <td>
    <button class="btn_ss" type="button" name="cancelar" id="cantidadPaleta99" tabindex="99" onclick="cancelaPaleta()">Cancelar</button>
     </td>
     <td>
     <button  class="btn_ss" type="button" name="enviar" tabindex="100" onclick="cierraPaleta()">Enviar</button>
     </td>
     </tr>
     </table>
     </form> 
    
    </div>


</div>

  <div id="divEspera" style="display: none; position:fixed; overflow:hidden; z-index:1000; top:0px; left:0px; bottom:0px; right:0px; background-color: black; opacity: 1; filter: alpha(opacity=100);">
  <!--Comienza la ventana en bloqueo-->
    <div class="espera_ctn">
      <img src="img/loading.gif">
      <p>Esperando autorización...</p>
      <button name="cancelar" id="cancelar" data-role="button">X Cancelar</button>
    </div>
  </div>
<!--Termina la ventana en bloqueo-->
<!--Implementación de oscar 24-12-2017-->
  <?php
    if(isset($s_f_c)){
      $s_f_c = round( $s_f_c, 2 );
  ?>
    <div style="width:300px;" style="border:0;position:fixed;right:30px;">
      <table style="border:0;position:absolute;width:100%;background:transparent;margin:0;border-spacing : 0;">
        <tr style="background:rgba(225,0,0,0.5);">
        <?php
            if(isset($abonado)&$abonado!=0){//si se trata de un cambio en apartados
              echo '<td style="color:white;font-size:30px;padding:10px;" align="center"><b>Cambio en Apartado</b><div style="font-size:22px;">Ingrese los productos nuevos</div></td>';
            }else{
        ?>
          <td align="right" width="50%" style="font-size:120% !important;border:inset 0pt;color:white; padding : 10px;">
            <b>Saldo a favor del cliente: $</b></td>
            <td width="25%" style="border: inset 0pt;">
            <input type="text" value="<?php echo $s_f_c;?>" id="saldoAFavor" style="color:black;font-size:120% !important;background:transparent;font-weight:bold;margin:0;
            position:relative;left:0px;color:white;border : none;" class="form-control">
          </td>
          <td width="25%" align="center">
            <a href="javascript:regresaEfectivo('password');" class="bot_reg_efec">Regresar efectivo</a>
          </td>
        <?php
          }//fin de else
        ?>
        </tr>
      </table>
    </div><br>
  <?php
   if(isset($abonado)&&$abonado!=0){
    echo '<br><br>';
   }
  }
  ?>
<!-- quaggajs function-->
<!-- 2023 -->

  <div class="row">
    <div class="col-4">
        <ul  data-role="listview" data-inset="true" class="ui-grid-a">
          <li class="ui-block-b" style="display:none">
            <label for="pedido">Pedido</label>
            <input type="text" readonly value="<?php echo $es_pedido ? (isset($folio) ? $folio : "") : ""; ?>" disabled="disabled" name="folioP"  id="folio_pedido">
          </li>
        </ul>
    </div>  
  </div>
<!-- botones -->
  <div class="row" style="padding: 10px !important;">
    <div class="col-3 text-center">
    <?php
     /* 
DESHABILITADO POR OSCAR 2023 ( ESCANER DE BARCODES CON CAMARA )
     $sql = "SELECT 
                permite_escanear_con_camara AS allow_scann_camera
              FROM sys_configuracion_sistema
              WHERE id_configuracion_sistema = 1";
      $stm = mysql_query( $sql ) or die( "Error al consultar permiso de cámara : {mysql_error()}" );
      $row_camera =mysql_fetch_assoc( $stm );
      if( $row_camera['allow_scann_camera'] == 1 ){
        <div>
          <button class="btn " id="btnEscanear"><!-- style="width : 15%;" -->
            <img style="padding-left:10px;" src="../scannerBC/zxing/assets/barcode.png" width="60" height="50">
            <p><!-- style="display:block; position:relative; top:-1.2em; left:.4em;" -->
               Escanear<br>con Cámara
            </p>
          </button>
        </div>
      }*/
    ?>
      <div class="input-group">
        <input type="password" class="form-control" placeholder="Password" 
          id="exhibition_mannager_password"
          onkeyup="enable_exhibition_by_password( event );"
        >
        <button
          class="btn btn-warning"
          onclick="enable_exhibition_by_password( 'intro' );"
          id="exhibition_mannager_password_btn"
        >
          <i class="icon-barcode"></i>
        </button>
      </div>
    </div>
    <div class="col-3 text-center row">
      <div class="col-6 text-end">
        <img style="top:-10px; position:relative;" src="img/teclado.png" width="50" height="50">
      </div> 
      <div class="col-6">
        <p class="text-start" style="color : red;"><!-- style="display:block; position:relative; top:-1.7em; left:1.2em;" -->
          Ocultar<br>teclado
        </p>
      </div>
    </div>

    <div class="col-3 text-center">
       <p 
          type="button"
          class="btn" 
          style="color : orange; top : -10px; position : relative !important;"
          onclick="document.getElementById('resBus').style.display='none';document.getElementById('buscadorLabel').value=''; document.getElementById('buscadorLabel').focus();">
          <i class="icon-eye-off" style="font-size : 200%;"></i>
          Borrar
          <br>
          Búsqueda
        </p>
    </div>
  </div>
  <h2 class="text-center text-danger" id="exhibition_message" style="display : none;"><b>*** ESTE PRODUCTO SERÁ TOMADO DE EXHIBICIÓN ***</b></h2>
<?php
  /*implementacion Oscar 2023 para indicar que es cotizacion*/
  if( isset( $_GET['is_quotation'] ) ){
   // echo "<input type=\"hidden\" id=\"is_just_quotation\" value=\"{$_GET['is_quotation']}\">";
    echo "<h3 class=\"text-center text-danger\" 
          style=\"font-size : 200% !important; position : relative; top : -20px;\">***Cotizacion***<h3>";
  }
?>

<!-- /2023 -->
<!--Fin de cambio-->
<div data-role="content">
  <!--div title="Escanear producto"  style="position:absolute; display:block; width:100px;top:100px;right:0px;">
     <a class="button" id="startButton">
       <img style="padding-left:10px;" src="../scannerBC/zxing/assets/barcode.png" width="60" height="50">
       <p style="display:block; position:relative; top:-1.2em; left:.4em;">
          Escanear
       </p>
     </a>
  </div-->

<div class="row" style="padding: 10px !important; position : relative; top : -20px;">
  <div class="col-7">
      <label for="producto"> Producto</label><br>
    <div class="input-group">
      <input type="text" name="buscadorLabel"  id="buscadorLabel" style="background: transparent; display:block;"   onfocus="this.setSelectionRange(0, this.value.length);"
      onkeyup="seek_product(this, event)"
      class="form-control"/>
      <button 
        type="button"
        onclick="seek_product('#buscadorLabel', 'intro');"
        class="btn btn-primary"
      >
        <i class="icon-search"></i>
      </button>
    </div>
      <label id="msjConfirmacion"> </label>
      <div id="resBus" class="lista_producto" style="display:none; position:relative; z-index:500;width:150%"></div>
      <input type="hidden" name="id_productoN" value="" />     
    
  </div>
  <div class="col-3"><!-- style="background: transparent; width:15% !important ; display:block; position:relative;"-->
     <label>Cantidad</label>
    <input type="text" maxlength="7"  id="cantidad2" class="cantidad form-control" name="cantidad" onfocus="muestraDesc(0)" onkeyup="validaKey(event, this.form)" onkeypress="return validarNumero(event,0,'cantidad2')">
  </div>  
    
    <div class="col-2 text-start"><!--style="background: transparent; zoom:1.5; -webkit-transform:scale(1.5,1.5);transform:scale(1.5,1.5); width:40px !important ;  display:block; position:relative;" -->
    Agregar<br>
      <button 
        name="AgregaFila" 
        id="AgregaFila"
        class="btn btn-success"
        onclick="agregaFila(this.form)">
        <i class="icon-plus"></i>
      </button>     
   </div>
  
  </div> 
      
 
   
   
    <input type="hidden" name="id_producto"  id="id_producto" />
    <input type="hidden" name="id_pedido"  id="id_pedido" value="<?php echo $id_pedido; ?>" />
    <!-- </form> -->
 
  <!---Grid-->
  <div class="row products_list_container">
   <div class="contenedorGrid" id="contenedorGrid">
    <!-- Nuevo botón para Solicitar muestras -->
    <button 
        type="button"
        onclick="solicitarMuestras();"
        class="btn btn-primary buttonMuestras"
      >
        <b>Solicitar muestras</b>
      </button>
    <table id="listaProductos" class="table table-striped table-bordered" style="margin-left:20;" cellpadding="0" cellspacing="0">
    <thead class="product_list_header">
      <tr class="cabecera">
        <th width="100"><p>Producto</p></th>
        <th width="350"><p>Descripción</p></th>
        <th width="100"><p>Cant</p></th>
        <th width="100"><p>Precio</p></th>
      <!--Cambio de omitir oferta en venta por mayoreo Oscar (31-10.2017)-->
      <?php
        if($tv!=1){
      ?>  
        <th width="100"><p>Oferta</p></th>
      <?php
        }
      ?>
        <th width="100"><p>Monto</p></th>
        <th width="100"><p>Quitar</p></th>
        <th colspan="10" width="0" style="display:none;"><p></p></th>
        <th width="100">
          <p>Muestra</p>
          <p><input type="checkbox" class="cbSelectAllMuestras" onclick="selectAllMuestras(this)" title="Seleccionar / Deseleccionar todo"></p>
        </th>
         <!--  <td width="0"></td>
        <td width="0"></td>
        <td width="0"></td>
        <td width="0"></td>
        <td width="0"></td>-->
      </tr>
    </thead>
    <tbody>
      <!--Termina la cabecera-->
      <?php if (count($productos)) { foreach ($productos as $producto) { ?>
      <tr class="move">
        <td class="tabla_id_producto"><p><?php echo $producto["id_producto"]; ?></p></td>
        <td><p><?php echo $producto["producto"]; ?></p></td>
        <td class="tabla_cantidad"><p><?php echo $producto["cantidad"]; ?></p></td>
        <td class="tabla_precio"><p>$ <?php echo number_format($producto["precio"], 2); ?></p></td>
        <td class="tabla_total"><p>$ <?php echo number_format($producto["monto"], 2); ?></p></td>
        <td class="tabla_detalles"><a href="javascript:void(0)" onclick="eliminarItem(this)" class="eliminar">
          <i class="icon-cancel-alt-filled"></i>
        </a>
        <!--<td class="tabla_descuento_producto" style="display:block;"><p></p></td> Desactivado por Oscar 31.08.2018-->
        <input type="hidden" class="es_regalo"  value="<?php echo $producto["es_regalo"]; ?>"/></td>
      </tr>
      <?php }} ?>
    </tbody>
    </table>
    </div>
  </div>
  <!--seccion-->
<!--  <a class="regalo" id="img_regalo"  href="javascript:void(0)"><span>regalo</span></a>-->

      <div class="row" style="width: 100% !important;max-width: 100% !important;">    
        <div class="col-7">

        </div>
        <div class="col-5 text-center"><!--data-role="fieldcontain"-->
          <label>
            <strong style="font-weight:bold; font-size:20px; padding-right: 2px;">
              Total
            </strong>
          </label>
          <input type="text" class="form-control" readonly value="$ <?php echo number_format($subtotal, 2); ?>"  name="total" id="total">
        </div>
    </div>
  <!--div class="row"><!--style="position : relative; width : 60%; left : 20%;" >
    <div class="col-1"></div>
    <div class="col-10">
      <button
        class="btn btn-warning form-control"
        onclick="deleteLocalStorageProducts( true );"
      >
        <i>Borrar almacenamiento temporal</i>
      </button>
    </div>
  </div-->
  <br>
  <div class="row"><!--class="ui-block-bwidth:48%;float:left;"-->
    <div class="col-3"></div>
    <div class="col-6">
      <button data-theme="a" 
        name="cerrar" 
        id="cerrar" 
        class="btn1 btn btn-primary form-control" 
        style="min-width:180px; margin-right:50px;"
        >✔ Enviar pedido</button>
      </div>
      <div class="col-3"></div>
  </div>
  <!--Termina seccion-->
  </div>
  <br>
  <div class="ui-grid-a" style="">
    <div class="ui-block-b">
      <div style=" display:none;float:left; width:175px">
         <fieldset  data-role="controlgroup"  data-type="horizontal">
           <label data-mini="true">
			  	    <input  type="checkbox" name="pedido" id="es_pedido" disabled="disabled" >Generar pedido
           </label>
         </fieldset>
        </div>
    <!--Descuentos-->
    
        <div class="row" style="align-items: center;justify-content: center;">
            <div class="col-2 text-center">
              <button 
                type="button"
                onclick="openDevolucionProductos();"
                class="btn btn-primary"
              >
                <b>Productos para devolver</b>
              </button>
            </div>
            <div class="col-2 text-center"><!-- width="20%" align="center" style="border: inset 0pt;" -->
             <fieldset  data-role="controlgroup" data-type="horizontal" style="display :none;">
               <label data-mini="true" style="width:100px;padding:10px;text-align:center;">
                  <input type="checkbox" name="paquete" id="es_paquete"/> <?php echo 'Paquete';?>
                </label>
              </fieldset>
              <br>
              <button 
                onclick="javascript:activa_paq();" 
                class="btn">
                <p id="paq1" >
                  Paquete<img src="../img/paquete.png" width="40px" height="40px">
                </p>
              </button>
            </div>
            <div class="col-3 text-center" onclick="habilitaDesc(1);">
              <div class="input-group">
                <span style="font-size: 20px; font-weight: bold;">-%</span>
                <input type="number" id="porcGeneral" style="padding: 10px; width: 80%; border: 2px solid; font-size: 20px; font-weight: bold; border-radius: 5px; background: white;"
                onkeyup="calculaTotalVenta(1);" class="form-control" value="" >
              </div>
            </div>
            <div class="col-3 text-center" onclick="habilitaDesc(2);">
              <div class="input-group">
                <span style="font-size: 20px; font-weight: bold;">-$</span>
                <input type="number" id="descGeneral" style="padding: 10px; width: 80%; border: 2px solid; font-size: 20px; font-weight: bold; border-radius: 5px; background: white;"
                onkeyup="calculaTotalVenta(2);" value="" class="form-control" name="descGeneral">
              </div>
               
            </div>
        </div>

        <div class="row text-center">
          <div class="col-4"></div>
          <div class="col-8">
            <button
              class="btn btn-primary"
              onclick="guardarVenta( 'quotation' );"
            >
              <i class="icon-dollar">Imprimir Cotización</i>
            </button>
          </div>
            
        </div>
<!-- 
<a href="" target="_blank" style="display : none ;" id="open_new_sale_link"></a>
 -->  
    </div>
  </div>
  <br>
  <br>
  <br>
</div>

<!--Implementación Oscar 13.11.2018 para guardar el temporal en los cambios sobre devolución
  <input type="hidden" id="detalle_nota_tmp" value="">
Fin de cambio Oscar 13.11.2018-->

<input type="hidden" id="cantidades_capturadas" value="">
<!--implementación Oscar 03.09.2018 para cargar apartados-->
  <?php
/*Implementación Oscar 02.03.2019 para el status de la devolucion*/
      echo '<input type="hidden" value="'.$id_ped.'" id="id_de_pedido_original">';
/*Fin de cambio Oscar 02.03.2019*/
      echo '<input type="hidden" value="'.$abonado.'" id="monto_abonado">';
    if($es_apart==1){
      echo '<input type="hidden" value="'.$id_ped.'" id="id_de_apartado">';
      echo '<script type="text/JavaScript">carga_ped_apartado('.$id_ped.',0);</script>';//mandamos llamar la función que carga el apartado en la tabla
      //window.onload= function carga(){ setTimeout("carga_ped_apartado('.$id_ped.')",3000);
    }
    /*implementación para cargar descuento de una nota anterior*/
      if(isset($dsc)&&$dsc>0){
        echo '<input type="hidden" id="desc_anterior" value="'.round($dsc*100).'">';
        //echo '<script type="text/JavaScript">carga_ped_apartado(0,1);</script>';
        //echo '<script>window.onload=document.getElementById(\'porcGeneral\').value=\''.round($dsc*100).'\';</script>';
      }
    /**/

/***/
  if(isset($dHJhbnNmZXJlbmNpYQ) && $dHJhbnNmZXJlbmNpYQ!=''){
    echo '<script type="text/JavaScript">carga_ped_apartado(0,'.$dHJhbnNmZXJlbmNpYQ.');</script>';
  }
/***/
  ?>
<!-- implementacion Oscar 2023 para utiliuzar el localstorage-->
<?php
  if( !isset( $_GET['aWRfcHJlY2lv'] ) ){
?>
  <script type="text/javascript">
    getProductsCatalogue();
    //setTimeout( function(){ 
      /*if( localStorage.getItem('productsCatalogue') == null ){
        getProductsCatalogue();
      }else{*/
        //getProductsCatalogueByLocalStorage();
      /*}*/
    //}, 1000 );
  </script>
<?php 
  }else{
?>
  <script type="text/javascript">
    getProductsCatalogue( <?php echo "{$_GET['aWRfcHJlY2lv']}";?> );
  </script>
<?php

  }
?>

<script type="text/javascript">
  if( sessionStorage.getItem('SALE_PRODUCTS_LIST') != null && sessionStorage.getItem('SALE_PRODUCTS_LIST') != '' ){
    getLocalStorageProducts();
    //document.getElementById( 'current_sale_btn' ).style.display='block';
    if( sessionStorage.getItem('IS_PACK') != null && sessionStorage.getItem('IS_PACK') != '' ){
      activa_paq();//Oscar 2023 para activar paquete por sessionStorage
    }
  }
</script>
<!-- fin de cambio Oscar 2023-->

<!--window.onload = function() {
  init();
  doSomethingElse();
};-->