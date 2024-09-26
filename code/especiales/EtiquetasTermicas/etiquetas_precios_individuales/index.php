<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../../../../css/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../../../css/icons/css/fontello.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/functions.js"></script>
    <script src="js/builder.js"></script>
    <script src="../../../../js/jquery-1.10.2.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div id="emergent">
        <div id="emergent_content"></div>
    </div>
    <div class="row global">
        <div class="header bg-primary">
            <h2 class="text-light">Generador de etiquetas de Precios</h2>
        </div>
        <br>
        <div class="text-center">
            <button
                type="button"
                class="btn btn-success"
                onclick="buildLocationForm();"
            >
                <i class="icon-location">Imprimir etiquetas de ubicaciones</i>
            </button>
        </div>
        <br>
        <br>
        <div class="row">
            <div class="col-8">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar / Escanear Producto" 
                    onkeyup="seek_product(this, event)" id="seeker_input">
                    <button
                        type="button"
                        class="btn btn-warning"
                    >
                        <i class="icon-barcode"></i>
                    </button>
                    <button
                        type="button"
                        class="btn btn-danger"
                        onclick="location.reload();"
                    >
                        <i class="icon-trash"></i>
                    </button>
                </div>
                <div id="resBus"></div>
            </div>
            <div class="col-4">
                <button
                    type="button"
                    class="btn btn-success"
                    onclick="print_tag_without_price();"
                >
                    <i class="icon-print">Sin precio</i>
                </button>
            </div>
        </div>
        <div class="row text-center" style="text-align : center !important; width : 100% !important;">
            <br>
            <h4 id="_current_product_name_"></h4>
            <div id="previous_container" class="text-center"></div>
        </div>
        <br><br><br><br><br><br>
                    
        <div class="row bg-primary footer">
            <button
                type="button"
                class="btn btn-light"
                onclick="location.href='../../../../index.php?';"
            >
                <i class="icon-home">Regresar al panel</i>
            </button>
        </div>
    </div>
</body>
</html>

<script>
    getProductsCatalogue();
    $( '#resBus' ).css( "display", 'none' );
   // create_template( 1, 1 );
</script>

<style>
   
    /*button{
        border:1px solid;
    }*/

</style>