<?php

	include("../../conect.php");
	
	extract($_POST);
	extract($_GET);
	
	if($busca == 'SI')
	{
		$sql="SELECT file FROM eq_otros WHERE id_cotizador_otros = $id_det";
		$res=mysql_query($sql) or die(mysql_error());
		
		$row=mysql_fetch_row($res);
		
		//echo $rooturl;
		
		$aux=$row[0];
		
		if($aux != '')
			$verDoc='SI';
	}
	
	if($guarda == 'SI')
	{
		
		if($id_det == '')
		{
		
			$sql="INSERT INTO eq_otros(id_cotizador, servicio, codigo, precio, temporal, fecha_up)
		                    VALUES ($id, '', '', 0, 1, NOW())";
		
			mysql_query($sql) or die("$sql <br><br>".mysql_error());
		
			$id_det=mysql_insert_id();
		}	
	
		if($_FILES['archivo']['tmp_name'])
		{
			$url_final=$rootpath."/files/eq_cotizador_archivo_".rand(1, 10000)."_".$_FILES['archivo']['name'];
					
			//echo "<br>$url_final";
					
			if(copy($_FILES['archivo']['tmp_name'], $url_final))
			{
				$aux=str_replace($rootpath, $rooturl, $url_final);
				$sql="UPDATE eq_otros SET file='".$aux."' WHERE id_cotizador_otros=$id_det";
				
				mysql_query($sql) or die(mysql_error());
				
				$verDoc="SI";
			}
		}
	}
	
	if($cancelar == 'SI')
	{
		$sql="SELECT file FROM eq_otros WHERE id_cotizador_otros = $id_det";
		$res=mysql_query($sql) or die(mysql_error());
		
		$row=mysql_fetch_row($res);
		
		$aux=str_replace($rooturl, $rootpath, $row[0]);
		
		if(file_exists($aux))
			unlink($aux);
		
		$sql="UPDATE eq_otros SET file='' WHERE id_cotizador_otros = $id_det";
		
		mysql_query($sql) or die(mysql_error());
			
	}	

?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../../css/estilo_final.css" />
		<link rel="stylesheet" type="text/css" href="../../css/gridSW_l.css" />
	</head>
	<body>	
	
		<?php
		
			if($verDoc == 'SI')
			{
			
				?>
					<div style="margin-top: 5px">
						<a href="<?php echo $aux; ?>" target="_blank" class="texto_form">Ver documento</a>
						&nbsp;
						<a href="uploadFile.php?id=<?php echo $id; ?>&cancelar=SI&id_det=<?php echo $id_det; ?>" class="texto_form">Cancelar</a>
					</div>
					
					<script>
					
						obj=parent.document.getElementById('id_nuevo');
						obj.value=<?php echo $id_det; ?>
						
					
					</script>
			
				<?php
			
			}
			else
			{
		
		?>
	
		<form method="post" enctype="multipart/form-data" action="uploadFile.php" name="forma">
			<div id="obj1" class="texto_form" style="margin-top: 5px">
				<input type="hidden" name="guarda" value="SI" class="barra_tres">
				<input type="file" name="archivo" class="celdaTexto" size="15">
				<input type="hidden" name="id" value="<?php echo $id; ?>">
				<input type="hidden" name="id_det" value="<?php echo $id_det; ?>">
				<input type="button" value="Subir" class="boton" onclick="sube()">
			</div>	
			<div id="obj2" style="display:none; margin-top: 5px" class="texto_form">
				<span id="txt2">Subiendo</span>
			</div>
		</form>
		
		<script>
			
			function sube()
			{
				obj=document.getElementById('obj1');
				obj.style.display="none";
			
				obj=document.getElementById('obj2');
				obj.style.display="block";
				
				setTimeout('texto(1, 200)', 200);
				
				var f=document.forma;
				
				f.submit(); 
			}
			
			function texto(p, val)
			{
				obj=document.getElementById('txt2');
				
				var aux="Subiendo";
				if(p == 1)
					aux+=".";
				if(p == 2)
					aux+="..";
				if(p == 3)
					aux+="...";
				obj.innerHTML=aux;
				
				var i=p+1;
				
				if(i == 4)
					i=0;
				
				setTimeout('texto('+i+', '+val+')', val);
							
			}
			
		</script>
		<?php
			}
		?>	
		
	</body>	
</html>