<?php
	include( '../../../../conexionMysqli.php' );
//validacion de token
	if( !isset( $_GET['token'] ) ){
		die( "<h3 class=\"text-center\">No hay token, pide ayuda en la tienda donde realizaste la compra!</h3>" );
	}else{
		$sql = "SELECT 
					IF( NOW() < fecha_hora_vencimiento, 'is_valid', 'is_invalid' ) AS token_status
				FROM vf_tokens_alta_clientes
				WHERE token = '{$_GET['token']}'";
		$stm = $link->query( $sql ) or die( "Error al consultar si el token es valido!" );
		if( $stm->num_rows <= 0 ){
			die( "<h3 class=\"text-center\">El token es invalido, pide ayuda en la tienda donde realizaste la compra!</h3>" );
		}else{
			$row = $stm->fetch_assoc();
			if( $row['token_status'] == 'is_invalid' ){
			die( "<h3 class=\"text-center\">El token esta vencido, pide ayuda en la tienda donde realizaste la compra!</h3>" );
			}
		}
		echo "<input type=\"hidden\" id=\"current_token\" value=\"{$_GET['token']}\">";
	}
	$sql = "SELECT
				UPPER( nombre ) AS name
			FROM sys_estados";
	$stm = $link->query( $sql ) or die( "Error al consultar los estados : {$link->error}" );
	$states = "<option value=\"0\">-- Seleccionar --</option>";
	while( $row = $stm->fetch_assoc() ){
		$states .= "<option value=\"{$row['name']}\">{$row['name']}</option>";
	}

	$sql = "SELECT
				clave AS clue,
				nombre AS name
			FROM vf_cfdi";
	$stm = $link->query( $sql ) or die( "Error al consultar los cfdis : {$link->error}" );
	$cfdis = "<option value=\"0\">-- Seleccionar --</option>";
	while( $row = $stm->fetch_assoc() ){
		$cfdis .= "<option value=\"{$row['clue']}\">{$row['name']}</option>";
	}

	$sql = "SELECT
				clave_numerica AS clue,
				nombre_tipo_regimen_fiscal AS name
			FROM vf_tipos_regimenes_fiscales";
	$stm = $link->query( $sql ) or die( "Error al consultar los cfdis : {$link->error}" );
	$regimes = "<option value=\"0\">-- Seleccionar --</option>";
	while( $row = $stm->fetch_assoc() ){
		$regimes .= "<option value=\"{$row['clue']}\">{$row['name']}</option>";
	}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generador de URLs con QR</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--link rel="stylesheet" rel="preload" as="style" onload="this.rel='stylesheet';this.onload=null" href="library/milligram.min.css">
