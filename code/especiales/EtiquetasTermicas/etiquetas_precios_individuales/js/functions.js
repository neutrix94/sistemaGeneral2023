
var productsCatalogue = new Array();
var packsCatalogue = new Array();
  function getProductsCatalogue( price_list = '' ){
//alert( 'getProductsCatalogue' );
    var url = "../../../../touch_desarrollo/ajax/productsCatalogue.php?fl_db=getProductsCatalogue";
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
        if( productsCatalogue[product].product_name ){
            productsCatalogue[product].product_name = productsCatalogue[product].product_name.replaceAll( '<>', '' );
            productsCatalogue[product].product_name = productsCatalogue[product].product_name.replaceAll( "'", "" );
        }            //
      resp += `<div class="seeker_item objetoLista" id="product_item_${productsCatalogue[product].product_id}"
        onclick="ocultaBuscador(${productsCatalogue[product].product_id}, '${productsCatalogue[product].product_name}');"
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
    $( '#resBus' ).css( 'display', 'block' );
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
  function ocultaBuscador( product_id, product_name ){
    $( '#resBus' ).css( 'display', 'none' );
    $( '#current_product_name' ).html(product_name);
    create_template( product_id, product_name, 1);
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
//funcion ajaxR
    function ajaxR(url){
        if(window.ActiveXObject){       
            var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
        }
        else if (window.XMLHttpRequest)
        {       
            var httpObj = new XMLHttpRequest(); 
        }
        httpObj.open("POST", url , false, "", "");
        httpObj.send(null);
        return httpObj.responseText;
    } 