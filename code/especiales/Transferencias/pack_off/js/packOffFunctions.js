	var products_providers = new Array();	
	window.onload = function( e ){
		$( '#transfer_folio' ).focus();
		$( '#transfer_folio' ).select();
	//carga datos de proveedor producto
		$.ajax({
			type : 'post',
			url : 'ajax/TransferDB.php',
			data : { fl : 'getProductsProviders' },
			success : function ( dat ){
				products_providers = dat;
				/*gft_build_options( products_providers );
				for (x of products_providers) {
				  console.log(x);
				}
				//console.log( 'products_providers : ', products_providers );*/
			}
		});
	}

	function search_transfer( obj, e ){
		var tca = e.keyCode;
		if( tca != 13 && tca != 39 && tca != 40 && tca != undefined ){
			$( '.resBusc.transfer' ).css( 'display', 'none' );
			return false;
		}
		$.ajax({
			type : 'post',
			url : 'ajax/TransferDB.php',
			data : { fl : 'searchTransfer', txt : $( obj ).val() },
			success : function ( dat ){
				$( '.resBusc.transfer' ).html( dat );
				$( '.resBusc.transfer' ).css( 'display', 'block' );
			}
		});
	}
	function get_transfer( transfer_id, obj ){
		$( '#transfer_folio' ).val( $( obj ).html().trim() );
		$( '#transfer_folio' ).attr( 'readonly', 'true' );
		$( '#transfer_id' ).val( transfer_id );
		$.ajax({
			type : 'post',
			url : 'ajax/TransferDB.php',
			data : { fl : 'getTransfer', transfer_id : transfer_id },
			success : function ( dat ){
				//alert(dat);
				$( '#btn-search-transfer' ).css( 'display', 'none' );
				$( '#btn-refresh-transfer' ).removeClass( 'no_visible' );
				$( '.resBusc.transfer' ).html( '' );
				$( '.resBusc.transfer' ).css( 'display', 'none' );
				$( '.table_body' ).html( dat );
				$( '#transfer_detail_seeker' ).removeAttr( 'readonly' );
				$( '#transfer_detail_seeker' ).focus();
			}
		});
	}

	function extend_accordion( obj, product_id, action ){
		if( action == 1 ){
			$( '.' + product_id ).removeClass( 'no_visible' );
			$( obj ).attr('onclick', 'extend_accordion( this, ' 
				+ product_id + ', 0)');
			$( obj ).html('<i class="icon-up-open"></i>');
		}else{
			$( '.' + product_id ).addClass( 'no_visible' );
			$( obj ).attr('onclick', 'extend_accordion( this, ' 
				+ product_id + ', 1)');
			$( obj ).html('<i class="icon-down-open"></i>');
		}
	}
	function set_transfer_detail( transfer_id, provider_product_id ){

	}

	function remove_transfer_detail( transfer_id, provider_product_id ){

	}