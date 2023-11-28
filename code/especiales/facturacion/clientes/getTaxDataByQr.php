<!--html-->
	<!--script type="text/javascript" src="../js/jquery-1.10.2.min.js"></script-->
	<script type="text/javascript" src="cleaner.js"></script><head>
<!--/head-->
<!--body>
		<div class="row">
			<div class="col-1">
			</div>
			<div class="col-10">
				<div class="input-group">
					<input type="text" class="form-control" id="url_value" placeholder="Ingresa la url">
					<button type="button" class="btn btn-primary" id="get_data_btn" onclick="getDataSat();">
						Consultar
					</button>
				</div>
			</div>
		</div>
		<div id="response_container">
			
		</div>


</body-->
</html>
<script type="text/javascript">
	var global_popout = 0;
	function getDataSat( url_ = null ){
			//$( '#accordion' ).html( '' );
			var url = "";
			if( url_ == '' || url_ == null ){
				url = $( '#rfc_seeker' ).val().trim();//'https://siat.sat.gob.mx/app/qr/faces/pages/mobile/validadorqr.jsf?D1=10&D2=1&D3=16050344931_HELC720716ME6';
			}else{
				url = url_;
			}
			if( url.length <= 0 ){
				alert( "Es necesario ingresar una url para continuar!" );
				$( '#rfc_seeker' ).focus();
				return false;
			}
		//	url = 'https://siat.sat.gob.mx/app/qr/faces/pages/mobile/validadorqr.jsf?D1=10&D2=1&D3=20090196414_CLB200805GW0';
			var response = ajaxR( url );
			//alert( response );
			processData( response );
			$( '#fiscal_cedule' ).val( url );
			$( '#rfc_seeker' ).val('');
	}

	function processData( data ){
		data = clean_data( data );
	//	alert( data );
		ajaxR_write(data);
		//openProceesData( data );
	}

	function getIdentificationData(){
		var curpData, 
			nameData, 
			lastName1Data, 
			lastName2Data, 
			dateOfBirthData, 
			DateOfIncorporationDateData,
			situationData,
			lastSituationChangeData;
		var globalData = new Array();
		$( '#ubicacionForm:j_idt11:0:j_idt12:j_idt16_data tr' ).each( function ( index ){
			$( this ).children( 'tr' ).each( function(){
				globalData.push( $( this ).html().trim() );
			});
		} );
		console.log( globalData );
		alert( globalData );
	}

	function openProceesData( data ){
		var url_ = "popout.php?file=" + data ;
		global_popout = window.open( url_, 'Template', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=10,height=10,left=0,top=0');
		
		//global_popout.getElementById( 'pageContent' ).innerHTML = '<button type="button">Here</button>' ;
	}
/**/
	function build_content( info, rfc ){
		var name = "";
		isMoral = false;
		var costumer_regimes = new Array();
		$( '#rfc_input' ).val( rfc );//rfc
		$( '#rfc_input' ).attr( 'readonly', true );
		//tipo de persona
		if( rfc.length == 12 ){
			$( '#person_type_combo' ).val( 3 );
			$( '#person_type_combo' ).attr( 'disabled', true );
			isMoral = true;
		}else if( rfc.length == 13 ){
			$( '#person_type_combo' ).val( 2 );
			$( '#person_type_combo' ).attr( 'disabled', true );
		}
		//var resp = `<br>`;
		for ( var i = 0; i < info.length ; i++ ) {
			if( info[i].length > 0 ){
				//resp += `<table class="table table-striped">`;
				var info_final = info[i];
				for ( var j = 0; j < info_final.length ; j++ ) {
				//	resp += `<tr><td>${info_final[j]}</td>`;
					j ++;
				//	resp += `<td>${info_final[j]}</td></tr>`;
					//if( j == 1 ){
					//	alert( info_final[j] );
				/*nombre razon social*/
					if( i == 0 && isMoral ){//alert(1);
						name = info_final[1];
						//name = name.replaceAll( '"', '\"' );
						name = name.replace( '&amp;', '&' );
						name = name.replace( '&AMP;', '&' );
						//	alert( name );
						$( '#name_input' ).val( name );
						$( '#name_input' ).attr( 'readonly', true );
					}else{//alert( 2 );
						if( i == 0 && j == 3 ){
							name += info_final[j] + " ";//build_content( globalData );
						}
						if( i == 0 && j == 5 ){
							name += info_final[j] + " ";//build_content( globalData );
						}
						if( i == 0 && j == 7 ){
							name += info_final[j];//build_content( globalData );
							//name = name.replaceAll( '"', '\"' );
							name = name.replace( '&amp;', '&' );
							name = name.replaceAll( '&AMP;', '&' );
							//alert( name );
							$( '#name_input' ).val( name );
							$( '#name_input' ).attr( 'readonly', true );
						}
					}
				/*ESTADO*/
					if( i == 2 && j == 1 ){
						$( '#state_input' ).val( info_final[j] );
						$( '#state_input' ).attr( 'disabled', true );
					}
				/*MUNICIPIO*/
					if( i == 2 && j == 3 ){
						$( '#municipality_input' ).val( info_final[j] );
						$( '#municipality_input' ).attr( 'readonly', true );
					}
				/*COLONIA*/
					if( i == 2 && j == 5 ){
						$( '#cologne_input' ).val( info_final[j] );
						$( '#cologne_input' ).attr( 'readonly', true );
					}
				/*CALLE*/
					if( i == 2 && j == 9 ){
						$( '#street_name_input' ).val( info_final[j] );
						$( '#street_name_input' ).attr( 'readonly', true );
					}
				/*NO INT*/
					if( i == 2 && j == 11 ){
						$( '#internal_number_input' ).val( info_final[j] );
						$( '#internal_number_input' ).attr( 'readonly', true );
					}
				/*NO EXT*/
					if( i == 2 && j == 13 ){
						$( '#external_number_input' ).val( info_final[j] );
						$( '#external_number_input' ).attr( 'readonly', true );
					}
				/*CP*/
					if( i == 2 && j == 15 ){
					//	alert( info_final[j] );
						$( '#postal_code_input' ).val( info_final[j] );
						$( '#postal_code_input' ).attr( 'disabled', true );
					}
					if( i == 4 ){
						if( j % 2 != 0 && j < (info_final.length - 2) && info_final[j].includes( '-' ) == false ){
							costumer_regimes.push( info_final[j] );
						}

					//	$( '#postal_code_input' ).val( info_final[j] );
					//	$( '#postal_code_input' ).attr( 'disabled', true );
					}
					//}
				}
				//resp += `</table><br>`;
			}
		}
		//alert( 'here' );
		var matches = 0;
		$( "#regime_input" ).children( 'option' ).each( function( index ){
			if( index > 0 ){
				if( ! costumer_regimes.includes( $( this ).text() ) ){
					$( this ).css( 'display', 'none' );
				}else{
					matches ++;
					if( costumer_regimes.length == 2 ){
						$( this ).attr( 'selected', true );
						//$( "#regime_input" ).attr( 'disabled', true );
					}
				}
			}
			//alert( $( this ).text() );
		});
		if( matches == 0 ){
			$( "#regime_input" ).children( 'option' ).each( function( index ){
						$( this ).css( 'display', 'block' );
				//alert( $( this ).text() );
			});
		}
		//alert( costumer_regimes.length );
		$( '#country_combo' ).attr( 'disabled', true );
		$( '#fiscal_cedule' ).attr( 'disabled', true );
		global_popout.close();
		//console.log( costumer_regimes );
		//$( '#response_container' ).html( resp );
	}
/**/


	//llamadas asincronas

	function ajaxR_write(data){
		$.ajax({
			type : 'post',
			data : { data : data },
			url : 'saveContent.php',
			cache : false,
			success : function(dat){
			//	alert(dat);
				openProceesData( dat );
			} 
		});
	}
	function ajaxR(url){
	/*	$.ajax({
			type : 'post',
			data : { data : data },
			cache : false,
			success : function(){

			} 
		});*/

		if(window.ActiveXObject)
		{		
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



</script>

		<script type="text/javascript">
			//getData();
		</script>