<?php
	include('../../../../conectMin.php');
	if(!$fl=$_GET['fl']){
		$fl=$_POST['fl'];
	}
	$id=$_GET['id'];

/************************************Implementación Ocar 29.06.2018 para decargar cv de pantalla*************************************************/
	if($fl==3){		
		$nombre="previoPedido.csv";
		$data=$_POST['datos'];
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($data));		
	}

/************************************Fin de cambio***************************************************/	

//si es descargar CSV
	if($fl==1){
		$data="";
		$sql="SELECT 
				oc.folio,
				prov.nombre,
				oc.total,
				oc.fecha,
				oc.hora,
				CONCAT(u.nombre,' ',u.apellido_paterno,' ',u.apellido_materno)
			FROM ec_ordenes_compra oc
			LEFT JOIN ec_proveedor prov ON oc.id_proveedor=prov.id_proveedor
			LEFT JOIN sys_users u ON oc.id_usuario=u.id_usuario
			WHERE oc.id_orden_compra=$id";	
		$eje=mysql_query($sql)or die("Error al consultar encabezado de pedido!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$data.="Folio: ".$r[0].",Proveedor: ".$r[1].",Total: $".$r[2].",Fecha: ".$r[3].",Hora: ".$r[4].",Usuario: ".$r[5]."\n";
	//consultamos el detalle de la órden de compra
		$sql="SELECT 
				p.id_productos,
				/*REPLACE(p.clave,',','|'),remplazamos las comas para evitar conflictos en el csv*/
				p_p.clave_proveedor,
				p.nombre,
				ocd.cantidad,
				ocd.precio,
				ocd.cantidad/p_p.presentacion_caja,
				ocd.cantidad*ocd.precio
			FROM ec_oc_detalle ocd
			LEFT JOIN ec_productos p ON ocd.id_producto=p.id_productos
			LEFT JOIN ec_ordenes_compra oc ON ocd.id_orden_compra=oc.id_orden_compra 
			LEFT JOIN ec_proveedor_producto p_p ON ocd.id_producto=p_p.id_producto AND oc.id_proveedor=p_p.id_proveedor
			WHERE ocd.id_orden_compra=$id";
		$eje=mysql_query($sql)or die("Error al consultar el detalle de la órden de compra!!!\n\n".$sql."\n\n".mysql_error());
		$c=0;
		$data.="ID DE PRODUCTO,CLAVE,NOMBRE,CANTIDAD EN PIEZAS,PRECIO POR PIEZA,TOTAL CAJAS,MONTO TOTAL";//encabezado del detalle
	//generamos columnas
		while($rw=mysql_fetch_row($eje)){
			$data.="\n".$rw[0].','.$rw[1].','.$rw[2].','.$rw[3].','.$rw[4].','.$rw[5].','.round($rw[6]);//armamos filas del detalle
		}	

		$nombre=$r[0].".csv";
	//comprobamos si el archivo ya existe
		if(file_exists("../pedidos/".$nombre)){
			if(!unlink("../pedidos/".$nombre)){//eliminamos el archivo
				echo 'No se pudo eliminar el archiv anterior!!!';
			}
		}
	//Generamos el csv   
		if (!$handle = fopen("../pedidos/".$nombre, "w")) {  
    		echo "No se puede abrir el archivo";  
    		exit;  
		}  
	//escribimos en el csv
		if (fwrite($handle, utf8_decode($data)) === FALSE) {  
    		echo "Cannot write to file";  
    		exit;  
		}  
	//cerramos csv
		fclose($handle); 
		
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($data));
		//echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";//cerramos ventana
	}//termina proceso de desacarga csv

//si es enviar correo a proveedores
	if($fl==2){
		//die('aqui');
	if(!include("../../../../include/PHPMailer/PHPMailerAutoload.php")){
		die("No se encontró librería de e-mail");
	}
	//buscamos parámetros de correo
		$sq="SELECT smtp_user,correo_envios,smtp_server,smtp_pass,puerto
			FROM ec_conf_correo
			WHERE id_configuracion=1";
		$eje=mysql_query($sq)or die("Error al consultar parámetros de envío de correo\n\n".$sq."\n\n".mysql_error());
		$res=mysql_fetch_row($eje);

		$ids=$_POST['id_ordenes'];
		$id=explode("~",$ids);
		$estado="";
		for($i=0;$i<sizeof($id)-1;$i++){
			$mail = new PHPMailer();

			$mail->IsSMTP(true);
        	$mail->SMTPAuth = true;

			$mail->From =$res[0];//"no-reply@casadelasluces.com.mx"
        	$mail->FromName =$res[1];//Sistema general
        	$mail->Username =$res[0];//"no-reply@casadelasluces.com.mx"

        	$mail->Mailer = "smtp";
        	$mail->SMTPSecure = "ssl";
        	$mail->Host =$res[2];//"casadelasluces.com.mx"
        	#$mail->Username = $mailfrom;
        	$mail->Password = $res[3];//"P4ssgr4l@"
        	$mail->Port =$res[4] ;//465
        	$mail->CharSet = 'UTF-8';
    	//armamos consuta para extraer datos
    	    $sql="SELECT oc.folio,prov.correo ,prov.nombre_comercial
    	    		FROM ec_ordenes_compra oc
    	    		LEFT JOIN ec_proveedor prov ON oc.id_proveedor=prov.id_proveedor
    	    		WHERE oc.id_orden_compra=$id[$i]";
   		    $eje=mysql_query($sql)or die("Error al consultar datos de órden de compra y proveedor!!!\n\n".$sql."\n\n".mysql_error());
        	$r=mysql_fetch_row($eje);
    	//documento adjunto
    	    $nombre_doc_adj=$r[0].'.csv';
    	//verificamos que el archivo exista
    		if(!file_exists("../pedidos/".$nombre_doc_adj)){
    			$estado.="<br>El archivo ../pedidos/".$nombre_doc_adj." no existe, no se pudo mandar correo a: ".$r[2];
    		}else{
    		//destinatario
    		    $mail->addAddress($r[1], 'Casa de las luces');
    		   //$mail->addAddress('maryano_dh08@hotmail.com', 'Mario Alberto Manzano');
				$mail->Subject =utf8_decode('Pedido Pedro Estrada Ascevedo');
				$mail->Body =utf8_decode("Adjunto pedido.<br>");
				$mail->IsHTML(true);
			//adjuntamos archivo
				$mail->AddAttachment("../pedidos/$nombre_doc_adj","$nombre_doc_adj");
				//$mail->AddAttachment("../../../facturas/".$row[0].".xml", $row[0].".xml");
			//send the message, check for errors
				if (!$mail->send()){
					$estado.=("<br> No se pudo enviar correo a ".$r[2]."<br>Mailer Error: ".$mail->ErrorInfo);	
				}
				$estado.="<br>Correo exitoso enviado a ".$row[2];
			}
		}//fin de for i
		echo 'ok|'.$estado;
	}
?>