<link rel="stylesheet" href="library/modalStyle.css"-->
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>

	<div class="emergent">
		<div class="row">
			<div class="col-12 emergent_content" tabindex="1">
				<h2 class="icon-ok-circled text-success">Token valido</h2>
				<h2 class="text-warning icon-warning text-center"><i></i>Importante</h2>
				<ul>
					<li class="icon-right-big" style="list-style : none; padding : 10px;">Este token caduca en "X" tiempo</li>
					<li class="icon-right-big" style="list-style : none; padding : 10px;">Verifica bien tus datos antes de guardarlo, ya que de lo contrario no podremos facturar tu(s) compra(s)</li>
					<li class="icon-right-big" style="list-style : none; padding : 10px;">Captura un telefono en el que te podamos localizar de manera inmediata en caso de que necesitemos la corrección de algun dato para tu factura</li>
				</ul>
				<br><br>
				<div class="row">
					<div class="column"></div>
					<div class="column text-center">
						<button
							class="btn btn-success form-control"
							onclick="close_emergent();"
						>
							<i class="icon-ok-circle">Aceptar y continuar</i>
						</button>
						<br><br>
					</div>
				</div>
			</div>
		</div>
	</div>

	<h2 class="text-center bg-primary text-light" 
		style="position : sticky; top :3px !important; padding : 10px;">Alta de clientes</h2>
	<div class="row text-center" style="padding : 20px;">
		<label class="text-start">Buscador por RFC</label>
		<div class="input-group">
			<input type="text" id="rfc_seeker" class="form-control">
			<button
				class="btn btn-primary"
				onclick="check_if_exists_costumer( 'intro' );"
			>
				<i class="icon-search"></i>
			</button>
		</div>
		<div id="social_reason_container" class="row"></div>
		<div class="row">
		<!--accordion-->
			<div class="row text-center">
				<button
					class="btn btn-success"
					onclick="add_contact_form();"
				>
					<i class="icon-plus">Agregar contacto</i>
				</button>
			</div>
			<div id="accordion">
			</div>
		<!-- fin de acordion -->
		</div>
		<!--div class="col-sm-6">
			Nombre de Contacto<span class="text-danger">*</span>
			<input type="text" id="costumer_name_input" class="form-control" onblur="changeToUpperCase( this );">
		</div>
		<div class="col-sm-6">
			Telefono <span class="text-danger">*</span>
			<input type="number" id="telephone_input" class="form-control">
		</div>
		<div class="col-sm-6">
			Celular <span class="text-danger">*</span>
			<input type="number" id="cellphone_input" class="form-control">
		</div>
		<div class="col-sm-6">
			Correo <span class="text-danger">*</span>
			<input type="email" id="email_input" class="form-control">
		</div>
		<div class="col-sm-6">
			Uso CFDI <span class="text-danger">*</span>
			<select class="form-select" id="cfdi_input">
			<?php
				//echo $cfdis;
			?>
			</select>
		<br>
		</div-->
		<hr>
		<h2>Razon Social</h2>
		<hr>
		<div class="row">
			<!--button
				class="btn btn-info"
				onclick="enable_scann_camera();"
			>
				<i class="icon-qrcode">Escanear Cedula Fiscal</i>
			</button-->
			<?php
				include( 'reader.php' );
			?>
		</div>	
		<div class="col-sm-6">
			RFC <span class="text-danger">*</span>
			<input type="text" id="rfc_input" class="form-control" onblur="changeToUpperCase( this );">
		</div>
		<div class="col-sm-6">
			Nombre / Razon Social <span class="text-danger">*</span>
			<input type="text" id="name_input" class="form-control" onblur="changeToUpperCase( this );">
		</div>
		<div class="col-sm-6">
			Tipo Persona <span class="text-danger">*</span>
			<select class="form-select" id="person_type_combo">
				<option>--Seleccionar --</option>
				<option label="Sin Tipo Persona" value="1">Sin Tipo Persona</option>
				<option label="Persona Fisica" value="2">Persona Fisica</option>
				<option label="Persona Moral" value="3">Persona Moral</option>
			</select>
		</div>
		<div class="col-sm-6">
			Cedula fiscal :<br>
			<!--input type="checkbox">
			<input type="file">
			<button 
				class="btn btn-warning"
			>
				<i class="icon-file-image"></i>
			</button-->
		</div>

		<!--div class="col-sm-6">
			Razon Social<span class="text-danger">*</span>
			<input type="text" class="form-control">
		</div-->
		<div class="col-sm-6">
			Calle <span class="text-danger">*</span>
			<input type="text" id="street_name_input" class="form-control">
		</div>
		<div class="col-sm-3">
			# int : 
			<input type="text" id="internal_number_input" class="form-control">
		</div>
		<div class="col-sm-3">
			# ext : 
			<input type="text" id="external_number_input" class="form-control">
		</div>

		<div class="col-sm-6">
			Colonia <span class="text-danger">*</span>
			<input type="text" id="cologne_input" class="form-control">
		</div>

		<div class="col-sm-6">
			Delegacion / Municipio <span class="text-danger">*</span>
			<input type="text" id="municipality_input" class="form-control">
		</div>

		<div class="col-sm-6">
			C.P. <span class="text-danger">*</span>
			<input type="text" id="postal_code_input" class="form-control">
		</div>
		<!--div class="col-sm-6">
			Localidad
			<input type="text" id="location_input" class="form-control">
		</div>
		<div class="col-sm-6">
			Referencia
			<input type="text" id="reference_input" class="form-control">
		</div-->
		<div class="col-sm-6">
			Pais <span class="text-danger">*</span>
			<select id="country_combo" class="form-select">
				<option value="Mexico">México</option>
			</select>
		</div>
		<div class="col-sm-6">
			Estado <span class="text-danger">*</span>
			<select id="state_combo" class="form-select">
				<?php
					echo $states;
				?>
			</select>
		</div>
		<div class="col-sm-6">
			Regimen Fiscal <span class="text-danger">*</span>
			<select id="regime_input" class="form-select">
				<?php
					echo $regimes;
				?>
			</select>
			<br><br>
			<br><br>
		</div>
	</div>
	<div class="row text-center bg-primary" style="text-align : center;position : fixed; bottom : 0; width : 100%; left : 0; padding : 10px;">
		<div class="col-2"></div>
		<div class="col-8 text-center" style="text-align : center !important;">
			<button
				type="button"
				class="btn btn-success form-control"
				onclick="save_costumer();"
			>
				<i class="icon-floppy">Guardar</i>
			</button>
		</div>
	</div>
