<?php
	include( '../../config.ini.php' );
	include( '../../conexionMysqli.php' );

	if(	isset( $_POST['del_img'] ) ){
		//die( $_POST['route'] );
		if ( ! unlink( $_POST['route'] ) ){
			die( "La imágen no pudo ser eliminada!" );
		}
		$sql = "UPDATE ec_productos SET imagen = '' WHERE id_productos = '{$_POST['product_id']}' ";
		$exc = $link->query( $sql ) or die( "Error al resetear imágen del producto : " . $link->error );
		die( 'La imágen fue eliminada exitosamente!' );
	}
	if( isset( $_POST['get_img'] ) ){
		die( "<img src=\"{$_POST['src']}\" />" );
	}
	if( isset( $_POST['form_product_id'] ) ){
		if (($_FILES["file"]["type"] == "image/pjpeg")
	    || ($_FILES["file"]["type"] == "image/jpeg")
	    || ($_FILES["file"]["type"] == "image/png")
	    || ($_FILES["file"]["type"] == "image/gif")) {
	    	$insert_db = 0;
	    	if(  $_POST['image_name'] == '' ||  $_POST['image_name'] == null ){
	    		$arrAx=explode('.', $_FILES[$ax]['name']);
				$_POST['image_name'] = "../../../img_productos/ec_productos_adm_".rand(1, 10000)."_".$_POST['form_product_id'].".".str_replace('image/', '', $_FILES["file"]["type"]);
				//die( $url_final );
	    		$insert_db = 1;
	    	}else if ( ! unlink( $_POST['image_name'] ) ){
				die( "La imágen no pudo ser eliminada!" );
			}
	    	if (move_uploaded_file($_FILES["file"]["tmp_name"], $_POST['image_name'] ) ) {
	        //more code here...
	        	//echo "./" . $_FILES['file']['name'];
		    }else{
		        die( 'Error al cargar la imágen!' );
		    }
		}else{
		    die( 'la imágen no tiene un formato válido' );
		}
		if( $insert_db == 1 ){
			$sql = "UPDATE ec_productos SET imagen = '{$_POST['image_name']}' 
			WHERE id_productos = '{$_POST['form_product_id']}'";
			$exc = $link->query( $sql ) or die( "Error al actualizar la ruta de la imágen : " .$link->error );
		}
		die( 'La imagen fue cargada exitosamente!' );
	}

	if( isset( $_POST['get_form'] ) || isset( $_GET['get_form'] ) ){
?>
	<form method="post" action="#" enctype="multipart/form-data">
		<div class="form_container">
			<div class="row">
				<div class="col-6">
					<label for="form_product_id">Id de Producto</label>
					<input 
						type="text" 
						id="form_product_id"
						name="form_product_id"
						class="form-control read_only"
						readonly
					>

					<label for="form_product_name">Nombre del Producto</label>
					<textarea 
						id="form_product_name"
						name="form_product_name"
						class="form-control read_only"
						readonly
					></textarea>
					<input 
						type="hidden" 
						id="current_src"
					>
					<label for="form_product_image">Cambiar imágen</label>
					<input 
						type="file" 
						id="image"
						name="image"
						class="form-control read_only"
						accept=".jpg, .png"
					>

            		<input type="button" onclick="upload_img();" class="btn btn-primary" value="Subir Imágen">
            		<input type="button" onclick="delete_img();" class="btn btn-danger" value="Eliminar Imágen">
				</div>
				<div class="col-6 previous_img_containter">
					<img 
						src=""
						width="100%"
						id="previous_image"
					/>
				</div>
			</div>
<?php
		return '';
	}
?>

<link rel="stylesheet" type="text/css" href="../../css/bootstrap/css/bootstrap.css">
<script type="text/javascript" src="../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript">
	current_counter = 0;
	function reload( obj ){
		window.location = "images_validation.php?cat=" + $( '#f1' ).val() + "&type=" + $( '#f2' ).val();
	}

	function show_imagen_form( counter, close = null ){
		if( close != null ){
			current_counter = 0;
			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );
			return false;
		}
		current_counter = counter;
	//carga el formulario
		$.ajax({
			type : 'post',
			url : './images_validation.php',
			cache : false,
			data : { get_form : 1 },
			success : function ( dat ){
				//alert( dat );
				$( '.emergent_content' ).html( dat );
				$( '.emergent' ).css( 'display', 'block' );

				$( '#form_product_id' ).val( $( '#product_id_' + counter ).html().trim() );
				$( '#form_product_name' ).val( $( '#product_name_' + counter ).html().trim() );
				$( '#previous_image' ).attr( 'src', $( '#product_img_src_' + counter ).html().trim() );
				$( '#current_src' ).val( $( '#product_img_src_' + counter ).html().trim() );
			}
		});
	}
