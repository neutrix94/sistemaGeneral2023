
{literal}
	<script type="text/javascript">
		function make_responsive_grids(){
		//alert();
			$( '#productosMovimiento' ).css( 'width', '100%' );
			$( '#productosMovimiento' ).addClass( 'table' );
			$( '#celda_productosMovimiento_Cabecera' ).addClass( 'table' );
			$( '#H_productosMovimiento4' ).css( 'width', '20%' );
			$( '#H_productosMovimiento4' ).children( 'input' ).css( 'width', '100%' );
			$( '#H_productosMovimiento4' ).children( 'input' ).css( 'font-size', '130%' );
			$( '#H_productosMovimiento4' ).children( 'input' ).css( 'height', '30px' );

			$( '#H_productosMovimiento5' ).css( 'width', '40%' );
			$( '#H_productosMovimiento5' ).children( 'input' ).css( 'width', '100%' );
			$( '#H_productosMovimiento5' ).children( 'input' ).css( 'font-size', '130%' );
			$( '#H_productosMovimiento5' ).children( 'input' ).css( 'height', '30px' );

			$( '#H_productosMovimiento6' ).css( 'width', '20%' );
			$( '#H_productosMovimiento6' ).children( 'input' ).css( 'width', '100%' );
			$( '#H_productosMovimiento6' ).children( 'input' ).css( 'font-size', '130%' );
			$( '#H_productosMovimiento6' ).children( 'input' ).css( 'height', '30px' );

			$( '#H_productosMovimiento8' ).css( 'width', '20%' );
			$( '#H_productosMovimiento8' ).children( 'input' ).css( 'width', '100%' );
			//$( '#H_productosMovimiento8' ).children( 'input' ).css( 'font-size', '130%' );
			//$( '#H_productosMovimiento8' ).children( 'input' ).css( 'height', '30px' );


			$( '.buttonHeader' ).css( 'font-size', '130%' );
			$( '.buttonHeader' ).css( 'height', '30px' );

			$( '#Body_productosMovimiento tr' ).each( function( index1 ){//.children( 'tr' )
			//alert( index1 );
				$( '#productosMovimiento_Fila' + index1 ).children( 'td' ).each( function( index ){
					$( this ).css( 'font-size', '130%' );
					//productosMovimiento_Fila0
					if( index <= 4 && index > 0 ){
						$( this ).css( 'display', 'none' );
					}else if( index == 5 ){
						$( this ).css( 'width', '20%' );
					}else if( index == 6 ){
						$( this ).css( 'width', '40%' );
					}else if( index == 7 ){
						$( this ).css( 'width', '20%' );
					}else if( index == 8 ){
						$( this ).css( 'width', '0px' );
					}else if( index == 9 ){
						$( this ).css( 'width', '20%' );
					}
				});
			});

			$( '#Body_productosMovimiento' ).addClass( 'table' );
			

		}

var seeker_by_scan = false;
		function seekProductProviderByBarcode( e ){
			if( e.keyCode != 13 && e != 'intro' ){
				return false;
			}
			$( '.emergent_content' ).html( '<h3 class="text-center text-primary"><br><br>Validando...</h3>' );
			$( '.emergent' ).css( 'display', 'block' );
			var url = "../ajax/seekProductProviderByBarcode.php?barcode=" + $( '#barcode_seeker' ).val().trim();
			var resp = ajaxR( url ).split( '|' );
			if( resp[0] != 'ok' ){
				$( '.emergent_content' ).html( '' );
				$( '.emergent' ).css( 'display', 'none' );
				alert( resp );
				return false;
			}
			var aux = JSON.parse( resp[1] );
			//console.log( aux.view );
			//var exists ;
			seeker_by_scan == true;
			insertaBuscador( 0, aux.view, 9, 'productosMovimiento');
			setTimeout( function(){
				//validarEv( 'click', 0 );//$( '#img_add_0' ).click();
				var tmp = document.activeElement.id;
				if( tmp == 'cantidad_0' ){
					validarEv( 'click', 0 );
					//$( '#img_add_0' ).click();
					alert_scann( 'ok' );
					$( '#b_g_0' ).val( '' );
					$( '#barcode_seeker' ).val( '' );
					$( '#barcode_seeker' ).focus();
				}else{
					var tmp_value = parseFloat( $( `#${tmp}` ).val() ) + parseFloat(1);
					$( `#${tmp}` ).val( tmp_value );
					alert_scann( 'ok' );
					$( '#b_g_0' ).val( '' );
					$( '#barcode_seeker' ).val( '' );
					$( '#barcode_seeker' ).focus();
				}
				$( '.emergent_content' ).html( '' );
				$( '.emergent' ).css( 'display', 'none' );
			}, 300);
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
	</script>
{/literal}