<?php 
	if(isset($_GET['suc'])){
		$suc_activa=$_GET['suc'];
	}else{
		$suc_activa=1;
	}
	//echo '<input style="position:absolute;" type="hidden" id="id_sucu" value="'.$suc_activa.'">';
//armamos condicion por categorias
	if(!isset($_GET['cat']) || $_GET['cat']==''){
		$condicion_familias="";
	}else{
		$categorias=explode("~",$_GET['cat']);
		$condicion_familias="AND id_categoria IN(";
		for($i=0;$i<sizeof($categorias);$i++){
			if($i>0){
				$condicion_familias.=",";
			}
			$condicion_familias.=$categorias[$i];
		}
		$condicion_familias.=")";
		$condicion_familias=str_replace(",)", ")", $condicion_familias);
	}

//armamos condicion por subcategorias
	if(!isset($_GET['subcat']) || $_GET['subcat']==''){
		$condicion_subcategorias="";
	}else{
		$subcategorias=explode("~",$_GET['subcat']);
		$condicion_subcategorias="AND id_subcategoria IN(";
		for($i=0;$i<sizeof($subcategorias);$i++){
			if($i>0){
				$condicion_subcategorias.=",";
			}
			$condicion_subcategorias.=$subcategorias[$i];
		}
		$condicion_subcategorias.=")";
		$condicion_subcategorias=str_replace(",)", ")", $condicion_subcategorias);
	}


	include 'web.php';

//echo $condicion_familias;
 ?>
 

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Productos</title>
	<link rel="stylesheet" href="css/estilos.css">
	<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/menuCategorias.js"></script>

</head>

<style type="text/css">
.sidebar input[type=checkbox] {
  transform: scale(1.3);
}
</style>

<body>

	<?php include 'header.php'; ?>

	<div class="principal">
	
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

		 <main id="contenido" class="catalogo">

		 	<h3>ARTICULOS</h3>
		 	
		 

		 	<?php 
		 		$cont=0;
		 		while ($muestra = mysql_fetch_row($productos)) {
		 			$cont++;
		 			if ($muestra[3] == 28 || $muestra[3]==29) {
		 				$formato_img='class="galeria posicion"';
		 			}else{
		 				$formato_img='class="galeria1 posicion1"';
		 			}

		 			echo '<div id="prd_'.$cont.'" '.$formato_img.' value="'.$muestra[2].'" onclick="despliega_zoom('.$muestra[2].');">';

		 		//oferta; meter aqui el indicador de oferta
		 			if($muestra[5]=='off'){
		 				echo '<p align="right" class="indicador_oferta">oferta</p>';
		 			}

		 		//imagen del producto
		 			if($muestra[0]=='SIN FOTO'){
		 				echo '<img src="img/logo-casa.png">';
		 				echo '<p style="color:red; padding-top:-15px; font-size: 15px; font-weight:bold; position:relative; top:-17px;">SIN FOTO</p>';
		 			}else{
		 				echo '<img src="'.$muestra[0].'">';
		 			}

		 			echo '<p style="position:absolute;">'.$muestra[1].'</p>';
		 		/*precios
		 			echo '<p style="position:absolute;bottom:8%;">';
		 			$aux=explode(" | ", $muestra[4]);
		 			for($i=0;$i<sizeof($aux);$i++){
		 				if($i==0){
		 					$color_fuente="black";
		 				}else if($i==1){
		 					$color_fuente="green";
		 				}else if($i==2){
		 					$color_fuente="red";
		 				}if($i>0){
		 					echo ' | ';
		 				}
		 				echo '<span style="color:'.$color_fuente.';">'. $aux[$i].'</span>';
		 			}
		 			echo '</p>';*/

		 			echo '</div>';
		 		}
		 	 ?>

		 </main>
		
		<div class="fondo" id="emergente_productos">
			<button class="cerrar" onclick="cierra_emergentes(1);">
				<b>X</b>
			</button>
			
			<div class="galery" id="info_emergente">

			</div>


			<div class="flechas">
			
				<a href="javascript:adelante_atras(-1);" class="next" style=""><i class="fas fa-chevron-left"></i></a>

				

				<a href="javascript:adelante_atras(1);" class="prev"><i class="fas fa-chevron-right"></i></a>

			</div>


				
			
		</div>

	</div>

</body>
</html>
<script type="text/javascript">
		function despliega_zoom(id_prd){
			//alert(id_prd);
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'infoProductoZoom.php',
				cache:false,
				data:{flag:'despliega',id_producto:id_prd},
				success:function(dat){
					var arr=dat.split("|");
					if(arr[0]!='ok'){
						alert("Error!!!"+dat);
					}else{
						$("#info_emergente").html(arr[1]);
						$("#emergente_productos").css("display","flex");
					}
				}
			});
		}	

		function adelante_atras(flag){
			//enviamos datos por ajax
	//		alert($("#ord_de_lsta").html());
			$.ajax({
				type:'post',
				url:'infoProductoZoom.php',
				cache:false,
				data:{flag:flag,
						orden_lista:$("#ord_de_lsta").html(),
						catego:$("#array_familias").val(),
						subc:$("#array_subcategorias").val()					
					},
				success:function(dat){
//					alert(dat);
					var arr=dat.split("|");
					if(arr[0]!='ok'){
						alert("Error!!!"+dat);
					}else{
						if(arr[1]=='no'){
							alert("No hay mas proucto por mostrar");
							return false;
						}
						$("#info_emergente").html(arr[1]);
						$("#emergente_productos").css("display","flex");
					}
				}
			});
		}
		function cierra_emergentes(flag){
			if(flag==1){
				$("#info_emergente").html("");
				$("#emergente_productos").css("display","none");
			}
		}
</script>

<?php
	if(isset($_GET['cHJvZHVjdG8'])){
		echo '<script>despliega_zoom(\''.base64_decode($_GET['cHJvZHVjdG8']).'\');</script>';
	}
?>