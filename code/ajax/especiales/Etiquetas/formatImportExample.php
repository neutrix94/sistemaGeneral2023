<?php
	$nombre="formato_ejemplo_importacion_etiquetas.csv";
//generamos descarga
	header('Content-Type: aplication/octect-stream');
	header('Content-Transfer-Encoding: Binary');
	header('Content-Disposition: attachment; filename="'.$nombre.'"');
	echo "Id Producto,Nombre producto\n";
	echo "1821,Serie LED 50 Luces Blanca C/Transparente 3.5M\n";
	echo "1822,Serie LED 50 Luces Calida c/Verde 6.5M\n";
	die('');//<script>window.close();</script>
?>