<?php

	extract($_GET);

	if($tipo == 1)
	{
		$aux=explode('-', $valor);
		//print_r($aux);die();
		if(checkdate($aux[1], $aux[2], $aux[0]))
			die("exito");
	}
	elseif($tipo == 2)
	{
		die($valor);
	}

?>