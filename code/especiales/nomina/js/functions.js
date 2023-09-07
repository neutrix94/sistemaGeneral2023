
	var editing = 0, obj_edit = 0;
	var tmp_ceil = '<input type="$ELEMENT_TYPE" onblur="desedit_ceil();" id="tmp_ceil" onkeydown="validate_key(this, event);">';
	var new_row = '<tr>'
			+ '<td style="display : none;">0</td>'
			+ '<td>$EMPLOYE_NAME</td>'
			+ '<td onclick="edit_row( this, \'date\' );">$NEW_DATE</td>'
			+ '<td onclick="edit_row( this, \'time\' );">$START_HOUR</td>'
			+ '<td onclick="edit_row( this, \'time\' );">$END_HOUR</td>'
			+ '<td class="checks">$HOURS_CALC</td>'
			+ '<td class="checks"><input type="checkbox"> Retardo</td>'
			+ '<td class="checks"><input type="checkbox"> Falta</td>'
			+ '<td class="checks"><input type="checkbox"> Descanso</td>'
			+ '<td class="checks"><input type="checkbox"> Permiso</td>'
		+ '<tr>';

	function user_search( obj ){
		var txt = $( obj ).val();
		if ( txt.length <= 2 ){
			$( '.users_results' ).html( '' );
			$( '.users_results' ).css( 'display', 'none');
			return false;
		}
		$.ajax({
			type : 'post',
			url : 'ajax/search_user.php',
			cache : false,
			data : { sucursal_id : $('#sucursal').val(), search : txt},
			success : function ( dat ){
				console.log( dat );
				var tmp = dat.split('|');
				if( tmp[0] == 'ok' ){
					$( '.users_results' ).html( build_options( tmp ) );
					$( '.users_results' ).css( 'display', 'block' );
				}
			}
		});
	}

	function build_options( arr ){
		var resp = '';
		for ( var i = 1; i < arr.length; i++ ) {
			var tmp = arr[i].split('~');
			resp += '<p class="resultado"'
				+' onclick="select_specific_user(' + tmp[0] + ', \'' + tmp[1] + '\')">' 
					+ tmp[1]
				+ '</p>';
		}
		return resp;
	}

	function select_specific_user( id, name ){
		$( '#nomina_list tbody' ).empty();
		$( '#search' ).val( name );
		$( '#search' ).attr( 'disabled', true );
		$( '.users_results' ).html( '' );
		$( '.users_results' ).css( 'display', 'none');
	//carga registros de nomina
		$('#button_generate').attr( 'onclick', 'getData(' + id + ')' );
	//agrega el empleado a la columna de empleado
		$( '#employee' )
			.empty()
			.append('<option value="' + id + '">' + name + '</option>');
	}

	function getData( id = null ){
		$( '#nomina_list tbody' ).empty();
		if( validate_dates() != 'ok' ){
			var tmp = validate_dates().split('|');
			alert( tmp[1] );
			$( '#' + tmp[0] ).select();
			return true;
		}

		$.ajax({
			type : 'post',
			url : 'ajax/data.php',
			cache : false,
			data : { 
					sucursal_id : $('#sucursal').val(), 
					user_id : id,
					initial_date : $( '#start_date' ).val(),
					final_date : $( '#end_date' ).val()
				},
			success : function ( dat ){
				console.log( dat );
				$( '#nomina_list tbody' ).append(dat);
			}
		});
	}
	function validate_dates(){
		if( $( '#start_date' ).val() == '' ){
			return 'start_date|Seleccione una fecha inicial';
		}
		if( $( '#end_date' ).val() == '' ){
			return 'end_date|Seleccione una fecha final';
		}
		return 'ok';

	}

	function reset_search(){
		$( '#search' ).attr( 'disabled', false );
		$( '#search' ).val( '' );
		$( '#button_generate').attr( 'onclick', 'getData()' );
		$( '#nomina_list tbody' ).empty();
		$( '#employee' ).empty();
	}

	function change_sucursal( obj ){
		var suc = $( obj ).val();
		if( suc != 0){
			$( '.accordion-item' ).css('display', 'none');
			$( '#suc_' + suc ).css('display', '');
		} else {
			$( '.accordion-item' ).css('display', '');
		}
	}

	function validate_key( obj, e ){

		e.preventDefault();
		var key = e.keyCode;
		var cord, x, y;
		cord = $( obj_edit ).attr( 'id' ).split( '_' );
		x = parseInt( cord[1] );
		y = parseInt( cord[0] );
		//alert(x +','+ y);
		switch ( key ){
			case 37 ://izquierda
				if ( x > 1 ) {
					desedit_ceil();
					$( '#' + y + '_' + parseInt( x - 1 ) ).click();
				}
			break;
			case 38 ://arriba
				if ( y > 0 ) {
					desedit_ceil();
					$( '#' + parseInt( y - 1 ) + '_' + x ).click()
				}
				//(y == 0 ? null : $( '#' + parseInt( y - 1 ) + '_' + x ).click());
			break;
			case 39 ://derecha
				if ( x < 3 ) {
					desedit_ceil();
					$( '#' + y + '_' + parseInt( x + 1 ) ).click();
				}
			break;
			case 40 ://abajo
				if ( y > parseInt($( '#total_details_rows' ).val().trim()) ) {
					desedit_ceil();
					$( '#' + parseInt( y + 1 ) + '_' + x ).click()
				}
				//(y == 3 ? null : $( '#' + parseInt( y + 1 ) + '_' + x ).click());
			default : 
				return true;
			break;
		}
	}	

	function edit_row( obj, type ){
		if( obj_edit == 0 ){
			var tmp_val = text_to_date( $( obj ).html().trim() );
			var tmp = tmp_ceil.replace('$ELEMENT_TYPE', type);
			$(obj).html( tmp );
			obj_edit = obj;
			$( '#tmp_ceil' ).val( tmp_val );
			$( '#tmp_ceil' ).select();
			return true;
		}
		return false;
	}

	function desedit_ceil(){
		var tmp_val = $( '#tmp_ceil' ).val().trim();
		//$( obj_edit ).html( tmp_val );
		$( obj_edit ).html( date_to_text( tmp_val ) );
		obj_edit = 0;
	}

