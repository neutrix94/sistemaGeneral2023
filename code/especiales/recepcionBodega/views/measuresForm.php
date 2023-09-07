<?php
	//echo  'id :' . $row['box_lenght'];

?>
<div class="group_card">
	<h5>
		<input type="checkbox" id="no_box_measures" 
			<?php
				echo ( (isset($row['tmp_id'] ) && 
						( $row['box_lenght'] != 0 || $row['box_width'] != 0 || $row['box_height'] != 0 ) ) || !isset($row['tmp_id'] ) ? ' checked' : '' );
			?>
			onclick="disabled_enabled_box_measures();"> 
		Medidas de la caja
	</h5>
	<div class="row" id="box_measures_container">
		<div class="col-4">
			<input type="number" id="box_lenght"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['box_lenght'] != 0 ?  " value=\"{$row['box_lenght']}\"" : '' );
			?>
			class="form-control">
			<label for="box_lenght" class="measures_label">Largo (cm)</label>
		</div>
		<div class="col-4">
			<input type="number" id="box_width"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['box_width'] != 0 ?  " value=\"{$row['box_width']}\"" : '' );
			?>
			class="form-control">
			<label class="measures_label">Ancho (cm)</label>
		</div>
		<div class="col-4">
			<input type="number" id="box_height"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['box_height'] != 0 ?  " value=\"{$row['box_height']}\"" : '' );
			?> 
			class="form-control">
			<label class="measures_label">Alto (cm)</label>
		</div>
	</div>
