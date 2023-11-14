<div class="emergent">
  <div class="emergent_content"></div>
</div>
<div class="row home_container">
  <div class="">
   <div  style="width:400px; margin:0 auto;">
    <?php
//
    if($mayoreo!='0'){  
  ?>  
<!-- position:absolute;top:10%;left:0%;background:rgba(210,225,0,.8);width:150px;height:50px;text-align:center;color:white;border-radius:5px; -->
<br>
    <button 
      onclick="activaMayoreo();" 
      style="position:absolute;top:50px;left:0%; padding : 10px; width : 42%;"
      class="btn btn-danger"
    ><!--position:absolute;top:70%;left:70%;-->
      <i class="icon-star-empty-2">Venta Especial</i>
    </button>

    <button 
      onclick="activaCotizacion();" 
      style="position:fixed;top:80% !important;left:0%; padding : 20px; border-radius : 50%;"
      class="btn btn-warning"
    ><!--position:absolute;top:70%;left:70%;-->
      <i class="icon-star-empty-2"><br>Cotizacion</i>
    </button>
    <button 
      onclick="activaMayoreo( 1 );" 
      style="position:fixed;top:80% !important;right:2%; padding : 20px; border-radius : 50%;"
      class="btn btn-danger"
    ><!--position:absolute;top:70%;left:70%;-->
      <i class="icon-star-empty-2">Cotizacion<br>Mayoreo</i>
    </button>
  <?php
    }
//verifica si tiene el permiso para la validacion de ventas
//echo 'perfil : ' . $perfil_usuario;
  $sql = "SELECT 
            IF( ver = 1 OR modificar = 1 OR eliminar =1 OR nuevo = 1 OR imprimir = 0 OR generar = 1, 1, 0  ) AS permission
          FROM sys_permisos
          WHERE id_menu = 237
          AND id_perfil = '{$perfil_usuario}'";
  $stm = mysql_query( $sql ) or die( "Error al consultar el permiso de Validación de Tickets de venta : {mysql_error()}" );
  $rows_permission = mysql_fetch_assoc( $stm );
  if( $rows_permission['permission'] == 1 ){
    echo "<button 
        class=\"btn btn-success\"
        style=\"position : absolute; left : 0%; top : 190px; padding : 10px;width : 42%;\"
        onclick=\"location.href='../touch/validation/index.php?';\"
        id=\"initital_validation_btn\"
      >
        <i class=\"icon-barcode\">Validación Ticket</i>
      </button>";
/*implementacion Oscar 2023/11/07 para reimpresion de ticket de cobro*/
    echo "<button 
        class=\"btn btn-primary\"
        style=\"position : absolute; left : 0%; top : 260px; padding : 10px;width : 42%;\"
        onclick=\"reprint_ticket_list();\"
        id=\"initital_validation_btn\"
      >
        <i class=\"icon-print\">Reimprimir Ticket de Cobro</i>
      </button>
    </div>";
  }
?>
    <!--img src="../img/img_casadelasluces/Logo.png" width="100%"/> < width="366" height="594"  alt=""-->
  </div>
  
  </div>
  <!--Implementación de Administración de paquetes Oscar 27.03.2018 -->
    <button 
      class="btn btn-dark"
      style="position:absolute;top:50px;right:2%; padding : 10px;width : 42%;" 
      onclick="location.href='index.php?scr=paquetes';"
      id="initital_packs_btn"
    >
      <i class="icon-dropbox-2">Paquetes</i>
    </button>

  <!--Implementación Oscar 03.03.2019 para las devoluciones que quedaron pendientes-->
    <button 
      class="btn btn-info"
      style="position:absolute;top:120px !important;right:2%; padding : 10px;width : 42%;"
      onclick="location.href='../code/general/listados.php?tabla=ZWNfZGV2b2x1Y2lvbg==&no_tabla=MQ==';"
      id="initital_returns_btn"
    >
      <i class="icon-spin3">Devs. Pend.</i>
    </button>
  <!--Implementación de Cobros de ventas Oscar 27.03.2018 -->
  <!--boton redirecciona catalogo-->

    <button 
      class="btn btn-primary"
      style="position:absolute;top:190px !important;right:2%; padding : 10px;"
      onclick="window.open('../../pagina/productos.php');"
    >
      <i class="icon-book-open">Catálogo</i>
    </button>
