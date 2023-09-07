<?php
/*version 30.10.2019*/
	include('../../../../conectMin.php');

	$sql="SELECT id_sucursal FROM sys_sucursales WHERE acceso=1
		UNION
		SELECT permite_abrir_caja_linea FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
	$eje=mysql_query($sql)or die("Error al consultar si se puede iniciar sesion de caja en linea!!!<br>".mysql_error());
	$r=mysql_fetch_row($eje);
	$r1=mysql_fetch_row($eje);
	if($r[0]==-1 && $r1[0]==0){
		//die($sql."<br>".$r[0]."|".$r1[0]);
		echo '<script type="text/Javascript">';
			echo 'alert("No se puede acceder a abrir caja desde el sistema en línea; hagalo localmente o contacte al administrador!!!");';
			echo 'location.href="../../../../"';
		echo '</script>';
		return;
	}

	$sql="SELECT IF(p.ver=1 OR p.modificar=1,1,0) 
			FROM sys_permisos p
			LEFT JOIN sys_users_perfiles perf ON perf.id_perfil=p.id_perfil
			LEFT JOIN sys_users u ON u.tipo_perfil=perf.id_perfil 
			WHERE p.id_menu=200
			AND u.id_usuario=$user_id";
	//die($sql);
	$eje=mysql_query($sql)or die("Error al consultar el permiso de cajero!!!<br>".mysql_error()."<br>".$sql);
	$es_cajero=mysql_fetch_row($eje);
	if($es_cajero[0]==0){
		echo '<script type="text/Javascript">';
			echo 'alert("No tiene acceso para esta pantalla;\nNecesita ser cajero!!!");';
			echo 'location.href="../../../../"';
		echo '</script>';
		return;
	}
//consultamos el login del cajero
	$sql="SELECT login FROM sys_users WHERE id_usuario=$user_id";
	$eje=mysql_query($sql)or die("Error al consultar el login del usuario logueado en este sistema!!!<br>".mysql_error());
	$r=mysql_fetch_row($eje);
	$login_cajero=$r[0];
?>
<style type="text/css">
	#global{position: absolute;width: 100%;height: 100%;background-image: url('../../../../img/img_casadelasluces/bg8.jpg');margin:0;padding:0;top:0;left:0;}
	#log_caja{position:absolute;width: 40%;border: 1px solid gray;background: #33C7FF;align-items: center;top:25%; left: 30%;border-radius: 10px;color:white;height: 65%;}
	.entrada_txt{padding: 15px;width: 80%;}
	#icono{border: 1px solid orange;position: absolute;width: 10%;border-radius: 50%;left: 45%;top:15%;z-index:2;background:gray;}
	#bot_abre:hover{background: #1DE94C;color: white;}
</style>
<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="../../../../js/passteriscoByNeutrix.js"></script>
<div id="global">
	<img src="../../../../img/img_casadelasluces/logocasadelasluces-easy.png" onclick="link(1);" title="Click para regresar al Panel de administración">
	<center>
	<div id="icono">
		<img src="../../../../img/especiales/caja_registradora.png" width="80%">
	</div>
	<form id="log_caja">
		<br><br><br><br><br><br>
		<input type="text" id="user" class="entrada_txt" placeholder="CAJERO.." value="<?php echo $login_cajero;?>" disabled style="background: white;color:black;"><br>
		<br><br><br>
		<input type="text" id="password1" class="entrada_txt" onkeyDown="cambiar(this,event,'password');" placeholder="***PASSWORD***">
		<input type="hidden" id="password">
		<br><br><br>
		<button type="button" style="padding: 10px;width: 30%;" id="bot_abre" onclick="abrir_caja();">
			<img src="../../../../img/especiales/key.svg" width="30px;"><br>
			Abrir Caja</button>
		<br><br>
	</form>
	</center>
</div>

<script>
	function link(flag){
		if(flag==1){
			if(confirm("Realmente desea regresar al panel?")==true){
				location.href=("../../../../index.php");
			}
		}
	}
//funcion que valida login
	function abrir_caja(){
	//validamos datos
	var log,contra;
	log=$("#user").val();
	if(log.length<=0){
		alert("El campo de cajero no puede ir vacío!!!");
		$("#user").focus();
		return false;
	}
	contra=$("#password").val();
	if(contra.length<=0){
		alert("La contraseña de cajero no puede ir vacía!!!");
		$("#password").focus();
		return false;
	}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'abreSesionCaja.php',
			cache:false,
			data:{login:log,contrasena:contra},
			success:function(dat){
				if(dat!='ok'){
					alert(dat);
					//location.reload();
				}else{
					alert("Sesión de caja iniciada exitosamente!!!");
					location.href='../../../../index.php?';
				}
			}
			});
	}
</script>