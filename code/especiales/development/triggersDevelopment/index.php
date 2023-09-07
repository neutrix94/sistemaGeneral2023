<html>
<head>
	<title>Generación de Triggers</title>
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="js/builder.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
</head>
<body>

	<button
		class="btn btn-success"
		style="z-index:100; position : fixed;"
		onclick="minimize_and_expand( this );"
	>
		<i class="icon-left-big"></i>
	</button>
	<div class="row global_container"><!-- row global_container -->
	<h3 class="text-center"><i class="icon-table"></i>Tabla actual: <b id="header_table_name"></b></h3>
	<?php
		include 'classes/MasterConnection.php';
		include 'classes/FrontEnd.php';
		include 'classes/Triggers.php';
		$MasterConnection = new MasterConnection( 'localhost', 'root', '', 'information_schema' );// $db_host, $db_user, $db_pass, $db_name
		$link = $MasterConnection->getConnection();

		$schema_name = 'wwsist_sistema_general_produccion_2023';
		$Triggers = new Triggers( $link, $schema_name );

		echo $Triggers->getTablesSchema();
	?>
	<!-- contenedor de datos de las tablas -->
		<div class="col-3" id="table_structure"></div>

		<div class="col-6" id="table_triggers">
			<div class="row">
				<div class="col-6 text-center">
					<h3>Triggers Actuales</h3>
				</div>
				<div class="col-6 text-center">
					<h3>Triggers Nuevos</h3>
				</div>
			</div>
			<div class="row text-center">
				<h4 class="text-center">INSERTAR</h4>
				<div class="col-6">
					<div class="row">
						<div class="col-2">
							<b><i class=""></i>NOMBRE</b>
						</div>
						<div class="col-10">
							<input type="text" class="form-control" id="trigger_insert_name">
						</div>
						<div class="col-2">
							<b><i class=""></i>TIEMPO</b>
						</div>
						<div class="col-10">
							<select class="form-control" id="trigger_insert_timing">
								<option value="0">--SELECCIONAR--</option>
								<option value="AFTER">DESPUES</option>
								<option value="BEFORE">ANTES</option>
							</select>
						</div>
					</div>
					<textarea id="trigger_insert" class="form-control" style="height:auto;"></textarea>
					<button
						type="button"
						class="btn btn-success"
					>
						<i>Descargar</i>
					</button>
				</div>
				<div class="col-6">
					<div class="row">
						<div class="col-2">
							<b><i class=""></i>NOMBRE</b>
						</div>
						<div class="col-10">
							<input type="text" class="form-control">
						</div>
						<div class="col-2">
							<b><i class=""></i>TIEMPO</b>
						</div>
						<div class="col-10">
							<select class="form-control">
								<option value="0">--SELECCIONAR--</option>
								<option value="AFTER">DESPUES</option>
								<option value="BEFORE">ANTES</option>
							</select>
						</div>
					</div>
					<textarea id="trigger_insert_new" class="form-control" style="height:auto;"></textarea>
					<button
						type="button"
						class="btn btn-success"
					>
						<i>Descargar</i>
					</button>
				</div>
			</div>
			<div class="row text-center">
					<h4 class="text-center">ACTUALIZAR</h4>
				<div class="col-6">
					<textarea id="trigger_update" class="form-control" style="height:auto;"></textarea>
					<button
						type="button"
						class="btn btn-success"
					>
						<i>Descargar</i>
					</button>		
				</div>

				<div class="col-6">
					<textarea id="trigger_update_new" class="form-control" style="height:auto;"></textarea>
					<button
						type="button"
						class="btn btn-success"
					>
						<i>Descargar</i>
					</button>		
				</div>
			</div>
			<div class="row text-center">
					<h4 class="text-center">ELIMINAR</h4>
				<div class="col-6">
					<textarea id="trigger_detele" class="form-control" style="height:auto;"></textarea>
					<button
						type="button"
						class="btn btn-success"
					>
						<i>Descargar</i>
					</button>
				</div>
				<div class="col-6">
					<textarea id="trigger_detele_new" class="form-control" style="height:auto;"></textarea>
					<button
						type="button"
						class="btn btn-success"
					>
						<i>Descargar</i>
					</button>
				</div>
			</div>
		</div>
	</div>
</body>
</html>


<style type="text/css">
	*{
		font-size: 95%;
	}
	.global_container{
		position: absolute;
		top : 0;
		padding : 10px;
		height: 100% !important;
		max-height: 100% !important;
		left : 0;
		width: 100%;
		max-width: 100%;
		overflow: hidden;
	}
	.tables_catalogue{
		position: fixed;
		left: 0;
		top: 0;
		width: 20%;
		position: relative;
		max-height: 100% !important;
		overflow: scroll;
	}
	/*#tables_structure{
		position: fixed;
		left: 20%;
		top: 0;
		max-height: 100%;
		overflow: auto;
		width: 20%;
	}*/
	.row_type{
		font-size: 90%;
		color: orange;
	}

	.group_card{
		padding: 10px;
		box-shadow: 1px 1px 10px rgba( 0,0,0,.5 );
	}
	.tables_list{
		position: relative;
		height: 750px;
		max-height: 750px;
		overflow: scroll;
	}

	#table_triggers{
		position: relative;
		height: 750px;
		overflow: scroll;
	}
	.title_sticky{
		position: sticky;
		top: 0;
		background: white;
		padding: 10px;
		z-index: 10;
	}
	#fields_container{
		position: relative;
		height: 600px;
		max-width: 600px;
		overflow: scroll;
	}
	.trigger_container{
		box-shadow: 2px 2px 15px rgba( 0,0,0,.4 );
	}
</style>