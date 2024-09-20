    var global_json = null;
    function create_template( product_id, product_name, template_type ){
        var url = `ajax/TagsGenerator.php?TagsGeneratorFl=getPreviousPrices&product_id=${product_id}`;
        var resp = ajaxR( url );
        var json = JSON.parse( resp );//alert( json.templates );
        if( json.templates ){
            $( '#previous_container' ).html( json.templates );
            global_json = json.product;
            console.log( global_json );
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
        alert( resp );
    }