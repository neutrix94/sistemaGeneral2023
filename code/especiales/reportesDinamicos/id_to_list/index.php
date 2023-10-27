<?php
	include( 'header.php' );
?>
<!DOCTYPE html>
<head>
	<title></title>
<!-- JQuery-->
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="../../../../js/papaparse.min.js"></script>
	<script type="text/javascript" src="functions.js"></script>
<!-- bootstrap -->
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.min.css"/>
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
	<form>
	<div class="row">
		<div class="col-sm-2" style="text-align:center;">
			<img src="../../../../img/img_casadelasluces/Logo.png" width="80px" onclick="panel();">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
		</div>
		<div class="col-sm-8">
			<div class="row" style="border:0px solid;">	
				<div class="col-sm-12">
					<?php
						echo $lists;
					?>
				</div>
				<div class="" style="text-align : left; border:0px solid; padding :; ">
					<img src="https://static.thenounproject.com/png/515901-200.png" width="59px" style="position:absolute; right:-20px; top : -10px; z-index : -1;">
				</div>
			</div><br />
			<div>
				<input type="file" id="file_import" class="form-control">
			</div><br />

			<div id="before">
				<button type="button" id="import" class="btn btn-warning form-control" onclick="import_csv();">Importar</button>
			</div>

			<div  id="after_1" style="display : none;">
				<button type="button" id="clean" class="btn btn-info form-control" onclick="location.reload();">Limpiar</button>
			</div>
			<div  id="after" style="display : none;">
				<button type="button" id="export" class="btn btn-success form-control" onclick="export_grid();">Exportar Datos</button>
			</div>
		</div>
		<div class="col-sm-2">
		</div>
	</div>
	<br />
	<div id="results"></div>
	</form>
	<form id="TheForm" method="post" action="getList.php" target="TheWindow">
		<input type="hidden" name="fl" value="1" />
		<input type="hidden" id="datos" name="datos" value=""/>
	</form>
</body>
</html>