<hr>
	
	<h5>
		<input type="checkbox" id="no_pack_measures" 
			<?php
				echo ( ( isset($row['tmp_id'] ) && 
						( $row['pack_lenght'] != 0 || $row['pack_width'] != 0 || $row['pack_height'] != 0 ) ) || !isset($row['tmp_id'] ) ? ' checked' : '' );
			?> 
			onclick="disabled_enabled_pack_measures();"> 
		Medidas del paquete
	</h5>

	<div class="row" style="font-size:90%;">
		<div class="row" id="takePhotoContainer">
			<div class="col-4">
				<input type="number" id="pack_lenght"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['pack_lenght'] != 0 ?  " value=\"{$row['pack_lenght']}\"" : '' );
			?>
				class="form-control">
				<label class="measures_label">Largo (pzas)</label>
			</div>
			<div class="col-4">
				<input type="number" id="pack_width"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['pack_width'] != 0 ?  " value=\"{$row['pack_width']}\"" : '' );
			?>
				class="form-control">
				<label class="measures_label">Ancho (pzas)</label>
			</div>
			<div class="col-4">
				<input type="number" id="pack_height"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['pack_height'] != 0 ?  " value=\"{$row['pack_height']}\"" : '' );
			?>  
				class="form-control">
				<label class="measures_label">Alto (pzas)</label>
			</div>
			<br><br>
			<div class="col-3 text-center" style="padding-top : 8px;">
				<b>Bolsa :</b>
			</div>
			<div class="col-9">
				<?php echo getComboPackBags( $link, ( isset( $row['pack_bag_id'] ) ? $row['pack_bag_id'] : null ) ); ?>
			</div>
		    <!-- div fotos -->
		    <h5>
				<input type="checkbox" id="no_pack_measures_photos" 
					<?php
						echo ( ( isset($row['tmp_id'] ) && 
								( $row['pack_lenght'] != 0 || $row['pack_width'] != 0 || $row['pack_height'] != 0 ) ) 
						|| !isset($row['tmp_id'] ) ? ' checked' : '' );
					?> 
					onclick="disabled_enabled_pack_photos();"> 
				Fotografías del paquete
			</h5>
			<div id="photos_measures">
				<?php
					include( $path_camera_plugin . 'plugins/takePhoto.php' );
				?>
			    <br>
			    <div id="options_buttons">
			        <!--button type="button" onclick="open_camera()" class="btn btn-info form-control">
			            <i class="icon-instagram" id="camera_btn">Abrir Camara</i>
			        </button-->
			        <button type="button" id="boton" onclick="takeScreen( '#video_container', '#img_1', '<?php echo ( isset( $save_img_path ) ? $save_img_path : '' ) . 'ajax/db.php?fl=savePhoto' . ( isset( $product_provider_id ) ? "&product_provider_id=" . $product_provider_id : "" ); ?>', '', 1 )" class="btn btn-success form-control">
			            <i class="icon-picture-outline">Tomar foto</i>
			        </button>
			        <p id="estado">
			        </p>
			        <div class="row">
			            <div class="col-4">
			                <img <?php
									echo ( isset($row['tmp_id'] ) && $row['image_1'] != '' ?  "src=\"{$home_path}files/packs_img_tmp/{$row['image_1']}\"" : "src=\"{$home_path}img/frames/open_box.png\"" );
								?> id="previous_img_1" width="100%"
								onclick="set_global_photo_render( 1, 'open_box.png' )"
							>
			                <p align="center" style="color : blue; font-size : 90%;">Caja Abierta</p>
			               	<button 
			               		type="button"
			               		class="btn btn-info form-control"
			               		onclick="upload_pack_img( 1 );"
			               	>
			               		Subir imágen
			               	</button>
			                <input type="file" id="pack_photo_file_1" onchange="previewImage( 1, '<?php echo ( isset( $save_img_path ) ? $save_img_path : '' ) . 'ajax/db.php?fl=savePhoto' . ( isset( $product_provider_id ) ? "&product_provider_id=" . $product_provider_id : "" ); ?>' );" class="no_visible">
			            </div>
			            <div class="col-4">
			                <img <?php
									echo ( isset($row['tmp_id'] ) && $row['image_2'] != '' ?  "src=\"{$home_path}files/packs_img_tmp/{$row['image_2']}\"" : "src=\"{$home_path}img/frames/length_height.png\"" );
								?> id="previous_img_2" width="100%"
								onclick="set_global_photo_render( 2, 'length_height.png' )"
							>
			                <p align="center" style="color : blue; font-size : 90%;">Superior</p>
			               	<button 
			               		type="button"
			               		class="btn btn-info form-control"
			               		onclick="upload_pack_img( 2 );"
			               	>
			               		Subir imágen
			               	</button>
			                <input type="file" id="pack_photo_file_2" onchange="previewImage( 2, '<?php echo ( isset( $save_img_path ) ? $save_img_path : '' ) . 'ajax/db.php?fl=savePhoto' . ( isset( $product_provider_id ) ? "&product_provider_id=" . $product_provider_id : "" ); ?>' );" class="no_visible">

			            </div>
			            <div class="col-4">
			                
			                <img <?php
									echo ( isset($row['tmp_id'] ) && $row['image_3'] != '' ?  "src=\"{$home_path}files/packs_img_tmp/{$row['image_3']}\"" : "src=\"{$home_path}img/frames/frontal.png\"" );
								?> id="previous_img_3" width="100%" 
								onclick="set_global_photo_render( 3, 'frontal.png', '20%' )"
							>
			                <p align="center" style="color : blue; font-size : 90%;">Lateral</p>
			               	<button 
			               		type="button"
			               		class="btn btn-info form-control"
			               		onclick="upload_pack_img( 3 );"
			               	>
			               		Subir imágen
			               	</button>
			                <input type="file" id="pack_photo_file_3" onchange="previewImage( 3, '<?php echo ( isset( $save_img_path ) ? $save_img_path : '' ) . 'ajax/db.php?fl=savePhoto' . ( isset( $product_provider_id ) ? "&product_provider_id=" . $product_provider_id : "" ); ?>' );" class="no_visible">
			            </div>
			        </div>
			    </div>
			</div><!-- fin div fotos-->
		</div>
	</div>
	
	<hr>
	
	<h5>
		<input type="checkbox" id="no_piece_measures" 
			<?php
				echo ( ( isset($row['tmp_id'] ) && 
						( $row['piece_lenght'] != 0 || $row['piece_width'] != 0 || $row['piece_height'] != 0
							|| $row['piece_weight'] != 0 ) ) || !isset($row['tmp_id'] ) ? ' checked' : '' );
			?> 
			onclick="disabled_enabled_piece_measures();"> 
		Medidas de la pieza
	</h5>
	<div class="row" id="piece_measures_container" style="font-size:90%;">
		<div class="col-3">
			<input type="number"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['piece_lenght'] != 0 ?  " value=\"{$row['piece_lenght']}\"" : '' );
			?>  
				 id="piece_lenght" class="form-control">
			<label class="measures_label">Largo (cm)</label>
		</div>
		<div class="col-3">
			<input type="number"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['piece_width'] != 0 ?  " value=\"{$row['piece_width']}\"" : '' );
			?>  
				 id="piece_width" class="form-control">
			<label class="measures_label">Ancho (cm)</label>
		</div>
		<div class="col-3">
			<input type="number"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['piece_height'] != 0 ?  " value=\"{$row['piece_height']}\"" : '' );
			?>  
				 id="piece_height" class="form-control">
			<label class="measures_label">Alto (cm)</label>
		</div>
		<div class="col-3">
			<input type="number"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['piece_weight'] != 0 ?  " value=\"{$row['piece_weight']}\"" : '' );
			?>  
				 id="piece_weight" class="form-control">
			<label class="measures_label">Peso (kg)</label>
		</div>
	</div>

	<br>

	<div class="row">
		<div class="col-1"></div>
		<div class="col-5">
			<button class="btn btn-success form-control" 
			<?php
				if( !isset( $product_provider_id ) ){
					echo ' onclick="save_measures( ' . ( isset($row['tmp_id'] ) ?  $row['tmp_id'] : '' ) . ');"';
				}else{
					echo " onclick=\"save_p_p_meassures({$product_provider_id});\"";
				}
			?>
			>
			 
				Guardar
			</button>
		</div>	

		<div class="col-5">
			<button class="btn btn-danger form-control" id="btn_close_meassures_form" 
			<?php
				if( !isset( $type ) ){
					echo 'onclick="if( localStream != 1 ){ open_camera(0); } setTimeout( function(){ close_emergent(); }, 300);"';	
				}else{//if( localStream != 1 ){ open_camera(0); } 
					echo 'onclick="if( localStream != 1 ){ open_camera(0); } setTimeout( function(){ close_emergent_3(); }, 300);"';
				}
			?>
				
			>
				Cerrar
			</button>
		</div>
		<div class="col-1"></div>
	</div>
