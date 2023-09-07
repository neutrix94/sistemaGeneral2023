<style type="text/css">
	/*.paquetes_div{
		width: 95%;
		left: 2.5%;
		position: relative;
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
	
	.busc_p_1{
		right:30%;
		position: relative;
	}
	.res_bsc:hover{
		background: rgba(225,0,0,.5);
	}*/
	.res_bsc> td{
		padding: 10px !important;
		font-size: 130% !important;
		box-shadow: 1px 1px 10px rgba( 0,225,0,.2 );
	}
	.paquetes_div{
		padding: 10px;
	}
	#emer_pqt{
		position: fixed;
		z-index:3;
		background: rgba(0,0,0,.8);
		width: 100%;
		height:100%;
		top:0;
		z-index:10000;
		display:none;
	}
	.table_header{
		background-color: rgba( 225,0,0,1 );
		color : white;
		text-align: center;
		position : sticky;
		top : 0;
		font-size: 120% !important;
	}
	
	.table_container{
		max-width: 150%;
		overflow: auto;
		max-height: 500px !important;
		height: 500px !important;
		position: relative;
	}
	.description_td{
		height: 100px !important;
		max-height: 100px !important;
		overflow: hidden;
	}
	td{
		vertical-align: middle;
	}
	@media (max-width: 400px) {
		.table_container{
			max-width: 150%;
			position: relative;
			height: 500px !important;
			max-height: 500px !important;
			overflow: auto;
		}
		.packs_table{
			width: 200px !important;
			max-width: 200px !important;
		}
	}
	.img_ampliada{
		z-index: 20000;
	}


		
</style>

<div class="row paquetes_div">
	<div class="col-7">
		<!--p style="width:50%;" class="busc_p_1"-->
		<input 
			type="text" 
			class="form-control" 
			onkeyup="recargaListadoPaquetes(this,1);"
			placeholder="Buscador de paquetes..."
		>
		<br>
		<!--/p -->
	</div>
	<div class="col-5">
		<button 
			class="btn btn-success form-control"
			onclick="modificar(0);">
			<i class="icon-plus-circled">Crear Nuevo</i>
		</button>
	</div>

	<div class="col-12 table_container">
<!--encabezado de tabla-->
		<table class="table table-bordered table-striped packs_table">
			<thead class="table_header">
				<tr>
					<th>Nombre</th>
					<th style="width : 400px !important;">Descripción</th>
					<th>ver</th>
					<th>Edit</th>
					<th>Config</th>
					<th>Transf</th>
					<th>Elim</th>
				</tr>
			</thead>
			<tbody id="contenido_paquetes">
			<?php
				$sql="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_menu=203 AND id_perfil=$perfil_usuario";
				$eje=mysql_query($sql)or die("Error al buscar el permiso para permitir transferencias en paquetes!!!<br>".mysql_error()."<br>".$sql);
				$r_perm_tr=mysql_fetch_row($eje);
				$perm_tans=$r_perm_tr[0];
			//consultamos los paquetes existentes
				$sql="SELECT 
						/*0*/p.id_paquete,
						/*1*/p.nombre,
						/*2*/p.imagen,
						/*3*/REPLACE( p.descripcion, '-', '<br>' ),
						/*4*/p.trans_generada,
						/*5*/sp.estado_suc
					FROM ec_paquetes p
					LEFT JOIN sys_sucursales_paquete sp
					ON p.id_paquete = sp.id_paquete
					AND sp.id_sucursal = '{$sucursal_id}'
					WHERE p.activo=1
					AND p.id_sucursal_creacion = '{$sucursal_id}'";/*implementacion Oscar 2023 par que solo parezcan los paquetes dela sucursal*/
				$eje=mysql_query($sql)or die("Error al consultar listado de paquetes!!!\n\n".$sql."\n\n".mysql_error());
			//	echo '<table class="t_p_1 table table-bordered table">';
				$c=0;//declaramos contador en 0
				while($r=mysql_fetch_row($eje)){
					$c++;//incrementamos contador
					$color = "";
					if( $r[5] == 0 ){
						$color = 'color : red !important;';
					}
				//foramos fila
					echo '<tr id="fila_'.$c.'" style="' . $color . '">';
						echo '<td value="'.$r[0].'" id="cda_1_'.$c.'" style="display:none;"></td>';
						echo '<td id="cda_2_'.$c.'">'.$r[1].'</td>';
						echo '<td class="description_td" id="cda_4_'.$c.'">'.$r[3].'</td>';
						echo '<td align="center"><button class="btn btn-primary" onclick="modificar(1,'.$c.','.$r[0].')"><i class="icon-eye"></i></button></td>';
						echo '<td align="center"><button class="btn btn-warning" onclick="modificar(2,'.$c.','.$r[0].')"><i class="icon-pencil"></i></button></td>';
						echo '<td align="center"><button class="btn btn-secondary" onclick="abre_config_paqt('.$r[0].');"><i class="icon-cog-1"></i></button></td>';
						echo '<td id="cda_3_'.$c.'" align="center">';
						if($perm_tans==1){
							echo '<button class="btn btn-info" onclick="crea_transfer('.$r[0].','.$r[4].');" style=""><i class="icon-paper-plane-1"></i></button>';
						}
						echo '</td>';//'.$r[2].'
						echo '<td align="center"><button class="btn btn-danger" onclick="modificar(3,'.$c.','.$r[0].')"><i class="icon-cancel-circled"></i></button></td>';
					echo '</tr>';
				}
			//	echo '</table>';
			?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3" class="text-center" style="color : black !important;">
						<i class="icon-stop">Habilitado</i>
					</td>
					<td colspan="3" class="text-center" style="color : red !important;">
						<i class="icon-stop">No habilitado</i>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<input type="hidden" id="total_resultados" value="<?php echo $c;?>">

	<div id="emer_pqt">
		<p align="right" style="padding:5px;z-index:2000;position: absolute;right:5px;">
			<img src="../img/especiales/cierra.png" height="40px" onclick="cierra_detalle();"><!--document.getElementById('emer_pqt').style.display='none';-->
		</p>
		<div id="cont_emergente_pqt">
		</div>
	</div>
</div>