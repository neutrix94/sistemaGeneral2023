<?php
//librerias canvas
    echo "<script type=\"text/javascript\" src=\"{$home_path}js/canvas2image.js\"></script>";
    echo "<script type=\"text/javascript\" src=\"{$home_path}js/html2canvas.min.js\"></script>";
    if( $include_jquery == 1 ){
        echo "<script type=\"text/javascript\" src=\"{$home_path}js/jquery-1.10.2.min.js\"></script>";
    } 
    ?>
<style type="text/css">
    
    #video_container{
        position: relative;
        width: 100%;
        height: 500px;
        max-height: 500px;
        left: 0%;
        overflow: hidden;
        display: none;
    }
    #options_buttons{
        position: relative;
        margin-top : 20px;
    }
    #video{
        position: relative;
        /*height: 500px;*/
        width: 100%;
        left: 0%;
        z-index: 1;
       /* height: 100%;*/
    } 
    #frame{
        position: relative;
        margin-top: 10px;
        display: none;
        z-index: 2;
        display: none !important;
        /*height: 70%;
        width: 50%;
       /*display : none ;*/
    }
    #boton{
        display: none;
    }
    /*@media only screen and (max-width: 600px) {
        #frame{
            width: 50px;
        }
    }
    @media only screen and (max-width: 700px) {
        #frame{
            width: 50px;
        }
    }
    @media only screen and (min-width: 800px) {
        #frame{
            width: 800px;
            top : -800px !important; 
            left: 0%;
            max-height: 500px;
        }
    }*/

</style>
    <div id="video_container">
        <video id="video"></video>
        <img id="frame"><!-- width="95%"  -->
    </div>
    <canvas id="canvas" style="display: none;"></canvas>
    <div class="response"></div>

<script type="text/javascript">
        var localStream = 1;
        var video = document.getElementById('video');
        var canvas_tmp = document.getElementById('canvas');
        var global_photo_render = '';
    function set_global_photo_render( num ,frame, width = null ){
        global_photo_render = '#previous_img_' + num;
        open_camera( 1, frame, width );
    }
<?php
   //if( !isset($frames_path) ){
        echo "var frames_path = '{$home_path}';";
    
?>
    function open_camera( type = 1, frame, TOP = null ) {
        if( (localStream != 1 || type == 0) && type != 1 ){
                document.getElementById( 'video_container' ).style.display = 'none';
                //document.getElementById ('camera_btn' ).innerHTML = 'Abrir Cámara';
                //$boton.style.display = 'none';
                $( '#boton' ).css( 'display', 'none' );
                localStream.getTracks().forEach(function(track) {
                  track.stop();
                });
                localStream = 1;
                return false;
        }
        document.getElementById( 'video_container' ).style.display = 'block';
        $( '#frame' ).attr( 'src', frames_path + 'img/frames/' + frame );
        //document.getElementById( 'camera_btn' ).innerHTML = 'Cerrar Cámara';
        $( '#boton' ).css( 'display', 'block');
        $( '#frame' ).css( 'display', 'block' );
        $( '#video_container' ).css( 'display', 'block' );
        video.style.width = document.width + 'px';
        video.style.height = document.height + 'px';
        video.setAttribute('autoplay', '');
        video.setAttribute('muted', '');
        video.setAttribute('playsinline', '');
        var constraints = {
             audio: false,
             video: {
                 facingMode: 'environment'/*user*/
             }
        }
        navigator.mediaDevices.getUserMedia(constraints).then(function success(stream) {
            localStream = stream;
            video.srcObject = localStream;
        });
    }

    function takeScreen( obj_screenShot, obj_to_render = null, path_save = null, name_save = '', close_camera = 0 ){//#, .response, ajax/db.php?fl=savePhoto
        html2canvas($( obj_screenShot )[0]).then(function(canvas) {
        //  
            if( obj_to_render ){
                $( obj_to_render ).html(canvas);
            }  
            if( path_save != null ){
                var contexto = canvas_tmp.getContext("2d");
                canvas_tmp.width = ( 100 + video.videoWidth);
                canvas_tmp.height = video.videoHeight;
            //creación de imágen
                contexto.drawImage(canvas, 0, 0, canvas_tmp.width, canvas_tmp.height);
                var foto = canvas_tmp.toDataURL(); //Esta es la foto, en base 64
                var xhr = new XMLHttpRequest();
                xhr.open("POST", path_save + "&home_path=" + global_meassures_home_path + "&type=" + global_save_meassure_type, true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.send(encodeURIComponent(foto)); //Codificar y enviar

                xhr.onreadystatechange = function() {
                    if(xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
                        //alert( "La foto fue enviada correctamente" + xhr.responseText );
                        console.log("La foto fue enviada correctamente" + xhr.responseText );
                        console.log(xhr);
                        if( obj_to_render ){
                           // var aux = xhr.responseText.replace( '../', '' );
                            $( global_photo_render ).attr( 'src', xhr.responseText );
                        }
                    }
                }
            }
            if( close_camera == 1 ){
                open_camera( 0 );
            }
        });
    }


</script>