</body>
</html>

<script type="text/javascript">
	function changeToUpperCase( obj ){
		$( obj ).val( $( obj ).val().toUpperCase() );
	}

	function save_costumer(){
		var costumer_contacts = "";
		var costumer_name, rfc, name, cellphone, telephone, email, person_type, street_name,
			internal_number, external_number, cologne, municipality, 
			postal_code, location, reference, country, state;

	//obtener datos de contacto
		$( '.card' ).each( function( index ){
			costumer_contacts += ( costumer_contacts == "" ? "" : "|~|" );
			costumer_contacts += $( '#costumer_name_input_' + index ).val() + "~";//nombre
			costumer_contacts += $( '#telephone_input_' + index ).val() + "~";//telefono
			costumer_contacts += $( '#cellphone_input_' + index ).val() + "~";//celular
			costumer_contacts += $( '#email_input_' + index ).val() + "~";//correo
			costumer_contacts += $( '#cfdi_input_' + index ).val();//uso cfdi
		});
		//alert( costumer_contacts ); return false;
		/*costumer_name = $( '#costumer_name_input' ).val();
		if( telephone == '' ){
			alert( "El campo NOMBRE DEL CLIENTE / EMPRESA no puede ir vacío!" );
			$( '#costumer_name_input' ).focus();
			return false;
		}
		cellphone = $( '#cellphone_input' ).val();
		/*if( cellphone == '' ){
			alert( "El campo CELULAR no puede ir vacío!" );
			$( '#cellphone_input' ).focus();
			return false;
		}*/
		/*telephone = $( '#telephone_input' ).val();
		if( telephone == '' ){
			alert( "El campo TELEFONO no puede ir vacío!" );
			$( '#telephone_input' ).focus();
			return false;
		}

		email = $( '#email_input' ).val();
		if( email == '' ){
			alert( "El campo EMAIL no puede ir vacío!" );
			$( '#email_input' ).focus();
			return false;
		}*/

		rfc = $( '#rfc_input' ).val();
		if( rfc == '' ){
			alert( "El campo RFC no puede ir vacío!" );
			$( '#rfc_input' ).focus();
			return false;
		}
		name = $( '#name_input' ).val();
		if( name == '' ){
			alert( "El campo NOMBRE/RAZON SOCIAL no puede ir vacío!" );
			$( '#name_input' ).focus();
			return false;
		}
		person_type = $( '#person_type_combo' ).val();
		if( person_type == '' ){
			alert( "El campo TIPO DE PERSONA no puede ir vacío!" );
			$( '#person_type_combo' ).focus();
			return false;
		}

		street_name = $( '#street_name_input' ).val();	
		if( street_name == '' ){
			alert( "El campo CALLE no puede ir vacío!" );
			$( '#street_name_input' ).focus();
			return false;
		}

		internal_number = $( '#internal_number_input' ).val();	

		external_number = $( '#external_number_input' ).val();		

		cologne = $( '#cologne_input' ).val();	
		if( cologne == '' ){
			alert( "El campo no puede ir vacío!" );
			$( '#cologne_input' ).focus();
			return false;
		}

		municipality = $( '#municipality_input' ).val();	
		if( municipality == '' ){
			alert( "El campo MUNICIPIO/DELEGACIÓN no puede ir vacío!" );
			$( '#municipality_input' ).focus();
			return false;
		}

		postal_code = $( '#postal_code_input' ).val();	
		if( postal_code == '' ){
			alert( "El campo CODIGO POSTAL no puede ir vacío!" );
			$( '#postal_code_input' ).focus();
			return false;
		}
		
		location = $( '#location_input' ).val();	
		if( location == '' ){
			alert( "El campo LOCACIÓN no puede ir vacío!" );
			$( '#location_input' ).focus();
			return false;
		}
		
		reference = ( $( '#reference_input' ).val() == '' ? '' : $( '#reference_input' ).val() );
		
		country = $( '#country_combo' ).val();
		if( country == '' ){
			alert( "El campo PAIS no puede ir vacío!" );
			$( '#country_combo' ).focus();
			return false;
		}
		
		state = $( '#state_combo' ).val();
		if( state == '' ){
			alert( "El campo ESTADO no puede ir vacío!" );
			$( '#state_combo' ).focus();
			return false;
		}
/*cellphone : cellphone
costumer_name : costumer_name,
telephone :telephone,
email : email,*/
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			data : { rfc : rfc,
					name : name,
					person_type : person_type,
					street_name : street_name,
					internal_number : internal_number,
					external_number : external_number,
					cologne : cologne,
					municipality : municipality,
					postal_code : postal_code,
					location : location,
					reference : reference,
					country : country,
					state : state,
					token : $( '#current_token' ).val(),
					costumer_contacts : costumer_contacts
			},
			success : function( dat ){
				if( dat == 'ok' ){
					var content = `<div class="row" style="padding : 10px;">
						<h2 class="">El usuario fue registrado exitosamente!</h2>
						<br><br>
						<button
							class="btn btn-success form-control"
							onclick="location.href='index.php';"
						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
					</div>`;
					$( '.emergent_content' ).html( content );
					$( '.emergent' ).css( 'display', 'block' );
				}else{
					alert( "Error : " + dat );
				}
			}
		});
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}

	function check_if_exists_costumer( e ){
		if( e.keyCode != 13 && e != 'intro' ){
			return false;
		}
		var rfc = $( '#rfc_seeker' ).val();
		if( rfc == "" ){
			alert( "El RFC no puede ir vacio!" );
			$( '#rfc_seeker' ).focus();
			return false;
		}
		var url = "ajax/db.php?costumer_fl=seek_by_rfc&rfc=" + rfc;
		var resp = ajaxR( url ).split( "|" );
		if( resp[0] != 'ok' ){
			console.log( resp[0] );
			alert( "El rfc " + rfc + " no esta registrado!" );
		}else{
			var costumer = JSON.parse( resp[1].trim() );
		//muestra emergente
			$( '.emergent_content' ).html( "Cargando datos..." );
			$( '.emergent' ).css( "display", "block" );
		//obtiene los datos de contacto
			getCostumerDB( costumer );
		}
	}

	function getCostumerDB( costumer ){
		//$( '#costumer_name_input' ).val( costumer.costumer_id );
		$( '#rfc_input' ).val( costumer.rfc );
		$( '#name_input' ).val( costumer.bussines_name );
		$( '#person_type_combo' ).val( costumer.person_type );
		//$( '#' ).val( costumer.delivery_fiscal_certificate );
		//$( '#' ).val( costumer.fiscal_certificate_url );
		$( '#street_name_input' ).val( costumer.street_name );
		$( '#internal_number_input' ).val( costumer.internal_number );
		$( '#external_number_input' ).val( costumer.external_number );
		$( '#cologne_input' ).val( costumer.cologne );
		$( '#municipality_input' ).val( costumer.municipality );
		$( '#postal_code_input' ).val( costumer.postal_code );
		$( '#state_combo' ).val( costumer.state );
		$( '#regime_input' ).val( costumer.tax_regime );
	//carga los datos de contacto
		var contacts = getCostumerContacts( costumer.costumer_id );
		var contacts_view = ``;
		for( var position in contacts ){
			contacts_view += buildCostumerContacts( contacts[position] );
		}
		$( '#accordion' ).empty();
		$( '#accordion' ).html( contacts_view );

		$( '#social_reason_container' ).html( contacts_view );
	//oculta emergente
		$( '.emergent_content' ).html( "" );
		$( '.emergent' ).css( "display", "none" );

	}

	function getCostumerContacts( costumer_id ){
		var url = "ajax/db.php?costumer_fl=getCostumerContacts&costumer_id=" + costumer_id;
		var resp = ajaxR( url ).split( "|" );
		if( resp[0] != 'ok' ){
			alert( "Error al consultar los datos de contacto del cliente : " + resp );
		}else{
			var contacts = JSON.parse( resp[1] );
			return contacts;
		}
	}
	function add_contact_form(  ){
		buildCostumerContacts( null );
	}
	function buildCostumerContacts( contact ){
		var content = ``;
		var position = $( '.card' ).length;
		content += `<div class="card">
		    	<div class="card-header" id="heading${position}">
		      	<h5 class="mb-0">
		       	<button 
		       		class="btn btn-link" 
		       		data-toggle="collapse" 
		       		data-target="#collapse${position}" 
		       		aria-expanded="false" 
		       		aria-controls="collapse${position}"
		       	>
		        ${contact.name} | Correo : ${contact.email}
		        </button>
		      </h5>
		    </div>

		    <div id="collapse${position}" class="collapse" aria-labelledby="heading${position}" data-parent="#accordion">
		      	<div class="card-body">
		      		<div class="col-sm-6">
						Nombre de Contacto<span class="text-danger">*</span>
					<input type="text" id="costumer_name_input_${position}" value="${contact.name}" class="form-control" onblur="changeToUpperCase( this );">
					</div>
					<div class="col-sm-6">
						Telefono <span class="text-danger">*</span>
						<input type="number" id="telephone_input_${position}" value="${contact.telephone}" class="form-control">
					</div>
					<div class="col-sm-6">
						Celular <span class="text-danger">*</span>
						<input type="number" id="cellphone_input_${position}" 
						value="${contact.cellphone}" 
						class="form-control">
					</div>
					<div class="col-sm-6">
						Correo <span class="text-danger">*</span>
						<input type="email" id="email_input_${position}" value="${contact.email}" class="form-control">
					</div>
					<div class="col-sm-6">
						Uso CFDI <span class="text-danger">*</span>
						<select class="form-select" id="cfdi_input_${position}">
						<?php
							//echo $cfdis;
						?>
						</select>
					<br>
					</div>
		        </div>
		    </div>
		  </div>`;
		return content;
	}

	function enable_scann_camera(){
		//$.ajax({
		//	type : "post",
		//	url : "reader.php",
		//	cache : false,
		//	success : function( dat ){
				$( '.emergent_content' ).load( "reader.php" );
				$( '.emergent' ).css( 'display', '' );
		//	}
		//});
	}

</script>