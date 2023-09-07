<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
	<link rel="stylesheet" href="css/estilos.css">
	<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/menuCategorias.js"></script>
</head>
<body>


		<aside class="sidebar" id="sidebar">
		 	
		 	<h3>CATEGORIAS</h3>

		 	<?php 
		 	$cont_chk=0;

				echo '<input type="hidden" id="array_familias" value="'.$_GET['cat'].'">';	
				echo '<input type="hidden" id="array_subcategorias" value="'.$_GET['subcat'].'">';

		 	while ($item = mysql_fetch_row($subcategoria)) {
		 			$cont_chk++;
		 			$habilitado="";		 			
		 			for($i=0;$i<sizeof($subcategorias);$i++){
		 				if($subcategorias[$i]==$item[0]){
		 					$habilitado="checked";
		 				}
					}

		 			echo '<div class="check">
		 		
		 					<input type="checkbox" value="'.$item[0].'" name="check" id="chk_'.$cont_chk.'" onclick="recorre_categorias();" '.$habilitado.'>
		 					<label for="check1">'.$item[1].'</label>

		 				  </div>';
		 			# code...
		 		}

		 		echo '<input type="hidden" id="tope_categorias" value="'.$cont_chk.'">';//cantidad de familias
			
		 	 ?>
		 
		 </aside>

	
</body>
</html>