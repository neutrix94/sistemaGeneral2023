<script>

	{literal}

		function cambiaDesc(pos, grid, posOri, posFin)
		{
			var val=celdaValorXY(grid, posOri, pos);
			var aux=val.split(":");
			valorXY(grid, posFin, pos, aux[1]);
			valorXYNoOnChange(grid, posOri, pos, aux[0]);
		}
		
		
	
	{/literal}
	
	{if $tabla eq 'ec_transferencias'}
		{literal}
		
			function actProdDes(pos)
			{
				
				var aux=celdaValorXY('transferenciasProductos', 2, pos);
				
				//alert(aux);
				
				var ax=aux.split(":");
				var id=ax[0];
				
				valorXY('transferenciasProductos', 5, pos, id);
				
				/*var suc_dest=document.getElementById('id_sucursal_destino').value;
				
				if(id != '')
				{
					var url="../ajax/catalogos/getProdEq.php?id_producto="+id+"&suc_dest"+suc_dest;
					var res=ajaxR(url);
					var aux=res.split('|');
					if(aux[0] == 'exito')
					{
						valorXY('transferenciasProductos', 5, pos, id);
					}
					else
					{
						alert(res);
						valorXY('transferenciasProductos', 2, pos, '');
						//htmlXY('tansferenciasProductos', 2, pos, '');
						htmlXY('transferenciasProductos', 3, pos, '&nbsp;');
						return false;
					}
				}*/
			}
		
		
		{/literal}
	{/if}	
	
	
	{if $tabla eq 'ec_conf_oc'}
		{literal}
		
			function cambiaEd(obj)
			{
				
				var opre=document.getElementById('prefijo_folio');
				var ocon=document.getElementById('contador_folio');
				
				if(obj.checked == true)
				{
					opre.readOnly=true;
					ocon.readOnly=true;
					opre.className="barra";
					ocon.className="barra";
					opre.value="";
					ocon.value=0;
				}
				else
				{
					opre.readOnly=false;
					ocon.readOnly=false;
					opre.className="barra_tres";
					ocon.className="barra_tres";
				}
			}
		
		
		{/literal}
	{/if}
	
	{if $tabla eq 'ec_clientes' && $no_tabla eq '0'}
		{literal}
		
			function cambiaPrecs(pos,tipoD)
			{
				//alert(tipoD)
				if(tipoD == 1)
				{
					//alert("1?");
					valorXYNoOnChange('clientesProductos', 5, pos, 0);
					htmlXY('clientesProductos', 5, pos, '0.00%');
					valorXYNoOnChange('clientesProductos', 6, pos, 0);
					htmlXY('clientesProductos', 6, pos, '$0.00');
				}
				if(tipoD == 2)
				{
					//alert("2?");
					valorXYNoOnChange('clientesProductos', 4, pos, 0);
					htmlXY('clientesProductos', 4, pos, '$0.00');
					valorXYNoOnChange('clientesProductos', 6, pos, 0);
					htmlXY('clientesProductos', 6, pos, '$0.00');
				}
				if(tipoD == 3)
				{
					//alert("3?");
					valorXYNoOnChange('clientesProductos', 4, pos, 0);
					//htmlXY('clientesProductos', 4, pos, '$0.00');
					valorXYNoOnChange('clientesProductos', 5, pos, 0);
					//htmlXY('clientesProductos', 5, pos, '0.00%');
				}
			}
		
		
		
		{/literal}
	{/if}
	
	{if $tabla eq 'ec_notas_credito' && $no_tabla eq '0'}
	
		{literal}
		
			function actPreImp(pos)
			{
				
				var aux=celdaValorXY('notascreditoProds', 2, pos);
				
				var ax=aux.split(":");
				
				var aux=ajaxR("../ajax/catalogos/getDatosProd.php?id="+ax[0]);
				
				ax=aux.split('|');
				
				if(ax[0] == 'exito')
				{
					
					
					valorXY('notascreditoProds', 5, pos, ax[4]);
					
					valorXY('notascreditoProds', 9, pos, ax[2]);
					
					valorXY('notascreditoProds', 10, pos, ax[3]);
					
					valorXY('notascreditoProds', 4, pos, 0);
					
					valorXY('notascreditoProds', 4, pos, 1);
				}
				else
					alert(aux);
				//alert('11');	
			}
			
			function calculaTotales()
			{
				var totmont=0;
				var totiva=0;
				var totieps=0;
				
				for(var i=0;i<NumFilas('notascreditoProds');i++)
				{
					var aux=celdaValorXY('notascreditoProds', 6, i);
					var can=celdaValorXY('notascreditoProds', 4, i);
					var iva=celdaValorXY('notascreditoProds', 7, i);
					var ieps=celdaValorXY('notascreditoProds', 8, i);
					
					can=isNaN(parseFloat(can))?0:parseFloat(can);
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					ieps=isNaN(parseFloat(ieps))?0:parseFloat(ieps);
					
					//alert(iva);
					
					totmont+=isNaN(parseFloat(aux))?0:parseFloat(aux);
					totiva+=can*iva;
					totieps+=can*ieps;
					
					//var aux=celdaValorXY('ocproductos', 7, i);
				}
				
				
				document.getElementById('subtotal').value=redond(totmont, 2);
				document.getElementById('iva').value=redond(totiva, 2);
				//document.getElementById('ieps').value=redond(totieps, 2);
				document.getElementById('total').value=redond(totiva+totieps+totmont, 2);
				//cambiaPagado();
				
			}

			
		
		{/literal}
		
	
	{/if}
	
	
	
	{if $tabla eq 'ec_rutas'}
		{literal}
		
			function muestraCliente(pos)
			{
				var aux=celdaValorXY('rutasPedidos', 2, pos);
				var ax=aux.split(':');
				valorXY('rutasPedidos', 3, pos, ax[2]);
				valorXYNoOnChange('rutasPedidos', 2, pos, ax[0]);
			}	
		
		{/literal}
	{/if}	
	
	{if $tabla eq 'ec_maquila'}
		{literal}
			
			function cambiaMaquila(val)
			{
				//alert('OK');
				RecargaGrid('productosFinal', '../ajax/catalogos/prodMaquila.php?id='+val)
			}
			
			cambiaMaquila(document.getElementById('id_producto').value);
			
		{/literal}
	{/if}

	{if $tabla eq 'ec_pedidos'}
		{literal}
		
		
			function habilitaDir(val)
			{
				obj=document.getElementById('direccion');
				if(val != -1)
				{
					obj.className='barra';
					obj.readOnly=true;
					obj.value="";
				}
				else
				{
					obj.className='barra_dos';
					obj.readOnly=false;
				}
			}
			
			function validaNuevaFila()
			{
				var cliente=document.getElementById('id_cliente').value;
				
				if(cliente == -1 || cliente == '')
				{
					alert("Debe elegir antes un cliente");
					return false;
				}
				
				return true;
			}
			
		
			function calculaTotales()
			{
				var totmont=0;
				var totiva=0;
				var totieps=0;
				
				for(var i=0;i<NumFilas('pedidoProductos');i++)
				{
					var aux=celdaValorXY('pedidoProductos', 6, i);
					var can=celdaValorXY('pedidoProductos', 4, i);
					var iva=celdaValorXY('pedidoProductos', 8, i);
					var ieps=celdaValorXY('pedidoProductos', 9, i);
					
					can=isNaN(parseFloat(can))?0:parseFloat(can);
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					ieps=isNaN(parseFloat(ieps))?0:parseFloat(ieps);
					
					//alert(iva);
					
					totmont+=isNaN(parseFloat(aux))?0:parseFloat(aux);
					totiva+=can*iva;
					totieps+=can*ieps;
					
					//var aux=celdaValorXY('ocproductos', 7, i);
				}
				for(var i=0;i<NumFilas('pedidoOtros');i++)
				{
					var aux=celdaValorXY('pedidoOtros', 3, i);
					var iva=celdaValorXY('pedidoOtros', 4, i);
					
					
					
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					aux=isNaN(parseFloat(aux))?0:parseFloat(aux);
					
					iva=aux*(iva/100);
					
					//alert(iva);
					
					totmont+=aux;
					totiva+=iva;
					
					
					//var aux=celdaValorXY('ocproductos', 7, i);
				}
				
				
				//calculamos descuento
				var url="../ajax/catalogos/getDescuentos.php?id_cliente="+document.getElementById('id_cliente').value;
				url+="&subtotal="+totmont+"&iva="+totiva;
				
				var aux=ajaxR(url);
				var ax=aux.split('|');
				if(ax[0] != 'exito')
				{
					alert(aux);
					return false;
				}
				
				var descuento=parseFloat(ax[1]);
				var totiva=parseFloat(ax[2]);
				
				document.getElementById('subtotal').value=redond(totmont, 2);
				document.getElementById('descuento').value=redond(descuento, 2);
				document.getElementById('iva').value=redond(totiva, 2);
				document.getElementById('ieps').value=redond(totieps, 2);
				document.getElementById('total').value=redond(totiva+totieps+totmont-descuento, 2);
				cambiaPagado();
				
			}
			
			function calculaTotales2()
			{
				var totmont=0;
				var totiva=0;
				var totieps=0;
				
				for(var i=0;i<NumFilas('pedidoProductos');i++)
				{
					var aux=celdaValorXY('pedidoProductos', 7, i);
					var can=celdaValorXY('pedidoProductos', 4, i);
					var iva=celdaValorXY('pedidoProductos', 8, i);
					var ieps=celdaValorXY('pedidoProductos', 9, i);
					
					can=isNaN(parseFloat(can))?0:parseFloat(can);
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					ieps=isNaN(parseFloat(ieps))?0:parseFloat(ieps);
					
					//alert(iva);
					
					totmont+=isNaN(parseFloat(aux))?0:parseFloat(aux);
					totiva+=can*iva;
					totieps+=can*ieps;
					
					//var aux=celdaValorXY('ocproductos', 7, i);
				}
				for(var i=0;i<NumFilas('pedidoOtros');i++)
				{
					var aux=celdaValorXY('pedidoOtros', 3, i);
					var iva=celdaValorXY('pedidoOtros', 4, i);
					
					
					
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					aux=isNaN(parseFloat(aux))?0:parseFloat(aux);
					
					iva=aux*(iva/100);
					
					//alert(iva);
					
					totmont+=aux;
					totiva+=iva;
					
					
					//var aux=celdaValorXY('ocproductos', 7, i);
				}
				
				
				//calculamos descuento
				var url="../ajax/catalogos/getDescuentos.php?id_cliente="+document.getElementById('id_cliente').value;
				url+="&subtotal="+totmont+"&iva="+totiva;
				
				var aux=ajaxR(url);
				var ax=aux.split('|');
				if(ax[0] != 'exito')
				{
					alert(aux);
					return false;
				}
				
				var descuento=parseFloat(ax[1]);
				var totiva=parseFloat(ax[2]);
						
				document.getElementById('subtotal').value=redond(totmont, 2);
				document.getElementById('descuento').value=redond(descuento, 2);
				document.getElementById('iva').value=redond(totiva, 2);
				document.getElementById('ieps').value=redond(totieps, 2);
				document.getElementById('total').value=redond(totiva+totieps+totmont-descuento, 2);
				
				cambiaPagado();
				
			}
			
			
			
			//calculaTotales();
			
			
			
			function cambiaPagado()
			{
				//alert('si');
				var total=document.getElementById('total').value;
				total=isNaN(parseFloat(total))?0:parseFloat(total);
				
				totpagos=0;
				
				for(i=0;i<NumFilas('pedidoPagos');i++)
				{
					var monto=celdaValorXY('pedidoPagos', 6, i);
					monto=isNaN(parseFloat(monto))?0:parseFloat(monto);
					
					totpagos+=monto;
				}
				
				if(totpagos >= total)
				{
					document.getElementById('pagado').value=1;
				}
				else
				{
					document.getElementById('pagado').value=0;
				}
			}
		
			function actPreImp(pos)
			{
				var aux=celdaValorXY('pedidoProductos', 2, pos);
				var ax=aux.split(":");
				var aux=ajaxR("../ajax/catalogos/getDatosProd.php?id="+ax[0]+"&id_cliente="+document.getElementById('id_cliente').value);
				
				ax=aux.split('|');
				if(ax[0] == 'exito')
				{
					//alert(aux);
					valorXY('pedidoProductos', 5, pos, ax[1]);
					valorXY('pedidoProductos', 10, pos, ax[2]);
					valorXY('pedidoProductos', 11, pos, ax[3]);
					valorXY('pedidoProductos', 4, pos, 0);
					valorXY('pedidoProductos', 4, pos, 1);
				}
				else
					alert(aux);
			}
			
			
			function actPreImp2(pos)
			{
				var aux=celdaValorXY('pedidoProductos', 2, pos);
				var ax=aux.split(":");
				var aux=ajaxR("../ajax/catalogos/getDatosProd.php?id="+ax[0]+"&id_cliente="+document.getElementById('id_cliente').value);
				
				ax=aux.split('|');
				if(ax[0] == 'exito')
				{
					//alert(aux);
					valorXY('pedidoProductos', 6, pos, ax[1]);
					valorXY('pedidoProductos', 10, pos, ax[2]);
					valorXY('pedidoProductos', 11, pos, ax[3]);
					valorXY('pedidoProductos', 4, pos, 0);
					valorXY('pedidoProductos', 4, pos, 1);
				}
				else
					alert(aux);
			}
		
		{/literal}
	{/if}


	{if $tabla eq 'ec_ordenes_compra'}
		{literal}
		
			function calculaTotales()
			{
				var totmont=0;
				var totiva=0;
				var totieps=0;
				
				for(var i=0;i<NumFilas('ocproductos');i++)
				{
					var aux=celdaValorXY('ocproductos', 7, i);
					var can=celdaValorXY('ocproductos', 4, i);
					var iva=celdaValorXY('ocproductos', 8, i);
					var ieps=celdaValorXY('ocproductos', 9, i);
					
					can=isNaN(parseFloat(can))?0:parseFloat(can);
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					ieps=isNaN(parseFloat(ieps))?0:parseFloat(ieps);
					
					//alert(iva);
					
					totmont+=isNaN(parseFloat(aux))?0:parseFloat(aux);
					totiva+=can*iva;
					totieps+=can*ieps;
					
					//var aux=celdaValorXY('ocproductos', 7, i);
				}
				for(var i=0;i<NumFilas('ocOtros');i++)
				{
					var aux=celdaValorXY('ocOtros', 3, i);
					var iva=celdaValorXY('ocOtros', 4, i);
					
					
					
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					aux=isNaN(parseFloat(aux))?0:parseFloat(aux);
					
					iva=aux*(iva/100);
					
					//alert(iva);
					
					totmont+=aux;
					totiva+=iva;
					
					
					var aux=celdaValorXY('ocproductos', 7, i);
				}
				
				document.getElementById('subtotal').value=redond(totmont, 2);
				document.getElementById('iva').value=redond(totiva, 2);
				document.getElementById('total').value=redond(totiva+totieps+totmont, 2);
				cambiaPagado();
				
			}
			
			
			
			calculaTotales();
			
			
			
			function cambiaPagado()
			{
				//alert('si');
				var total=document.getElementById('total').value;
				total=isNaN(parseFloat(total))?0:parseFloat(total);
				
				totpagos=0;
				
				for(i=0;i<NumFilas('ocpagos');i++)
				{
					var monto=celdaValorXY('ocpagos', 5, i);
					monto=isNaN(parseFloat(monto))?0:parseFloat(monto);
					
					totpagos+=monto;
				}
				
				//alert(totpagos+" "+total);
				
				if(totpagos >= total)
				{
					document.getElementById('pagada').value=1;
				}
				else
				{
					document.getElementById('pagada').value=0;
				}
			}
		
			function actPreImp(pos)
			{
				var aux=celdaValorXY('ocproductos', 2, pos);
				var ax=aux.split(":");
				var prov=document.getElementById('id_proveedor').value;
				
				var aux=ajaxR("../ajax/catalogos/getDatosProd.php?id="+ax[0]+"&id_proveedor="+prov);
				
				
				
				ax=aux.split('|');
				if(ax[0] == 'exito')
				{
					//alert(aux);
					valorXY('ocproductos', 6, pos, ax[1]);
					valorXY('ocproductos', 10, pos, ax[2]);
					valorXY('ocproductos', 11, pos, ax[3]);
					valorXY('ocproductos', 4, pos, 0);
					valorXY('ocproductos', 4, pos, 1);
				}
				else
					alert(aux);
			}
		
		{/literal}
	{/if}

	{if $tabla eq 'ec_productos'}
	
		{literal}
	
			function cambiaGI(obj)
			{
				
				var of=document.getElementById("porc_iva");
				
				if(!of)
				{
					alert("Error objeto no encontrado");
					return false;
				}
				
				if(obj.checked == true)
				{
					of.readOnly=false;
					of.className="barra_tres";
				}
				else
				{
					of.readonly=true;
					of.className="barra";
					of.value="0";
				}
			}
			
			function cambiaGIE(obj)
			{
				
				var of=document.getElementById("porc_ieps");
				
				if(!of)
				{
					alert("Error objeto no encontrado");
					return false;
				}
				
				if(obj.checked == true)
				{
					of.readOnly=false;
					of.className="barra_tres";
				}
				else
				{
					of.readonly=true;
					of.className="barra";
					of.value="0";
				}
			}
	
	
		{/literal}
	
	{/if}

</script>