<link rel="stylesheet" type="text/css" href="../../inc/css/gridSW.css">
{include file="_header.tpl" pagetitle="$contentheader"}
 <div id="cosa">
<div class="titulo-icono" id="titulo-icono">
	     <div class="titulo" id="titulo">Reportes</div>
 </div>
<div class="tabla" id="tabla">


<form action="encabezados.php" name="forma_datos" id="forma_datos" method="post" >
<table class="campos">

{if 1 eq 1}
<tr>
<td>

	<table>
	<tr>
		<td><img src="{$ROOTURL}imagenes/general/ventas18.png" /></td>
		<td class="campos"><a href="#" onclick="despliegaDiv('divCont')">Reportes de Ventas.</a></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td id="divCont" style="display:none;">
			<table class="">
				              
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=1">Ventas por Cliente.</a></td>					
				</tr>                
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=5">Ventas con Folio.</a></td>
				</tr>
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=2">Ventas por Pedido.</a></td>					
				</tr>                
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=3">Ventas por Producto.</a></td>					
				</tr>                
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=6">Ventas por Tipo de Pago.</a></td>
				</tr>
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=7">Ventas por Estado.</a></td>
				</tr>
                <tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=18">Ventas por Pagos.</a></td>
				</tr>
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=4">Balance Anual de ventas.</a></td>
				</tr>
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=8">Cuentas por Cobrar.</a></td>
				</tr>
			</table>
		</td>
	</tr>	
	</table>
	</td>
</tr>

{/if}
{if 1 eq 1}
<tr>
<td>

	<table>
	<tr>
		<td><img src="{$ROOTURL}imagenes/general/ventas18.png" /></td>
		<td class="campos"><a href="#" onclick="despliegaDiv('divCont4')">Reportes Gastos.</a></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td id="divCont4" style="display:none;">
			<table class="">				
               
                
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=9">Reporte de Gastos por Proveedor</a></td>
				</tr>
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=10">Reporte de Gastos por Tipo de Egreso</a></td>
				</tr>
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=11">Reporte de Gastos por Tipo de Pago</a></td>
				</tr>
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=12">Balance Anual de Gastos</a></td>
				</tr>
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/ventas18.png" /><a href="code/reportes/reportes.php?reporte=13">Cuentas por Pagar</a></td>
				</tr>				
			</table>
		</td>
	</tr>	
	</table>
	</td>
</tr>

{/if}
{if 1 eq 1}
<tr>
<td>
 <br>
	<table>
	<tr>
		<td><img src="{$ROOTURL}imagenes/general/almacen18.png" /></td>
		<td class="campos"><a href="#" onclick="despliegaDiv('divCont2')">Reportes de Almac&eacute;n</a></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td id="divCont2" style="display:none;">
			<table class="">
				
                
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/almacen18.png" /><a href="code/reportes/reportes.php?reporte=14">Inventario</a></td>
				</tr>
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/almacen18.png" /><a href="code/reportes/reportes.php?reporte=15">Productos con Existencia baja</a></td>
				</tr>                
			</table>
		</td>
	</tr>
	</table>
	</td>
</tr>			
{/if}
{if 1 eq 1}
<tr>
<td>
 <br>
	<table>
	<tr>
		<td><img src="{$ROOTURL}imagenes/general/productividad18.png" /></td>
		<td class="campos"><a href="#" onclick="despliegaDiv('divCont3')">Reportes de Productividad.</a></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td id="divCont3" style="display:none;">
			<table class="">
				
                <tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/productividad18.png" /><a href="code/reportes/reportes.php?reporte=16">Ventas por Vendedor</a></td>
				</tr>
				<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/productividad18.png" /><a href="code/reportes/reportes.php?reporte=17">Balance Anual MesXMes</a></td>
				</tr>
				<!--<tr>
					<td class="campos"><img src="{$ROOTURL}imagenes/general/productividad18.png" /><a href="code/reportes/reportes.php?reporte=18">Comparativo de periodos</a></td>
				</tr>-->
                
				
			</table>
		</td>
	</tr>
	</table>
	</td>
</tr>			
{/if}
</table>	
</form>


</div>
{* toda esta parte de scrips se integrara a un archivo js generar propio de los catalagos
y alli se manejaran las excepciones
*} 

{literal}


<script type="text/javascript" language="javascript">
function despliegaDiv(div){
	var divDesp = document.getElementById(div);
	if(divDesp.style.display == 'none')
		divDesp.style.display = 'block';
	else
		divDesp.style.display = 'none';
	return true;
}

</script>
{/literal}

{include file="_footer.tpl" aktUser=$username}