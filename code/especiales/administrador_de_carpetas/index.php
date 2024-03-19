<?php
	include( '../../../conexionMysqli.php' );
	include( '../controladores/SysCarpetas.php' );
	if( isset( $_GET['action_fl'] ) || isset( $_POST['action_fl'] ) ){
		$action = ( isset( $_GET['action_fl'] ) ? $_GET['action_fl'] : $_POST['action_fl'] );
		$SysCarpetas = new SysCarpetas( $link );
		switch ( $action ) {
			case 'createFolder':
				$store_path = ( isset( $_GET['path'] ) ? $_GET['path'] : $_POST['path'] );
				$folder_name = ( isset( $_GET['folder_name'] ) ? $_GET['folder_name'] : $_POST['folder_name'] );
				$SysCarpetas->createFolder( $store_path, $folder_name );
			break;

			case 'eliminarCarpeta' :
				$id_carpeta = ( isset( $_GET['id'] ) ? $_GET['id'] : $_POST['id'] );
				$eliminar = $SysCarpetas->eliminarCarpeta( $id_carpeta );
				die( $eliminar );
			break;

			case 'getFoldersByPath':
				$store_path = ( isset( $_GET['path'] ) ? $_GET['path'] : $_POST['path'] );
				die( $SysCarpetas->getFoldersByPath( $store_path ) );
			break;
			
			default:
				// code...
				break;
		}

		die( 'here' );
	}

	//setAllFolders();
	$SysCarpetas = new SysCarpetas( $link );

	$paths = $SysCarpetas->getStorePathsFolders();
//obtiene modulos
	$modules = json_decode( $SysCarpetas->obtenerModulos() );
	//var_dump( $modules );
	//$Folders->setAllFolders();
?>
<div class="row" style="padding : 20px;">
	<h1>Administrador de carpetas</h1>
	<div class="row">
		
	</div>
	<div class="row">
		<!--div class="col-3">
			<label for="">Path : </label>
			<select id="new_folder_path" class="form-select">
				<option value="cache">cache</option>
				<option value="cache/ticket/">cache/ticket/</option>
			</select>
		</div-->
		<div class="col-3">
			<label for="">Sucursal : </label>
			<select class="form-select" onchange="getFoldersByPath();" id="store_path">
				<option value="">--Seleccionar--</option>
	<?php
		foreach ($paths as $key => $path) {
			echo "<option value=\"{$path['path_name']}\">{$path['path_name']}</option>";
		}
	?>
			</select>

		</div>
		<div class="col-3">
			<label for="">Módulo : </label>
			<select class="form-select" onchange="getFoldersByPath();" id="module_path">
				<option value="">--Seleccionar--</option>
		<?php
			foreach ($modules as $key => $module) {
				echo "<option value=\"{$module->folder}\">{$module->folder}</option>";
			}
		?>
			</select>
		</div>

		<div class="col-3">
			<label for="">Nombre</label>
			<input type="text" id="new_folder_name" class="form-control" style="border: solid 1px gray !important; padding : 6px !important;" disabled>
		</div>
		<div class="col-3">
			<br>
			<button 
				type="button"
				class="btn btn-success"
				onclick="addPrinterFolder();"
			>
				<i class="icon-plus">Agregar</i>
			</button>
		</div>
	</div>
	<div>
		<table class="table table-bordered table-striped">
    		<thead>
    			<tr>
    				<th class="text-center">Nombre de Carpeta</th>
    				<th class="text-center">Eliminar</th>
    			</tr>
    		</thead>
    		<tbody id="path_folders"></tbody>
    	</table>

	</div>
</div>

<script type="text/javascript">
	function addPrinterFolder(){
	//recopila informacion
		var store_path = $( '#store_path' ).val();
		var module_path = "/" + $( '#module_path' ).val();
		if( module_path == '/' || module_path == '/undefined' ){
			module_path = '';
		}
		var folder_name = $( '#new_folder_name' ).val();
		if( folder_name == '' ){
			alert( "El nombre de la nueva carpeta no puede ir vacia!" );
			$( '#new_folder_name' ).focus();
			return false;
		}
		var url = `../especiales/administrador_de_carpetas/index.php?action_fl=createFolder&path=${store_path}${module_path}&folder_name=${folder_name}`;
		//alert(url);return false;
		var resp = ajaxR( url );
		getFoldersByPath();
		alert( resp );
	}
	function getFoldersByPath(){
		var store_path = $( '#store_path' ).val();
		var module_path = "/" + $( '#module_path' ).val();
		if( module_path == '/' || module_path == '/undefined' ){
			module_path = '';
		}
		var url = `../especiales/administrador_de_carpetas/index.php?action_fl=getFoldersByPath&path=${store_path}${module_path}`;
		//alert( url );
		var resp = ajaxR( url );
		$( '#path_folders' ).empty();
		$( '#path_folders' ).html( resp );
		if( ( module_path == undefined || module_path == '' ||module_path == null ) || ( store_path == undefined || store_path == '' ||store_path == null ) ){
			$( '#new_folder_name' ).attr( 'disabled', true );
		}else{
			$( '#new_folder_name' ).removeAttr( 'disabled' );
		}
	}
	function eliminar_carpeta( id ){
		var url = `../especiales/administrador_de_carpetas/index.php?action_fl=eliminarCarpeta&id=${id}`;
		//alert( url );
		var resp = ajaxR( url );
		alert( resp );
		getFoldersByPath();
	}
</script>