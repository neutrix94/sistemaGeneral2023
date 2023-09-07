<?php

  extract($_GET);
  extract($_POST);

  include("../../conectMin.php");

  echo "exito|";
  
  $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

  $query = "SELECT Mes,CONCAT('$',FORMAT(Total,2)) AS Total  FROM balance WHERE Tipo = '1'";
  $res = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());
   
  for($i=0;$i<2;$i++)
  {

  	echo "<th class='headerReporte'>".mysql_field_name($res,$i)."</th>";

  }
  while($fila = mysql_fetch_row($res))
  {
  	echo "<tr>
  			<td class='datos'>".$fila[0]."</td>
  			<td  class='datos' align='right'>".$fila[1]."</td>
  	 	  </tr>";
  }	

  $query = "SELECT '','',SUM(TOTAL)  FROM balance WHERE Tipo = '1'";
  $res = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());
  $fila = mysql_fetch_row($res);

  echo "<tr class='filaSumatoria'>
			<td class='sumatoriasRep' align='center'>Total Ventas Anual</td>
			<td  class='sumatoriasRep' align='right'>$ ".money_format('%i',$fila[2])."</td>			
  		</tr>";

  $ventasTotales=$fila[2];		
 echo "<tr><td>&nbsp</td><td>&nbsp</td></tr>";

  $query = "SELECT Mes,CONCAT('$',FORMAT(Total,2)) AS Total  FROM balance WHERE Tipo = '2'";
  $res = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());
   
  for($i=0;$i<2;$i++)
  {

  	echo "<th class='headerReporte'>".mysql_field_name($res,$i)."</th>";

  }
  while($fila = mysql_fetch_row($res))
  {
  	echo "<tr>
  			<td class='datos'>".$fila[0]."</td>
  			<td  class='datos' align='right'>".$fila[1]."</td>
  	 	  </tr>";
  }	

  $query = "SELECT '','',SUM(TOTAL)  FROM balance WHERE Tipo = '2'";
  $res = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());
  $fila = mysql_fetch_row($res);

  echo "<tr class='filaSumatoria'>
			<td class='sumatoriasRep' align='center'>Total Compras Anual</td>
			<td  class='sumatoriasRep' align='right'>$ ".money_format('%i',$fila[2])."</td>			
  		</tr>";
 $comprasTotales = $fila[2];

  echo "<tr><td>&nbsp</td><td>&nbsp</td></tr>";
 	 echo "<tr class='filaSumatoria'>
			<td class='sumatoriasRep' align='center'>Utilidad Bruta</td>
			<td  class='sumatoriasRep' align='right'>$ ".money_format('%i',$ventasTotales)."</td>			
  		</tr>";
  	 echo "<tr class='filaSumatoria'>
			<td class='sumatoriasRep' align='center'>Utilidad Neta</td>
			<td  class='sumatoriasRep' align='right'>$ ".money_format('%i',$ventasTotales-$comprasTotales)."</td>			
  		</tr>";	
?>