<?php 

    header("Content-Type: text/plain;charset=utf-8");
    
    include("../../conectMin.php");//incluimos librería de conexión
    //include("../../conexionDoble.php");
    
    extract($_GET);
    
     mysql_query("BEGIN"/*,$local*/);//abrimos inicio de transacción
    try{//abrimos try
    //generamos consulta p/insertar cliente 
        $sql="INSERT INTO ec_clientes(nombre, telefono, movil, email, es_cliente, id_sucursal)
                VALUES('".utf8_decode($nombre)."', '$telefono', '$celular', '$correo', 1, $user_sucursal)";
        if(!mysql_query($sql/*,$local*/)){
            throw new Exception("No se pudo actualizar el pedido:\n\n " . mysql_error(/*$local*/));
        }
        $id=mysql_insert_id(/*$local*/);//capturmos el id_insertado
/*
    //implementado por Oscar 13-12-2017
        $sq="SELECT * FROM ec_clientes WHERE id_cliente=$id";
        $eje=mysql_query($sq,$local)or die("ERROR!!!\n\n".mysql_error($local));
        $rw=mysql_fetch_row($eje);

        //mysql_query("BEGIN",$linea);
        $sqlLinea="INSERT INTO ec_clientes VALUES(null,'$rw[0]','$rw[2]','$rw[3]','$rw[4]','$rw[5]','$rw[6]','$rw[7]','$rw[8]','$rw[9]','$rw[10]','$rw[11]','$rw[12]','$rw[13]','$rw[14]')";
        $ejeLinea=mysql_query($sqlLinea,$linea);

        if($ejeLinea){
            $id_nuevo=mysql_insert_id($linea);
        //mndamos el id equivalente
            $loc="UPDATE ec_clientes SET id_global=$id_nuevo WHERE id_cliente=$id";
            $ej=mysql_query($loc,$local);            
        }
        //mysql_query("COMMIT",$linea);
*/
    //actualizamos el cliente correspondiente al pedido
        $sql="UPDATE ec_pedidos_back SET id_cliente=$id WHERE id_pedido=$idp";
        
        if(!mysql_query($sql/*,$local*/)){
                throw new Exception("No se pudo actualizar el pedido:\n\n " . mysql_error($local));
        }                         
        mysql_query("COMMIT"/*,$local*/);//autorizamos transacción
        
        echo "exito";//regresamos que fue correcto                    
    }
//capturamos error
    catch (Exception $e)
    {
        echo "Error: " . $e->getMessage();
        mysql_query("ROLLBACK"/*,$local*/);
        mysql_close();
        exit ();
    } 

?>