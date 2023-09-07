<?php
	include('../../../../conectMin.php');
	include('ajax/makeList.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>Interface</title>
<!-- JQuery -->
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<!-- Estilos -->
	<link rel="stylesheet" href="css/estilos.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/gridSW_l.css"/>
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
<!-- bootstrap -->
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.min.css"/>
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- librerias de calendario -->
	<script type="text/javascript" src="../../../../js/calendar.js"></script>
	<script type="text/javascript" src="../../../../js/calendar-es.js"></script>
	<script type="text/javascript" src="../../../../js/calendar-setup.js"></script>
<!-- funciones JS de la página -->
	<script type="text/javascript" src="js/utils.js"></script>
</head>
<body>
<!-- emergente -->
	<div id="emergente">
		<div id="contenido_emergente">
		</div>
	</div>
<!-- div global -->
	<div class="global row">
		<div class="col-3" id="izquierda"><!--id="izquierda"-->
			<table class="table"><!-- width="99%" -->
				<thead>
					<tr class="encabezado">
						<th class="tools" align="center">
							<input type="text" class="form-control" 
							onkeyup="search_menu(this, event);" placeholder="Buscador..."/>
						</th>
						<th class="tools">
							<button onclick="carga_form(0);" class="btn btn-success" 
							title="Click para agregar nueva Herramienta">
								<b class="icon-plus-circled"></b>
							</button>
						</th>
					</tr>
				</thead>
			</table>
	<!-- Listado de Consultas -->
			<div class="queries_list">
				<h5 class="text-center">Consultas</h5>
			<?php
				echo build_accordeon('Vertical', 1);
			?>
			</div>	  
	<!-- Listado de Herramientas -->
			<div class="queries_list">
				<h5 class="text-center">Herramientas</h5>
			<?php
				echo build_accordeon('Horizontal', 2);
			?>
			</div>

		</div>
		<div class="col-9"><!-- id="derecha" -->
		<!--Filtros-->
			<div id="filtros" class="row">
				<b class="filter">Filtros</b>
			</div>
		<!--contenido-->
			<div id="resultados">
			</div>
			<div id="info_consulta">
				<button
					class="btn btn-success"
					onclick="show_and_hidde_right_bar( 'hidde', this );"
				>
					<i class="icon-right-2"></i>
				</button>
		<!--caja de texto donde se muestran las consultas-->
				<textarea class="consulta" id="txt_consulta" disabled onclick="habilitar_txt_consulta();"></textarea>
		<!--botones para modificar y agregar consultas-->
				<table class="btns">
					<tr>
						<td>
							<button id="edita_consulta" onclick="carga_form(-1);" class="btn_opc btn btn-primary"><!-- btn_opc -->
								Editar
							</button>
						</td>
					</tr>
					<tr>	
						<td>
							<button class="btn_opc btn btn-danger">
								Cancelar
							</button>
						</td>
					</tr>
					<tr>
						<td>
							<button class="btn_opc btn btn-success">
								Guardar Nuevo
							</button>
						</td>
					</tr>
					<tr>
						<td>
							<button class="btn_opc btn btn-warning" onclick="home_redirect();">
								Ir al Panel
							</button>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<form id="TheForm" method="post" action="ajax/genera_consulta.php" target="TheWindow">
		<input type="hidden" name="fl" value="1" />
		<input type="hidden" id="datos" name="datos" value=""/>
	</form>

</body>
</html>

<script type="text/javascript">
	
	function show_and_hidde_right_bar( type, obj ){
		if( type == 'hidde' ){
			$( '#info_consulta' ).css( 'width', '50px' );
			$( '#info_consulta' ).css( 'overflow', 'hidden' );
			$( obj ).children( 'i' ).removeClass( 'icon-right-2' );
			$( obj ).children( 'i' ).addClass( 'icon-left-2' );
			$( obj ).attr( 'onclick', "show_and_hidde_right_bar( 'show', this );" );
			$( '#resultados' ).css( 'width', '50% !important' );
			$( '#resultados' ).css( 'max-width', '50% !important' );
		}else{
			//$( '#info_consulta' ).css( 'width', '50px' );
			//$( '#info_consulta' ).css( 'overflow', 'hidden' );
			$( obj ).children( 'i' ).removeClass( 'icon-left-2' );
			$( obj ).children( 'i' ).addClass( 'icon-right-2' );
			$( '#info_consulta' ).css( 'width', '' );
			$( obj ).attr( 'onclick', "show_and_hidde_right_bar( 'hidde', this );" );
			$( '#resultados' ).css( 'width', '75% !important' );
			$( '#resultados' ).css( 'max-width', '75% !important' );
		}
	}
	
</script>