//vista previa de la imágen
	/*$(".pic_box").click(function(){
		$("#upload").click();
	});*/
			
	$("#form_product_image").on("change", function() {
		alert( "Here" );
	         var objUrl = getObjectURL (this.files[0]); // Obtener la ruta de la imagen, la ruta no es la ruta local de la imagen
			
		if (objUrl) {
		     $ ("#previous_image"). attr ("src", objUrl); // Guarde la ruta de la imagen en src y muestre la imagen
		    var items = $(this)[0].files;
		     var fileName = items[0].name; // obtiene el nombre del archivo
		     var fileSize = items[0].size; // obtiene el tamaño del archivo 
		     var fileType = items[0].type; // obtiene el tipo de archivo
		    $("#name").html(fileName);
		    $("#size").html(fileSize+"bytes");
		        $("#type").html(fileType);
		}
	});
	        
	function getObjectURL (archivo) {// Obtener URL
		var url = null ;
		if (window.createObjectURL!=undefined){ // basic
			url = window.createObjectURL(file) ;
		}else if (window.URL!=undefined){
			// mozilla(firefox)
			url = window.URL.createObjectURL(file);
		}else if (window.webkitURL!=undefined) {
			// webkit or chrome
			url = window.webkitURL.createObjectURL(file) ;
		}
		alert( url );
		return url ;
	}

	function upload_img () {
		//alert( $( '#current_src' ).val() );
	        var formData = new FormData();
	        var files = $('#image')[0].files[0];
	        formData.append('file',files);
	        formData.append('form_product_id', $( '#form_product_id' ).val() );
	        formData.append('product_name', $( '#form_product_id' ).val() );
	        formData.append('image_name', $( '#current_src' ).val() );
	        $.ajax({
	            url: './images_validation.php',
	            type: 'post',
	            data: formData,
	            contentType: false,
	            processData: false,
	            success: function(response) {
	            	alert( response );
	            	//put_img( $( '#product_img_src_' + current_counter ).html().trim() );
	            	location.reload();
	            	/*$( '.previous_img_containter' ).html('');
	            	$( '.previous_img_containter' ).html("<img src=\"\" width=\"100%\" id=\"previous_image\"/>");
	            	
					$( '#previous_image' ).removeAttr( 'src' );
					setTimeout( function (){
						$( '#previous_image' ).attr( 'src', $( '#product_img_src_' + current_counter ).html().trim() );
	            		}, 300
	            	);*/
	            }
	        });
	        return false;
	    }

    function put_img( source ){
	    $.ajax({
	        url: './images_validation.php',
	        type: 'post',
	        data : { get_img : 1, src : source  },
	        success: function(response) {
	           	$( '.previous_img_containter' ).html( response );

	        }
	    });
    }

    function delete_img(){
	    $.ajax({
	        url: './images_validation.php',
	        type: 'post',
	        data : { del_img : 1, 
	        			route : $( '#current_src' ).val(), 
	        			product_id : $( '#product_id_' + current_counter ).html().trim()  
    			},
	        success: function(response) {
	        	alert( response );
	        	location.reload();
	        }
	    });
    }

/*$(document).ready(function(e){
    $("#fupForm").on('submit', function(e){
        e.preventDefault();
        alert();
        $.ajax({
            type: 'POST',
            url: 'submit.php',
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
                $('.submitBtn').attr("disabled","disabled");
                $('#fupForm').css("opacity",".5");
            },
            success: function(msg){
                $('.statusMsg').html('');
                if(msg == 'ok'){
                    $('#fupForm')[0].reset();
                    $('.statusMsg').html('<span style="font-size:18px;color:#34A853">Form data submitted successfully.</span>');
                }else{
                    $('.statusMsg').html('<span style="font-size:18px;color:#EA4335">Some problem occurred, please try again.</span>');
                }
                $('#fupForm').css("opacity","");
                $(".submitBtn").removeAttr("disabled");
            }
        });
    });
    
    //file type validation
    $("#form_product_image").change(function() {
        var file = this.files[0];
        var imagefile = file.type;
        var match= ["image/jpeg","image/png","image/jpg"];
        if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2]))){
            alert('Please select a valid image file (JPEG/JPG/PNG).');
            $("#file").val('');
            return false;
        }
    });
});*/
</script>

