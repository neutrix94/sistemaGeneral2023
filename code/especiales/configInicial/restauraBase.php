<?php
	//include('../../../conexionDoble.php');
	include( '../../../conexionMysqli.php' );
/*implemetacion Oscar 2023 para restaurar por medio de API*/
	$sql = "SELECT 
	        	TRIM(value) AS path
	        FROM api_config WHERE name = 'path'";
	$stm = $link->query( $sql ) or die( "Error al consultar path de api : {$this->link->error}" );
	$config_row = $stm->fetch_assoc();
	$api_path = $config_row['path']."/rest/v1/restauracion";

	function send_petition( $api_path, $sql ){
		$petition_data = array( "QUERY"=>$sql );
		$post_data = json_encode( $petition_data );
		$resp = "";
		$crl = curl_init( "{$api_path}" );
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($crl, CURLINFO_HEADER_OUT, true);
		curl_setopt($crl, CURLOPT_POST, true);
		curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
		//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
		curl_setopt($crl, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'token: ' . $token)
		);
		$resp = curl_exec($crl);//envia peticion
		curl_close($crl);
		//var_dump($resp);
		//$response = json_decode($resp);
		return $resp;
	}

	function excecuteQuery( $sql, $link ){
		$link->autocommit( false );
		$stm = $link->query( $sql ) or die( "Error al ejecutar consulta desde excecuteQuery : {$sql} : {$link->error}" );
		$link->autocommit( true );
		return 'ok';
	}
/**/

	if(isset($_POST['fl']) && $_POST['fl']=='permiso'){
	//recibimos las variables
		$user=$_POST['usuario'];
		$pass=md5($_POST['clave']);
		$id_sucursal=$_POST['suc'];
		$conexion=$local;
		if($id_sucursal==-1){
			$conexion=$linea;
		}
	//verificamos los permisos
		$sql="SELECT id_usuario FROM sys_users WHERE login='$user' AND contrasena='$pass' AND (tipo_perfil=1 OR tipo_perfil=5)";
		//die($sql);
		//$eje=mysql_query($sql,$conexion);
		$eje = $link->query( $sql ) or die("Error al verificar si el usuario tiene los permisos para restaurar o generar una nueva BD!!! {$sql} : {$link->error}");
		if( $eje->num_rows == 1 ){
			die('ok|');
		}else{
			die("El usuario y/o contraseña son Incorrectos o el usuario no tiene los permisos para restaurar la BD, verifique sus datos y vuelva a intentar!!!");
		}
	}//fin de si es permiso

	$id_suc=$_POST['id_suc'];//recibimos la sucursal
	$tipo_bd=$_POST['t_bd'];//tipo de BD
	$tipo_sistema=$_POST['t_sys'];//tipo de sistema
	$fecha_rsp=$_POST['fecha'];
	$store_prefix = "";
//consulta el prefijo de la sucursal
	$sql = "SELECT prefijo AS store_prefix FROM sys_sucursales WHERE id_sucursal = {$id_suc}";
	$stm = $link->query( $sql ) or die( "Error al consultar prefijo de la sucursal : {$sql} : {$link->error}" );
	$row = $stm->fetch_assoc();
	$store_prefix = $row['store_prefix'];
	if($tipo_sistema==1){//si es nueva Base de datos
		//include("eliminaSobrantesLocal.php");
		//include("actualizaEquivalentes.php");
	}
	//die('ok');

/**/
	$s=$hostLocal;
	$bd=$nombreLocal;
	$u=$userLocal;
	$p="";

	/*$conexion_sqli=new mysqli($s,$u,$p,$bd);
	if($conexion_sqli->connect_errno){
		die("sin conexion");
	}else{
		//echo "conectado";
	}*/

	$cadena_arreglo="";
	$fp = fopen("../../../respaldos/procedures.sql", "r")or die("Error");
	while (!feof($fp)){
	 	$linea = fgets($fp);
	 	$cadena_arreglo.=$linea;
	}
	fclose($fp);
//echo $cadena_arreglo;
	//$cadena_arreglo=str_replace("DELIMITER $$", "", $cadena_arreglo);
	$arreglo_procedure=explode("|", $cadena_arreglo);
	for($i=0;$i<sizeof($arreglo_procedure);$i++){
//		echo "Array: ".$arreglo_procedure[$i]."\n";
		$arreglo_procedure[$i]=str_replace("DELIMITER $$", "", $arreglo_procedure[$i]);
		$arreglo_procedure[$i]=str_replace("$$", "", $arreglo_procedure[$i]);
		$eje = $link->multi_query($arreglo_procedure[$i]);
		if(!$eje){
			die( "Error al insertar procedures : {$link->error}" );//mysqli_error($conexion_sqli)
		}
	}
	die('ok|');
	/**/
/*********************************************************Proceso de restauración de BD****************************************/
	/*


	*/
?>