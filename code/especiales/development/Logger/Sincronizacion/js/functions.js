 function show_or_hidde( counter ){
        if( $( '#body_' + counter ).hasClass( "hidden" ) ){
            $( '#body_' + counter ).removeClass( "hidden" );
        }else{
            $( '#body_' + counter ).addClass( "hidden" );
        }
    }
    function close_emergent(){
        $( '.emergent_content' ).html( '' );
        $( '.emergent' ).css( 'display', 'none' );
    }
    function show_detail( obj ){
        var button = `<div class="text-end">
            <button
                class="btn btn-danger"
                onclick="close_emergent();"
            >
                X
            </button>
        </div>`;
        var val = $( obj ).val().trim();
        val = val.replaceAll( "	            ", "" );
        val = val.replaceAll( "		       		", "" );
        val = val.replaceAll( "			", "" );
        val = val.replaceAll( "		         ", "" );
        val = val.replaceAll( "                   ", "" );
        $( '.emergent_content' ).html( `${button}<textarea class="textarea_full">${val}</textarea>` );
        $( '.emergent' ).css( 'display', 'block' );
    }

    function filtra_por_tabla(obj){
        table_name = $( '#table_filter' ).val().trim();
        limit = $( '#limite_input' ).val();
        //setTimeout( function(){
            $( '.emergent_content' ).html( "Cargando..." );
            $( '.emergent' ).css( "display", "block" );
        //}, 500);
		$.ajax({
			type : 'post',
			url : 'ajax/LoggerViewer.php',
			data : { log_flag : 'filter_by_table', table : table_name, rows_limit : limit },
			success : function( dat ){
                $( '.content' ).html(dat);
                setTimeout( function(){
                    close_emergent();
                }, 500);
			}
		});
    }
    function filtra_por_folio(obj){
        folio = $( obj ).val().trim();
        if( folio.length <= 0 ){
            alert( "El folio no puede ir vacio!" );
            $( obj ).focus();
            $( obj ).select();
        }
        setTimeout( function(){
            $( '.emergent_content' ).html( "Cargando..." );
            $( '.emergent' ).css( "display", "block" );
        }, 500);
		$.ajax({
			type : 'post',
			url : 'ajax/LoggerViewer.php',
			data : { log_flag : 'filter_by_folio', folio : folio },
			success : function( dat ){
                $( '.content' ).html(dat);
                setTimeout( function(){
                    close_emergent();
                }, 500);
			}
		});
    }

