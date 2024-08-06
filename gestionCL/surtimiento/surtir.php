<?php
$id = isset($_GET['id']) ? $_GET['id'] : null;
$perfil = isset($_GET['perfil']) ? $_GET['perfil'] : '';
require_once '../classes/surtimiento.php';
$surtimientoCRUD = new SurtimientoCRUD();
$listaSurtir = $surtimientoCRUD->listaDetalleSurtimiento($id);
$pendientes = (count($listaSurtir)>0) ? 1: 0 ;
// error_log(print_r($listaSurtir,true));
// error_log($listaSurtir[0]['nombre']);
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

</head>
<body>
    <!-- Tabla principal -->
    <div class="container mt-5">
        <center><p id="indexSurtimiento">Partida: <span id="index">0 de 0</span></p></center>
        <center><h2 id="nombreProducto">Nombre del Producto</h2></center>
        <h3 id="ubicacionProducto">Ubicación del Producto</h3>
        <b><p id="cantidadPiezas">Piezas: <span id="cantidad">0</span></p></b>
        
        <div class="form-group">
            <label for="codigoProducto">Escanear</label>
            <input type="text" id="codigoProducto" class="form-control" onkeypress="if(event.key === 'Enter') leerCodigo()">
        </div>
        
        <div class="form-group" id="surtidoGroup" style="display: none;">
            <label for="cantidadSurtida">Surtido</label>
            <input type="number" id="cantidadSurtida" class="form-control">
        </div>
        
        <button class="btn btn-danger" onclick="noHayExistencia()">No hay Existencia</button>
        <button class="btn btn-warning" onclick="abandonarSurtimiento()">Abandonar Surtimiento</button>
        <button class="btn btn-primary" onclick="siguiente()">Siguiente</button>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="surtidoModal" tabindex="-1" aria-labelledby="surtidoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
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
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        var listaSurtir = {};
        var indiceSurtir = 0;
        var perfil = '<?php echo $perfil ?>';
        document.addEventListener('DOMContentLoaded', function() {
            indiceSurtir = 0;
            listaSurtir = []
            const id = '<?php echo $id ?>';
            if( <?php echo $pendientes?>){
              listaSurtir = <?php echo json_encode($listaSurtir) ?>;
              refreshView();
            }else{
              alert('No hay partidas pendientes de surtir');
              window.location.href = 'lista.php';
            }
        });

        function leerCodigo() {
            const codigoProducto = document.getElementById('codigoProducto').value;
            if (!codigoProducto) {
                alert('Por favor, ingrese el código del producto.');
                return;
            }
            if(listaSurtir[indiceSurtir].codigos_barras.split(",").includes(codigoProducto)){
                document.getElementById('surtidoGroup').style.display = 'block';
                document.getElementById('cantidadSurtida').value = Number(listaSurtir[indiceSurtir].cantidad_solicitada);
                document.getElementById('cantidadSurtida').focus();
            }else{
                alert('El código de borradas no coincide con el producto.');
            }


        }

        function noHayExistencia() {
          // Lógica para pausar el surtimiento en la base de datos
          if (confirm('¿Estás seguro de marcar sin existencia este producto?')) {
                $.ajax({
                    url: '../classes/surtimiento.php',
                    type: 'POST',
                    data: {
                        action: 'sinInventario',
                        id: listaSurtir[indiceSurtir].id
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
          }
        }

        function abandonarSurtimiento() {
            // Lógica para abandonar surtimiento
            if(perfil != 2){
                alert('No puedes realizar esta acción, pide al encargado que lo realice');
                return;
            }
            if (confirm('¿Estás seguro de abandonar el surtimiento?')) {
                window.location.href = 'asignar.php?id=' +'<?php echo $id ?>';
            }
        }

        function siguiente() {
            if(Number($('#cantidadSurtida').val()) == 0){
                alert('Ingresa la cantidad surtida');
                return;
            }
            // Lógica para ir al siguiente producto
            if( Number($('#cantidadSurtida').val()) > Number(listaSurtir[indiceSurtir].cantidad_solicitada)){
               alert('No puedes ingresar una cantidad mayor a la solicitada');
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
            document.getElementById('nombreProducto').textContent = listaSurtir[indiceSurtir].nombre;
            document.getElementById('ubicacionProducto').textContent = 'Ubicación: '+ listaSurtir[indiceSurtir].numero_ubicacion_desde + '-' +listaSurtir[indiceSurtir].altura_desde;
            document.getElementById('cantidad').textContent = listaSurtir[indiceSurtir].cantidad_solicitada;
            document.getElementById('index').textContent = Number(indiceSurtir)+1 +' de '+ Number(listaSurtir.length);  
        }
        
        function surtidoCompletado(){
           //Surtimiento completado
           document.getElementById('nombreVendedor').innerText = listaSurtir[0].nombre_vendedor;
           $('#surtidoModal').modal('show');
        }
    </script>
</body>
</html>
