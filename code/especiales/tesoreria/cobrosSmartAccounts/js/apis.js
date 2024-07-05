
	function sendTerminalPetition( counter, terminal_id ){
        var log_status = $( "#log_status" ).val();		
		var sale_id = $( '#id_venta' ).val();
		var amount = $( `#t${counter}` ).val().replaceAll( ',', '' );
		var sale_folio = $( `#buscador` ).val().trim();
		if( sale_id == 0 || sale_folio == '' ){
			alert( "Es necesario elegir una nota de venta para continuar!" );
			$( `#buscador` ).select();
			return false;
		}
		if( amount <= 0 ){
			alert( "El monto debe de ser mayor a cero!" );
			$( `#t${counter}` ).focus();
			return false;
		}
    
        if( $( '#tarjeta_' + counter ).val() == '--Seleccionar--' ){/*Implementacion Oscar 2024-06-25 para no permitir envio de transaccion si no se selecciona terminal valida */
            alert( "Debes seleccionar una terminal valida para continuar!" );
            $( '#tarjeta_' + counter ).focus();
            return false;
        }/*Fin de cambio Oscar 2024-06-25*/
		var url = "ajax/db.php?fl=sendPaymentPetition&amount=" + amount;
		url += "&terminal_id=" + $( '#tarjeta_' + counter ).val();
		url += "&sale_id=" + sale_id;
		url += "&sale_folio=" + sale_folio;
        url += "&counter=" + counter;
		url += "&session_id=" + $( '#session_id' ).val();
        if( respuesta.monto_saldo_a_favor > parseFloat( respuesta.total_real ) ){
            url += "&pago_por_saldo_a_favor=" + parseFloat( respuesta.total_real );
        }
        url += "&id_venta_origen=" + $( "#id_venta_origen" ).val();
        
        if( respuesta.id_devolucion != null && respuesta.id_devolucion != 'null' && respuesta.id_devolucion != 0  ){
			url += "&id_devolucion_relacionada=" + respuesta.id_devolucion;
		}
        url += "&log_status=" + log_status;
       // alert( url );return false;
		//url += "&user_id=" + user_id;
		//alert( url );
		var resp = ajaxR( url );
		//console.log( resp );
		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
	} 

	function rePrintByOrderId( transaction_id ){
        var sale_folio = $( `#buscador` ).val().trim();
        var url = "ajax/db.php?fl=rePrintByOrderId&transaction_id=" + transaction_id;
        url += "&sale_folio=" + sale_folio;
        url += "&session_id=" + $( '#session_id' ).val();
		var resp = ajaxR( url );
		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
	}

    function rePrintByOrderIdManual(){
        var orderId = $( '#reverse_input' ).val();
        if( orderId == '' ){
            alert( "Debes ingresar un folio valido para continuar!" );
            $( '#reverse_input' ).focus();
            return false;
        }
        var sale_folio = $( `#buscador` ).val().trim();
        var url = "ajax/db.php?fl=rePrintByOrderIdManual&sale_folio=" + sale_folio;
        url += "&session_id=" + $( '#session_id' ).val();
        url += "&orderId=" + orderId;
        var resp = ajaxR( url );
        $( '.emergent_content' ).html( resp );
        $( '.emergent' ).css( 'display', 'block' );
    }

	function cancelByOrderId( transaction_id ){
		var url = "ajax/db.php?fl=cancelByOrderId&transaction_id=" + transaction_id;
		var resp = ajaxR( url );
		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
	}

    function Mascara(mascara, valor){   
        //validamos que realmente haya una mascara a evaluar
        if(mascara == 'null' || mascara == null || mascara.length <= 0)
            return valor;   
            
            
        //Obtenemos los datos relevantes de la mascara
        var aux=mascara.split(",");
        var coma=aux.length;
        var aux=mascara.replace(',','');
        aux=aux.replace('.','');
        aux=aux.split('#');
        var prefijo=aux[0];
        var posfijo=aux[aux.length-1];
        var aux=mascara.split(".");
        if(aux.length > 1)
        {
            var ndec=aux[1].replace(posfijo,'').length;
        }
        else
            var ndec=0;
                
        //Empezamor a evaluar   
        var cad=valor;
        
        //Proceso para numero de posiciones decimales
        if(ndec > 0)
        {
            cad=parseFloat(cad);
            cad=Math.round(cad*Math.pow(10,ndec))/Math.pow(10,ndec);
            if(cad<0)
            {
                cad=Math.abs(cad);
                prefijo='-'+prefijo;
            }
            cad=cad.toString();
        }
        
        
        //Comas en numeros
        if(coma > 1)
        {           
            //alert(cad);
            var aux=cad.split('.');
            cM=0;
            var ax="";
            for(m=aux[0].length-1;m >= 0;m--)
            {
                cM++;
                ax=aux[0].charAt(m)+ax;
                if(cM == 3 && m != 0)
                {

                    ax=','+ax;
                    cM=0;
                }
            }
            cad=ax;
            if(aux.length > 1 && ndec > 0)
            {
                dec=aux[1];
                for(var i=dec.length;i<ndec;i++)
                    dec+="0";
                cad=cad+"."+dec;
            }
            else if(ndec > 0)
            {
                dec=aux[0];
                nu='';
                for(var i=nu.length;i<ndec;i++)
                    nu+="0";
                cad=cad+"."+nu;
            }
        }
        
        //Prefijos y posfijos
        if(prefijo.length > 0)
            cad=prefijo+cad;
        if(posfijo.length > 0)
            cad=cad+posfijo;9
            
        return cad;
    } 
    
    //marcar notificacion como vista
	function marcar_notificacion_vista( folio_unico, remove = false ){
        let folios = ws.viewedFolios ? ws.viewedFolios : [];
        ws.viewedFolios = [folio_unico, ...folios];
        ws.sendViewedTransactions();
        if( remove ){
            $( '#card_payment_row_' + emergent_count_tmp ).remove();
        }
        close_emergent();
        carga_pedido( $( '#id_venta' ).val() );
	}    //marcar notificacion como vista
	
    function buscar_repuesta_peticion_por_folio( folio_unico ){
        ws.currentTransaction = {
            folio_unico: folio_unico,
            id_sucursal: $sucursal_websocket,
        };
        ws.refreshTransaction();
		//close_emergent();
	}

    function informar_folio( folio_unico ){
        ws.actualFolio = folio_unico;
        //console.log( folio_unico );
        /*{
            folio_unico: folio_unico,
            id_sucursal: $sucursal_websocket,
        };*/
        ws.informFolio();
		//close_emergent();
	}
     
