<?php

    include("../../include/fpdf153/nclasepdf.php");    
    include("../../conectMin.php");
    require("class.phpmailer.php");
    
    extract($_GET);
    
    
    $orient_doc="P";
    $unid_doc="cm";
    $alto_doc=27.9;
    $ancho_doc=21.6;
    $ftam=10;
    $tamano_doc=array($ancho_doc,$alto_doc);
    $ypag=27.9;
    $impDoc++;
    include("cotizacion.php");
    
    $file="cotizacion.pdf";
    
    //Salva elPDF en un archivo
    $pdf->Output($file);
    
    
    //buscamos los datos de conexion
    
    $sql="SELECT
            u.correo,
            servidor_correo,
            user_correo,
            password_correo,
            puerto,
            usa_ssl,
            cuerpo_correo,
            encabezado,
            color_fondo_r,
            color_fondo_g,
            color_fondo_b,
            nombre_empresa,
            CONCAT(u.nombre, ' ', u.apellido_paterno) AS usuario,
            cl.correo AS correo_cliente,
            cl.nombre AS nombre_cliente
            FROM sys_users u
            JOIN eq_configuracion c ON c.id_configuracion=1
            JOIN eq_cotizador ct ON ct.id_cotizador=$id
            JOIN eq_clientes cl ON ct.contacto = cl.id_cliente
          WHERE u.id_usuario=$user_id";
          
    $res=mysql_query($sql) or die(mysql_error());
    
    $row=mysql_fetch_assoc($res);
    extract($row);
    
    $mail1 = new PHPMailer();
    
    //Luego tenemos que iniciar la validación por SMTP:
    $mail1->IsSMTP();

    $mail1->SMTPAuth = true;

    if($usa_ssl == '1')
        $mail1->SMTPSecure = "ssl";

    $mail1->Host = $servidor_correo; // SMTP a utilizar. Por ej. smtp.elserver.com

    $mail1->Username = $user_correo; // Correo completo a utilizar

    $mail1->Password = $password_correo; // Contraseña
    $mail1->Port = $puerto; // Puerto a utilizar

    //Con estas pocas líneas iniciamos una conexión con el SMTP. Lo que ahora deberíamos hacer, es configurar el mensaje a enviar, el //From, etc.
    $mail1->From = $correo;
    // Desde donde enviamos (Para mostrar)
    $mail1->FromName = $usuario;

    //Estas dos líneas, cumplirían la función de encabezado (En mail() usado de esta forma: “From: Nombre <correo@dominio.com>”) de //correo.


    $mail1->AddAddress($correo_cliente);
    
    $mail1->AddAttachment("cotizacion.pdf");

    // Esta es la dirección a donde enviamos

    $mail1->IsHTML(true);
    // El correo se envía como HTML

    $body='<style>
body{ font-family:sans-serif;
}

p{font-size:15pt;
padding-left:15px;
color:#DE5A78;
}
#texto {font-size:normal;
padding-left:12px;
padding-right:12px;
color:#666;
}
h1{color:#20215C;
padding-left:45px;
border-bottom:#DE5A78 2px solid;
margin-bottom:20px;
}
h4, h2{color:#333;

}

#textofinal
{font-size:15pt;
color:#DE5A78;
padding-left:10px;
padding-right:10px;
}

#footer{
    background: #DE5A78 url(/img/footer.png);
    min-height:50px;
    border-top:#999 3px solid;
    font-size:15pt;
    color:#fff;
    padding:10px 10px;
    margin-top:10px;
    font-weight:bold;
    padding-left:10px;
padding-right:10px;
    
    
}
#izquierda
{float:left;

}
#derecha
{float:right;
text-align:right;
}

</style>

    <font face="sans-serif">

 <table width="100%">
    <tr>
        <td>
            <p style="color: #'.$color_fondo_r.$color_fondo_g.$color_fondo_b.'">Estimado(a)</p>
            <b>'.$nombre_cliente.'</b>
        </td>
        <td align="right">
            <img alt="0" src="'.$encabezado.'" width="280px" height="60px">
        </td>        
    </tr>    
    <tr>
        <td colspan="2">
            <hr style="color: #'.$color_fondo_r.$color_fondo_g.$color_fondo_b.';">
        </td>
    </tr>    
 </table>

<div id="texto">
        <h2>En atenci&oacute;n a su solicitud le enviamos la cotizaci&oacute;n de los productos de su inter&eacute;s</h2>
        
        <span>'.$cuerpo_correo.'</span>
        <br>
        <span style="color: #'.$color_fondo_r.$color_fondo_g.$color_fondo_b.'">Gracias por contactarnos, esperamos su pronta respuesta.</span>
</div>
<div id="footer" style="background-color: #'.$color_fondo_r.$color_fondo_g.$color_fondo_b.'">
<span id="izquierda" style="color: #FFFFFF">Capslim de M&eacute;xico S.A. de C.V.</span>

</div>
</font>
    ';
    
    //$body.=$cuerpo_correo;
    

    $mail1->Subject =utf8_decode("Cotización");

    // Este es el titulo del email.



    //$cuerpo="Test";
        
        
    $mail1->Body = $body; // Mensaje a enviar 

    $exito = $mail1->Send(); 
    
    
    if($exito)
        die("exito");        
    

?>