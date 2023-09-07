<html>
	<head>
		<script type="text/javascript" src="js/swfobject.js"></script>
		<script type="text/javascript">

			swfobject.embedSWF(
			"open-flash-chart.swf", "my_chart",
			"850", "550", "9.0.0", "expressInstall.swf",
			{"data-file":"<?php
			
			extract($_GET);
			
			echo "graficaHorizontal.php?id_reporte=$id_reporte&fecdel=$fecdel&fecal=$fecal&tipoFec=$tipoFec&titulo=$titulo";
			
			$ar=fopen("parametros.txt", "wt");
			if($ar)
			{
				fputs($ar, "$id_reporte|$fecdel|$fecal|$tipoFec|$titulo");
			}
			else
			{
				die("No write file");
			}
			fclose($ar);
			
			
			?>"} );

		</script>
	</head>
	<body>



	<div id="my_chart">
		
	</div>

	</body>
</html>	