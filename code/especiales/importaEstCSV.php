<?php
    //php_track_vars;
    
    extract($_GET);
    extract($_POST);
    
//CONECCION Y PERMISOS A LA BASE DE DATOS
    include("../../conect.php");
    
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
                $contador=0;//declaramos el contador
                $est_dep=0;
                $factor_final=0;
                while(!feof($ar))
                {
                    $contador++;//incrementamos el contador
                    $aux=fgets($ar, 10000);
                    
                    if($nver > 1 && $aux != '')
                    {
                        //echo "$aux<br>";
                        $ax=explode(",", $aux);
                        if(sizeof($ax) != 5)
                        {
                            mysql_query("ROLLBACK");
                            $mensaje.="Error en la linea ".($nver+1).", numero de campos no valido\n";
                        }
                        
                        if($ax[0] != 'NO'){
                        /*implementación Oscar 10.09.2018 para afectar estacionalidad final cuando se modifica la estacionalidad alta*/
                            if($contador==3){
                                $sql="SELECT 
                                        est.es_alta,/*0*/
                                        s.id_sucursal,/*1*/
                                        s.factor_estacionalidad_final,/*2*/
                                        (SELECT id_estacionalidad FROM ec_estacionalidad WHERE id_sucursal=s.id_sucursal AND es_alta=0)/*3*/ 
                                    FROM ec_estacionalidad est
                                    LEFT JOIN sys_sucursales s ON est.id_sucursal=s.id_sucursal 
                                    WHERE est.id_estacionalidad=$ax[1]";
                            //die($sql);
                                $eje=mysql_query($sql)or die("Error al consultar el tipo de estacionalidad!!!");
                                $res=mysql_fetch_row($eje);
                                if($res[0]==1){//si es estacionalidad alta
                                    $est_dep=$res[3];
                                    $factor_final=$res[2];
                                }
                            }

                            $sql="UPDATE ec_estacionalidad_producto SET
                                  id_producto=$ax[2],
                                  /*minimo=$ax[4],
                                  medio=$ax[5],
                                  */maximo=$ax[4]
                                  WHERE id_estacionalidad_producto=$ax[0]";
                                  
                            //echo $sql;
                            if(!mysql_query($sql)){
                                mysql_query("ROLLBACK");
                                $mensaje.="Error en la linea ".($nver+1).", formato de campos no valido\n";
                            }
                        //si es estacionalidad alta;
                            if($res[0]==1){
                                $dato=round($ax[6]*$factor_final);
                            //actualizamos la estacionalidad final
                                $sql="UPDATE ec_estacionalidad_producto SET
                                        maximo=$dato
                                    WHERE id_estacionalidad=$est_dep AND id_producto=$ax[2]";
                                  
                                if(!mysql_query($sql)){
                                    $error=mysql_error();
                                    mysql_query("ROLLBACK");
                                    $mensaje.="Error al actualizar la estacionalidad dependiente!!!".$sql."\n\n".$error;
                                }
                            }
                    /*Fin de cambio Oscar 10.09.2018*/
                        }else{
                            $sql="INSERT INTO ec_precios_detalle(id_precio, de_valor, a_valor, precio_venta, precio_oferta, id_producto)
                                                          VALUES($id_precio, $ax[4], $ax[5], $ax[6], $ax[7], $ax[2])";
                            if(!mysql_query($sql)){
                                mysql_query("ROLLBACK");
                                $mensaje.="Error en la linea ".($nver+1).", formato de campos no valido\n";
                            }                                                           
                        }                        
                    }
                    $nver++;
                }
            }
            else
                $mensaje="No se pudo procesar el archivo";
            
            fclose($ar);
            
        }
        else
            $mensaje="No se pudo procesar el archivo.";
        
        
        if($mensaje == "")
            $mensaje="Se ha procesado el archivo con exito";
        
        
        $smarty->assign("mensaje", $mensaje);
        
        
        mysql_query("COMMIT");
    }
    
    
    $smarty->assign("id_estacionalidad", $id_estacionalidad);
    
    $smarty->display("especiales/importaEstCSV.tpl");
    
    
?>  