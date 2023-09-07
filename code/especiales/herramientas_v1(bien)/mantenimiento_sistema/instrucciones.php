<button type="button" style="display : none;" id="lanza_modal" data-bs-toggle="modal" data-bs-target="#instruccionesModal"></button>
<div class="modal fade" id="instruccionesModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content" style="width:200%; left : -50%;">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel"><b>Instrucciones de la Pantalla</b></h5>
				<!--button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button-->
			</div>
			<div class="modal-body" style="text-align: justify; max-height: 400px; overflow:auto;">
				<div id="instrucciones_agrupacion">
					<h6>1. Agrupación de 
						<b>Movimientos de Almacén</b>, 
						<b>Ventas y Devoluciones</b>
					</h6>
					<p style="color:blue;">Las agrupaciones deben de ser primero por día, 
						después por año y posteriormente por todos los anteriores
					</p>
					<ul>
						<li id="1_1"><span class="desc_inst">Agrupar por día (con un año de antigüedad) : </span>
							Se agrupan los movimientos de almacen o ventas y devoluciones
							por sucursal y dia para reducir el número de registros; poner una fecha
							con un año de anterioridad, por ejemplo para el año presente, la fecha que se tendría
							que asignar para reducir los registros sería : <b>
							<?php echo date('Y', strtotime(date('Y')."- 1 year")) . '-12-31'; ?></b>.
						</li>
						<li id="1_2"><span class="desc_inst">Agrupar por año (con dos años de antigüedad) : </span>
							Se agrupan los movimientos de almacen o ventas y devoluciones
							por sucursal y año para reducir el número de registros; poner una fecha
							con dos años de anterioridad, por ejemplo para el año presente, la fecha que se tendría
							que asignar para reducir los registros sería : <b>
							<?php echo date('Y', strtotime(date('Y')."- 2 year")) . '-12-31'; ?></b>.
						</li>
						<li id="1_3"><span class="desc_inst">Agrupar por todos los anteriores (con tres años de antigüedad) : </span>
							Se agrupan los movimientos de almacen o ventas y devoluciones a un solo registro por 
							cada tipo de movimiento y sucursal para reducir 
							el número de registros; poner una fecha con tres años de anterioridad, 
							por ejemplo para el año presente, la fecha que se tendría
							que asignar para reducir los registros sería : <b>
							<?php echo date('Y', strtotime(date('Y')."- 3 year")) . '-12-31'; ?></b>.</li>
					</ul>
				</div>
				<div id="instrucciones_eliminar_sin_uso">
					<h6>2. Eliminar   
						<b>registros inecesarios </b>, esto eliminará los siguientes registros
					</h6>
					<p style="color:blue;">
						Para eliminar registros inecesarios la fecha recomendada sería el :
						<b style="color:black;">
							<?php echo date('Y', strtotime(date('Y')."- 1 year")) . '-12-31'; ?>
						</b> 
					</p>
					<ul>
						<li><b>ec_movimiento_temporal : </b>
							Movimientos temporales del Sistema
						</li>
						<li><b>ec_pedidos_back : </b>
							Venta temporal para pasar de una pantalla a otra en el punto de Venta
						</li>
						<li><b>ec_registro_nomina : </b>
							Registros de asistencias de empleados
						</li>
						<li><b>ec_sincronizacion_registros : </b>
							Registros de Sincronización de Datos
						</li>
						<li><b>ec_temporal_exhibicion : </b>
							Registros de productos tomados de exhibición temporalmente
						</li>
						<li><b>ec_transferencias : </b>
							Registros de transferencias que no tienen relacionado un movimiento de Almacén
						</li>
					</ul>
				</div>
				<div id="instrucciones_eliminar_sin_inventario">
					<h6>3. Eliminar  
						<b> alertas de inventario insuficiente</b>, esto eliminará los siguientes registros
					</h6>
					<b style="color:red;">
						*** NOTA : Hacer esta operación una vez que se hayan calculado las 
						estacionalidades del año actual ya que estos registros son usados para calcular el aumento
						de estacionalidades ***
					</b>
					<p style="color:blue;">
						Para eliminar alertas inecesarias la fecha recomendada sería el :
						<b style="color:black;">
							<?php echo date('Y', strtotime(date('Y')."- 1 year")) . '-12-31'; ?>
						</b><br/>
					</p>
					<ul>
						<li><b>ec_productos_sin_inventario : </b>
							Productos que iban a ser vendidos pero el inventario del producto era menor a la cantidad solicitada</li>
					</ul>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
				<button 
					type="button" 
					class="btn btn-danger" 
					onclick="location.href='../../../../index.php?';"
				>
					Cancelar y Salir
				</button>
			</div>
		</div>
	</div>
</div>