</div>
<script type="text/javascript">
/*funciones para ocultar / mostrar los formularios de medidas*/
	var global_box_measures = 1;
	function disabled_enabled_box_measures(){
		if( ! $( '#no_box_measures' ).prop( 'checked' ) ){
			$( '#box_measures_container' ).css( 'display' , 'none' );
			global_box_measures = 0;
			return false;
		}else{
			$( '#box_measures_container' ).css( 'display' , 'flex' );
			global_box_measures = 1;
			return false;
		}
	}
	var global_pack_measures = 1;
	function disabled_enabled_pack_measures(){
		if( ! $( '#no_pack_measures' ).prop( 'checked' ) ){
			$( '#takePhotoContainer' ).css( 'display' , 'none' );
			global_pack_measures = 0;
			 $( '#no_pack_measures_photos' ).removeAttr( 'checked' );
			disabled_enabled_pack_photos();
			return false;
		}else{
			$( '#takePhotoContainer' ).css( 'display' , 'flex' );
			global_pack_measures = 1; 
			$( '#no_pack_measures_photos' ).prop( 'checked', true );
			disabled_enabled_pack_photos();
			return false;
		}
	}

	var global_pack_photos = 1;
	function disabled_enabled_pack_photos(){
		if( ! $( '#no_pack_measures_photos' ).prop( 'checked' ) ){
			$( '#photos_measures' ).css( 'display' , 'none' );
			global_pack_photos = 0;
			return false;
		}else{
			$( '#photos_measures' ).css( 'display' , 'flex' );
			global_pack_photos = 1;
			return false;
		}
	}

	var global_piece_measures = 1;
	function disabled_enabled_piece_measures(){
		if( ! $( '#no_piece_measures' ).prop( 'checked' ) ){
			$( '#piece_measures_container' ).css( 'display' , 'none' );
			global_piece_measures = 0;
			return false;
		}else{
			$( '#piece_measures_container' ).css( 'display' , 'flex' );
			global_piece_measures = 1;
			return false;
		}
	}
	/*guardar medidas del formulario*/
	function save_p_p_meassures( product_provider_id, counter ){
		var box_measure_lenght = 0, box_measure_width = 0, box_measure_height = 0,
			pack_photo_1 = '', pack_photo_2 = '', pack_photo_3 = '', bag_type_id = 0,
			pack_measure_lenght = 0, pack_measure_width = 0, pack_measure_height = 0,
			piece_measure_lenght = 0, piece_measure_width = 0, piece_measure_height = 0, piece_measure_weight = 0;
		var url =  global_product_provider_path + 'ajax/getProductProvider.php?fl=saveMeasures&product_id=' + $( '#product_id' ).val();
			url += '&product_provider_id=' + product_provider_id;
			/*url += '&home_path=' + global_meassures_home_path;
			url += '&include_jquery=' + global_meassures_include_jquery;
			url += '&path_camera_plugin=' + global_meassures_path_camera_plugin;*/
	//caja
		if( $( '#no_box_measures' ).prop( 'checked' ) ){
			box_measure_lenght = $( '#box_lenght' ).val();
			if( box_measure_lenght <= 0 ){
				alert( "El largo de la caja no puede ir vacío!" );
				$( '#box_lenght' ).focus();
				return false;
			}else{
				url += '&box_lenght=' + box_measure_lenght;
			}

			box_measure_width = $( '#box_width' ).val();
			if( box_measure_width <= 0 ){
				alert( "El ancho de la caja no puede ir vacío!" );
				$( '#box_width' ).focus();
				return false;
			}else{
				url += '&box_width=' + box_measure_width;
			}

			box_measure_height = $( '#box_height' ).val();
			if( box_measure_height <= 0 ){
				alert( "El alto de la caja no puede ir vacío!" );
				$( '#box_height' ).focus();
				return false;
			}else{
				url += '&box_height=' + box_measure_height;
			}
		}

	//aplica paquete
		if( $( '#no_pack_measures' ).prop( 'checked' ) ){
			pack_measure_lenght = $( '#pack_lenght' ).val();
			if( pack_measure_lenght <= 0 ){
				alert( "El largo del paquete no puede ir vacío!" );
				$( '#pack_lenght' ).focus();
				return false;
			}else{
				url += '&pack_lenght=' + pack_measure_lenght;
			}

			pack_measure_width = $( '#pack_width' ).val();
			if( pack_measure_width <= 0 ){
				alert( "El ancho del paquete no puede ir vacío!" );
				$( '#pack_width' ).focus();
				return false;
			}else{
				url += '&pack_width=' + pack_measure_width;
			}

			pack_measure_height = $( '#pack_height' ).val();
			if( pack_measure_height <= 0 ){
				alert( "El alto del paquete no puede ir vacío!" );
				$( '#pack_height' ).focus();
				return false;
			}else{
				url += '&pack_height=' + pack_measure_height;
			}
			bag_type_id = $( '#pack_bag' ).val();
			if( bag_type_id <= 0 ){
				alert( "El tipo de bolsa no puede ir vacío!" );
				$( '#pack_bag' ).focus();
				return false;
			}else{
				url += '&bag_type=' + bag_type_id;
			}
			
		//imágenes
			pack_photo_1 = $( '#previous_img_1' ).attr( 'src' );
			if( pack_photo_1 == '' || pack_photo_1 == '../../../img/frames/camera_icon.jpeg' ){
				alert( "La fotografía de caja abierta no puede ir vacía!" );
				$( '#previous_img_1' ).click();
				return false;
			}else{
				url += '&photo_1=' + pack_photo_1.replace( '../../../files/packs_img/', '' );
			}
			
			pack_photo_2 = $( '#previous_img_2' ).attr( 'src' );
			if( pack_photo_2 == '' || pack_photo_2 == '../../../img/frames/camera_icon.jpeg' ){
				alert( "La fotografía frontal no puede ir vacía!" );
				$( '#previous_img_2' ).click();
				return false;
			}else{
				url += '&photo_2=' + pack_photo_2.replace( '../../../files/packs_img/', '' );
			}
			
			pack_photo_3 = $( '#previous_img_3' ).attr( 'src' );
			if( pack_photo_3 == '' || pack_photo_3 == '../../../img/frames/camera_icon.jpeg' ){
				alert( "La fotografía del ancho no puede ir vacía!" );
				$( '#previous_img_3' ).click();
				return false;
			}else{
				url += '&photo_3=' + pack_photo_3.replace( '../../../files/packs_img/', '' );
			}
		}

	//pieza
		if( $( '#no_piece_measures' ).prop( 'checked' ) ){
			piece_measure_lenght = $( '#piece_lenght' ).val();
			if( piece_measure_lenght <= 0 ){
				alert( "El largo de la pieza no puede ir vacío!" );
				$( '#piece_lenght' ).focus();
				return false;
			}else{
				url += '&piece_lenght=' + piece_measure_lenght;
			}

			piece_measure_width = $( '#piece_width' ).val();
			if( piece_measure_width <= 0 ){
				alert( "El ancho de la pieza no puede ir vacío!" );
				$( '#piece_width' ).focus();
				return false;
			}else{
				url += '&piece_width=' + piece_measure_width;
			}

			piece_measure_height = $( '#piece_height' ).val();
			if( piece_measure_height <= 0 ){
				alert( "El alto de la pieza no puede ir vacío!" );
				$( '#piece_height' ).focus();
				return false;
			}else{
				url += '&piece_height=' + piece_measure_height;
			}

			piece_measure_weight = $( '#piece_weight' ).val();
			if( piece_measure_weight <= 0 ){
				url += '&piece_weight=0';
			}else{
				url += '&piece_weight=' + piece_measure_weight;
			}
		}
		if( ! $( '#no_box_measures' ).prop( 'checked' ) 
			&& ! $( '#no_pack_measures' ).prop( 'checked' )
			&& ! $( '#no_piece_measures' ).prop( 'checked' )  ){
			alert( "Debe elegir al menos ua categoría para guardar medidas( Caja, Paquete o Pieza )" );
			return false;
		}
		//alert( url ); //return false;
		var response = ajaxR( url );
		var aux = response.split( '|' );
		if( aux[0] != 'ok' ){
			alert( response );
		}else{
			$( '#measure_tmp_id' ).val( aux[1] );
			var resp = "<div class=\"row\"><div class=\"col-2\"></div>";
				resp += "<div class=\"col-8\">";
					resp += "<h5 style=\"color : green\">Las medidas fueron guardas exitosamente!</h5>";
					resp += "<button onclick=\"close_emergent();\" class=\"btn btn-success form-control\">";
						resp += "<i class=\"icon-ok-circle\">Aceptar y cerrar</i>";
					resp += "</button>";
				resp += "</div>"
			resp += "</div>";
			$( '.emergent_content' ).html( resp );
			$( '.emergent' ).css( 'display', 'block' );
			$( '.emergent_content' ).focus();
			setTimeout( function(){close_emergent_3(); }, 100 );
			show_measures( product_provider_id, null );
		}
	}

	function upload_pack_img( number ){
		$( '#pack_photo_file_' + number ).click();
		open_camera( 0 );
	}
	function previewImage( number, path_save = '' ){
		var formData = new FormData();
        var files = document.getElementById('pack_photo_file_' + number ).files[0];
        formData.append('file',files);
        //alert( global_meassures_home_path );
        $.ajax({
            url: path_save + "&home_path=" + global_meassures_home_path + "&type=" + global_save_meassure_type,
            type: 'post',
            data: formData,
            contentType: false,
            processData: false,
            success: function( response ) {
            	//alert( response );
				response = response.replace( '/files/files', '/files' );
				$( '#previous_img_' + number ).attr( 'src', response );
                /*if (response != 0) {
                    $(".card-img-top").attr("src", response);
                } else {
                    alert('Formato de imagen incorrecto.');
                }*/
            }
        });
        return false;
	}
	/*function previewImage( number, path_save = '' ) {   //saveImageToServer  
		//alert( number );
	    var reader = new FileReader();         
	    reader.readAsDataURL(document.getElementById( 'pack_photo_file_' + number ).files[0]);         
	    reader.onload = function (e) {     
	        document.getElementById( 'previous_img_' + number ).src = e.target.result;         
	    }     
	}*/
