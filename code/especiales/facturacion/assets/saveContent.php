<?php
	$name = "archivoDatosQR.txt";
	$file = fopen("{$name}", "w");

	fwrite($file, $_POST['data'] . PHP_EOL );

	//fwrite($file, "Otra más" . PHP_EOL);

	fclose($file);
	echo $name;
?>
