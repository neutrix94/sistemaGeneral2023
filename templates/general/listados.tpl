{include file="_header.tpl" pagetitle="$contentheader"}

{include file="../../conectMin.php" pagetitle="example"}
{include file="general/emergents.tpl" pagetitle="$contentheader"}
{literal}
	<script>
		var file_control_version = "1.1 Separación de Proceso Transferencias rápidas ( 2024-08-12 )";
		var current_file_name = "/templates/general/<b>listados.tpl";
	</script>
{/literal}
<!--implementacion Oscar 28.08.2019 para el botón de editar desde la pantalla de pedidos-->
	{if $tabla eq 'ZWNfb3JkZW5lc19jb21wcmE=' && $no_tabla eq "MQ=="}
		<button style="position:absolute;top:340px;left:70%;background:transparent;border:0;" onclick="abrePedido('null',0)">
			<img src="../../img/especiales/pedidos.svg" width="80px"><br>
			<b style="color:#47A7DE;font-size:20px;">Abrir<br>Pantalla<br>Pedidos</b>
		</button>
	{/if}
<!--Fin de cambio Oscar 28.08.2019-->

<!--implementación Oscar 21.08.2019 para recargar salidas en asistencia de usuarios-->
	{if $tabla eq 'ZWNfcmVnaXN0cm9fbm9taW5h' && $no_tabla eq 'MQ=='}
		<button style="position:absolute;top:340px;left:70%;background:transparent;border:0;" onclick="location.reload();">
			<img src="../../img/especiales/recargar.png" width="80px"><br>
			<b style="color:#47A7DE;font-size:20px;">Recargar</b>
		</button>
	{/if}
<!--Fin de cambio Oscar 21.08.2019-->

<!--implementación Oscar 30.07.2019 para capturar el historico de productos con estacionalidad por sucursal-->
	{if $tabla eq 'c3lzX3N1Y3Vyc2FsZXM=' && $no_tabla eq 'MA==' || $tabla eq 'ZWNfZXN0YWNpb25hbGlkYWQ=' && $no_tabla eq 'MA==' }
		{php}
			$id_user_perfil=$this->get_template_vars('perfil_id');
			$sql="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_menu=192 AND id_perfil=$id_user_perfil";
			$eje=mysql_query($sql)or die("Error al consultar el permio especial para el histórico de estacionalidades!!!");
			$r=mysql_fetch_row($eje);
			if($r[0]==1){
				echo '<button type="button" style="position:absolute;border-radius:10px;padding:5px;top:120%;right:10%;"
				title="Presione este botón para obtener el reumen de estacionalidades en este año" onclick="resumen_estacionalidades();">
					<img src="../../img/especiales/resumir.png" width="60px"><br>
					<b>Resumir<br>Estac.</b>
				</button>';
			}
		{/php}
	{/if}
<!--Fin de cambio Oscar 30.07.2019-->

