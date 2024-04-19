<?php
	//echo "here\\'";
	include('../../../../conectMin.php');
	include('../../../../conexionMysqli.php');
	include('ajax/scriptVersioner.php');
	$sV = new scriptVersioner( $link );
	$current_configuration = $sV->getVersionerConfig();
	$sql = "SELECT 
				vr.id_rama AS branch_id,
				vr.nombre AS branch_name
			FROM versionador_configuracion vc
			LEFT JOIN versionador_ramas vr
			ON vc.id_rama_versionador = vr.id_rama";
	$stm = $link->query( $sql ) or die( "Error al consultar la rama actual de este versionador : {$link->error}" );
	$configuration = $stm->fetch_assoc();
//	var_dump( $current_configuration );
	//include('ajax/makeList.php');
//obtiene la configuracion

?>
<!DOCTYPE html>
<html>
<head>
	<title>Versionamiento MySQL</title>
<!-- JQuery -->
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<!-- Estilos -->
	<link rel="stylesheet" type="text/css" href="../../../../css/gridSW_l.css"/>
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
<!-- bootstrap -->
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.min.css"/>
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- funciones JS de la página
	<script type="text/javascript" src="js/utils.js"></script-->
</head>
<body>
	<div id="emergent">
		<!--div class="row">
			<button class="btn btn-danger">X</button>
		</div-->
		<div id="emergent_content">

		</div>
	</div>
	<div class="row" style="padding:10px !important;">
		<div class="col-12 text-center btn-info">
			<h4>Rama actual : <b class="green" id="branch_name"><?php echo $configuration['branch_name']; ?></b>
				<button
					class="btn"
					onclick="show_versioner_config();"
				>
					<i class="icon-cog"></i>
				</button>
			</h4>
			<input type="hidden" id="branch_id" value="<?php echo $configuration['branch_id']; ?>">
		</div>
		<div class="col-12 row" id="">
			<!--div class="col-1">
				<h5 class="icon-flow-tree"></h5>
			</div>
			<div id="branches_container"-->
			<div class="col-3">
				<label class="row branch" for="branch_1" id="branch_label_1">
					<div class="col-10">
						<i class="icon-flow-branch" id="branch_name_1">Desarrollo</i>
					</div>
					<div class="col-2">	
						<i class="icon-tools" onclick="show_branch_settings( 1 );"></i>
						<input type="radio" id="branch_1" name="branch" class="hidden" onchange="change_branch( 1 );">
					</div>
				</label>
			</div>
			<div class="col-3">
				<label class="row branch" for="branch_2" id="branch_label_2">
						<input type="radio" id="branch_2" name="branch" class="hidden" onchange="change_branch( 2 );">
					<div class="col-10">
						<i class="icon-flow-branch" id="branch_name_2">Pruebas</i>
					</div>
					<div class="col-2">	
						<i class="icon-tools" onclick="show_branch_settings( 2 );"></i>
					</div>
				</label>
			</div>
			<div class="col-3">
				<label class="row branch" for="branch_3" id="branch_label_3">
					<div class="col-10">
						<i class="icon-flow-branch" id="branch_name_3">Produccion</i>
					</div>
					<div class="col-2">	
						<i class="icon-tools" onclick="show_branch_settings( 3 );"></i>
						<input type="radio" id="branch_3" name="branch" class="hidden" onchange="change_branch( 3 );">
					</div>
				</label>
			</div>
			<div class="col-2">
				<button 
					class="btn btn-success"
					onclick="show_script_form();"
				>
					<i class="icon-plus">Agregar Script</i>
				</button>

				<button 
					class="btn btn-success"
					onclick="show_pull_form();"
				>
					<i class="icon-plus">Hacer pull</i>
				</button>
			</div>
			<!--/div-->
			<!--div class="row scripts_containers">
				<div class="row">
					<div class="col-10">
						<h5 class="icon-code text-warning">Por ejecutar</h5>
					</div>
					<div class="col-2 text-center">
						<button class="btn text-primary" onclick="pull( 1 );">
							<i class="icon-flow-cross"></i>
						</button>
					</div>
					<div class="col-12">
						<div id="to_upload_scripts_container"></div>
					</div>
				</div>
				<div class="row">	
					<div class="col-10">
						<h5 class="icon-code text-success">Exitosos</h5>
					</div>
					<div class="col-2 text-center">
						<button class="btn text-primary">
							<i class="icon-flow-cross"></i>
						</button>
					</div>
					<div class="col-12">
						<div id="uploaded_scripts_container"></div>
					</div>
				</div>
				<div class="row">
					<div class="col-10">
						<h5 class="icon-code text-danger">Erroneos</h5>
					</div>
					<div class="col-2 text-center">
						<button class="btn text-primary">
							<i class="icon-flow-cross"></i>
						</button>
					</div>
					<div class="col-12">
						<div id="errors_scripts_container"></div>
					</div>
				</div>
			</div-->
		</div>
		<div class="row scripts_list_container">
			<div class="col-1"></div>
			<div class="col-10">
				<table class="table table-bordered table-striped">
					<thead class="branch_scripts_list_header">
						<tr>
							<th>ID</th>
							<th>Descripcion</th>
							<th>Rama Creacion</th>
						</tr>
					</thead>
					<tbody id="branch_scripts_list">

					</tbody>
				</table>
			</div>
		</div>
	</div>

