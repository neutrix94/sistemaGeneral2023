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
                        <input type="text" id="number_from" class="form-control"
                        min="1" max="99" onkeyup="numberValidation(this);" placeholder="# desde">
                    </div>
                    <div class="col-sm-6">
                        <label>Hasta</label>
                        <input type="text" id="number_to" class="form-control"
                        min="1" max="99" onkeyup="numberValidation(this);" placeholder="# hasta">
                    </div>
                </div>
            </div>
            <div class="col-6 row">
                <div class="row">
                    <h3 class="text-center">Altura (Letra)</h3>
                    <div class="col-sm-6">
                        <label>Desde</label>
                        <input type="text" id="letter_from" class="form-control"
                        maxlength="1" onkeyup="letterValidation(this);" placeholder="letra desde">
                    </div>
                    <div class="col-sm-6">
                        <label>Hasta</label>
                        <input type="text" id="letter_to" class="form-control"
                        maxlength="1" onkeyup="letterValidation(this);" placeholder="letra hasta">
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