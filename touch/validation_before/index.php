<?php
//conexiones a la base de datos
	include( '../../config.inc.php' );
	include( '../../conect.php' );//sesión
	include( '../../conexionMysqli.php' );
	//die( $user_id );
?>
<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../css/icons/css/fontello.css">
	<script type="text/javascript" src="../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<link href="../../css/icons/css/fontello.css" rel="stylesheet" type="text/css"  media="all" />
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script type="text/javascript" src="../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="../../code/especiales/plugins/js/barcodeValidationStructure.js"></script>

	<title>Revisión de Ticket</title>
</head>
<body>
<?php
	echo '<input type="hidden" id="user_id" value="' . $user_id . '" >';
?>
	<audio id="audio" controls style="display : none;">
		<source type="audio/wav" src="../../files/scanner.mp3">
	</audio>
	<audio id="separate_this_product" controls style="display : none;">
		<source type="audio/wav" src="../../files/sounds/sales/separate_this_product.mp3">
	</audio>
	<audio id="ok" controls style="display : none;">
		<source type="audio/wav" src="../../files/sounds/ok.mp3">
	</audio>
	<audio id="error" controls style="display : none;">
		<source type="audio/wav" src="../../files/sounds/error.mp3">
	</audio>

	<div class="emergent" tabindex="1">
		<div style="position: relative; top : 120px; left: 90%; z-index:1; display : none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content"></div>
	</div>

	<div class="emergent_2" tabindex="1">
		<div style="position: relative; top : 120px; left: 90%; z-index:1; display : none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content_2"></div>
	</div>

	<div class="global_container">
		<div class="header">
			<div class="row">
				<div class="mnu_item invoices active" id="check_ticket" onclick="show_view( this, '.check_ticket');">
					<i class="icon-tag-2"></i><br>
					Seleccionar Ticket
				</div>
				<div class="mnu_item source" id="check_ticket_detail" onclick="show_view( this, '.check_ticket_detail');">
					<i class="icon-ok-circle"></i><br>
					Revisar Ticket
				</div>
				<!--div class="mnu_item source" onclick="show_view( this, '.validate_transfers');">
					<i class="icon-ok-circle"></i><br>
					Verificar
				</div-->
			</div>
		</div>

		<div class="content_container">
			<div class="content_item check_ticket">
				<?php 
					include( 'views/check_ticket.php' );
				?>
			</div>

			<div class="content_item check_ticket_detail hidden">
				<?php 
					include( 'views/check_ticket_detail.php' );
				?>
			</div>

			<!--div class="content_item receive_transfers hidden">
				<?php 
					//include( 'views/receive_transfers.php' );
				?>
			</div>


			<div class="content_item validate_transfers hidden">
				<?php 
					//include( 'views/validate_transfers.php' );
				?>
			</div-->

		</div>
		<div class="footer">
			<div class="row">
				<div class="col-2 txt_alg_left">
					<button 
						class="btn btn-light"
						onclick="redirect('home');"
					>
						<i class="icon-home-1"></i>
					</button>
				</div>

				<div class="col-8">
					<button
						type="button"
						class="btn btn-success form-control"
						onclick="finish_validation();"
						id="validation_finish_btn"
					>
						Finalizar Revisión
					</button>
				</div>

				<div class="col-2 txt_alg_right">
					<button class="btn btn-light">
						<i class="icon-off"></i>
					</button>
				</div>
			</div>
		</div>
	</div>
</body>
</html>

<script type="text/javascript">
	