</body>
</html>
<script type="text/javascript">
var current_branch_id = null;
/*scripts*/
	function save_script(){
		var code = $( '#script_box' ).val().trim();
		var comment = $( '#script_description' ).val().trim();
		var execute = ( $( '#excecute_script' ).prop( 'checked' ) == true ? 1 : 0 );
		//alert( execute ); return false;
		$.ajax({
			type : 'post',
			url : 'ajax/scriptVersioner.php',
			data : { scriptVersioner_fl : 'setScript', 
					code : code, 
					comment : comment, 
					branch_id : current_branch_id,
					branch_name : $( branch_name ).html().trim().toLowerCase(),
					execute_script : execute },
			success : function( dat ){
				alert( dat );
				$( '#script_description' ).val( '' );
				$( '#script_box' ).val( '' );
				get_scripts( current_branch_id );
				close_emergent();

			}
		});
	}
	function update_script( script_id ){
		var code = $( '#script_box' ).val().trim();
		var comment = $( '#script_description' ).val().trim();
		$.ajax({
			type : 'post',
			url : 'ajax/scriptVersioner.php',
			data : { scriptVersioner_fl : 'updateScript', 
					code : code, 
					comment : comment, 
					script_id : script_id,
					branch_name : $( branch_name ).html().trim().toLowerCase() },
			success : function( dat ){
				var aux = dat.split( '|' );
				if( aux[0] != 'ok' ){
					alert( aux );
				}else{
					$( '#script_description' ).val( '' );
					$( '#script_box' ).val( '' );
					$( '#save_btn' ).attr( 'onclick', 'save_script();' );
					get_scripts( current_branch_id );
					close_emergent();
				}
			}
		});
	}
/*ramas*/
	function change_branch( branch_id ){
		current_branch_id = branch_id;
		$( '.branch' ).children( 'div' ).children( 'i' ).removeClass( 'green' );
		$( '#branch_label_' + branch_id ).children( 'div' ).children( 'i' ).addClass( 'green' );
		get_scripts( current_branch_id );
	}

	function show_branch_settings( branch_id ){
		$.ajax({
			type : 'post',
			url : 'components/branch_config.php',
			data : { branch_id : branch_id,
					branch_name : $( branch_name ).html().trim().toLowerCase() },
			success : function( dat ){
				$( '#emergent_content' ).html( dat );
				$( '#emergent' ).css( 'display', 'block' );	
			}
		});
	}
	function close_emergent( type ){
		$( '#emergent_content' ).html( '' );
		$( '#emergent' ).css( 'display', 'none' );
	}