</script>
<style type="text/css">
	/*.measures_label{
		font-size: 60%;
		color: red;
	}
	#options_buttons{
		margin-top: 15% !important;
	}*/
/*responsive para tableta de 600px de ancho hacia arriba*
	@media only screen and (min-width: 600px){/*and (max-width: 800px) *

        #video_container{
            max-height: 50% !important;
            width: 80%;
        	left: 10%;
        	top : 0;
        	position: relative;
        } 

        #frame{
        	/*border : 1px solid;*
            top: 90%;
          /*  left: 5%;*
        	width: 75%;
        	left: 12.5%;
        	position: relative;
        } 

        #video{
           /* max-height: 700px;    * 
           /* width: 100% !important;*
            height: 50%;
        }

        #options_buttons{
            padding-top : 20% !important;
        }

    }
/*responsive para celular de 600px*
	@media only screen and (min-width: 300px){/*and (max-width: 800px) *

        #video_container{
            max-height: 60% !important;
            overflow: hidden;
            width: 80%;
        	left: 10%;
        	top : 0;
        	position: relative;
        	background-color: red;
        } 

        #frame{
        	/*border : 1px solid;*
            top: 90%;
          /*  left: 5%;*
        	width: 70%;
        	left: 15%;
        	position: relative;
        } 

        #video{
           /* max-height: 700px;    * 
            width: 100% !important;
            height: 60% !important;
        }

        #options_buttons{
            padding-top : 20% !important;
        }

    }*/

</style>
<script> 
//alert("La resolución de tu pantalla es: " + screen.width + " x " + screen.height) 
</script>