var productsCatalogue = new Array();
  function getProductsCatalogue(){
//alert( 'getProductsCatalogue' );
    var url = "../../touch_desarrollo/ajax/productsCatalogue.php?fl_db=getProductsCatalogue";
    url += ( global_current_ticket > 0 ? "&sale_id=" + global_current_ticket : "" );
    var response = ajaxR( url ).split( '|' );
    if( response[0] != 'ok' ){
      alert( "Error : " + response );
      console.log( response );
      return false;
    }
    productsCatalogue = JSON.parse( response[1] );
console.log( productsCatalogue );     
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
//alert( 'buildProductsCatalogue' );
    var resp = `<button 
          class="btn rounded-circle hidde_seeker_response_btn"
          onclick="hidde_seeker_response();"
        >
          <i class="icon-up-big"></i>
        </button>`;
    var resp2 = `<button 
          class="btn rounded-circle hidde_seeker_response_btn"
          onclick="hidde_seeker_response();"
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
/*modificaciones oscar 2023 para el error del buscador en local storage*/
    //  alert( productsCatalogue[product].product_prices );
      resp += `<div class="seeker_item objetoLista" id="product_item_${productsCatalogue[product].product_id}"
        onclick="put_barcode( '#product_barcode_seeker', '${productsCatalogue[product].product_name}' );"//insertProductRow( ${productsCatalogue[product].product_id} );
      >${productsCatalogue[product].list_order} | ${productsCatalogue[product].product_name} | ${productsCatalogue[product].product_prices}
      </div>`;

      resp2 += `<div class="seeker_item objetoLista" id="product_item_${productsCatalogue[product].product_id}"
        onclick="put_barcode( '#product_barcode_seeker_pieces', '${productsCatalogue[product].product_name}' );"//seekTicketBarcode( 'enter', '#product_barcode_seeker_pieces', 'seekProductBarcode', null );insertProductRow( ${productsCatalogue[product].product_id} );
      >${productsCatalogue[product].list_order} | ${productsCatalogue[product].product_name} | ${productsCatalogue[product].product_prices}
      </div>`;
    }
    //alert( resp );
   // $( '#resBus' ).html( resp );
    document.getElementById( 'product_barcode_seeker_response' ).innerHTML = resp;
    document.getElementById( 'product_barcode_seeker_response' ).style.display = 'none';
    document.getElementById( 'product_barcode_seeker_pieces_response' ).innerHTML = resp2;
    document.getElementById( 'product_barcode_seeker_pieces_response' ).style.display = 'none';
   // if( localStorage.getItem('productsCatalogue') == null ){
      localStorage.setItem('productsCatalogue', JSON.stringify( productsCatalogue ));
    //}
   // $( '#resBus' ).css( 'display', 'block' );
  }

  function put_barcode( object, barcode ){
    $( '#product_barcode_seeker_response' ).css( 'display', 'none' );
    $( '#product_barcode_seeker_pieces_response' ).css( 'display', 'none' );
    barcode = barcode.split( '_' );
    $( object ).val( ( barcode[0] != '' ? barcode[0] : ( barcode[1] != '' ? barcode[1] : barcode[2] ) ) );
    setTimeout( function(){ seekTicketBarcode( 'enter', object, 'seekProductBarcode', null ) }, 100 );
  }
/**fin de modificaciones oscar 2023 para el error del buscador en local storage*/
  function seek_product(obj, e, response_object ){
    //alert( obj );
    if( e.keyCode == undefined && ( e.keyCode != 13 && e != 'intro' ) ){
      hidde_seeker_response();
      //alert();
      return false;
    }


    var txt_orig = $( obj ).val().trim().toUpperCase();
    var txt = txt_orig.split(' ');

    //var tmp_txt = txt.split( ' ' );
    if( txt.length == 4 ){
      txt_orig = '';
      for ( var i = 0; i < (txt.length - 1 ); i++ ) {
        txt_orig += ( txt_orig != '' ? ' ' : '' );
        txt_orig += txt[i];
      }
    }
    var size = productsCatalogue.length;
    var resp;
    if( ( $( obj ).val().length <= 2 || ( e.keyCode != 13 && e != 'intro' ) ) 
      && ( txt[1].includes( 'PQ' ) || txt[1].includes( 'CJ' ) ) ){//Oscar 2023 e.keyCode != 13 ( para que solo busque al dar enter )
      $( '.seeker_item' ).css( 'display', 'block' );
      $( response_object ).css( 'display', 'none' );
      return false;
    }
    var ref_comp = txt.length;
    var was_finded_by_barcode = null;
   // alert( txt );
    if( e.keyCode == 13 || e == 'intro' ){
  //busca por codigo de barras
      var tmp;
      for (var product in productsCatalogue){
  /*orde de lista
        if( productsCatalogue[product].list_order != '' 
          && productsCatalogue[product].list_order == txt_orig ){
              was_finded_by_barcode = tmp[i];
              break;
        }*/
  //codigos de pieza
        if( productsCatalogue[product].codigo_barras_pieza_1 != '' && productsCatalogue[product].codigo_barras_pieza_1 != null ){
          tmp = productsCatalogue[product].codigo_barras_pieza_1.split( ' __ ' );
          for( var i = 0; i < tmp.length; i++ ){
            if( tmp[i] == txt_orig ){
              was_finded_by_barcode = tmp[i];
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
              was_finded_by_barcode = tmp[i];
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
              was_finded_by_barcode = tmp[i];
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
              was_finded_by_barcode = tmp[i];
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
              was_finded_by_barcode = tmp[i];
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
              was_finded_by_barcode = tmp[i];
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
				  was_finded_by_barcode = tmp[i];
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
    //  alert( 'here' );//'#product_barcode_seeker'
    	seekTicketBarcode( 'enter', "#" + $( obj ).attr( 'id' ).trim(), 'seekProductBarcode', was_finded_by_barcode );
    //  $( '#product_item_' + productsCatalogue[was_finded_by_barcode].product_id ).click();
      //insertProductRow( productsCatalogue[was_finded_by_barcode].product_id );
    //  $( obj ).val( '' );
      //hidde_seeker_response( true );
      return false;
    }
   // alert();
  //seeker_response
  	var counter = 0;
    $( response_object ).children( 'div' ).each( function( index ){
      var txt_comp = $( this ).html().toUpperCase().trim();
      var matches  = 0;
      for (var j = 0;j < ref_comp; j++) {//comparacion de cadena de texto
        txt_comp.includes(txt[j]) ? matches ++ : null;
      }     
      $( this ).css('display', matches == ref_comp ? 'block' : 'none');
      counter = (  matches == ref_comp ? counter + 1 : counter );
    });
    $( response_object ).css( 'display', 'block' );
    $( '.icon-eye-5' ).addClass( 'icon-eye-off' );
    $( '.icon-eye-5' ).removeClass( 'icon-eye-5' );

    if( was_finded_by_barcode != null ){
      hidde_seeker_response( true );
    }
    if( counter == 0 && ( e.keyCode == 13 || e == 'intro' ) ){
      alert_scann( 'error' );
    	alert( "No se encontraron coincidencias!" );
      $( response_object ).css( 'display', 'none' );
    	return false;
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

	var audio_is_playing = false;
	function alert_scann( type ){
		if( audio_is_playing ){
			audio = null;
		}
		var audio = document.getElementById( type );
		
		audio_is_playing = true;
		audio.currentTime = 0;
		audio.playbackRate = 1;
		audio.play();
	}
	$( '#barcode_seeker' ).focus();

	if( localStorage.getItem('productsCatalogue') == null ){
      getProductsCatalogue();
    }else{
      getProductsCatalogueByLocalStorage();
    }
</script>