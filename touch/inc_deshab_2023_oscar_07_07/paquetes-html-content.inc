<style type="text/css">
	.paquetes_div{
		/*border:1px solid;*/
		width: 100%;
		height:auto;		
	}
	.enc_tab{
		width: 90%;
	}
	#contenido_paquetes{
		width:90%;
		height:320px;
		padding:0;
		border:1px solid;
		overflow: auto;
	}
	th{
		padding: 5px;
		background: rgba(225,0,0,.5);
		color:white;
	}
	.buscador_paq{
		width: 30px;
	}
	.t_p_1{
		width: 100%;
	}
	#emer_pqt{
		position: fixed;
		z-index:3;
		background: rgba(0,0,0,.8);
		width: 100%;
		height:100%;
		top:0;
		z-index:5;
		display:none;
	}
	.busc_p_1{
		right:30%;
		position: relative;
	}
	.res_bsc:hover{
		background: rgba(225,0,0,.5);
	}
</style>

<div class="paquetes_div">
<center>
	<p style="width:35%;" class="busc_p_1">
		<input type="text" class="buscador_paq" onkeyup="recargaListadoPaquetes(this,1);">
	</p>
<!--encabezado de tabla-->
	<table class="enc_tab">
		<tr>
			<tr>
				<th width="20%">Nombre</th>
				<th width="35%">Descripción</th>
				<th width="10%">ver</th>
				<th width="10%">Edit</th>
				<th width="7%">Config</th>
				<th width="10%">Transferencia</th>
				<th width="6.5%">Elim</th>
				<th width="1.5%"></th>
			</tr>
		</tr>
	</table>
<!--Fin de enncbezado-->
	<div id="contenido_paquetes">
		<?php
			$sql="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_menu=203 AND id_perfil=$perfil_usuario";
			$eje=mysql_query($sql)or die("Error al buscar el permiso para permitir transferencias en paquetes!!!<br>".mysql_error()."<br>".$sql);
			$r_perm_tr=mysql_fetch_row($eje);
			$perm_tans=$r_perm_tr[0];
		//consultamos los paquetes existentes
			$sql="SELECT /*0*/id_paquete,/*1*/nombre,/*2*/imagen,/*3*/descripcion,/*4*/trans_generada FROM ec_paquetes WHERE activo=1";
			$eje=mysql_query($sql)or die("Error al consultar listado de paquetes!!!\n\n".$sql."\n\n".mysql_error());
			echo '<table class="t_p_1">';
			$c=0;//declaramos contador en 0
			while($r=mysql_fetch_row($eje)){
				$c++;//incrementamos contador
			//foramos fila
				echo '<tr id="fila_'.$c.'">';
					echo '<td value="'.$r[0].'" id="cda_1_'.$c.'" style="display:none;"></td>';
					echo '<td id="cda_2_'.$c.'" width="20%">'.$r[1].'</td>';
					echo '<td id="cda_4_'.$c.'" width="35%">'.$r[3].'</td>';
					echo '<td align="center" width="10%"><img src="../img/especiales/ver.png" width="50%" height="30px" onclick="modificar(1,'.$c.','.$r[0].')"></td>';
					echo '<td align="center" width="10%"><img src="../img/especiales/edita.png" width="50%" height="30px" onclick="modificar(2,'.$c.','.$r[0].')"></td>';
					echo '<td align="center" width="7.5%"><img src="img/config.png" width="40px" onclick="abre_config_paqt('.$r[0].');"></td>';
					echo '<td id="cda_3_'.$c.'" align="center" width="10%">';
					if($perm_tans==1){
						echo '<img src="img/transfer.png" width="40px" onclick="crea_transfer('.$r[0].','.$r[4].');" style="color:red;background:orange;">';
					}
					echo '</td>';//'.$r[2].'
					echo '<td align="center" width="7.5%"><img src="../img/especiales/del.png" width="40px" onclick="modificar(3,'.$c.','.$r[0].')"></td>';
				echo '</tr>';
			}
			echo '</table>';
		?>
	</div>
</center>
	<input type="hidden" id="total_resultados" value="<?php echo $c;?>">
	<p style="width:15%;left:80%;position:relative;" align="right">
		<button onclick="modificar(0);"><img src="../img/especiales/add.png" width="50px"><br>Nuevo</button>
	</p>
</div>
<div id="emer_pqt">
	<p align="right" style="padding:5px;z-index:2000;position: absolute;right:5px;">
		<img src="../img/especiales/cierra.png" height="40px" onclick="cierra_detalle();"><!--document.getElementById('emer_pqt').style.display='none';-->
	</p>
	<div id="cont_emergente_pqt">
	</div>
</div>