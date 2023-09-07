<?php
	//include("../../conect.php");
	include("../../conectMin.php");
	
	extract($_POST);
	extract($_GET);
	$campo_nombre = utf8_decode($campo);
	$valor = strtoupper($valor);
	$sql = "SELECT DISTINCT $campo_nombre FROM $tabla WHERE UPPER($campo_nombre) LIKE '$valor%' LIMIT 15";
	$ref = @mysql_query($sql);
	$datos = array();	
	if(mysql_num_rows($ref)>0)
	{
		$j=0;
		while($linea = mysql_fetch_row($ref))
		{
			if($j>0)
				echo "|";
			for($i=0;$i<count($linea);$i++)
			{
				if($i>0)
					echo "~";
				echo $linea[0]; 
			}
			$j++;
		}
			
	}
	mysql_free_result($ref);
//	$search_queries = $datos;
//	$results = search($search_queries, $q);
	//sendResults($datos);

function search($search_queries, $query) {
	if(strlen($query) == 0)
		return $search_queries;
	$query = strtolower($query);
	$results = array();
	for($i = 0; $i < count($search_queries); $i++){
		if(strcasecmp(substr($search_queries[$i],0,strlen($query)),$query) == 0)
			array_push($results,$search_queries[$i]);
	}
	return $results;
}

function sendResults($results) {
	die('["'.implode('","',array_map('utf8_encode',$results)).'"]');
//	for ($i = 0; $i <count($results); $i++)
//		print "$results[$i]|$results[$i]\n";
}
?>