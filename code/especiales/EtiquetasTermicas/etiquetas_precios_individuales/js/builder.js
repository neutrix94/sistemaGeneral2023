    var global_json = null;
    function create_template( product_id, product_name, template_type ){
        var url = `ajax/TagsGenerator.php?TagsGeneratorFl=getPreviousPrices&product_id=${product_id}`;
        var resp = ajaxR( url );
        var json = JSON.parse( resp );//alert( json.templates );
        if( json.templates ){
            $( '#previous_container' ).html( json.templates );
            global_json = json.product;
            $( '#current_product_name' ).html( `${json.product.name_part_one} ${json.product.name_part_two}` );
          //  console.log( global_json );
            $( '#seeker_input' ).val();
        }else if( json.error ){
            alert( json.error );
        }
    }

    function printTag( flag ){
        const jsonString = JSON.stringify(global_json);
    // Codificar el JSON string para que sea seguro incluirlo en una URL
        const encodedJson = encodeURIComponent(jsonString);
        var url = `ajax/TagsGenerator.php?TagsGeneratorFl=${flag}&product=${encodedJson}`;
        //alert( url );return false;
        var resp = ajaxR( url );
        if( resp.trim() != 'ok' ){
            alert( "Error" + resp );
        }else{
            alert( "Etiqueta generada exitosamente." );
        }
       // alert( resp );
    }

    function print_tag_without_price(){
        if( global_json == null ){
            alert( "No hay ningun producto cargado, selecciona/escanea un producto para continuar." );
            return false;
        }
        const jsonString = JSON.stringify(global_json);
    // Codificar el JSON string para que sea seguro incluirlo en una URL
        const encodedJson = encodeURIComponent(jsonString);
        var url = `ajax/TagsGenerator.php?TagsGeneratorFl=PrintTagWithoutPrice&product=${encodedJson}`;
        var resp = ajaxR( url );
        if( resp.trim() != 'ok' ){
            alert( "Error" + resp );
        }else{
            alert( "Etiqueta generada exitosamente." );
        }
    }

    function printLocationTags(){
        var letter_from, letter_to, number_from, number_to;
        number_from = $( '#number_from' ).val();
        if( number_from == '' ){
            alert( "El numero DESDE la ubicación no puede ir vacio." );
            $( '#number_from' ).focus();
            return false;
        }
        number_to = $( '#number_to' ).val();
        if( number_to == '' ){
            alert( "El numero HASTA la ubicación no puede ir vacio." );
            $( '#number_to' ).focus();
            return false;
        }
        letter_from = $( '#letter_from' ).val();
        if( letter_from == '' ){
            alert( "La letra DESDE de altura no puede ir vacio." );
            $( '#letter_from' ).focus();
            return false;
        }
        letter_to = $( '#letter_to' ).val();
        if( letter_to == '' ){
            alert( "La letra HASTA de altura no puede ir vacio." );
            $( '#letter_to' ).focus();
            return false;
        }
    //envia peticion para enviar etiquetas de ubicacion
        var url = `ajax/TagsGenerator.php?TagsGeneratorFl=createLocationTags&number_from=${number_from}&number_to=${number_to}&letter_from=${letter_from}&letter_to=${letter_to}`;
       // alert(url);
        var resp = ajaxR( url );
        if( resp.trim() != 'ok' ){
            alert( "Error" + resp );
        }else{
            alert( "Etiqueta(s) generada(s) exitosamente." );
        }
    }

    function buildLocationForm(){
        var content = `<div class="row">
            <div class="text-end">
                <button class="btn btn-light" onclick="close_emergent();">
                    X
                </button>
            </div>
            <div class="col-6">
                <div class="row">
                    <h3 class="text-center">Ubicación (Número)</h3>
                    <div class="col-sm-6">
                        <label>Desde</label>
                        <input type="number" id="number_from" class="form-control"
                        min="1" max="99">
                    </div>
                    <div class="col-sm-6">
                        <label>Hasta</label>
                        <input type="number" id="number_to" class="form-control"
                        min="1" max="99">
                    </div>
                </div>
            </div>
            <div class="col-6 row">
                <div class="row">
                    <h3 class="text-center">Altura (Letra)</h3>
                    <div class="col-sm-6">
                        <label>Desde</label>
                        <input type="text" id="letter_from" class="form-control"
                        maxlength="1" pattern="[a-z]">
                    </div>
                    <div class="col-sm-6">
                        <label>Hasta</label>
                        <input type="text" id="letter_to" class="form-control"
                        maxlength="1" pattern="[a-z]">
                    </div>
                </div>
            </div>
            <div class="text-center">
                <br>
                <button class="btn btn-success" onclick="printLocationTags();">
                    <i>Imprimir Etiquetas</i>
                </button>
                <br>
                <br>
            </div>
        </div>`;
        $( '#emergent_content' ).html( content );
        $( '#emergent' ).css( 'display', 'block' );
    }

    function close_emergent(){
        $( '#emergent_content' ).html( '' );
        $( '#emergent' ).css( 'display', 'none' );
    }