<?php
/*if($perfil_usuario==7){*/
  $sql="SELECT IF(p.ver=1 OR p.modificar=1,1,0) 
      FROM sys_permisos p
      LEFT JOIN sys_users_perfiles perf ON perf.id_perfil=p.id_perfil
      LEFT JOIN sys_users u ON u.tipo_perfil=perf.id_perfil 
      WHERE p.id_menu=200
      AND u.id_usuario=$user_id";
  //die($sql);
  $eje=mysql_query($sql)or die("Error al consultar el permiso de cajero!!!<br>".mysql_error()."<br>".$sql);
  $es_cajero=mysql_fetch_row($eje);
  if($es_cajero[0]==1){
/*
  Deshabilitado por Oscar 2023 para cobros
    $sql="SELECT multicajero FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
    $eje=mysql_query($sql)or die("Error al consultar si la sucursal es multicajero!!!<br>".mysql_error());
    $r_c=mysql_fetch_row($eje);
   // die('here');
    if($r_c[0]==1){*/
?>
    <p align="center" class="pqt" style="top:70%;" onclick="location.href='../code/especiales/tesoreria/cobros/index.php?';">
      <img src="img/cobros.png" height="70%" width="70%" title="ingresae para cobrar ventas">
      <br>Cobrar
    </p>
<?php
//  }
}
?>
</div>
<style type="text/css">
  .pqt{border-radius: 100%;border:2px solid brown;width: 100px;height: 100px;position: absolute;top:25%;padding: 5px;right:5%;z-index: 2;color: brown;
      font-size : 80%;}
  .pqt:hover{
    border:4px solid gray;
    background:green;
    color: white;
  }
  #page{
    background-image : url( "../img/img_casadelasluces/Logo.png" ) !important;
    background-position: center; /* Center the image */
    background-repeat: no-repeat; /* Do not repeat the image */
    background-size: 50%;
    background-color: rgba(0,0,0,.1);
  }
  .emergent{
    position: fixed;
    top : 0;
    left: 0;
    width: 100%;
    height : 100%;
    background-color : rgba( 0,0,0,.7 );
    z-index : 9000;
    display : none; 
  }
  .emergent_content{
    position: relative;
    width : 80%;
    height : 70%;
    top : 10%;
    left : 10%;
    background-color: white; 
  }

  @media only screen and (max-width: 600px) {
    #page{
      background-size: 80%;
    }
    .pqt{
      font-size : 50%;
    }
    .list-container{
      max-height: 300px !important;
    }
    .emergent_content{
      height: 85% !important;
    }
    .tickets_table{
      font-size : 80% !important;
    }
  }

</style>
<!--Fin de implementación Oscar 27.03.2018-->

<script type="text/javascript">
  function reprint_ticket_list(){
    var content = `<div class="row" style="padding : 20px;">
      <div class="col-1 text-end"></div>
      <div class="col-10 text-end">
        <h2 class="text-center fs-3">Reimpresion</h2>
      </div>
      <div class="col-1 text-end">
        <button
          type="button"
          class="btn btn-light"
          style="background-color : transparent; border : none;"
          onclick="close_emergent();"
        >
          <i class="text-danger fs-7">X</i>
        </button>
      </div>
      </div>
      <div class="input-group" style="padding : 5px;">
        <input type="text" id="tickets_seeker" class="form-control" onkeyup="seek_ticket( event );"  style="box-shadow : 1px 1px 1px rgba( 0,20,0,.5 );" placeholder="Buscar...">
        <button class="icon-search btn btn-primary" onclick="seek_ticket( 'intro' );"></button>
      </div>
      <div style="height : 350px;max-height : 350px; overflow : scroll; position : sticky;" class="list_container">
        <table class="table table-bordered table-striped tickets_table">
          <thead style="position : sticky; top : 0; background-color : white;">
            <tr>
              <th class="text-center">Vendedor</th>
              <th class="text-center">Folio</th>
              <th class="text-center">Monto</th>
              <th class="text-center">Cliente</th>
              <th class="text-center">Fecha</th>
              <th class="text-center">Imprimir</th>
            </tr>
          </thead>
          <tbody id="tickets_results">
            ${getLastTickets()}
          </tbody>
        </table>
    </div>
    <!--table>
      <tfoot>
        <tr>
          <th class="text-center">
            <i class="icon-left-big"></i>
          </th>
          <th class="text-center" colspan="2"></th>
          <th class="text-center">
            <i class="icon-right-big"></i>
          </th>
        </tr>
      </tfoot>
    </table-->`;
    $( '.emergent_content' ).html( content );
    $( '.emergent' ).css( 'display', 'block' );
    //alert( '' );
  }

  function seek_ticket( e ){
    if( e.keyCode != 13 && e != 'intro' ){
      return false;
    }
  //limpia 
    $( '#tickets_results' ).empty();
  //mustra resultados
    var results = getLastTickets( $( '#tickets_seeker' ).val().trim() );
    $( '#tickets_results' ).append( results );
  }

  function getLastTickets( txt = null ){
    //$(  )
    var url = "ajax/getTicketsToReprint.php";
    if( txt != null ){
      url += "?key=" + txt;
    }

    var resp = ajaxR( url );
    //alert( resp );
    return resp;
  }

  function close_emergent(){
    $( '.emergent_content' ).html( '' );
    $( '.emergent' ).css( 'display', 'none' );
  }

  function print_ticket( sale_id ){
    var url = "index.php?scr=talon_pago&idp=" + sale_id + "&noImp=1";
    var resp = ajaxR( url );
    $( '.emergent_content' ).html( `<div class="row text-center">
        <div class="col-1"></div>
        <div class="col-10 text-center">
          <br><br><br>
          <br><br><br>
          <h3 class="fs-1 text-success text-center">Impresion Generada exitosamente</h3>
          <br><br>
          <button
            type="button"
            class="btn btn-success"
            onclick="close_emergent();"
          >
            <i class="icon-ok-circled fs-2">Aceptar</i>
          </button>
        </div>
      </div>` );
    //alert( "Impresion Generada" );
  }

  function ajaxR(url){
    if(window.ActiveXObject){       
        var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
    }
    else if (window.XMLHttpRequest)
    {       
        var httpObj = new XMLHttpRequest(); 
    }
    httpObj.open("POST", url , false, "", "");
    httpObj.send(null);
    return httpObj.responseText;
  } 

</script>