/*status / scripts*/
	function get_scripts( branch_id ){
		// branch_name = '';//$( branch_name ).html().trim().toLowerCase()
		var branch_name = $( '#branch_name_' + branch_id ).html().trim().toLowerCase();
		//alert( branch_name );
		$.ajax({
			type : 'post',
			url : 'ajax/scriptVersioner.php',
			data : { scriptVersioner_fl : 'getScriptList', 
					branch_id : branch_id,
					branch_name : branch_name },
			success : function( dat ){
				//console.log( dat );
				var scripts_content = buildScriptList( JSON.parse( dat ) );
				$( '#branch_scripts_list' ).html( scripts_content );
			}
		});
	}

	function buildScriptList( scripts, checkbox = false ){
		var resp = ``;
		var onclick = ``;
		for ( var i in scripts ) {
			if( checkbox == false ){
				onclick = `show_script_detail( ${scripts[i]['script_id']} );`;
			}else{
				//onclick = `document.getElementById('script_to_update_${i}').click();`;
			}
			resp += `<tr onclick="${onclick}">
				<td class="">
					${scripts[i]['script_id']}
				</td>
				<td class="">
					${scripts[i]['description']}
				</td>
				<td class="text-center">`;
			if( checkbox != false ){
				resp += `<input type="checkbox" id="script_to_update_${i}" 
							value="${scripts[i]['script_id']}" 
							onclick="scripts_order_evaluation( ${i} );">`;
			}else{
				resp += `${scripts[i]['creation_date']}`;
			}
			resp +=	`</td>
			</tr>`;
    	}
    	return resp;
	}

	function show_script_detail( script_id ){
		$.ajax({
			type : 'post',
			url : 'ajax/scriptVersioner.php',
			data : { scriptVersioner_fl : 'getScript', 
			script_id : script_id,
			branch_name : $( branch_name ).html().trim().toLowerCase() },
			success : function( dat ){
				var scripts_content = JSON.parse( dat );
				show_script_form( scripts_content['code'], scripts_content['description'], script_id );
			}
		});
	}
	function show_script_form( code = null, description = null, script_id = null ){
		$.ajax({
			type : 'post',
			url : 'components/script_form.php',
			data : { comment : description,
					code : code
			},
			success : function( dat ){
		
				$( '#emergent_content' ).html( dat );
				$( '#emergent' ).css( 'display', 'block' );
				if( script_id != null ){
					$( '#save_btn' ).attr( 'onclick', 'update_script(' + script_id + ');' );
				}
			}
		});
	}

	function show_pull_form(){
		$.ajax({
			type : 'post',
			url : 'components/pull_form.php',
			//data : { },
			success : function( dat ){
				$( '#emergent_content' ).html( dat );
				$( '#emergent' ).css( 'display', 'block' );
			}
		});
	}

//configuracion de ramas
	function show_versioner_config(){
		$.ajax({
			type : 'post',
			url : 'components/versioner_config_form.php',
			//data : { scriptVersioner_fl : 'getScript', script_id : script_id },
			success : function( dat ){
				//alert( dat );
				$( '#emergent_content' ).html( dat );
				$( '#emergent' ).css( 'display', 'block' );	
			}
		});
	}

</script>

<script type="text/javascript">
	<?php
		if( $configuration != null ){
			echo "\$( '#branch_label_{$configuration['branch_id']}' ).click();";
		}
	?>
</script>
<style type="text/css">
	*{

    font-family: sans-serif;
    font-size: 95%;
	}
/*demergente*/
	#emergent{
		position : fixed;
		z-index: 100;
		width: 100%;
		height: 100%;
		background-color: rgba( 0,0,0,.5 );
		display : none;
		padding : 20px;
	}

	#emergent_content{
		position : relative;
		z-index: 100;
		width: 90%;
		left: 5%;
		height: 90%;
		top: 5%;
		background-color: white;
		overflow: auto;
		padding : 20px;
	}
	.script_label{
		box-shadow: 1px 1px 5px rgba( 0,0,0,.3 );
	}
	#script_box{
		height: 300px;
		font-size: 90% !important;
	}
	.branch{
		padding: 5px;
		text-align: left !important;
		box-shadow: 1px 1px 5px silver;
	}
	.hidden{
		display: none;
	}
	.orange{
		color: orange;
	}	
	.green{
		color: green;
	}
	.scripts_containers{
		position: relative;
		max-height: 600px;
		max-height: 600px !important;
		overflow: auto;
	}
	.scripts_list_container{
		max-height : 550px;
		overflow : scroll;
	}
	.branch_scripts_list_header{
		position : sticky;
		top : 0;
		background-color : white;
	}
</style>