<!--implementación Oscar 26.09.2018 para permitir/denegar proceso de transferencia en local-->
{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && ($no_tabla eq 'NA==' || $no_tabla eq 'Mg==')}
	
	{php}
		if($this->get_template_vars('tipo_sistema')=='local'){
			$sucursal_id=$this->get_template_vars('user_sucursal');/*aqui recibimos la variable de id_sucursal_logueo*/ 
			$sql="SELECT permite_transferencias FROM sys_sucursales WHERE id_sucursal=$sucursal_id";
			$eje=mysql_query($sql)or die("Error al verificar si se tiene permiso de continuar con el proceso de la tranasferencia!!!\n\n".$sql."\n\n".mysql_error());
			$permiso_transfer=mysql_fetch_row($eje);
			if($permiso_transfer[0]==0){
				die('<script>alert("No es posible continuar el proceso de transferencia localmente.\nContacte al Administrador");
					location.href="../../index.php?";</script>');
			}
		}
	{/php}
{/if}
<!--fin de cambio-->
{php}
	$sql="SELECT id_sucursal,nombre from sys_sucursales WHERE id_sucursal>0";
	$eje=mysql_query($sql)or die("error al consultar las sucursales!!!");
	$combo_sucs='<p style="position:absolute;top:-150px;right:200px;" align="center"><b>Sucursal:</b><select id="sucursales_corte"   style="padding: 12px;" onchange="recarga_corte();">
	<option value="-1">Todas las sucursales</option>';
	while($r=mysql_fetch_row($eje)){
		$combo_sucs.='<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	$combo_sucs.='</select></p>';

{/php}

<!--Implementación Oscar 13.07.2019 para insertar una remisión de proveedor-->
{if $tabla eq 'ZWNfb2NfcmVjZXBjaW9u' && $no_tabla eq 'MA=='}
	<button onclick="alta_remision();" style="background:white;position:absolute;top:320px;left:600px;align-items:center;border-radius:50%;padding:10px;">
		<img src="../../img/especiales/remision.png" width="50px" height="30px"><br>
		Nueva<br>Remision
	</button>
{/if}
<!---->


{literal}
<script language="JavaScript" type="text/javascript" src="../../js/papaparse.min.js"></script>
<!--implementación Oscar 29.04.2019 para la librería passterisco-->
<script type="text/javascript" src="../../js/passteriscoByNeutrix.js"></script>
<!--Fin de cambio Oscar 29.04.2019-->
<!--Implementado por Oscar 11.04.2018-->
	<style>
		.bot_exp{
			position:absolute;
			bottom: -480px;
			z-index: 100;
			right: 130px;
			border-radius: 10px;
			background:#FFFACD;
			border:1px solid green;
		}
		.bot_imp{
			position:absolute;
			bottom: -480px;
			z-index: 100;
			right:230px;
			border-radius: 10px;
			background:#FFFACD;
			border:1px solid green;
		}
		.bot_imp:hover,.bot_exp:hover{
			background: gray;
			color:white;
		}
		.nom_csv{
			position:absolute;
			bottom: -485px;
			z-index: 100;
			right:320px;
			width: 230px;
		}
		.exportaEst_btn{
			border-radius: 8px;
		}
		.exportaEst_btn:hover{
			background: rgba(0,0,0,.5);
			color:white;
		}
	</style>
<!--Fin de implementación 11.04.2018-->

{/literal}

<!--Implementacion Oscar 01.07.2019 para los filtros de la sucursal-->
{if $tabla eq 'ZWNfc2VzaW9uX2NhamE='}
	<div style="position: absolute;top:70%;right: 10%;">
		{php}
			echo $combo_sucs;
		{/php}
		<p style="position:absolute;top:-150px;right:50px;" align="center"><b>Diferencia:</b>
			<select id="opciones_filtro_corte" onchange="recarga_corte();" style="padding: 12px;">
				<option value="1">Ver todos</option>
				<option value="2">Ver con diferencia</option>
				<option value="3">Ver sin diferencia</option>
			</select>
		</p>

		<p style="position:absolute;top:-150px;right:-50px;" align="center"><b>Status:</b>
			<select id="opciones_filtro_corte_status" onchange="recarga_corte();" style="padding: 12px;">
				<option value="1">Ver todos</option>
				<option value="2">No validados</option>
				<option value="3">Validados</option>
			</select>
		</p>
	</div>
{/if}
<!--Fin de cambio Oscar 01.07.2019-->

	<div id="emergenteAutorizaTransfer" style="width:100%;height:160%;background:rgba(0,0,0,.8);position:absolute;display:none;z-index:100;top:0;left:0;">
		<br><br><br><br><br><br><br><br>
	<center>
		<div id="contenidoInfo" style="border:1px solid white;width:60%;top:300px;border-radius:20px;background:rgba(0,0,0,0.5);">
			<p align="center" id="textInfo" style="font-size:30px;color:white;">
				<b>Guardando transferencia...</b>
			</p>
			<p align="center" id="imgInfo">
				<img src="../../img/img_casadelasluces/load.gif" height="15%" width="17%">		
			</p>
		</div>
	</center>
	</div>
<div id="campos">
	 <!--<div id="titulo">{$datos[1]}</div>-->
      <img  class="icono"width="35" height="35" border="0" src="{$rooturl}{$imgMenu}"><div id="titulo">{$datos[1]}</div>
<!--Implementación de Buscador (Oscar)--> 
	 <div id="filtros">
	 	{if $datos[11] eq '1'}
	 		<p align="left" style=""><b>Buscador:</b>
				<input type="text" style="width:45%;" id="seeker" onkeyup="buscaLista(this, event, '{$datos[0]}','{$tabla}');"><!--Cambio de Oscar 24.05.2018 transf impresas en verde; se envía variable de tabla -->
				<button 
        			type="button"
        			onclick="buscaLista('#seeker', 'enter', '{$datos[0]}','{$tabla}', this.form);"
        			style="padding : 10px; background : green; color : white;margin-left : -4px ;"
        		>
            			<i class="icon-search">Buscar</i>
            	</button>
			</p>
	 	{/if}
	 	{literal}
	 		<script>
	 			function buscaLista(obj, e, gr,tabla_list){//se agrega la variable de la tabla de listado para pintar de verde transferencias ya imprimidas Oscar 24.05.2018	
	 				var obj_b = $( obj ).val().trim();
	 				if( e.keyCode != 13 && e != 'enter' ){
						return false;
					}
	 				//alert(obj_b.length);
	 				if(obj_b.length<3){
	 					if(obj_b.length<=1){
						//CargaGrid('listado');
	 					}
	 				}
					{/literal}
						var url="datosListados.php?id_listado={$datos[0]}";
					{literal}
				
				url+="&valor="+obj_b;//&campo="+f.campo.value+"&operador="+f.operador.value+"
				RecargaGrid('listado', url,tabla_list);//se envía la variable de la tabla de listado para pintar de verde transferencias ya imprimidas Oscar 24.05.2018	
			}	
	 		</script>
	 	{/literal}
<!--Implmentación de filtros de Fecha en bitácora de sincronización Oscar 14.08.2018-->
		{if $tabla eq 'c3lzX21vZHVsb3Nfc2luY3Jvbml6YWNpb24='}
			<table width="60%;" border="0">
				<tr>
						<td width="15%" align="center"><b>Filtrar por fecha del: </b></td>
						<td width="40%" align="center"><p style="width:80%;" align="center"><input type="text" id="f_del" onfocus="calendario(this);"></p></td>
						<td width="5%" align="center"><b> al: </b></td>
						<td width="40%"><p style="width:80%;"><input type="text" id="f_al" onfocus="calendario(this);"></p></td>
					</tr>
				</table>
		{/if}
<!--Fin de implementación de bitácora de sincronización-->

	 	<form id="form1" name="form1" method="post" action="">
		<input type="hidden" name="tabla" value="{$tabla}"/>
		<input type="hidden" name="no_tabla" value="{$no_tabla}"/>
		<table  style="position:relative;left:65%;width:50%;top:-70px;"><tr>
          		{if $tabla eq 'ZWNfcGVkaWRvcw==NO'}
          			<td width="70">
            		<input name="button3" type="button" class="boton" id="button3" value="CSV" onclick="prueba()"/>

				{/if}
          		<td width="100" align="left"><table width="37" border="0" align="left">
          	  {if $mostrar_nuevo eq '1' && $datos[10] eq '1'}		
			  <tr>

			    <td valign="bottom" class="nuevo nuevobtn nuv-img">
               
               <img src="{$rooturl}img/nuevo.png" alt="Crear Nuevo" border="0"  onclick="abreMod(0,'#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';"/>
               
           </td>
                <table>
                 <tr>
                 	<td class="btn-infe"><p>Agregar nuevo</p></td></tr>
                 </table></td>     
              </tr>
            
		      {/if}
			</table></td>
	 <!--Deshabilitador por Oscar 14/02/2018-->
	 	<table width="872" border="0">
        	<tr>
          		<td width="52" class="motivo">Campo</td>
          		<td width="62">				
		            <span id="spryselect1">              
              			<select name="campo" id="campo" >
							{html_options values=$datos[4] output=$datos[9]}
						</select>
            		</span>
          		</td>
          		<td width="31" class="motivo">que</td>
          		<td width="52">
            		<span id="spryselect1">              
              			<select name="operador" id="select1" >
							<option value="contiene">contiene a...</option>
							<option value="=">igual a (&#61;)</option>
							<option value="!=">diferente de (&ne;)</option>
							<option value=">">mayor que (&gt;)</option>
							<option value="<">menor que (&lt;)</option>
							<option value=">=">mayor o igual que (&ge;)</option>
							<option value="<=">menor o igual que (&le;)</option>
							<option value="empieza">empieza con...</option>	
						</select>
            		</span>
          		</td>
          		<td width="15" class="motivo">a</td>
          		<td width="100">
            		<span id="sprytextfield1">            		
              			<input name="valor" type="text" class="barra2" id="text1" />
              		</span>
          		</td>
          		<td width="35">
            		<input name="button" type="button" class="boton" id="button" value="Filtrar" onclick="filtra(1)"/>
            	<td width="70">
            		<input name="button2" type="button" class="boton" id="button2" value="Ver todos" onclick="filtra(0)"/> 
          		</td>
          		</td>
        	</tr>
      </table>
	  </form>
<!--Hasta aqui se había comentado para deshabilitar filtros anteriores-->
    </div><!--Fin de div #filtros-->

	<div id="bg_seccion">
		 <div class="name_module" align="center">
			<p>Listado</p>		    
		</div>
		<div id="cosa1">
	
			<table align="center">
	    
				<!-- Codigo Grid de Iván -->
				<tr>
                <td align="center">
					<table id="listado" cellpadding="0" cellspacing="0" Alto="255"
						   conScroll="S" validaNuevo="false" AltoCelda="25" auxiliar="0" ruta="../../img/grid/" validaElimina="false" Datos="datosListados.php?id_listado={$datos[0]}&id={$id}"
						   verFooter="N" guardaEn="False" listado="S" class="tabla_Grid_RC" paginador="S" datosxPag="30" pagMetodo='php' ordenaPHP="S" title="Listado de Registros">
						<tr class="HeaderCell">
							{section loop=$datos[3] name=x}	
								{if $datos[3][x] eq "ID" or $datos[2][x] eq '0'}
									<td offsetwidth="0" width="0" tipo="oculto" modificable="N" campoBD="{$datos[4][x]}" sentido="ASC">{$datos[9][x]}</td>								
								{else}
									<td width="{$datos[2][x]}" offsetWidth="{$datos[2][x]}" align="{$datos[3][x]}" tipo="texto" modificable="N" campoBD="{$datos[4][x]}">{$datos[9][x]}</td>	
								{/if}	
							{/section}



						
<!--Implementación Oscar 2022 para Botónes de transferencia-->
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'Ng=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Autorizar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="authorize_transfers('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para continuar proceso de la transferencia"/>
								</td>
<!--Deshabilitado por Oscar 2023
								<td width="56" offsetWidth="56" tipo="libre" valor="Transito" align="center" campoBD='{$valuesEncGrid[x]}'>
									<button 
										class="icon-truck" 
										width="22" 
										height="22" 
										border="0" 
										onclick="put_transfer_in_transit('#')" 
										style="color : ; background : orange;"
										onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para continuar proceso de la transferencia"></button>
								</td-->
						<Fin de cambio Oscar 15.04.2017-->
							{/if}

						<!--Implementación Oscar 15.04.2017 para Botón para redireccionar a proceso de transferencia-->
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'MA=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Continuar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="autorizaTrans('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para continuar proceso de la transferencia"/>
								</td>
						<!--Fin de cambio Oscar 15.04.2017-->
							{/if}

					<!--aqui se genera boton de visualizar-->
							{if $datos[5] eq '1'}
								<td width="56" offsetWidth="56" tipo="libre" valor="Ver" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="vermini" src="{$rooturl}img/vermini.png" height="22" width="22" border="0"  onclick="abreMod(2,'#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Ver" title="Ver Registro"/>
								</td>
							{/if}	
							{if $datos[6] eq '1' && $mostrar_mod eq '1'}
								<td width="62" offsetWidth="56" tipo="libre" valor="{if $tabla eq 'ZWNfb3JkZW5lc19jb21wcmE=' && $no_tabla eq 'Mg=='}Recibir{else}Modificar{/if}" align="center" campoBD="{$valuesEncGrid[x]}">
									<img class="editarmini" src="{$rooturl}img/editarmini.png" width="22" height="22" border="0" onclick="abreMod(1,'#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Mod" title="Modificar Registro"/>
								</td>
							{/if}
					<!--Aqui modificar condicion para deshabilitar eliminado de transfer-->
						<!---->
							{if $datos[7] eq '1' && $mostrar_eli eq '1' && $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM='}<!---->
                				<td width="55" offsetWidth="55" tipo="libre" valor="Eliminar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="eliminarmini" src="{$rooturl}img/eliminarmini.png" width="22" height="22" border="0" onclick="cancelarTransfer(3,'#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Eliminar" title="Eliminar Transferencia"/>
								</td>
							{/if}<!--fin de cambio 27.05.2018-->

							{if $datos[7] eq '1' && $mostrar_eli eq '1' && $tabla neq 'ZWNfdHJhbnNmZXJlbmNpYXM='}
                				<td width="55" offsetWidth="55" tipo="libre" valor="Eliminar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="eliminarmini" src="{$rooturl}img/eliminarmini.png" width="22" height="22" border="0" onclick="abreMod(3,'#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Eliminar" title="Eliminar Registro"/>
								</td>
			                {/if}
			
							{if ($tabla eq 'ZWNfb3JkZW5lc19jb21wcmE=NOVA' || ($tabla eq 'ZWNfcGVkaWRvcw==' && $no_tabla eq 'MA==')
							     || ($tabla eq 'ZWNfcGVkaWRvcw==' && $no_tabla eq 'MQ==')) && $mostrar_imp eq '1'}
                				<td width="56" offsetWidth="56" tipo="libre" valor="Imprimir" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="imprimirmini" src="{$rooturl}img/imprimirmini.png" width="22" height="22" border="0" onclick="imprimirCot('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Imprimir" title="imprimir Registro"/>
								</td>
			                {/if}
			                
			                {if ($tabla eq 'ZWNfb3JkZW5lc19jb21wcmE=' && $no_tabla eq 'MA==') || ($tabla eq 'ZWNfcGVkaWRvcw==NOVER' && $no_tabla eq 'MA==')}
                				<td width="56" offsetWidth="56" tipo="libre" valor="Autorizar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="autorizaCot('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="imprimir Registro"/>
								</td>
			                {/if}
			                {if $tabla eq 'ZWNfcGVkaWRvcw==NOVER' && $no_tabla eq 'MQ=='}
                				<td width="56" offsetWidth="56" tipo="libre" valor="Facturar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="facturarmini" src="{$rooturl}img/imprimirmini.png" width="22" height="22" border="0" onclick="facturar('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Facturar" title="facturar Nota de Venta"/>
								</td>
			                {/if}
							
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'MA=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Visualizar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="imprimirmini" src="{$rooturl}img/img_casadelasluces/pdf_icon.png" width="22" height="22" border="0" onclick="viewTrans('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Ver pdf" title="De clic para imprimir la transferencia"/>
								</td>
							{/if}

							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'Ng=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="PDF" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="imprimirmini" src="{$rooturl}img/img_casadelasluces/pdf_icon.png" width="22" height="22" border="0" onclick="viewTrans('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Ver pdf" title="De clic para imprimir la transferencia"/>
								</td>
							{/if}
						<!-- Recibir transferencia rapida -->
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'Ng=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Finalizar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<button 
										class="icon-ok-1" 
										width="22" height="22" border="0" 
										onclick="finish_transfer_by_button('#');" 
										style="color : white; background : green;"
										alt="Ver pdf" 
										title="De clic para finalizar la transferencia">
									</button>
								</td>
							{/if}
						<!--  -->

							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'MA=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Imprimir" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="imprimirmini" src="{$rooturl}img/imprimirmini.png" width="22" height="22" border="0" onclick="imprimeTrans('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para imprimir la transferencia"/>
								</td>
							{/if}
							
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'MQ=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Dar salida" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="salidaTrans('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Dar salida" title="De clic para dar salida a la transferencia"/>
								</td>
							{/if}
					<!--Aqui es el boton de recepcion de Transferencias-->
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'Mg=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Dar recepción" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="recepcionTrans('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Dar salida" title="De clic para dar recepción a la transferencia"/>
								</td>
							{/if}
							
							
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'Mw=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Verificar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="varificarTrans('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Dar salida" title="De clic para verificar la transferencia"/>
								</td>
							{/if}

							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'NA=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Resolver" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="resolverTrans('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Dar salida" title="De clic para darle resolución a la transferencia"/>
								</td>
							{/if}
							
							
							{if $tabla eq 'ZWNfZGV2b2x1Y2lvbl90cmFuc2ZlcmVuY2lh' && $no_tabla eq 'MQ=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Resolver" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="resolverDev('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Dar salida" title="De clic para darle resolución a la devolución"/>
								</td>
							{/if}
							<!-- ********************** BOTON FACTURAR EN VENTAS ******************************-->
							{if $tabla eq 'ZWNfdmVudGFz' && $no_tabla eq 'MA=='}
								<td width="65" offsetWidth="65" tipo="libre" valor="Facturar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="factura('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Facturar" title="De clic para facturar"/>
								</td>
							{/if}
							<!-- ********************** BOTON E-MAIL EN VENTAS ******************************-->
							{if $tabla eq 'ZWNfdmVudGFz' && $no_tabla eq 'MA=='}
								<td width="65" offsetWidth="65" tipo="libre" valor="Facturar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="enviarMail('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Facturar" title="De clic para facturar"/>
								</td>
							{/if}
			<!--Implementación Oscar 21.02.2019 para agregar botón de reimpresión de resolución de Transferencias-->
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'NQ=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Imprimir" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="imprimirmini" src="{$rooturl}img/impresion_tkt.png" width="22" height="22" border="0" onclick="imprimeTicketTrans('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para imprimir ticket de resolucion"/>
								</td>
							{/if}
			<!--Fin de cambio Oscar 21.02.2019-->

			<!--Implementación Oscar 22.02.2019 para agregar botón de impresión de Transferencias en ticket-->
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && ( $no_tabla eq 'MA==' || $no_tabla eq 'MTI=' ) }<!-- implementacion Oscar 2023 para el listado de resoluciones -->
								<td width="56" offsetWidth="56" tipo="libre" valor="Ticket" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="imprimirmini" src="{$rooturl}img/impresion_tkt.png" width="22" height="22" border="0" onclick="imprimeTicketTrans('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para imprimir ticket de resolucion"/>
								</td>
							{/if}
			<!--Fin de cambio Oscar 21.02.2019-->

			<!-- Implementacion Oscar 2023/11/05 para el ticket de transferencias rapidas -->
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla eq 'Ng==' }
								<td width="56" offsetWidth="56" tipo="libre" valor="Ticket" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="imprimirmini" src="{$rooturl}img/impresion_tkt.png" width="22" height="22" border="0" onclick="show_emergent_fast_transfer_ticket('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para imprimir ticket de resolucion"/>
								</td>
							{/if}
			<!-- Fin de cambio Oscar 2023/11/05 -->

			<!--Implementación Oscar 03.03.2019 para agregar botón de impresión de Transferencias en ticket-->
							{if $tabla eq 'ZWNfZGV2b2x1Y2lvbg==' && $no_tabla eq 'MQ=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Terminar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="imprimirmini" src="{$rooturl}img/ir.png" width="22" height="22" border="0" onclick="continua_devolucion('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De Click para continuar con el proceso de esta devolución"/>
								</td>
							{/if}
			<!--Fin de cambio Oscar 03.03.2019-->

			<!--Implementación Oscar 24.03.2019 para agregar botón de enviar a exclusión de trasferencias-->
							{if $tabla eq 'ZWNfdHJhbnNmZXJlbmNpYV9yYWNpb25lcw==' && $no_tabla eq 'MA=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Excluir" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="imprimirmini" src="{$rooturl}img/ir.png" width="22" height="22" border="0" onclick="envia_exclusion('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De Click para enviar esta producto a exclusión de Transferencias"/>
								</td>
							{/if}
			<!--Fin de cambio Oscar 24.03.2019-->

			<!--implementacion Oscar 19.08.2019 para el botón de impresión de credencial de usuario-->
						{if $tabla eq 'c3lzX3VzZXJz' && $no_tabla eq 'MA=='}
							<td width="56" offsetWidth="56" tipo="libre" valor="imp Cred" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/especiales/credencial_usuario.png" width="22" height="22" border="0" onclick="imprimeCredencial('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para imprimir credencial de usuario"/>
							</td>
						{/if}
			<!--Fin de cambio Oscar 19.08.2019-->

			<!--implementacion Oscar 28.08.2019 para el botón de editar desde la pantalla de pedidos-->
						{if $tabla eq 'ZWNfb3JkZW5lc19jb21wcmE=' && $no_tabla eq "MQ=="}
							<td width="56" offsetWidth="56" tipo="libre" valor="Edit Pedido" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/especiales/edita.png" width="22" height="22" border="0" onclick="abrePedido('#',1);" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para editar desde la pantalla de pedidos"/>
							</td>
						{/if}
			<!--Fin de cambio Oscar 28.08.2019-->

<!--Implementación Oscar 2023/09/23 para el boton de reimprimir desde el listado de cola de impresion-->
							{if $tabla eq 'c3lzX2FyY2hpdm9zX2Rlc2Nhcmdh' && $no_tabla eq 'MA=='}
								<td width="56" offsetWidth="56" tipo="libre" valor="Reenviar" align="center" campoBD='{$valuesEncGrid[x]}'>
									<button 
										class="icon-forward" 
										width="22" 
										height="22" 
										border="0" 
										onclick="reprint_since_queue_list('#')" 
										style="color : ; background : green;"
										onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para continuar proceso de la transferencia"></button>
								</td>
<!--Fin de cambio Oscar 2023/09/23-->
							{/if}

							<!--Implementación de oscar para agregar botones de link hacia pantalla de pedidos
							{if $tabla eq 'ZWNfb2NfcmVjZXBjaW9u' && $no_tabla eq 'MA=='}
								<td width="65" offsetWidth="65" tipo="libre" valor="Pagos" align="center" campoBD='{$valuesEncGrid[x]}'>
									<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="enviarMail('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Facturar" title="De clic para facturar"/>
								</td>
							{/if}-->
						<!--Implementación de oscar para agregar botones de link hacia pantalla de pedidos-
							{if $tabla eq 'ZWNfb3JkZW5lc19jb21wcmE=' && $no_tabla eq 'MQ=='}
								<td width="22" height="22" offsetwidth="65">
									
								</td>
								<td width="22" height="22" offsetwidth="65">

									elim
								</td>
							{/if}
						Fin de cmambio-->

						<!--Implementación de Oscar para botónes de importar y exportar productos 11.04.2018-->
							
							{if $datos[0] eq '12'}

							<button style="padding:5px;border-radius:10px;" onclick="guarda_historico_precio_compra();">
								<b>Guardar Historico<br>Precio Compra</b>
							</button>	
								<button onclick="exportaProds(1);" title="Exportar lista de todos los productos" class="bot_exp">
									<img src="../../img/especiales/exportaCSV1.png" height="40px"><br>Exportar
								</button>
								
								<button onclick="exportaProds(2);" title="Importar lista de productos" class="bot_imp" id="bot_imp">
									<img src="../../img/especiales/importaCSV1.png" height="40px"><br>Importar
								</button><br><br>

								<form class="form-inline">
									<input type="file" id="imp_csv_prd" style="display:none;">
									<p class="nom_csv">
    									<input type="text" id="txt_info_csv" style="display:none;" disabled>
    								</p>
    								<button type="submit" id="submit-file" style="display:none;" class="bot_imp">
    									<img src="../../img/especiales/sube.png" height="40px;">
    									<br>Actualiza
    								</button>
								</form>
							{/if}
						<!--fin de implementación-->
				</tr>           
				 
				  <script>	
					
						CargaGrid('listado','{$tabla}');

						{if $tabla eq 'ZWNfcHJvZHVjdG9z'}
							ordena(2, 'listado','{$tabla}');
					    {else}
							ordena(1, 'listado','{$tabla}');//se agrega variable tabla para transf impresas en Verde Oscar 25.05.2018
						{/if}
				  </script>
				</tr>
			</table>
		</div>


<!--Implementación Oscar 27.09.2018 para exportar/importar todas las estacionalidades-->
		{if $tabla eq 'ZWNfZXN0YWNpb25hbGlkYWQ=' && $no_tabla eq 'MA=='}
		<!--Boton para guardar historico de precios de compra-->
			<br>
		<!--formulario-->
			<form id="TheForm" method="post" action="../ajax/exportaImportaTodasEstacionalidades.php" target="TheWindow">
				<input type="hidden" name="fl" value="1" />
				<input type="hidden" name="f_1" id="f_1" value="" />
				<input type="hidden" name="f_2" id="f_2" value="" />
				<input type="hidden" name="f_3" id="f_3" value="" />
				<input type="hidden" name="f_4" id="f_4" value="" />
			</form>
		<!--fin de formulario-->
			<table width="90%" border="0">
				<tr>
					<td>
						<b>Filtro de fechas de venta más alta</b><br><br>
						<b>Filtro de fechas del promedio</b>
					</td>
					<td>
						<b>Del:</b> <input type="text" id="f_del" style="width:70%;" onfocus="calendario(this);">
						<br><br>
						<b>Del:</b> <input type="text" id="f_del_1" style="width:70%;" onfocus="calendario(this);">
					</td>
					<td>
						<b>Al:</b> <input type="text" id="f_al" style="width:70%;" onfocus="calendario(this);">
						<br><br>
						<b>Al:</b> <input type="text" id="f_al_1" style="width:70%;" onfocus="calendario(this);">
					</td>
					<td align="center">
						<button onclick="exporta_todas_estacionalidades();" class="exportaEst_btn">
							<img src="../../img/especiales/exportaCSV1.png" height="40px">
							<br><b>Exporta Estacionalidades</b>
						</button>
					</td>
				</tr>
			</table>
			<script type="text/javascript">
			{literal}

				var ventana_abierta;
				
				function exporta_todas_estacionalidades(){
				//validamos fechas
					var f1,f2,f3,f4;
					f1=$("#f_del").val();
					f2=$("#f_al").val();
					f3=$("#f_del_1").val();
					f4=$("#f_al_1").val();
					if(f1==''||f1==null){alert("Esta fecha no puede ir vacía!!!");$("#f_del").focus();$("#f_del").click();return false;}
					if(f2==''||f2==null){alert("Esta fecha no puede ir vacía!!!");$("#f_al").focus();$("#f_al").click();return false;}
					if(f3==''||f3==null){alert("Esta fecha no puede ir vacía!!!");$("#f_del_1").focus();$("#f_del_1").click();return false;}
					if(f4==''||f4==null){alert("Esta fecha no puede ir vacía!!!");$("#f_al_1").focus();$("#f_al_1").click();return false;}
				//asignamos los valores a las variables del formulario
				//asignamos los valores a las variables del formulario
					$("#f_1").val(f1);
					$("#f_2").val(f2);
					$("#f_3").val(f3);
					$("#f_4").val(f4);
				//abrimos ventana
					ventana_abierta=window.open('', 'TheWindow');	
				//mandamos el fomrulario
					document.getElementById('TheForm').submit();
				//cerramos la ventana
					//setTimeout(cierra_pestana,2000);
				}/*
				function cierra_pestana(){
					ventana_abierta.close();
				}*/
				</script>
			{/literal}
		{/if}
<!--Fin de cambio 27.09.2018-->

	</div>
</div>
	{literal}
	<script type="text/JavaScript">
	/**/

		function recarga_corte(){
			var filt_sucs,filt_tipo,filt_status_corte,filtros_adicionales="&fltros_adic=";
		//sacamos los filtros
			filt_sucs=$("#sucursales_corte").val();
			filt_tipo=$("#opciones_filtro_corte").val();
			filt_status_corte=$("#opciones_filtro_corte_status").val();
			//CargaGrid('listado','ZWNfc2VzaW9uX2NhamE=');
			{/literal}
				var url="datosListados.php?id_listado={$datos[0]}";
			{literal}
			if(filt_sucs!=-1){
				filtros_adicionales+="AND sc.id_sucursal="+filt_sucs;
			}
		//combo de tipo
			if(filt_tipo==2){
				filtros_adicionales+=" AND sc.total_monto_ventas!=sc.total_monto_validacion";
			}
			if(filt_tipo==3){
				filtros_adicionales+=" AND sc.total_monto_ventas=sc.total_monto_validacion";
			}

			if(filt_status_corte==2){
				filtros_adicionales+=" AND sc.verificado = 0";
			}
			if(filt_status_corte==3){
				filtros_adicionales+=" AND sc.verificado = 1";
			}
			url+=filtros_adicionales;
			//alert(url);
			RecargaGrid('listado', url,'ZWNfc2VzaW9uX2NhamE=');
			//location.href="listados.php?tabla=ZWNfc2VzaW9uX2NhamE=&no_tabla=MA==&f_tip="+filt_tipo+"&filt_suc="+filt_sucs;
		}
	/**/
	
	/*Implementación de Oscar 28.08.2019 para agregar nuevo,ver o modificar desde nueva pantalla de pedidos*/
		function abrePedido(pos,flag){
			var id=$("#listado_0_"+pos).attr("valor");//extraemos el id
			var url="../especiales/pedidos/pedidos.php?";
			if(flag==1){//si es edición
				url+="acc="+flag+"&id_oc="+id;
			}
			location.href=url;
			return false;
		}
		/*Fin de cambio 19.06.2018*/

	/*Implemetación Osscar 19.08.2019 para impresión de la credencial*/
		function imprimeCredencial(pos){//capturamos el id del usuario 
			var id=$("#listado_0_"+pos).attr("valor");
		//mandamos a hacer la imagen del codigo de barras si no existe
			$.post( "../../touch/inc/img_codigo.php", {flag:'credencial',id_usuario:id},function(dat) {
				alert("Credencial Creada!!!");
			});
		}
	/*Fin de cambio Oscar 19.08.2019*/

		function alta_remision(){
	 	//obtenemos los datos para la pantalla emergente
			var aux_ajax=ajaxR('../ajax/getPantallaRemisiones.php?flag=obtener');
			$("#contenidoInfo").html(aux_ajax);
	 		$("#emergenteAutorizaTransfer").css("display","block");
	 	}
	 	function guarda_remision(){
	 	//ocultamos el botón de la remisión
	 		document.getElementById("btn_gda_remision").style.display="none";
	 		$("#btn_gda_remision").css("display","none");
	 		var datos='';
	 	//proveedor
	 		if($("#remision_proveedores").val()==-1){
	 			alert("Debe elegir un proveedor valido!!!");
	 			$("#remision_proveedores").focus();
		 		$("#btn_gda_remision").css("display","block");
	 			return false;
	 		}
	 		datos+=$("#remision_proveedores").val()+'~';
	 	//folio	 		
	 		if($("#remision_folio").val().length<=0){
	 			alert("El folio de la remision no puede ir vacío!!!");
	 			$("#remision_folio").focus();
		 		$("#btn_gda_remision").css("display","block");
	 			return false;
	 		}
	 		datos+=$("#remision_folio").val()+'~';
	 	//monto
	 		if($("#remision_monto").val().length<=0){
	 			alert("El folio de la remision no puede ir vacío!!!");
	 			$("#remision_monto").focus();
		 		$("#btn_gda_remision").css("display","block");
	 			return false;
	 		}
	 		datos+=$("#remision_monto").val()+'~';
	 	//piezas
	 		if($("#remision_piezas").val().length<=0){
	 			alert("La remision no puede ir sin piezas!!!");
	 			$("#remision_piezas").focus();
		 		$("#btn_gda_remision").css("display","block");
	 			return false;
	 		}
	 		datos+=$("#remision_piezas").val()+'~';
	 	//fecha
	 		if($("#remision_fecha").val().length<=0){
	 			alert("La  fehca de remision no puede ir vacía!!!");
	 			$("#remision_fecha").focus();
		 		$("#btn_gda_remision").css("display","block");
	 			return false;
	 		}
	 		datos+=$("#remision_fecha").val();

	 		var aux_ajax=ajaxR('../ajax/getPantallaRemisiones.php?flag=insertar&dats='+datos);
			var ax=aux_ajax.split("|");
			alert(aux_ajax);
	 		location.reload();
	 	}

		function continua_devolucion(pos){
		//obtenemos el id de la devolución 
			var id=$("#listado_0_"+pos).attr("valor");
		//obtenemos la url
			var aux_ajax=ajaxR('../ajax/validaAccion.php?flag=devolucion&id='+id);
			var ax=aux_ajax.split("|");
			if(ax[0]!='ok'){
				alert(ax);
				return false;
			}
			location.href="../../touch/"+ax[1];
		}


		function resumen_estacionalidades(){
		//mandamos llamar instruccion por ajax
			var aux_ajax=ajaxR('../ajax/resumirEstacionalidades.php');
			if(aux_ajax.trim()=='ok'){
				alert("Historico generado exitosamente!!!");
				location.reload();
			}else{
				alert("Erorr: " + aux_ajax);
			}
		}

/*Implementación Oscar 24.03.2019 para agregar botón de enviar a exclusión de trasferencias*/
	function envia_exclusion(pos,flag){
		//alert($("#listado_4_"+pos).html());return false;
		if($("#listado_4_"+pos).html()!=0){
			alert("Este producto no se puede excuir porque aún tiene piezas por racionar!!!");return false;
		}
	    if(flag==null){
	    	if(!confirm("Realmente desea enviar el producto "+$("#listado_2_"+pos).html()+" a la exclusión?\nSi continúa, el producto ya no aparecerá en las transferencias")){
				return false;
			}
			var contenido_exclusion='<p style="color:white;font-size:25px;">'+$("#listado_2_"+pos).html()+'<br>Observaciones de la exclución:</p>';
			contenido_exclusion+='<textarea id="observacion_exclusion" style="width:80%;height:200px;background:white;"></textarea><br>';
			contenido_exclusion+='<button style="padding:10px;margin:15px;" onclick="envia_exclusion('+pos+',1);">Excluir</button>';
			contenido_exclusion+='<button style="padding:10px;margin:15px;" onclick="cancela_autorizacion_trnsf();">Cancelar</button>';
	    	$("#contenidoInfo").html(contenido_exclusion);
	 	  	$("#emergenteAutorizaTransfer").css("display","block");
	 	  	$("#observacion_exclusion").focus();
	    	return false;
	    }
	//obtenemos el id del producto
		var id=$("#listado_0_"+pos).attr("valor");

		var aux_ajax=ajaxR('../ajax/validaAccion.php?flag=excluye&id='+id+'&observaciones='+$("#observacion_exclusion").val());
		var ax=aux_ajax.split("|");
	//verificamos que el cambio sea satisfactorio
		if(ax[0]!='ok'){
			alert("Error!!!\n"+aux_ajax);
		}else{
		//$("#listado_5_"+pos).html('Cancelado');//actuaizamos el estatus en el grid
			alert(ax[1]);
			location.reload();
		}
	}
/*Fin de cambio Oscar 24.03.2019*/
	
/*Implementación de Oscar 27.07.2018*/
	function cancelarTransfer(flag,pos){//recibimos flag y posición
		if(!confirm("Realmente desea eliminar la transferencia "+$("#listado_1_"+pos).html()+"?")){
			return false;
		}
	//obtenemos el id de la transferencia
		var id=$("#listado_0_"+pos).attr("valor");
	//enviamos datos por ajax
		var aux_ajax=ajaxR('../ajax/validaAccion.php?flag=1&id='+id);
		var ax=aux_ajax.split("|");
	//verificamos que el cambio sea satisfactorio
		if(ax[0]!='ok'){
			alert("Error!!!\n"+aux_ajax);
		}else{
		//$("#listado_5_"+pos).html('Cancelado');//actuaizamos el estatus en el grid
			alert(ax[1]);
			if(ax[1]=='Transferencia cancelada exitosamente!!!'){//si la transferencia se eliminó correctamente
				$("#listado_Fila"+pos).remove();
			}
		}
	//actualizamos el estatus del grid
	}

	/**************************************************implementación para exportar/importar productos Oscar 11.04.2018****************************************************/
	$('#submit-file').on("click",function(e){
    e.preventDefault();
    $('#imp_csv_prd').parse({
        config: {
            delimiter:"auto",
            complete: importaProds,
        },
        before: function(file, inputElem)
        {
            //console.log("Parsing file...", file);
        },
        error: function(err, file)
        {
            console.log("ERROR:", err, file);
        	alert("Error!!!:\n"+err+"\n"+file);
        },
        complete: function(){
            //console.log("Done with all files");
        }
    });
});
	function exportaProds(flag){
		if(flag==1){//exportar	
			document.location.href='../ajax/importaExportaProds.php?fl='+flag;
			return true;				
		}else if(flag==2){//importar
			$("#imp_csv_prd").click();//activamos el file que recibe csv
		}	
	}

	function importaProds(results){
	    var data = results.data;//guardamos en data los valores delarchivo CSV
	    var arr="";
	    for(var i=1;i<data.length;i++){
	    	//arr+=data[i];
	    	var row=data[i];
	    	var cells = row.join(",").split(",");
	    	for(j=0;j<cells.length;j++){
	            arr+=cells[j];
	            if(j<cells.length-1){
	            	arr+=",";
	            }
	        }
	        if(i<data.length-2){
	        	arr+="|";
	        }
	    }
	    //$("#cosa1").html(arr);
	   // alert(arr);
	    $("#contenidoInfo").html('<b>Actualizando Productos</b><p align="center" id="imgInfo"><img src="../../img/img_casadelasluces/load.gif" height="15%" width="17%"></p>');
	    $("#emergenteAutorizaTransfer").css("display","block");
	    //return false;
	//enviamos datos por Ajax
	//alert(arr);
	    $.ajax({
	    	type:'post',
	    	url:'../ajax/importaExportaProds.php',
	    	cache:false,
    		data:{datos:arr,fl:2},
    		success:function(dat){
    			var ax=dat.split("|");
    			if(ax[0]!="ok"){
    				alert("Error!!!\n"+dat);
    				return false;
    			}
    			else{
    				alert("Codigos alfanuméricos modificados exitosamente!!!");
    				$("#emergenteAutorizaTransfer").css("display","none");
    				location.reload();//recargamos página
    			}
    			//$("#lista_prods").html(ax[1]);
    			//$(".opc_menu").css("display","block");
    		}
    	});
	}

//detectamos archivo cargado
$("#imp_csv_prd").change(function(){
        var fichero_seleccionado = $(this).val();
        var nombre_fichero_seleccionado = fichero_seleccionado.replace(/.*[\/\\]/, '');
       /* if(nombre_fichero_seleccionado==='') {
           $('#delCarta').addClass('invisible');
        } else {
           $('#delCarta').removeClass('invisible'); 
        }*/
        if(nombre_fichero_seleccionado!=""){
        	$("#bot_imp").css("display","none");//ocultamos botón de importación
        	$("#submit-file").css("display","block");//mostramos botón de inserción
        	$("#txt_info_csv").val(nombre_fichero_seleccionado);//asignamos nombre del archivo seleccionado
        	$("#txt_info_csv").css("display","block");//volvemos visible el nombre del archivo seleccionado
        	//$("#importa_csv_icon").css("display","none");
        }else{
        	alert("No se seleccionó ningun Archivo CSV!!!");
        	return false;
        }
    });
	/*************************************************Fin de implementación 11.04.2018*********************************************************/

		function resolverDev(pos){
			var f=document.form1;
			var id=celdaValorXY('listado', 0, pos);
			var tabla=f.tabla.value;
			var no_tabla=f.no_tabla.value;
			
			aux=ajaxR('../ajax/validaAccion.php?tipo='+1+'&id_valor='+id+'&tabla='+tabla);
			ax=aux.split('|');
			
			if(ax[0] == 'SI')
			{
				var url="contenido.php?aab9e1de16f38176f86d7a92ba337a8d="+tabla+'&a1de185b82326ad96dec8ced6dad5fbbd='+ax[2]+'&a01773a8a11c5f7314901bdae5825a190='+ax[1]+"&bnVtZXJvX3RhYmxh="+no_tabla;
				location.href=url;
			}
		}
	
		function viewTrans( pos ){
			var id=celdaValorXY('listado', 0, pos);
			window.open('../pdf/imprimeDoc.php?tdoc=transferencia&id=' + id + '&view=1');
		}

		function imprimeTrans(pos, just_view = null)
		{
			var id=celdaValorXY('listado', 0, pos);
			
			if(celdaValorXY('listado', 4, pos) == 'No autorizado')
			{
				alert("La transferencia debe estar autorizada para poder imprimirla");
				return false;
			}
			window.open('../pdf/imprimeDoc.php?tdoc=transferencia&id='+id);
		//implementación Oscar 24.04.2018 para marcar como impresa la transferencia
			$("#listado_Fila"+pos).css('background','rgba(0,225,0,.5)');
		//fin de cambio 24.04.2018
		}

/*Implementación Oscar 22.02.2019 para impresión de Ticket de Trnasferencia*/
		function imprimeTicketTrans(pos){
			var id=celdaValorXY('listado', 0, pos);
			var impr_tkt=ajaxR("../especiales/Transferencias/ticket_transferencia/ticket_transf.php?flag=reimpresion&id_transf="+id);
			var split_resp=impr_tkt.split("|");
			if(split_resp[0]!="ok"){
				alert(impr_tkt);
				return false;
			}
			window.open("../../cache/ticket/"+split_resp[1]);
			$("#listado_Fila"+pos).css('background','rgba(0,225,0,.5)');
		}
/*Fin de cambio Oscar 22.02.2019*/

/*implementacion Oscar 2023/11/06*/
	function show_emergent_fast_transfer_ticket( pos ){
		var content = `<div class="row" style="text-align : center;">
			<div class="col-3"></div>
			<div class="col-6">
				<h3>Ingresa el numero de cajas : </h3>
				<input type="number" id="boxes_quantity" class="form-control">
				<br><br>
				<button
					type="button"
					class="btn btn-success"
					onclick="print_fast_transfer_ticket( ${pos} )"
				>
					<i class="icon-">Imprimir</i>
				</button>
			</div>
			<div class="col-3"></div>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function print_fast_transfer_ticket( pos ){
		var limit = $( '#boxes_quantity' ).val();
		for( var i = 1; i<= limit ; i++  ){
			var id=celdaValorXY('listado', 0, pos);
			var impr_tkt=ajaxR("../especiales/Transferencias/ticket_transferencia/ticket_fast_transfer.php?flag=reimpresion&id_transf="+id+"&limit=" + limit + "&limit_counter=" + i );
			var ax = impr_tkt.split( '|' );
			if( ax[0] != 'ok' ){
				alert( "Error al imprmir ticket : \n" + impr_tkt );
			}
			//alert( impr_tkt );
		}
		alert( "Tickets generados exitosamente!" );
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}
/*fin de cambio Oscar 2023/11/06*/
		
		function resolverTrans(pos){
			var f=document.form1;
			var id=celdaValorXY('listado', 0, pos);
			var tabla=f.tabla.value;
			var no_tabla=f.no_tabla.value;
			
			aux=ajaxR('../ajax/validaAccion.php?tipo='+1+'&id_valor='+id+'&tabla='+tabla);
			ax=aux.split('|');
			
			if(ax[0] == 'SI'){
				url='../especiales/resolucionTransferencias.php?a1de185b82326ad96dec8ced6dad5fbbd='+ax[2]+'&a01773a8a11c5f7314901bdae5825a190='+ax[1];
				location.href=url;
			}	
		}
//aqui podemos visualizar el detalle de la transferencia
		function varificarTrans(pos){
			//alert();
			var f=document.form1;
			var id=celdaValorXY('listado', 0, pos);
			var tabla=f.tabla.value;
			var no_tabla=f.no_tabla.value;
			//alert(id);
		//enviamos por ajax el id para hacer consulta de transferencia
			/*
			$.ajax({
				type:"POST",
				url:"";
				data{}
			});
			*/
			aux=ajaxR('../ajax/validaAccion.php?tipo='+1+'&id_valor='+id+'&tabla='+tabla);
			ax=aux.split('|');
			
			if(ax[0] == 'SI'){
				var url="contenido.php?aab9e1de16f38176f86d7a92ba337a8d="+tabla+'&a1de185b82326ad96dec8ced6dad5fbbd='+ax[2]+'&a01773a8a11c5f7314901bdae5825a190='+ax[1]+"&bnVtZXJvX3RhYmxh="+no_tabla;
				location.href='www.google.com';
			}
		}
	
	
		function salidaTrans(pos){
			var f=document.form1;
			var id=celdaValorXY('listado', 0, pos);
			var tabla=f.tabla.value;
			var no_tabla=f.no_tabla.value;
			
			aux=ajaxR('../ajax/validaAccion.php?tipo='+1+'&id_valor='+id+'&tabla='+tabla);
			ax=aux.split('|');
			
			if(ax[0] == 'SI')
			{
				var url="contenido.php?aab9e1de16f38176f86d7a92ba337a8d="+tabla+'&a1de185b82326ad96dec8ced6dad5fbbd='+ax[2]+'&a01773a8a11c5f7314901bdae5825a190='+ax[1]+"&bnVtZXJvX3RhYmxh="+no_tabla;
				location.href=url;
			}
		}
		
		function recepcionTrans(pos){
		//aqui se implementa el test del servidor Oscar(07-11-2017)
			/*var urlVerif=ajaxR('../especiales/sincronizacion/conexionSincronizar.php?verifServ='+1);
			if(urlVerif=='no'){
				alert("No se pueden dar Recepción a las transferencias debido a que no se tiene conexión con el servidor\n"+
					"Verifique su conexion a internet y vuelva a intentar!!!");
				return false;
			}*/
			var f=document.form1;
			var id=celdaValorXY('listado', 0, pos);
			var tabla=f.tabla.value;
			var no_tabla=f.no_tabla.value;

			aux=ajaxR('../ajax/validaAccion.php?tipo='+1+'&id_valor='+id+'&tabla='+tabla);
			ax=aux.split('|');
			
			if(ax[0] == 'SI'){
				var url="contenido.php?aab9e1de16f38176f86d7a92ba337a8d="+tabla+'&a1de185b82326ad96dec8ced6dad5fbbd='+ax[2]+'&a01773a8a11c5f7314901bdae5825a190='+ax[1]+"&bnVtZXJvX3RhYmxh="+no_tabla;
				location.href=url;
			}
		}
	
		var posTransAut=-1;
	
		function autorizaTrans(pos,flag){
		//obtenemos el id de la transferencia
			var texto_info='';
			var id=celdaValorXY('listado', 0, pos);
			if(flag=='password'){
				texto_info=$("#texto_trns").val();
				if(texto_info.length<=0){
					alert("El nombre no puede ir vacía!!!");
					$("#texto_trns").focus();
				}
				//alert('entra!!!');
			}
		//enviamos datos por ajax
			$.ajax({
				url:'../ajax/autorizaTrans.php',
				type:'post',
				cache:false,
				data:{id_transferencia:id,autorizacion:texto_info},
				success:function(dat){
					var aux=dat.split("|");
//					alert(dat);return false;
					if(aux[0]!='ok'){
						alert(dat);
						return false;
					}
					if(aux[1]==0){//si es autorizar					
						document.getElementById('mensajesPop').style.display='block';
						posTransAut=pos;
					}
					if(aux[1]==1){
						//alert(aux[2]);
						location.reload();
					}
					if(aux[1]==2){
						location.href="../../"+aux[2];
					}
					if(aux[1]=='pedir_pass'){
						var pide_autorizacion='<p style="color:white;font-size:25px;" id="msg_trans"><b>'+aux[2]+'</b></p>';
						//pide_autorizacion+='<p style="width:50%;"><input type="text" id="passWord_1" placeholder="Contraseña..." onkeyDown="cambiar(this,event,\'passWord\');"></p>';
						pide_autorizacion+='<p style="width:50%;"><input type="text" id="texto_trns"></p>';
						//pide_autorizacion+='<input type="hidden" id="passWord" value="">   ';
						pide_autorizacion+='<button onclick="autorizaTrans('+pos+',\'password\')" style="padding:10px;margin:15px;">Confirmar</button>';
						pide_autorizacion+='<button onclick="cancela_autorizacion_trnsf();" style="padding:10px;margin:15px;">Cancelar</button><br><br>';
						$("#contenidoInfo").html(pide_autorizacion);
						$("#emergenteAutorizaTransfer").css('display','block');
						$("#texto_trns").focus();
						if(aux[3]=='yellow'){
							$("#contenidoInfo").css("background",aux[3]);
							$("#msg_trans").css("color","black");
						}
					}					
				}
			});
		}

/*implementacion Oscar 2023 para modificar los status de las transferencias rapidas*/
		function authorize_transfers( pos ){
			var transfer_id = celdaValorXY('listado', 0, pos);
			var url = "../../code/especiales/Transferencias_desarrollo/ajax/fastTransfers.php?freeTransferFl=buildEmergent&transfer_id=" + transfer_id;
			
			var resp = ajaxR( url );
			$( '.emergent_content' ).html( resp );
			$( '.emergent' ).css( 'display', 'block' );
			//$( '#btn_close_emergent' ).css( 'display', 'block' );
		//alerta log
			/*$( '.emergent_3' ).css( 'display', 'block' );$( '.emergent_3' ).css( 'display', 'block' );
			$( '.emergent_content_3' ).css( 'background', 'blue' );
			$( '.emergent_content_3' ).css( 'color', 'white' );
			$( '.emergent_content_3' ).css( 'top', '50%' );
			$( '.emergent_content_3' ).html( `<h3>Versión : ${file_control_version}</h3>
			<h3>Ruta y nombre archivo : ${current_file_name}</b></h3>
			<h3>Función : authorize_transfers( pos ) ( Lanza emergente que indica proceso de autorización de transferencias rápidas )</b></h3>
			<h3>Url petición : ${url}</h3>
			<div style="text-align : center;">
				<button
					type="button"
					onclick="close_emergent_3();"
				>
					<i class="icon-ok-circled" onclick="start_fast_transfer_proccess( ${transfer_id} )">Aceptar y continuar</i>
				</button>
			</div>` );*/

			setTimeout( ()=>{start_fast_transfer_proccess( transfer_id );}, 1000 );
			//alert( "here" );return false;
			//update_fast_transfer_status( pos, 'transferAuthorization', null, null );
		}
		function start_fast_transfer_proccess( transfer_id, steep = 0 ){
			if( steep == 0 ){
				$( '.emergent_content_4' ).html( `<div class="text-center"><button type="button" class="btn btn-success" onclick="start_fast_transfer_proccess( ${transfer_id}, 1 );"><i class="icon-ok-circled">Comenzar primer paso</button></div>` );
				$( '.emergent_4' ).css( 'display', 'block' );
			}
		
			else if( steep == 1 ){//primer paso
				close_emergent_4();
				setTimeout( function(){
					var url = "../../code/especiales/Transferencias_desarrollo/ajax/fastTransfers.php?freeTransferFl=updateTransfer&transfer_id=" + transfer_id;
					var resp = ajaxR( url );//alert( resp );
					resp = resp.replaceAll(`\r\n\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\n\t\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(``, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t`, `\n`);
					resp = resp.replaceAll(`\\t`, `    `);
					resp = resp.replaceAll(`\\r\\n`, `\n`);
					resp = resp.replaceAll(`,"`, `,\n"`);
					resp = resp.replaceAll(`,{`, `,\n{`);
					if( resp.trim() == '' ){
						resp = `{"respuesta" : "Este proceso ya se habia realizado."}`;
					}
					$( '#json_steep_one' ).html( resp );
					$( '.emergent_content_4' ).html( `<div class="text-center"><button type="button" class="btn btn-success" onclick="start_fast_transfer_proccess( ${transfer_id}, 2 );"><i class="icon-ok-circled">Continuar segundo paso</button></div>` );
					$( '.emergent_4' ).css( 'display', 'block' );
				}, 500 );
			}
			//hljs.initHighlighting.called = false;
			//hljs.highlightAll();
			//setTimeout( function(){
			//alert( resp );
			else if( steep == 2 ){//segundo paso
				close_emergent_4();
				setTimeout( function(){
					var url = "../../code/especiales/Transferencias_desarrollo/ajax/fastTransfers.php?freeTransferFl=updateTransfer&transfer_id=" + transfer_id;
					var resp = ajaxR( url );//alert( resp );
					resp = resp.replaceAll(`\r\n\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\n\t\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t`, `\n`);
					resp = resp.replaceAll(`\\t`, `    `);
					resp = resp.replaceAll(`\\r\\n`, `\n`);
					resp = resp.replaceAll(`,"`, `,\n"`);
					resp = resp.replaceAll(`,{`, `,\n{`);
					if( resp.trim() == '' ){
						resp = `{"respuesta" : "Este proceso ya se habia realizado."}`;
					}
					$( '#json_steep_two' ).html( resp );
					$( '.emergent_content_4' ).html( `<div class="text-center"><button type="button" class="btn btn-success" onclick="start_fast_transfer_proccess( ${transfer_id}, 3 );"><i class="icon-ok-circled">Continuar tercer paso</button></div>` );
					$( '.emergent_4' ).css( 'display', 'block' );
				}, 500 );
			}
			//}, 1000 );
			//hljs.initHighlighting.called = false;
			//hljs.highlightAll();
			//setTimeout( function(){
			
			else if( steep == 3 ){//tercer paso
				setTimeout( function(){
					var url = "../../code/especiales/Transferencias_desarrollo/ajax/fastTransfers.php?freeTransferFl=updateTransfer&transfer_id=" + transfer_id;
					var resp = ajaxR( url );//alert( resp );
					resp = resp.replaceAll(`\r\n\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t\t`, `\n`);//\n\t\t\t\t\t\t
					resp = resp.replaceAll(`\n\t\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t`, `\n`);
					resp = resp.replaceAll(`\\t`, `    `);
					resp = resp.replaceAll(`\\r\\n`, `\n`);
					resp = resp.replaceAll(`,"`, `,\n"`);
					resp = resp.replaceAll(`,{`, `,\n{`);
					if( resp.trim() == '' ){
						resp = `{"respuesta" : "Este proceso ya se habia realizado."}`;
					}
					$( '#json_steep_three' ).html( resp );
					$( '#btn_close_emergent' ).css( 'display', 'block' );
					close_emergent_4();
					hljs.initHighlighting.called = false;
					hljs.highlightAll();
				}, 500 );
			}
			//}, 1000 );
			//setTimeout( function(){close_emergent_3()}, 1000 );
		}

		function finish_transfer_by_button( pos ){
			update_fast_transfer_status( pos, 'finishTransfer', null, 
				"Transferencia finalizada desde el boton de Finalizar en Transferencias Rapidas" );
		}

		function put_transfer_in_transit( pos, emergent = null ){
			if( celdaValorXY('listado', 9, pos) > 8  ){
				alert( "Esta transferencia ya fue puesta en Transito anteriormente y no puede ser puesta en Transito nuevamente!" );
				return false;
			}else if( celdaValorXY('listado', 9, pos) < 7 ){
				alert( "Esta transferencia no esta lista para poner en Transito, verifica e intenta nuevamente!" );
				return false;
			}
			if( emergent == null ){
				if( celdaValorXY('listado', 8, pos) == '10'  ){
					alert( "Esta Transferencia es entre la misma sucursal y no puede ser puesta en Transito!" );
					return false;
				}
				get_transfer_transit_form( pos );
				return false;
			}
			var driver_info = $( '#transfer_transit_info' ).val();
			if( driver_info.length < 3 ){
				alert( "Es necesario que escribas el nombre del chofer!" );
				$( '#transfer_transit_info' ).focus();
				return false;
			}
			var truck_info = $( '#transfer_transit_truck_info' ).val();
			if( truck_info.length < 5 ){
				alert( "Es necesario que escribas las placas de la camioneta!" );
				$( '#transfer_transit_truck_info' ).focus();
				return false;
			}
			var observations = " CHOFER : " + driver_info + "  PLACAS CAMIONETA : " + truck_info;
			update_fast_transfer_status( pos, 'putTransferInTransit', null, observations );	
		}



		function get_transfer_transit_form( pos ){
			$( '.emergent_content' ).html( `<div style="text-align : center;"><br><br><br>
				<h5>Ingresa el nombre del chofer : </h5>
				<input type="text" class="form-control" id="transfer_transit_info">
				<h5>Ingresa las placas de la camioneta donde sera enviada la Transferencia </h5>
				<input type="text" class="form-control" id="transfer_transit_truck_info">

				<button
					class="btn btn-success"
					onclick="put_transfer_in_transit( ${pos} , 1 );"
				>
					Aceptar
				</button>
			</div>` );
			$( '.emergent' ).css( 'display', 'block' );
		}

		function update_fast_transfer_status( pos, flag, status = null, observations = null ){
			var url = "../../code/especiales/Transferencias_desarrollo/ajax/fastTransfers.php?freeTransferFl=" + flag;
			url += "&transfer_id=" + celdaValorXY('listado', 0, pos);alert( "URL : " + url );return false;
			if( status != null ){
				url += "&transfer_status=" + status;
			}	
			if( observations != null ){
				url += "&observations=" + observations;
			}
			//alert( url ); return false;
			var response = ajaxR( url );
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );

				$.ajax({
					type:'post',
					url:'../ajax/autorizaTrans.php',
					cache:false,
					data:{id_transferencia:id,nval:nval,autoriza_transferencia:1},
					success:function( dat ){
						$( '#emergent_content' )
					}
				});
		}
		/*
		function finish_transfer_whith_button( pos ){
			finish_transfer( pos, true, null );
		}

		function update_transfer_validation( pos ){
			finish_transfer( pos, false, 2 );
		}

		


		function finish_transfer( pos, finish_transfer = false, status= null, observations = null ){
			var url = "../ajax/autorizaTrans.php?fl_transfer=";
			url += ( finish_transfer == true ? "finishTransfer" : "updateTransfer" );
			url += "&transfer_id=" + celdaValorXY('listado', 0, pos);
			if( status != null ){
				url += "&transfer_status=" + status;
			}	
			if( observations != null ){
				url += "&observations=" + observations;
			}	
//alert(url);return false;
			var response = ajaxR( url );
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );

			{/literal}
			var url="datosListados.php?id_listado={$datos[0]}";
			{literal}
			
			//alert(url);
			RecargaGrid('listado', url);
		}
/*fin de cambios */
		function cancela_autorizacion_trnsf(){
			$("#contenidoInfo").html('');//limpiamos el contenido
			$("#emergenteAutorizaTransfer").css('display','none');//ocultamos la emergente
		}

		function autorizaTrans2(nval){
			//var conf;
			var id=celdaValorXY('listado', 0, posTransAut);
			var temp='';	
		//si el flag es 5 ceramos ventana
			if(nval==5){
				document.getElementById('mensajesPop').style.display='none';
				return false;
			}
			document.getElementById('mensajesPop').style.display='none';
			document.getElementById('filtros').style.display='none';//escondemos filtros debido a su posicion
			document.getElementById('emergenteAutorizaTransfer').style.display='block';
			
				$.ajax({
					type:'post',
					url:'../ajax/autorizaTrans.php',
					cache:false,
					data:{id_transferencia:id,nval:nval,autoriza_transferencia:1},
					success:function(datos){
						var aux=datos.split("|");
						if(aux[0]!='ok'){
							alert(datos);
						}
						temp=datos;
						if(temp=="No cuenta con los permisos para realizar esta acción"){
						document.getElementById('emergenteAutorizaTransfer').style.display='none';//ocultamos mensaje de estado de transferencia
						document.getElementById('filtros').style.display='block';//desocultamos los filtros
						alert(temp);
						return false;
					}
					if(temp=="La transferencia ya ha sido autorizada"){
						document.getElementById('emergenteAutorizaTransfer').style.display='none';//ocultamos mensaje de estado de transferencia
						document.getElementById('filtros').style.display='block';//desocultamos los filtros
						alert(temp);
						return false;
					}
					if(temp=="No es posible continuar con el proceso de transferencia localmente.\nContacte al administrador para continuar!!!"){
						document.getElementById('emergenteAutorizaTransfer').style.display='none';//ocultamos mensaje de estado de transferencia
						document.getElementById('filtros').style.display='block';//desocultamos los filtros
						alert(temp);
						return false;
					}				
					posTransAut=-1;
					window.location.reload();//recargamos pagina
					return false;
					}
				});
		}		
	
		function filtra(val)
		{
			var f=document.form1;
			
			if(val == 0)
			{
				{/literal}
				var url="datosListados.php?id_listado={$datos[0]}";
				{literal}
				
				//alert(url);
				RecargaGrid('listado', url);
				
			}				
			else
			{
				if(f.valor.value == '')
				{
					alert('Es necesario que elija un valor a filtrar');
					f.valor.focus();
				}
				{/literal}
				var url="datosListados.php?id_listado={$datos[0]}";
				{literal}
				
				url+="&campo="+f.campo.value+"&operador="+f.operador.value+"&valor="+f.valor.value;
				RecargaGrid('listado', url);
			}	
		}
		
		
		
		function imprimirCot(val)
		{
			var f=document.form1;
			var id=celdaValorXY('listado', 0, val);
			var tabla=f.tabla.value;
			var no_tabla=f.no_tabla.value;
			
			if(tabla == 'ZWNfb3JkZW5lc19jb21wcmE=' && no_tabla == 'MA==')
				tipo="REQ";
			if(tabla == 'ZWNfb3JkZW5lc19jb21wcmE=' && no_tabla == 'MQ==')
				tipo="OC";
			if(tabla == 'ZWNfcGVkaWRvcw==' && no_tabla == 'MA==')
				tipo="PED";
			if(tabla == 'ZWNfcGVkaWRvcw==' && no_tabla == 'MQ==')
				tipo="NV";
			if(tabla == 'ZWNfbW92aW1pZW50b19hbG1hY2Vu' && no_tabla == 'MA==')
				tipo="MA";				
			
			if((tabla == 'ZWNfcGVkaWRvcw==' && no_tabla == 'MQ==') || (tabla == 'ZWNfcGVkaWRvcw==' && no_tabla == 'MA==')){
			//si es pedidos
				window.open('../../touch_desarrollo/index.php?printPan=1&scr=ticket&idp='+id);
			/*implementación Oscar 10.08.2018 para reimpresión de ticket*/
				var reimprime=ajaxR("../../touch_desarrollo/ajax/ticket-php-head-reimpresion.php?id_ped="+id+"&reimpresion=1");
			}else{
				window.open('../pdf/imprimeDoc.php?tdoc='+tipo+'&id='+id);
			}
			
			
		}
		
	//Aqui visualizamos detalle de listados(LUPA)	
		function abreMod(tipo, pos){
			var f=document.form1;
			var id=celdaValorXY('listado', 0, pos);
			var tabla=f.tabla.value;
			var no_tabla=f.no_tabla.value;
			var url;
	/*implementacion Oscar 2022 */
			if( tabla == "ZWNfdHJhbnNmZXJlbmNpYXM=" && no_tabla == "Ng==" && tipo == 0 ){ 
				location.href = "../especiales/Transferencias/transferencias_cortas/index.php?";
				return false;
			}
			if( tabla == "ZWNfdHJhbnNmZXJlbmNpYXM=" && no_tabla == "Ng==" && tipo == 2 ){ 
				location.href = "../especiales/Transferencias/transferencias_cortas/index.php?pk=" + id;
				return false;
			}
	/*Fin de cambio Oscar 2022 */

	/*implementacion Oscar 26.10.2019 para no dejar insertar un nuevo gasto si no existe la sesion del cajero*/
			if(tabla=="ZWNfZ2FzdG9z" && no_tabla=="MA==" && tipo==0){
				var verif_sesion_cajero=ajaxR("../ajax/verificatransferenciasPendientes.php?fl=sesion_caja");
				//var aux_sesion_cajero=verif_trans.split("|");
				if(verif_sesion_cajero!='ok'){
					alert(verif_sesion_cajero);
					return false;
				}
			}
	/*Fin de Cambio Oscar 26.10.2019*/

	/*implementacion Oscar 24.10.2019 para no dejar abrir ciertas pantallas localmente*/
			if(( (tabla == "c3lzX3VzZXJz" && (no_tabla == "MA==" || no_tabla == "MQ==")) 
				|| tabla == "ZWNfdHJhc3Bhc29zX2JhbmNvcw==" && no_tabla == "MA==" 
				|| tabla == "ZWNfcHJvZHVjdG9z" && no_tabla == "MA=="
				|| tabla == "ZWNfY2FqYV9vX2N1ZW50YQ==" && no_tabla =="MA=="
			)
				&& (tipo == 0 || tipo==1 || tipo==3) ){/*nuevo, editar, eliminar*/	
				{/literal}
					if("{$tipo_sistema}"!='linea')
					{literal}
					{
						alert("Estos registros no pueden ser agregados/modificados localmente.\nRealice esta acción dede el sistema en linea!!!");
						return false;
					{/literal}
					}
				{literal}
			}
	/*Fin de camio Oscar 24.10.2019*/
			
			if(tabla == 'ZWNfbWFxdWlsYQ==' && tipo == 3){
				{/literal}
				if(confirm("{$letMaquila}"))
				{literal}
				{
					var res=ajaxR("../ajax/cancelaMaq.php?id_maquila="+id);
					
					var aux=res.split('|');
					if(aux[0] != 'exito')
					{
						alert(res);
						return false;
						
					}
					RecargaGrid('listado', '');
				}
			}else{
				//ZWNfdHJhbnNmZXJlbmNpYXM=
			//verificamos si corresponde a transferencia el boton y mandamos id de transferencia en caso de cumplir condiciones
				if(tabla=='ZWNfdHJhbnNmZXJlbmNpYXM=' && tipo==2 && ( no_tabla=="MA==" || no_tabla=="MTI=" || no_tabla=="OQ==" ) ){
					//alert('0*0');
					location.href="../especiales/Transferencias_desarrollo_racion/nuevaTransferencia.php?idTransfer="+id;
					return false;
				} 

		/*Implementación de Oscar para direccionarse a pantalla de recepción*/
			if(tabla=='ZWNfb3JkZW5lc19jb21wcmE=' && no_tabla=='Mg=='){
				location.href="../especiales/pedidos/recepcionPedidos/rececpcionPedido.php?href="+id;
				return false;
			}

		/**/
		/*implementación de Oscar 17-08-2018 para */
			if(tabla=='ZWNfbW92aW1pZW50b19hbG1hY2Vu' && no_tabla=='MQ=='){
				location.href="../reportes/reportes_mod.php?trash=&no_tabla=MA==&id_reporte=40&no_tabla=MA==&folio="+id;
				return false;
			}	
		/*fin de cambio*/

				//alert(tipo);
				aux=ajaxR('../ajax/validaAccion.php?tipo='+tipo+'&id_valor='+id+'&tabla='+tabla);
			//alert(aux);//prueba de Oscar 26.03.2018
				ax=aux.split('|');
				
				if(ax[0] == 'SI'){
					
				/**/
					if(tabla=="ZWNfdHJhbnNmZXJlbmNpYXM=" & no_tabla=="MA=="){
		
		/*implementacion Oscar 14.12.2019 para mandar llamar a la instruccion que raciona los productos*/
						/*var racion_trans=ajaxR("../especiales/Transferencias/proceso_racion.php");
						var aux_racion=racion_trans.split("|");
						if(aux_racion[0]!='ok'){
							alert("Error al verificar transferencias pendientes!!!");
							return true;
						}*/
		/*Fin de cambio Oscar 14.12.2019*/

						var verif_trans=ajaxR("../ajax/verificatransferenciasPendientes.php");
						var aux_trsnf=verif_trans.split("|");
						if(aux_trsnf[0]!='ok'){
							alert("Error al verificar transferencias pendientes!!!");
							return true;
						}
						if(aux_trsnf[1]!='ok'){
							$("#contenidoInfo").html(aux_trsnf[1]);
							//$(".nuevo.nuevobtn.nuv-img").css("display","none");
							$("#emergenteAutorizaTransfer").css("display","block");
							return true;
						}

					/*deshabilitado por oscar 01.05.2018
						$.ajax({
							type:'post',
							url:'../especiales/sincronizacion/validarNuevosProductos.php',
							cache:false,
							data:{},
							success:function(datos){
									//alert(datos);	
								if(datos!='ok'){					
									alert('No se pueden hacer transferencias!!!\nHay productos nuevos pendientes por sincronizar');
									return false;
								}*/

							url="../especiales/Transferencias_desarrollo_racion/transf.php";
							location.href=url;
							/*return false;
							}
						});
					}//termina if de ajax,   deshabilitado por oscar 01.05.2018*/
					}else{
					/***/
						var llave="";
						if(tabla=='ZWNfb3JkZW5lc19jb21wcmE=' && no_tabla=='Mg=='){
							tabla='ZWNfb2NfcmVjZXBjaW9u';
						//	llave='&llave='+id;
							no_tabla='MA==';
							ax[2]='MA==';
						}
					/*****/
						url="contenido.php?aab9e1de16f38176f86d7a92ba337a8d="+tabla+'&a1de185b82326ad96dec8ced6dad5fbbd='+ax[2]+'&a01773a8a11c5f7314901bdae5825a190='+ax[1]+"&bnVtZXJvX3RhYmxh="+no_tabla+llave;

					/*implementación de Oscar 14.08.2018 para cargar el grid por ranfo de fechas*/
						if(tabla=='c3lzX21vZHVsb3Nfc2luY3Jvbml6YWNpb24='){
						//extraemos los filtros del rango de fecha
							var filt_fcha_1=$("#f_del").val();
							var filt_fcha_2=$("#f_al").val();
						//concatenamoslos filtros de fechas a la url
							url+="&fcha_filtros="+filt_fcha_1+"|"+filt_fcha_2;

						}
					/*fin de Cambio Oscar 14.08.2018*/

				//alert(tabla+"||||||||||"+no_tabla);
						location.href=url;
					} 
				}
				else{
					alert(aux);
				}
			}		
						
		}
/*Implementación Oscar 2023/09/23 para el boton de reimprimir desde el listado de cola de impresion*/
		function reprint_since_queue_list( pos ){
			alert( pos );
		}
/*Fin de cambio Oscar 2023/09/23*/
		
		function autorizaCot(pos)
		{
			var f=document.form1;
			var id=celdaValorXY('listado', 0, pos);
			var tabla=f.tabla.value;
			
			if(tabla == 'ZWNfb3JkZW5lc19jb21wcmE=')
				var aux=ajaxR('../ajax/general/validaCot.php?id='+id);
			if(tabla == 'ZWNfcGVkaWRvcw==')	
				var aux=ajaxR('../ajax/general/validaPed.php?id='+id);
			alert(aux);
		}
		
		function facturar(pos)
		{
			var id=celdaValorXY('listado', 0, pos);
			
			var aux=ajaxR('../ajax/general/facturar.php?id='+id);
			
			var ax=aux.split('|');
			
			if(ax[0] == 'exito')
				location.href="contenido.php?aab9e1de16f38176f86d7a92ba337a8d=ZWNfcGVkaWRvcw==&a1de185b82326ad96dec8ced6dad5fbbd=MQ==&a01773a8a11c5f7314901bdae5825a190="+ax[1]+"&bnVtZXJvX3RhYmxh=Mw==";
			else
				alert(aux);	
		}
	


 function prueba(){
      
          $( "#dialog-modal" ).dialog({

                height: 350,
                width:360,
                modal: true,
                buttons:{
                    "Generar":function(){
              
                      var fecha_ini = document.getElementById('fecha').value;  
					  var fecha_fin = document.getElementById('fecha_f').value;
					  var f_pago = document.getElementById('f_pago').value; 
					  var ini = parseInt(fecha_ini);
					  var fin = parseInt(fecha_fin);

					  console.log(fecha_ini);
					  console.log(fin);
					  if(fecha_ini == '')
					  {
					  	alert('Elige una fecha inicial por favor');
					  	document.getElementById('fecha').focus();
					  	return false;
					  }
					  if(fecha_fin == '')
					  {
					  	alert('Elige una fecha final por favor');
					  	document.getElementById('fecha_f').focus();
					  	return false;
					  } 
					  if(fecha_ini > fecha_fin)
					  {
					  	alert('Verifica la segunda fecha');
					  	document.getElementById('fecha_f').focus();
					  	return false;
					  }

					  window.open('../../code/ajax/especiales/CSV/generaCSV.php?fecha_1='+fecha_ini+'&fecha_2='+fecha_fin+"&tipo_pago="+f_pago); 	                    
                    },
                    "Cancelar":function(){
                         $( this ).dialog( "close" );
                    }
                }
            });    
    }

    function guarda_historico_precio_compra(){
    	if(!confirm("Este botón hará que se actualice el histórico de precios de Compra; Realmente desea continuar?")){
    		return false;
    	}
	//eviamos datos por ajax    	
		$.ajax({
			type:'post',
			url:'../../code/ajax/validaAccion.php',
			cache:false,
			data:{fl:'historico_precio_compra'},
			success:function(dat){
				alert(dat);
			}
		});
    }

/*******************************Implementación Oscar 14.08.2018****************************/
	function calendario(objeto){
    	Calendar.setup({
        	inputField     :    objeto.id,
        	ifFormat       :    "%Y-%m-%d",
        	align          :    "BR",
        	singleClick    :    true
		});
	}

/***************************************Fin de implementación******************************/
    	</script>
	{/literal}

	 <div id="dialog-modal" title="Generar CSV" style='display:none'>
   <table>
        <tr>
         <td>Del:</td>
         <td><input type="date" name="fecha_agenda"  id="fecha"></td>
        </tr>
        <tr>
            <td>Al:</td>
            <td><input type="date" name="fecha_fin" id="fecha_f"</td>
        </tr> 
        <tr>
               	<td>Forma de pago:</td>
               	<td>
               		<select name="campo" id="f_pago" >
							{html_options values=$vals output=$textos}
				   </select>
               	</td>
        </tr>       
   </table>
</div>

</table>
</div>

{literal}
	<style>
		.mensajesPop{
			position:absolute;
			top: 0px;
			left: 0px;
			width:100%;
			height:200%;
			opacity:0.85;
			background:rgba(0,225,0,.5);
		}
		
		.ventanaMens{
			position:absolute;
			top: 520px;
			left: 35%;
			width:380px;
			height:150px;
			z-index:1000000000;
			background:#FFFFFF;
			padding: 10px;
		}
		
		.botonCerrMensaje{
			
			background:#FFFFFF;
			border-style: solid;
   			border-color: #000000;
			border-width: 1px;
			width:65px;
			height:30px;
		}
		
		.dbotcerr{
			position:relative;
			top: -25px;
			left: 320px;
		}
		
		
		
	</style>
{/literal}

<div class="mensajesPop" id="mensajesPop" style="display:none">
	<div class="ventanaMens">
		{$letAutTrans}
		<div class="dbotcerr">
			<input type="button" value="X" onclick="document.getElementById('mensajesPop').style.display='none'" class="botonCerrMensaje">
		</div>
		<br>
		<div align="right">
			<input type="button" value="SI" onclick="autorizaTrans2(2)" class="botonCerrMensaje">
			<input type="button" value="NO" onclick="autorizaTrans2(5)" class="botonCerrMensaje">
		</div>	
	</div>
</div>
{include file="_footer.tpl" pagetitle="$contentheader"}