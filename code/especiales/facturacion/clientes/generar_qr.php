<?php
    include( '../../../../conect.php' );
    include( '../../../../conexionMysqli.php' );
//genera token
    $token = md5( uniqid( "token" ) );
    $sql = "INSERT INTO vf_tokens_alta_clientes ( id_token_cliente, token, fecha_hora_alta, 
        fecha_hora_vencimiento, id_sucursal, id_usuario )
        VALUES ( NULL, '{$token}', NOW(), ( SELECT DATE_ADD(NOW(), INTERVAL 60 MINUTE ) ), {$user_sucursal}, {$user_id} )";
    $stm = $link->query( $sql ) or die( "Error al insertar registro de token : {$sql} {$link->error}" );
    $last_id = $link->insert_id;
// Incluir la biblioteca de generación de códigos QR
    include "phpqrcode/qrlib.php";
//if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["url"])) {
    $url = "http://192.168.1.71/General2023/code/especiales/facturacion/clientes/index.php?token={$token}";
  
    // Asegúrate de que la URL esté codificada correctamente para evitar problemas con caracteres especiales
    //$url = urlencode($url);
    //echo $url;
    // Ruta donde se guardará el archivo de imagen QR (puedes cambiarlo según tus necesidades)
    $ruta_imagen_qr = 'qr_codes/' . time() . '.png';

    //QRcode::png($codeContents, $tempDir.'007_4.png', QR_ECLEVEL_L, 4);
    // Generar el código QR y guardarlo como imagen
    QRcode::png($url, $ruta_imagen_qr, QR_ECLEVEL_L, 10);
    // Mostrar el código QR en la página
    
    //$svgCode = QRcode::svg('PHP QR Code :)');
    //echo $svgCode;

//}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generador de URLs</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
</head>
<body>
    <div class="row text-center">
        <div class="col-12 bg-primary"
            style="position : fixed; top : 0 ; left : 0; width : 100%;padding : 1%;">
            <h1 class="text-light">Escanea el codigo Qr para ir a la liga y dar de alta al Cliente</h1>
        </div>
    </div>
    <div class="text-center">
        <br><br><br><br><br><br>
        <?php 
            echo '<img src="' . $ruta_imagen_qr . '" alt="Código QR"><br>';
            echo "<div class=\"row text-center\"><h2 class=\"text-center\">Url :</h2>
            <input type=\"text\" value=\"{$url}\" class=\"form-control\"></div>";
        ?>
    </div> 
    <div class="bg-primary text-center"
            style="position : fixed; bottom : 0 ; left : 0; width : 100%;padding : 1%;">
        <button
            type="button"
            class="btn btn-light"
        >
            <i class="icon-home-1">Regresar al Panel</i>
        </button>
    </div>  
</body>
</html>