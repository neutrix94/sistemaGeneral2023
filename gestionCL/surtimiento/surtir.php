<?php
include('../../conect.php');
$id = isset($_GET['id']) ? $_GET['id'] : null;
$perfil = isset($_GET['perfil']) ? $_GET['perfil'] : '';
//error_log('id:'.$id);
//error_log('$sucursal_id:'.$sucursal_id);
require_once '../classes/surtimiento.php';
$surtimientoCRUD = new SurtimientoCRUD();
$asignar = $surtimientoCRUD->tomarSurtimiento($id, $sucursal_id, $user_id);
$listaSurtir = $surtimientoCRUD->listaDetalleSurtimiento($id,$sucursal_id);
$pendientes = (count($listaSurtir)>0) ? 1: 0 ;
$indiceSurtir = 0;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surtir Producto</title>
    <!-- Cargar jQuery desde un CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Cargar Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style type="text/css">
      .text-primary {
        background-color: #eeeded; 
      }

      .p-2 {
        color: #140db2!important
      }

      .txt-content {
        font-weight: bold;
        font-size: 20px;
      }

      .txt-val-modal {
        font-weight: bold;
      }

    </style>

</head>
<body>
    <!-- header -->
    <nav class="navbar navbar-default navbar-fixed-top" style="background-color: #b10015;">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="../../index.php" style="color: #fff">
            Casa de las Luces 🎄✨
          </a>
        </div>
      </div>
    </nav>
    <!-- Tabla principal -->
    <div class="container mt-5">
        <a href="javascript: history.go(-1)">⬅️ Lista de pedidos</a><br>
        <center><h2>Pedido: #<span id="noPedido">0</span></h2></center>
        <center><p id="indexSurtimiento"><b>Partida: </b><span id="index">0 de 0</span></p></center>
        <center><h2 id="nombreProducto">Nombre del Producto</h2></center>
        <center>
          <span id="claves_proveedor"></span>
        </center>
        <h3 id="ubicacionProducto">Ubicación del Producto</h3>
        <b><p id="cantidadPiezas">Piezas: <span id="cantidad">0</span></p></b>
        <div class="form-group">
            <label for="codigoProducto">Escanear</label>
            <input type="text" id="codigoProducto" class="form-control" onkeypress="if(event.key === 'Enter') leerCodigo()">
            <span id="codigos_barras">0</span>
        </div>
        
        <div class="form-group" id="surtidoGroup" style="display: none;">
            <label for="cantidadSurtida">Surtido</label>
            <input type="number" id="cantidadSurtida" class="form-control">
        </div>
        
        <button class="btn btn-danger" onclick="noHayExistencia()">➖ No hay Existencia</button>
        <button class="btn btn-warning" onclick="abandonarSurtimiento()">✖️ Abandonar Surtimiento</button>
        <button class="btn btn-primary" onclick="siguiente()">✔️ Siguiente</button>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="surtidoModal" tabindex="-1" aria-labelledby="surtidoModalLabel" aria-hidden="true">
         <!-- <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="surtidoModalLabel">Surtido Finalizado</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> 
                </div>
                <div class="modal-body">
                    <h6 class="text-dark">No hay más productos por surtir.</h6>
                    <p class="text-muted">Entrega la mercancía a: <b><span id="nombreVendedor"></span></b></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="window.location.href='lista.php'">Aceptar</button>
                </div>
            </div>
        </div>  -->
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content text-center">
            <div class="modal-header">
              <h5 class="modal-title text-danger w-100" id="surtidoModalLabel">SURTIDO FINALIZADO</h5>
            </div>
            <div class="modal-body">
              <p class="txt-content">No hay más productos por surtir.</p>
              <p>Entrega la mercancía a:</p>
              <p class="text-primary p-2 txt-val-modal"><span id="nombreVendedor">Nombre del vendedor</span></p>
              <p>Folio de la nota:</p>
              <p class="text-primary p-2 txt-val-modal" id="folioNotaModal"></p>
              <p>Productos surtidos parcialmente:</p>
              <div id="listaProductosSurtidosParcialmente"></div>
              <!-- 
                <p class="text-primary p-2 txt-val-modal">Producto 1<br>Producto 2</p>
              -->
              <p>Productos no surtidos:</p>
              <div id="listaProductosNoSurtidos"></div>
              <!-- <p class="text-primary p-2 txt-val-modal">Producto 1<br>Producto 2</p>  -->
            </div>
            <div class="modal-footer d-flex flex-column align-items-center w-100">
              <button type="button" class="btn btn-success mb-2" style="width: 50%;" onclick="imprimeTicket()">IMPRIMIR</button>
              <button type="button" class="btn btn-primary"  style="width: 50%;" onclick="window.location.href='javascript: history.go(-1)'">LISTA DE PEDIDOS</button>
            </div>
          </div>
        </div>
    </div>
    <!-- Modal: Alertas -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel">Título de la Alerta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="alertModalContent">Contenido de la alerta...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="alertModalCancelButton" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="alertModalAcceptButton">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal: Confirmación Passowrd -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Título de la Alerta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="passwordModalContent">Contenido de la alerta...</p>
                    <input type="password" id="passwordModalInput" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="passwordModalCancelButton" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="passwordModalAcceptButton">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        var listaSurtir = {};
        var indiceSurtir = 0;
        var id = 0;
        var perfil = '<?php echo $perfil ?>';
        document.addEventListener('DOMContentLoaded', function() {
            indiceSurtir = 0;
            listaSurtir = []
            id = '<?php echo $id ?>';
            if( <?php echo $pendientes?>){
              listaSurtir = <?php echo json_encode($listaSurtir) ?>;
              refreshView();
            }else{
              //alert('No hay partidas pendientes de surtir');
              showAlertModal(
                'Sin partidas',
                'No hay partidas pendientes de surtir',
                false,
                '',
                true,
                'Aceptar'
              ); 
              $('#alertModalAcceptButton').off('click').on('click', function() {
                  $('#alertModal').modal('hide');  
                  window.location.href = "javascript: history.go(-1)";
              });
              
            }
        });

        function leerCodigo() {
            const codigoProducto = document.getElementById('codigoProducto').value;
            if (!codigoProducto) {
                showAlertModal(
                  'Producto incorrecto',
                  'Por favor, ingrese el código del producto',
                  true,
                  'Cerrar',
                  false,
                  'Aceptar'
                ); 
                return;
            }
            if(listaSurtir[indiceSurtir].codigos_barras.split(",").includes(codigoProducto)){
                document.getElementById('surtidoGroup').style.display = 'block';
                document.getElementById('cantidadSurtida').value = '';//Number(listaSurtir[indiceSurtir].cantidad_solicitada);
                document.getElementById('cantidadSurtida').focus();
            }else{
                showAlertModal(
                  'Producto incorrecto',
                  'El producto no coincide con el solicitado, favor de validar!',
                  true,
                  'Cerrar',
                  false,
                  ''
                ); 
            }


        }

        function noHayExistencia() {
          // Lógica para pausar el surtimiento en la base de datos
          showAlertModal(
            'Sin existencia',
            '¿Estás seguro de marcar sin existencia este producto?',
            true,
            'Cancelar',
            true,
            'Aceptar'
          ); 
          $('#alertModalAcceptButton').off('click').on('click', function() {
              $.ajax({
                  url: '../classes/surtimiento.php',
                  type: 'POST',
                  data: {
                      action: 'sinInventario',
                      item: listaSurtir[indiceSurtir]
                  },
                  success: function(response) {
                      indiceSurtir++;
                      if(indiceSurtir >= listaSurtir.length){
                        //Concluyó asignación
                        surtidoCompletado();
                      }else{
                        //Sigue con siguiente producto
                        refreshView();
                      }
                      
                      
                  },
                  error: function(xhr, status, error) {
                      alert('Hubo un error al cancelar la asignación: ' + error);
                  }
              });
              $('#alertModal').modal('hide');                
          });

        }

        function abandonarSurtimiento() {
            // Lógica para abandonar surtimiento
            showPasswordModal(
              'Abandonar surtimiento',
              'Ingresa la contraseña del encargado de sucursal',
              true,
              'Cancelar',
              true,
              'Aceptar'
            ); 
    
            // Establecer la función de callback para el botón de aceptar
            $('#passwordModalAcceptButton').off('click').on('click', function() {
                if( $('#passwordModalInput').val().trim() == ''){
                   alert('Ingrese contraseña de encargado');
                   return;
                }
                var valid = false;
                $.ajax({
                    url: '../classes/surtimiento.php',
                    type: 'POST',
                    data: {
                        action: 'validaPwd',
                        pwd: $('#passwordModalInput').val(),
                        sucursal: '<?php echo $sucursal_id; ?>'
                    },
                    success: function(response) {
                        if(response){
                            $.ajax({
                                url: '../classes/surtimiento.php',
                                type: 'POST',
                                data: {
                                    action: 'cancelarSurtimiento',
                                    id: '<?php echo $id; ?>'
                                },
                                success: function(response) {
                                    window.location.href = 'lista.php';
                                },
                                error: function(xhr, status, error) {
                                    alert('Hubo un error al cancelar la asignación: ' + error);
                                }
                            });
                        }else{
                          alert('Contraseña incorrecta');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Hubo un error al cancelar la asignación: ' + error);
                    }
                });
                $('#passwordModalInput').val("");
                $('#passwordModal').modal('hide');
            });
            
        }

        function siguiente() {
            if(document.getElementById('codigoProducto').value == ""){
                showAlertModal(
                  'Información faltante',
                  'Por favor, ingrese el código del producto',
                  true,
                  'Cerrar',
                  false,
                  ''
                ); 
                return;
            }
            if(Number($('#cantidadSurtida').val()) == 0){
                showAlertModal(
                  'Información incorrecta',
                  'Ingresa la cantidad surtida',
                  true,
                  'Cerrar',
                  false,
                  ''
                ); 
                return;
            }
            // Lógica para ir al siguiente producto
            if( Number($('#cantidadSurtida').val()) > Number(listaSurtir[indiceSurtir].cantidad_solicitada)){
               showAlertModal(
                 'Información incorrecta',
                 'No puedes ingresar una cantidad mayor a la solicitada',
                 true,
                 'Cerrar',
                 false,
                 ''
               ); 
            }else{
              listaSurtir[indiceSurtir].cantidad_surtida = document.getElementById('cantidadSurtida').value;
              $.ajax({
                  url: '../classes/surtimiento.php',
                  type: 'POST',
                  data: {
                      action: 'productoSurtido',
                      item: listaSurtir[indiceSurtir]
                  },
                  success: function(response) {
                      indiceSurtir++;
                      if(indiceSurtir >= listaSurtir.length){
                        //Concluyó asignación
                        surtidoCompletado();
                      }else{
                        //Sigue con siguiente producto
                        refreshView();
                      }
                      
                      
                  },
                  error: function(xhr, status, error) {
                      alert('Hubo un error al cancelar la asignación: ' + error);
                  }
              });
              
              //Limpia inputs
              document.getElementById('cantidadSurtida').value = '';
              document.getElementById('codigoProducto').value = '';
              document.getElementById('surtidoGroup').style.display = 'none';
            }
            
        }
        
        function refreshView(){
            //Actualiza datos de la vista
            document.getElementById('noPedido').textContent = listaSurtir[indiceSurtir].no_pedido;
            document.getElementById('nombreProducto').textContent = listaSurtir[indiceSurtir].nombre;
            document.getElementById('ubicacionProducto').textContent = 'Ubicación: '+ listaSurtir[indiceSurtir].numero_ubicacion_desde + '-' +listaSurtir[indiceSurtir].altura_desde;
            document.getElementById('cantidad').textContent = listaSurtir[indiceSurtir].cantidad_solicitada;
            document.getElementById('index').textContent = Number(indiceSurtir)+1 +' de '+ Number(listaSurtir.length);  
            document.getElementById('codigos_barras').textContent = '**(Sólo habilitado para pruebas) Códigos de barras permitidos: '+ listaSurtir[indiceSurtir].codigos_barras ;
            
            if(listaSurtir[indiceSurtir].claves_proveedor !== undefined && listaSurtir[indiceSurtir].claves_proveedor !== null){
              var htmlClaves = '';
              Object.keys(listaSurtir[indiceSurtir].claves_proveedor.split(',')).forEach(key=>{
                  console.log(listaSurtir[indiceSurtir].claves_proveedor.split(',')[key]);
                  if (listaSurtir[indiceSurtir].claves_proveedor.split(',')[key] == listaSurtir[indiceSurtir].clave_prioridad_maxima) {
                    htmlClaves += '<span class="badge badge-primary">'+listaSurtir[indiceSurtir].claves_proveedor.split(',')[key]+'</span>';
                  }else{
                    htmlClaves += '<span class="badge badge-secondary">'+listaSurtir[indiceSurtir].claves_proveedor.split(',')[key]+'</span>';
                  }
              })
              document.getElementById('claves_proveedor').innerHTML = htmlClaves;
            }
        }
        
        function surtidoCompletado(){
           //Surtimiento completado
           document.getElementById('nombreVendedor').innerText = listaSurtir[0].nombre_vendedor;
           //Mandamos a llamar para llenar modal
           var currentUrl = window.location.href;
      
          var parts_url = currentUrl.split('/gestionCL');
          var apiUrl = parts_url[0] + '/rest/v1/surte/Faltante';

          var queryString = window.location.search;
          // Crea un objeto URLSearchParams con la cadena de consulta
          var urlParams = new URLSearchParams(queryString);
          // Obtén el valor del parámetro 'id'
          var idValue = urlParams.get('id');
          var id = idValue;

          $.ajax({
            type: 'POST',
            url: apiUrl,
            headers: {
              'Token':'9aca3d54-6eae-48f4-8597-6022be714915'
            },
            data: {
                "pedido": id
            },
            datatype: 'json',
            success: function (data) {
              console.log("algo");
              window.dataSurtmiento = data;
              
              if( data.result.resultado == "Faltante" ){
                $('#surtidoModal').modal('show');

                var folio = data.result.detalle.folioPedido;
                var productosParciales = data.result.detalle.surtidoParcial;
                var stringProductosParciales = "";
  
                var productosNoSurtidos = data.result.detalle.noSurtido;
                var stringProductosNoSurtidos = "";
  
                if( productosParciales.length > 0 ){
                  
                  for (let index = 0; index < productosParciales.length; index++) {
                    
                    stringProductosParciales += "<br>" + productosParciales[index].nombre;
                  }
                }
  
                if( productosNoSurtidos.length > 0 ){
                  for (let index = 0; index < productosNoSurtidos.length; index++) {
                    
                    stringProductosNoSurtidos += "<br>" + productosNoSurtidos[index].nombre;
                  }
                }
  
                $('#folioNotaModal').text(folio);
  
                if( stringProductosParciales !="" ){
                  $('#listaProductosSurtidosParcialmente').append('<p class="text-primary p-2 txt-val-modal">' + stringProductosParciales + '</p>');
                }else{
                  $('#listaProductosSurtidosParcialmente').append('<p class="text-primary p-2 txt-val-modal">&nbsp;</p>')
                }
  
                if( stringProductosNoSurtidos != "" ){
                  $('#listaProductosNoSurtidos').append('<p class="text-primary p-2 txt-val-modal">' + stringProductosNoSurtidos + '</p>');
                }else{
                  $('#listaProductosNoSurtidos').append('<p class="text-primary p-2 txt-val-modal">&nbsp;</p>');
                }
              }else{

                //No se muestra detalle para imprimir ya que todo el Pedido se surtió completo

                alert( data.result.resultado );
                window.location.href='javascript: history.go(-1)';

              }


            },
            error:function(error){
              console.log("ERROR".error);
            }
          });
           

           //imprimeTicket();
        }
        
        function showAlertModal(title, content, showCancel, titleCancel, showAccept, titleAccept) {
            //Establece título y contenido
            document.getElementById('alertModalLabel').innerText = title;
            document.getElementById('alertModalContent').innerText = content;
            //Habilita botón cancelar
            if(showCancel){
              $('#alertModalCancelButton').show();
              $('#alertModalCancelButton').text(titleCancel);

            }else{
              $('#alertModalCancelButton').hide();
            }
            //Habilita botón aceptar
            if(showAccept){
              $('#alertModalAcceptButton').show();
              $('#alertModalAcceptButton').text(titleAccept);
            }else{
              $('#alertModalAcceptButton').hide();
            }
            $('#alertModal').modal('show');
        }

        function imprimeTicket(){
          
          const dataToSend = window.dataSurtmiento;

          // Realiza la solicitud a ticket.php
          fetch(`../surtimiento/pdf/ticket.php`, {
              method: 'POST', // Método POST para enviar datos
              headers: {
                  'Content-Type': 'application/json' // Especifica que se envía JSON
              },
              body: JSON.stringify(dataToSend) // Convierte el objeto a JSON y lo envía
          })
          .then(response => response.text())
          .then(data => {
              console.log("PDF generado y guardado en la ruta especificada");
              // Muestra una alerta o actualiza la interfaz de usuario
              alert("PDF generado exitosamente");

              window.location.href = 'lista.php'; // Redirige a la página lista.php
          })
          .catch(error => {
              console.error("Error al generar el PDF:", error);
              alert("Error al generar el PDF");
          });
        }
        
        function showPasswordModal(title, content, showCancel, titleCancel, showAccept, titleAccept) {
            //Establece título y contenido
            document.getElementById('passwordModalLabel').innerText = title;
            document.getElementById('passwordModalContent').innerText = content;
            //Habilita botón cancelar
            if(showCancel){
              $('#passwordModalCancelButton').show();
              $('#passwordModalCancelButton').text(titleCancel);

            }else{
              $('#passwordModalCancelButton').hide();
            }
            //Habilita botón aceptar
            if(showAccept){
              $('#passwordModalAcceptButton').show();
              $('#passwordModalAcceptButton').text(titleAccept);
            }else{
              $('#passwordModalAcceptButton').hide();
            }
            $('#passwordModal').modal('show');
        }

    </script>
</body>
</html>
