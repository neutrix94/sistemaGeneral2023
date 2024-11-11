<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codigos Barras</title>
    <link rel="stylesheet" href="../../../css/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../../css/icons/css/fontello.css">
    <script src="../../../js/jquery-1.10.2.min.js"></script>
    <script src="js/functions.js"></script>
</head>
<body class="bg-primary">
    <div>
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
        <!--div class="input-group">
            <input type="text" class="form-control">
        </div>
        <button
            type="button"
        >
            <i class=""></i>
        </button-->
    </div>
    <div id="barcode">
        
    </div>
</body>
</html>

<script>
    getProductsCatalogue();
    $( '#resBus' ).css( "display", 'none' );
   // create_template( 1, 1 );
</script>
