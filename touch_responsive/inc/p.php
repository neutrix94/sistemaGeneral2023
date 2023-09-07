<?php
	 $ar = fopen("leyenda.txt","r") or exit('No se pudo abrir el archivo');

    while(!feof($ar))
    {
        $linea=fgets($ar);
        $lineasalto=nl2br($linea);
    }
    fclose($ar);
  echo utf8_decode($lineasalto);
    #$ticket->Output();
?>