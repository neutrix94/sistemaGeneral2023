var current_triggers = null;
var insert_trigger_count = 0;
var update_trigger_count = 0;
var delete_trigger_count = 0;
	function getTableStructure( table ){
		current_triggers = new Array();
		insert_trigger_count = 0;
		update_trigger_count = 0;
		delete_trigger_count = 0;
		var url = "classes/db.php?fl_triggers=getTableStructure&table_name=" + table;
		var response = ajaxR( url );
		$( '#table_structure' ).html( response );
	//carga triggers actuales
		url = "classes/db.php?fl_triggers=getTableTriggers&table_name=" + table;
		response = ajaxR( url );//.split( '|~|' );
		current_triggers = JSON.parse( response );
		var resp = buildTriggers();
		//alert( resp );
		$( '#table_triggers' ).html( resp );
		$( '#header_table_name' ).html( table );
		//console.log( current_triggers );
		/*$( '#trigger_insert' ).val( response[0] );
		$( '#trigger_update' ).val( response[1] );
		$( '#trigger_detele' ).val( response[2] );*/
	}

	function makeTriggers( table ) {
		var fields = getValidTableFields( );
		var key_field = getFieldsKey();
		if( key_field == null ){
			alert( "Es necesario seleccionar un campo principal!" );
			return false;
		}
		var url = "classes/db.php?fl_triggers=buildTriggers&table_name=" + table;
		url += "&fields=" + fields + "&key_field=" + key_field;
		//alert( url );
		var response = ajaxR( url ).split( '|~|' );
		//alert( response[0] );
		$( '#trigger_insert_new' ).val( response[0] );
		$( '#trigger_update_new' ).val( response[1] );
		$( '#trigger_detele_new' ).val( response[2] );

	}

	function getValidTableFields(){	
		var valid_fields = new Array();
		$( '#fields_container' ).children( 'div' ).each( function(index){
			if( $( '#valid_row_' + index ).prop( 'checked' ) == true ){
				valid_fields.push( $( '#valid_row_' + index ).attr( 'value' ) );
			}
		});
		return valid_fields;
	}

	function getFieldsKey(){
		var key_field = null;
		$( '#fields_container' ).children( 'div' ).each( function(index){
			if( $( '#key_row_' + index ).prop( 'checked' ) == true ){
				key_field = $( '#key_row_' + index ).attr( 'value' );
			}
		});
		return key_field;
	}


	function minimize_and_expand( obj ){
		if( $( '.tables_list' ).hasClass( 'col-3' ) ){
			$( '.tables_list' ).removeClass( 'col-3' );
			$( '.tables_list' ).addClass( 'col-1' );
			$( '#table_structure' ).removeClass( 'col-3' );
			$( '#table_structure' ).addClass( 'col-1' );
			$( '#table_triggers' ).removeClass( 'col-6' );
			$( '#table_triggers' ).addClass( 'col-10' );
			$( obj ).children( 'i' ).removeClass('icon-left-big' );
			$( obj ).children( 'i' ).addClass('icon-right-big'  );//icon-resize-full
		}else{
			$( '.tables_list' ).removeClass( 'col-1' );
			$( '.tables_list' ).addClass( 'col-3' );
			$( '#table_structure' ).removeClass( 'col-1' );
			$( '#table_structure' ).addClass( 'col-3' );
			$( '#table_triggers' ).removeClass( 'col-10' );
			$( '#table_triggers' ).addClass( 'col-6' );
			$( obj ).children( 'i' ).removeClass('icon-right-big' );
			$( obj ).children( 'i' ).addClass('icon-left-big'  );

		}

	}







	//llamadas asincronas
	function ajaxR(url){
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