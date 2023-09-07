<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="{$rooturl}css/estilo_final1.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="css/icons/css/fontello.css">
		<script type="{$rooturl}js/"></script>
		<title>Administraci&oacute;n Easy-count</title>
	<!--incluimos la librería para cambiar password-->
	<script type="text/javascript" src="{$rooturl}js/jquery-1.10.2.min.js"></script>
	</head>
	<body>
   
		<div id= "contenido_login">
        
	<div id="loginP">
    
    </div>
    
			<div class="datos">
			
                 <div class="admin"><!--<img src="img/login.png"/>-->
                         <h1 class="logo-base"><a href="https://www.easycount.com.mx/" title="la casa de las luces"><span>La casa de las luces</span></a></h1>
                </div> 
                	{if $error_login eq 'YES'}
                
                 
					<span class="texto_error_uc">Nombre de usuario y contrase&ntilde;a incorrectos</span>
				{/if}
                
				<div id="formulario">
                   
                
                <form id="forma1" name="forma1" method="post" action="{$rooturl}index.php" autocomplete="off">
					<input type="hidden" name="form_login" value="YES" />
					<input type="hidden" name="url_act" value="{$url_act}" />
					<table width="295" height="171" border="0">
						<tr>
							<td  valign="middle">
								<input name="login_user" class="usuario" placeholder="Usuario" type="text"  id="text1" name="text1" onkeypress="keyEnter(event, this.form)" 
								x-autocompletetype="ninguno-ninguno" autocomplete="false"/>
							</td>
					    </tr>
						<tr>							
							<td valign="middle" class="">
								<div class="row">
									<div class="col-10 text-end">
										<input  
											placeholder="Contraseña" 
											type="password"  
											id="password1"
											name="pass_user" 
											onkeypress="keyEnter(event, this.form)"
											x-autocompletetype="ninguno-ninguno" 
											autocomplete="false"
											class="form-control"
										/>
									</div>
									<div class="col-1 text-start">
										<button 
											class="btn form-control"
											type="button" 
											onclick="change_input_type( this );">
											<i class="icon-eye"></i>
										</button>
									</div>
									<!--class="contrasena"password onkeyup="cambia(this,event);" onkeydown="cambiar(this,event,'password1');"-->
								</div>
							</td>
						<!---->
							<td>
								<!--input type="hidden" id="password1_1" name="password1_1" value=""-->
							</td>
						<!---->
						</tr>
						<tr>
							
							<td valign="middle">
								<select name="sucursal_user" class="barra_tres" onkeypress="keyEnter(event, this.form)">
									{html_options values=$arrZonaids output=$arrZonanames}
								</select>
							</td>
						</tr>
						<!--<input type="hidden" name="sucursal_user" value="1" />-->
						<tr>
							<td height="29" colspan="2" align="center">
								<input name="button" type="button" class="boton-log" id="button" value="Entrar" onclick="valida(this.form)"/>
							</td>
						</tr>
					</table>
					
				</form>	 </div>
                
                 
                <div id="creditos">
               <!--<p>Una creaci&oacute;n de <a style="text-decoration:none;" href="http://www.terminus.mx/"><strong class="terminux">T&eacute;rminus<img src="¨../../img/numero_terminus.png" width="13" height="18" alt=""/></strong></a></p>-->
               
                </div>
			</div>
			
		</div>
				
		
		<script>
			{literal}
			/*window.onload=function(){
				setTimeout('limpiar_campos()',10);
			}

			function limpiar_campos(){
				document.getElementById("text1").value='';
				document.getElementById("password1").value='';
				//alert('ya limpió');
			}*/

			document.forma1.login_user.focus();


			function valida(f)
			{
				//alert(f.pass_user.value);
				if(f.login_user.value.length == 0)
				{
					alert('Es necesario que inserte su login');
					f.login_user.focus();
					return false;
				}
				if(f.pass_user.value.length == 0)
				{
					alert('Es necesario que inserte su contraseña');
					f.pass_user.focus();
					return false;
				}
				if(f.sucursal_user.value==0){
					alert('Primero elija una sucursal');
					f.sucursal_user.focus();
					return false;
				}
				
				f.submit();
				
			}
			
			function keyEnter(eve, f)
			{
				var key=0;	
				key=(eve.which) ? eve.which : eve.keyCode;
				
				if(key == 13)
					valida(f);				
			}

			function change_input_type( obj ){
				if( $( '#password1' ).attr( 'type' ) == 'password' ){
					$( '#password1' ).attr( 'type', 'text' );
				}else{
					$( '#password1' ).attr( 'type', 'password' );
				}
			}
			
			{/literal}
		</script>
		
	<div class="fondo1"></div>	
	</body>
</html>
