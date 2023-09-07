<?php
    //php_track_vars;
    
    extract($_GET);
    extract($_POST);
    
//CONECCION Y PERMISOS A LA BASE DE DATOS
    include("../../conect.php");
    //die("Precio: ".$id_precio);
    
    if($procesa == 'SI')
    {
        $mensaje="";
        
        mysql_query("BEGIN");
        
        if($_FILES["archivo"]['tmp_name'])
        {
            $nver=0;
            $ar=fopen($_FILES["archivo"]['tmp_name'], "rt");
            if($ar)
            {
                while(!feof($ar))
                {
                    $aux=fgets($ar, 10000);
                    
                    if($nver > 0 && $aux != '')
                    {
                        //echo "$aux<br>";
                        $ax=explode(",", $aux);
                        if(sizeof($ax) != 23)
                        {
                            mysql_query("ROLLBACK");
                            $mensaje.="Error en la linea ".($nver+1).", numero de campos no valido\n";
                        }
                        
                        if($ax[6] != '0' && $ax[6]!='')
                        {
                            $sql="UPDATE ec_precios_detalle SET
                                  id_producto=$ax[0],
                                  de_valor=$ax[8],
                                  a_valor=$ax[9],
                                  precio_venta=$ax[10],
                                  precio_etiqueta=$ax[11],
                                  es_oferta=$ax[12]
                                  WHERE id_precio_detalle=$ax[6]";
                                  
                            //echo $sql;
                            if(!mysql_query($sql))
                            {
                                mysql_query("ROLLBACK");
                                $mensaje.="Error en la linea ".($nver+1).", formato de campos no valido : " . sizeof($ax) . "\n".mysql_error();
                            }                                  
                        }
                        else if($ax[6]!='')
                        {
                            $sql="INSERT INTO ec_precios_detalle(id_precio, de_valor, a_valor, precio_venta, precio_etiqueta, id_producto,es_oferta)
                                                          VALUES($id_precio, $ax[8], $ax[9], $ax[10], $ax[11], $ax[0], $ax[12])";
                            if(!mysql_query($sql))
                            {
                                $mensaje.="Error en la linea ".($nver+1)."\n".$sql;
                                mysql_query("ROLLBACK");
                                die($mensaje."\n".$sql);
                            }                                                           
                        }
                        
                    }
                    $nver++;
                }
				if($nver > 0)
				{
					$sql="UPDATE ec_precios SET ultima_actualizacion=NOW() WHERE id_precio = $id_precio";
					if(!mysql_query($sql))
                    {
						mysql_query("ROLLBACK");
						$mensaje.="Error al actualizar la lista de precios\n";
					} 
				}	
            }
            else{
                $mensaje="No se pudo procesar el archivo";
            }
            
            fclose($ar);
            
        }else{
            $mensaje="No se pudo procesar el archivo.";
        }
        
        if($mensaje == "")
            $mensaje="Se ha procesado el archivo con exito";
        
        
        $smarty->assign("mensaje", $mensaje);
        
        
        mysql_query("COMMIT");
    //    echo '~~~~ok';
    }
    
    
    $smarty->assign("id_precio", $id_precio);
    
    $smarty->display("especiales/importaCSV.tpl");
    
    
?>  