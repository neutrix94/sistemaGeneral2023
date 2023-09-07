<style>
	*{
		margin: 0;
	}
	
	.emerge{
		background:rgba(0,0,0,.8);
		width:100%;
		height:100%;
		position:absolute;
		display: block;
		z-index: 100;
	}
</style>
<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
<!--<script language="Javascript" src="js/funcionesAjusteInvntario.js"></script>-->
<!--incluimos la librería de Oscar-->
	<script type="text/javascript" src="../../../js/passteriscoByNeutrix.js"></script>
<!--Fin de cambio-->

	<div id="emergente" class="emerge">
		<center>
		<div style="background:rgba(0,0,0,.7);border:1px solid red;top:150px;position:relative;width:80%;height:550px;border-radius:15px;">
			<div id="cont_vta_emerge">
				<p id="info_emerge" style="color:white;font-size:25px;" align="center">
					<p align="center" style="font-size:50px;color:white;padding:15px;">IMPORTANTE!</p>
					<p align="left" style="font-size:30px;color:white;padding:15px;">1)Antes de relizar los ajustes de inventario no debe tener ninguna venta pendiente de cerrar; si tiene una venta pendiente cierrela antes de continuar</p>
					<p align="left" style="font-size:30px;color:white;padding:15px;"><br>2)Mientra esta pantalla este abierta, todo producto tomado para su venta deberá de ser capturado en la pantalla de ventas al momento</p>
					<p align="center" style="font-size:30px;color:white;">
						Contraseña del encargado:<br>
						<input type="text" id="pass_enc_1" onkeydown="cambiar(this,event,'pass_enc');" style="padding:10px;font-size:25px;border-radius:10px;"><br>
						<input type="hidden" id="pass_enc" value="">
						<input type="button" value="Aceptar" style="padding:10px;font-size:25px;" onclick="verifica_permiso();">
					</p>
				</p>
			</div>
		</div>
		</center>
	</div>

<script type="text/javascript">
	function verifica_permiso(){
	//sacamos el valor de la contraseña
		var pass=$("#pass_enc").val();
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/guardaAjuste.php',
			cache:false,
			data:{fl:'verifica_pass',clave:pass},
			success:function(dat){
				if(dat!='ok'){
					alert("La contraseña del encargado es incorrecta!!!");
					$("#pass_enc").val('');
					$("#pass_enc_1").val('');
					$("#pass_enc_1").focus();
					return false;
				}else{
					location.href='inventario.php';
				}
			}
		});
	}
</script>
