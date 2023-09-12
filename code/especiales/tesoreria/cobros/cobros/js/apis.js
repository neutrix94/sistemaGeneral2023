
	function sendTerminalPetition( counter, terminal_id ){		
		var sale_id = $( '#id_venta' ).val();
		var amount = $( `#t${counter}` ).val();
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
		var url = "ajax/db.php?fl=sendPaymentPetition&amount=" + amount;
		url += "&terminal_id=" + $( '#tarjeta_' + counter ).val();
		url += "&sale_folio=" + sale_folio;
		url += "&counter=" + counter;
		//url += "&user_id=" + user_id;
		//alert( url );
		var resp = ajaxR( url );
		console.log( resp );
		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
	} 

	function rePrintByOrderId( transaction_id ){
		var url = "ajax/db.php?fl=rePrintByOrderId&transaction_id=" + transaction_id;
		var resp = ajaxR( url );
		//var data = JSON.parse( resp ).trim();
		var content = `<div class="row">
			<h3 class="text-success text-center">Reimpresion Enviada</h3>
			<div class="text-center">
				<button
					type="button"
					class="btn btn-success"
					onclick="close_emergent();"
				>
					<i class="icon-ok">Aceptar</i>
				</button>
			</div>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}
	function cancelByOrderId( transaction_id ){
		var url = "ajax/db.php?fl=cancelByOrderId&transaction_id=" + transaction_id;
		var resp = ajaxR( url );
		//var data = JSON.parse( resp );
		var content = `<div class="row">
			<h3 class="text-danger text-center">Cancelacion enviada</h3>
			<div class="text-center">
				<button
					type="button"
					class="btn btn-success"
					onclick="close_emergent();"
				>
					<i class="icon-ok">Aceptar</i>
				</button>
			</div>
		</div>`;
		$( '.emergent_content' ).html( content );
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
     
