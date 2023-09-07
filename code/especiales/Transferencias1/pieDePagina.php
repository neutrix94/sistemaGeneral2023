<script language="JavaScript" src="js/jquery-1.10.2.min.js"></script>
<script type="text/JavaScript">
//funcion que redirecciona a home
    function botones(flag){
        //alert();
        if(flag==4){
            var conf=confirm('Ha elegido Cancelar, Esta seguro de salir?');
            if(!conf){
                return false;
            }

            location.href="../../general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==";
            return false;
        }
        if(document.getElementById('modificaciones')){
            var modificado=document.getElementById('modificaciones').value;
            if(modificado!=0){
                alert('No puede salir sin guardar los cambios!!!');
                    return false;
            }
        }
    //var salir=confirm("Desea salir sin completar la transferencia?");
    //if(salir==true){
        if(flag==1){
            location.href="../../../index.php"; 
        }else if(flag==2){
            location.href="../../especiales/Transferencias/transf.php";
        }else if(flag==3){
            location.href="../../general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==";

        }
    //}else{
        //return false;
    //}
}
</script>
<div style="background:#83B141; bottom:0; position:fixed; width:100%; height:10%;">
    		<div style="padding:10px;text-decoration:none; background:rgba(0,0,0,.2); width:98%; height:100%;">
            	<table border="0" width="100%;" height="100%">
            		<tr>
            			<td width="20%" id="b1">
            				<input type="button" class="regresar" value="Panel Principal" style="padding:6px;border-radius:10px;color:white;bottom:10px;" 
            				onclick="<?php if(isset($status)){echo 'botones(3);';}else{echo'botones(1);';}?>" />
        				</td>
                        <td width="20%" align="center" id="b2">
                          <!-- <div style="border:1px solid gray;height:100%;width:100%;">-->
                                    <!--<p>-->
                                <input type="button" class="regresar" value="Ver Listado" 
                                        style="padding:6px;border-radius:10px;color:white;bottom:10px;"
                                        onclick="botones(3);" />
                                <!---->
                                   
                        </td>
                        <td align="center" style="border: 0px;">
                            <input type="button" value="<?php if($status==1){echo 'Guardar Cambios';}else{echo 'Guardar';}?>" id="btn_guardar" class="regresar"
                                style="padding:10px;border-radius:10px;color:black;bottom:10px;width:150px;font-size:20px;background:darkgray;
                                <?php if($status>1){echo 'display:none;';} ?>"  
                                onclick="<?php if($status==1){ echo 'desenfocar(2);';}else{echo 'desenfocar(1);';}?>" />
                        </td>
                        <td align="center" width="20%" id="b3">
                            <?php 
                                    if(!isset($status) || ($status<=1)){echo '<input type="button" class="regresar" value="Salir sin guardar" onclick="botones(4);"
                                    style="padding:6px;border-radius:10px;color:white;bottom:10px;">';} 

                                if($perfil_usuario==1){//si es administrador
                                   echo '<input type="button" class="regresar" value="Activar Productos" onclick="activa_productos();" style="padding:6px;border-radius:10px;color:white;bottom:10px;">';
                                }
                            ?>
                                <!---->
                        </td>
        				<td width="20%" align="right" id="b4">
        				    <input type="button" class="regresar" value="Nueva Transferencia" style="padding:6px;border-radius:10px;color:white;bottom:10px;" 
            				onclick="botones(2);"/>
        				</td>
    				</tr>
    			</table>
            </div>
        </div>
        <style>
        </style>