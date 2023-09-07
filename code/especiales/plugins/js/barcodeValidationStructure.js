function validateBarcodeStructure( barcode ){
//alert(barcode);
		barcode = barcode.replace( '  ', ' ' );//reemplaza el dobles espacio
		barcode = barcode.toUpperCase();
		var tmp_txt = barcode.split( ' ' );
		if( tmp_txt.length == 4 ){
			txt = '';
			txt += tmp_txt[0] + " ";
		//es caja
			if( tmp_txt[1].includes( 'CJ' )  ){
				if( tmp_txt[3].length == 3 ){//solo numeros
					if( isNaN( tmp_txt[3] ) ){
					//	alert( "El codigo de barras no cumple con la estructura, verifica y vuleve a intentar" );
						return false;	
					}
				}else if( tmp_txt[3].length == 4 ){
					if( ! validateUniqueBarcodeWithPrefix( tmp_txt[3], 'box' ) ){
						return false;
					}
					/*if( !isNaN( tmp_txt[3] ) ){
						//alert( "El codigo de barras no cumple con la estructura, verifica y vuleve a intentar" );
						return false;	
					}	*/
				}else{
					//alert( "El codigo de barras no cumple con la estructura, verifica y vuleve a intentar" );
					return false;	
				}
			}else if( tmp_txt[1].includes( 'PQ' )  ){
				if( tmp_txt[3].length == 4 ){//solo numeros
					if( isNaN( tmp_txt[3] ) ){
						//alert( "El codigo de barras no cumple con la estructura, verifica y vuleve a intentar" );
						return false;	
					}
				}else if( tmp_txt[3].length == 5 ){
					
					if( ! validateUniqueBarcodeWithPrefix( tmp_txt[3], 'box' ) ){
						return false;
					}
					/*if( !isNaN( tmp_txt[3] ) ){
					//	alert( "El codigo de barras no cumple con la estructura, verifica y vuleve a intentar" );
						return false;	
					}*/	
				}else{
					//alert( "El codigo de barras no cumple con la estructura, verifica y vuleve a intentar" );
					return false;	
				}
			}
		}
		return true;
	}

	function validateUniqueBarcodeWithPrefix( text, type ){
		var resp = true;
		switch( type ){
			case 'box':
				var numbers = text[1] + text[2] + text[3];
				var letter = text[0];
				if( !isNaN( letter ) ){

					resp = false;
					return false
				}

				if( isNaN( numbers ) ){
					resp = false;
					return false
				}
			break;

			case 'pack':
				var numbers = text[1] + text[2] + text[3] + text[4];
				var letter = text[0];
				if( !isNaN( letter ) ){

					resp = false;
					return false
				}

				if( isNaN( numbers ) ){
					resp = false;
					return false
				}

			break;
		}
		return resp;
	}