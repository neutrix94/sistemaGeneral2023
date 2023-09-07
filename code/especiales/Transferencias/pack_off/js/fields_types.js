/*campos disponibles*/
	var gft_field_active = 0, x, y;
	var gft_data_types = {
		/*tipo, subtipo, */
		'text' : ['<input type="text" $class $id $value $onblur $events>' ],
		'number' : ['<input type="number" $class $id $value $onblur $events>'],
		'date' : ['<input type="date" $class $id $value $onblur $events>'],
		'time' : ['input', 'time'],
		'date_time' : ['input'],
		'select' : ['select', '']
	}
/*creacion de la celda editable*/
	function gft_build_field_editable( obj ){
		if( gft_field_active != 0 ){
			gft_desedit_field();
			gft_build_field_editable( obj );
			return false;
		}
		gft_field_active = obj;
		
		var complete_id = $( obj ).attr( 'id' ).split('_');
		x = parseInt( complete_id[0] );
		y = parseInt( complete_id[1] );
		$( obj ).html( gft_build_field( $( obj ).attr('gft_type'), $( obj ).html().trim() ) );
		$( '#gft_tmp_row' ).select();
	}

	function gft_build_field( data_type, current_value ){
		var resp = gft_data_types[data_type].toString();
		resp = resp.replace('$class', 'class="gft_tmp_row form-control"');
		resp = resp.replace('$id', 'id="gft_tmp_row"');
		resp = resp.replace('$value', 'value="' + current_value + '"');
		resp = resp.replace('$onblur', 'onblur="gft_desedit_field()"');
		resp = resp.replace('$events', 'onkeyup="gtf_validate_event( event, this )"');
		return resp;
	}

	function gft_desedit_field(){
		$( gft_field_active ).html( $( '.gft_tmp_row' ).val() );
		gft_field_active = 0;
	}
/*recorrido con teclas*/
	function gft_row_focus( obj ){
		$( '.active_row' ).removeClass( 'active_row' );
		$( obj ).addClass( 'active_row' );
	}
	function gtf_validate_event( e, obj ){
		var keyboard_key = e.keyCode;
		var limit_find = 1;
		switch ( keyboard_key ){
			
			case 37://left
				while ( limit_find != 0 ) {
					x --;
					if( $( '#' + x + '_' + y ).parent().hasClass( 'no_visible' ) ){
						alert();
			    	}else{
			    		$( '#' + x + '_' + y ).parent().focus();
						$( '#' + x + '_' + y ).click();
						limit_find = 0;
				    }
				} 
			break;
			
			case 38://up
				while ( limit_find != 0 ) {
					y --;
					if( $( '#' + x + '_' + y ).parent().hasClass( 'no_visible' ) ){
			    	}else{
			    		$( '#' + x + '_' + y ).parent().focus();
						$( '#' + x + '_' + y ).click();
						limit_find = 0;
				    }
				} 
			break;
			
			case 39://right
				while ( limit_find != 0 ) {
					x ++;
					if( $( '#' + x + '_' + y ).parent().hasClass( 'no_visible' ) ){
			    	}else{
			    		$( '#' + x + '_' + y ).parent().focus();
						$( '#' + x + '_' + y ).click();
						limit_find = 0;
				    }
				}
			break;

			case 13://down
			case 40://down
				while ( limit_find != 0 ) {
					y ++;
					if( $( '#' + x + '_' + y ).parent().hasClass( 'no_visible' ) ){
			    	}else{
			    		$( '#' + x + '_' + y ).parent().focus();
						$( '#' + x + '_' + y ).click();
						limit_find = 0;
				    }
				}
			break;
		}
	}

	function gft_save_data( dataGrid_tbody ){
		var data = '';
		$( '#table_bodys_ tbody tr').each(function (index) {
			if ( $( this ).attr( 'is_parent' ) != 1 ){
				data += (index > 0 ? '|' : ''); 
				$(this).children("td").each(function (index2) {
//					alert( $( this ).html() );	
				data += $( this ).html() + '~';
				});
			}
		});
		console.log(data);

	}
	/*var data_type = 'text';
	console.log( gft_build_field( data_type, 0 ) );*/