<style type="text/css">
	.group_card{
		box-shadow:1px 1px 10px rgba(0,0,0,.3) !important;  
		padding : 10px !important;  
		margin-bottom : 20px !important; 
	}
	.no_visible{
		display: none;
	}
	.emergent{
		position : fixed;
		width : 100%;
		height : 100%;
		z-index : 10 ;
		background-color: rgba( 0,0,0, .5 );
		display: none;
	}
	.emergent_content{
		position : fixed;
		width : 80%;
		height : 70%;
		top : 15% ;
		left: 10%;
		z-index : 10 ;
		background-color: white;
		box-shadow: 5px 5px 15px rgba( 0,0,0,.7);

	}
	.close_emergent{
		position: fixed;
		right: 9%;
		top : 12%;
		z-index: 15;
	}
	.form_container{
		margin: 10%;
	}
	.read_only{
		background: white !important;
	}
	.img_container{
		height: 200px;
	}

</style>
	<div class="emergent">
		<button class="btn btn-danger close_emergent" onclick="show_imagen_form( 0, 1 )">X</button>
		<div class="emergent_content">
			<div class="row">

			</div>
		</div>
	</div>

<?php
	
	$sql = "SELECT id_categoria, nombre FROM ec_categoria WHERE id_categoria > 0";
	$exc = $link->query( $sql ) or die( "Error al consultar categorias : " . $link->error );

	echo '<div class="row">';
		echo '<div class="col-2"></div>';
		echo '<div class="col-8">';
			echo '<select onchange="reload( this )" id="f1" class="form-control">';
			echo '<option value="0">-- Seleccionar --</option>';
			while ( $r = $exc->fetch_row()) {
				echo "<option value=\"{$r[0]}\"";
				if( $_GET['cat'] == $r[0] ){
					echo " selected";
				}
				echo ">{$r[1]}</option>";
			}
			echo "</select>";

	//		echo "<div class=\"row\">";
				echo "<select class=\"form-control\" onchange=\"reload()\" id=\"f2\">";
					echo "<option value=\"0\" >Ver todos los producto</option>";
					echo "<option value=\"1\" " . ( $_GET['type'] == 1 ? " selected" : "" ) . ">Ver producto con imágen</option>";
					echo "<option value=\"2\" " . ( $_GET['type'] == 2 ? " selected" : "" ) . ">Ver productos sin imágen</option>";
				echo "</select>";
	//		echo "</div>";

		echo '</div>';
	echo '</div>';



	$sql = "SELECT 
				id_productos,
				nombre,
				imagen
			FROM ec_productos
			WHERE id_productos > 1";
	if( $_GET['cat'] != 0 ){
		$sql .= " AND id_categoria = '{$_GET['cat']}'";
	}

	if( $_GET['type'] == 1 ){
		$sql .= " AND ( imagen IS NOT NULL AND imagen != '' ) ";
	}else if( $_GET['type'] == 2 ){
		$sql .= " AND( imagen IS NULL OR imagen = '' )";
	}
	$sql .= " ORDER BY orden_lista ASC";/**/
	//die( $sql);
	$exc = $link->query( $sql ) or die( "Error al consultar imágenes de productos : " . $sql .  $link->error );
	echo "<div class=\"row\" style=\"margin : 50px;\">";
	$counter = 0;
	while( $r = $exc->fetch_row() ){
		$counter ++;
		$aux = explode('_', $r[2]);
		$aux[5] = str_replace('.png', '', $aux[5]);
		$aux[5] = str_replace('.jpg', '', $aux[5]);
		echo "<div class=\"col-4\">";
		echo "<div class=\"group-card\" style=\"box-shadow:1px 1px 10px rgba(0,0,0,.3) !important;padding : 10px !important;margin-bottom : 20px !important;\">";
    			echo "<p>ID producto : <i class=\"\" id=\"product_id_{$counter}\">{$r[0]}</i></p>";
    			echo "<p>ID de imágen : {$aux[5]}</i></p>";
    			echo "<i class=\"no_visible\" id=\"product_img_src_{$counter}\">{$r[2]}</i>";
    			//echo "<p>nombre de imágen : <br>{$r[2]}</p>";
    			echo "<p class=\"\" id=\"product_name_{$counter}\">{$r[1]}</p>";
	    		echo "<div class=\"img_container\">";
	    			echo "<img 
							src=\"{$r[2]}\" 
							width=\"80%\" 
							id=\"img_{$counter}\"
						/>";
				echo "</div>";
    			echo "<button 
    					class=\"btn btn-warning form-control\"
						onclick=\"show_imagen_form( {$counter} );\"
    				>Cambiar imágen</button>";
		echo "</div>";
		echo "</div>";
	}	
	echo "</div>";

?>