//agregar registro de asistencia
	function add_row(){
		var user_id, new_date, new_start_hour, new_final_hour, sucursal_id;
		var tmp;
		user_id = $( '#employee' ).val();
		if( user_id == 0 ){ 
			alert("Primero elija un empleado!");
			$( '#employee' ).focus(); 
			return false; 
		}
		tmp = new_row.replace('$EMPLOYE_NAME', $('#employee option:selected').text());
		
		new_date = $( '#new_date' ).val();
		if( new_date == '' ){ 
			alert("Elija una fecha válida!");
			$( '#new_date' ).focus();
			return false; 
		}
		tmp = tmp.replace('$NEW_DATE', new_date);

		new_start_hour = $( '#new_initial_date' ).val();
		if( new_start_hour == '' ){ 
			alert("Elija una hora de entrada válida!");
			$( '#new_initial_date' ).focus();
			return false; 
		}
		new_start_hour += ':01';
		tmp = tmp.replace('$START_HOUR', new_start_hour);

		new_final_hour = $( '#new_final_date' ).val();
		if( new_final_hour == '' ){ 
			alert("Elija una hora de salida válida!");
			$( '#new_final_date' ).focus();
			return false; 
		}
		if( new_date < $( '#start_date' ).val().trim() ||  new_date > $( '#end_date' ).val().trim()){
			alert("La fecha no puede ser mayor ni menor al rango de fechas seleccionados!");
			$( '#new_date' ).select();
			return false;
		}
		if( new_start_hour >= new_final_hour ){
			alert("La hora de entrada no puede ser mayor ni igual a la hora de salida!");
			$( '#new_initial_date' ).focus();
			return false;
		}
		new_final_hour += ':01';
		tmp = tmp.replace('$END_HOUR', new_final_hour);
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/insertAssists.php',
			cache : false,
			data : {
				id : user_id,
				date : new_date,
				start : new_start_hour,
				end : new_final_hour
			},
			success : function ( dat ){
				if( dat != 'ok' ){
					alert( dat );
					return false;
				}
				getData( user_id );
			}
		});
	/*agrega la fila
		$( '#nomina_list tbody' ).append( tmp );*/
	}

	function validate_not_repeat(){

	}