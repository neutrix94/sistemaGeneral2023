<?php
require_once '../classes/surtimiento.php';
include('../../conect.php');

// Obtiene datos de usuario logueado & lista de pedidos
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
$fecha = empty($fecha) ? date("Y-m-d") : $fecha;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
$estado = isset($_GET['estado']) ? $_GET['estado'] : null;
$surtimientoCRUD = new SurtimientoCRUD();
$idUsuario = isset($user_id) ? $user_id : null;
$usuario =  $surtimientoCRUD->getUserProfile($idUsuario);
$perfil = (isset($usuario[0]) && ($usuario[0]['tipo_perfil'] == '4' || $usuario[0]['tipo_perfil'] == '8') &&  $usuario[0]['id_encargado'] == $idUsuario ) ? '2': '1';
$surtimientos = $surtimientoCRUD->listaSurtir($perfil,$idUsuario,$sucursal_id,$fecha,$tipo,$estado);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Pedidos</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }
        .table-striped tbody tr:nth-of-type(even) {
            background-color: #ececec;
        }
        .complemento-true {
            background-color: #cce5ff !important;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top" style="background-color: #b10015;">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand" href="../../index.php" style="color: #fff">
        Casa de las Luces 🎄✨
      </a>
    </div>
  </div>
</nav>
<div class="container mt-5">
    <h1 class="text-center">Lista de Pedidos (<?php echo count($surtimientos) ?>)</h1><br>
    <form id="filterForm" method="GET" action="lista.php" class="mb-4">
        <div class="row">
            <!-- Filtro por Fecha -->
            <div class="col-md-3">
                <label for="fecha">Fecha</label>
                <input type="date" name="fecha" id="fecha" class="form-control" value="<?php echo $fecha ?>"/>
            </div>
            
            <!-- Filtro por Estado -->
            <div class="col-md-3">
                <label for="estado">Estado</label>
                <select name="estado" id="estado" class="form-control" value="<?php echo $estado ?>">
                    <option value="">Default</option>
                    <option value="Todos" <?php echo $estado == "Todos" ? "selected" : "" ?> >Todos</option>
                    <option value="1" <?php echo $estado == "1" ? "selected" : "" ?> >Pendiente</option>
                    <option value="2" <?php echo $estado == "2" ? "selected" : "" ?> >Proceso</option>
                    <?php if ($perfil == 2): ?>
                      <option value="3" <?php echo $estado == "3" ? "selected" : "" ?> >Completado</option>
                      <option value="4" <?php echo $estado == "4" ? "selected" : "" ?> >Pausado</option>
                      <option value="5" <?php echo $estado == "5" ? "selected" : "" ?> >Cancelado</option>
                      <option value="Cerrados" <?php echo $estado == "Cerrados" ? "selected" : "" ?> >Cerrados</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Filtro por Tipo -->
            <div class="col-md-3">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" class="form-control" value="<?php echo $tipo ?>">
                    <option value="">Todos</option>
                    <option value="1" <?php echo $tipo == "1" ? "selected" : "" ?> >Muestra</option>
                    <option value="2" <?php echo $tipo == "2" ? "selected" : "" ?> >Pedido</option>
                </select>
            </div>

            <!-- Botón de aplicar filtros -->
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
            </div>
        </div>
    </form>
    <?php if ($perfil == 2): ?>
      <div class="row">
        <!-- Botón de aplicar filtros -->
        <div class="col-md-3 align-self-end">
            <button class="btn btn-warning" onclick="openCancelModal()">Cerrar solicitudes</button>
        </div>
      </div>
    <?php endif; ?>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>No Pedido</th>
                <th>Prioridad</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Vendedor</th>
                <th>Surtidor(es)</th>
                <?php if ($perfil == 2): ?>
                    <th>Asignar</th>
                <?php endif; ?>
                <th>Surtir</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($surtimientos)): ?>
            <tr>
                <td colspan="<?php echo $perfil == 2 ? 6 : 5; ?>" class="text-center">No hay solicitudes de surtimiento</td>
            </tr>
        <?php else: ?>
            <?php foreach ($surtimientos as $surtimiento): ?>
                <tr class="<?php echo $surtimiento['es_complemento'] ? 'complemento-true' : ''; ?>">
                    <td><b><?php echo $surtimiento['no_pedido']; ?></b></td>
                    <td>
                      <?php
                      $prioridad = $surtimiento['prioridad'];
                      $prioridad_id = $surtimiento['prioridad_id'];
                      $prioridadClass = '';
                      if ($prioridad_id == '1') {
                          $prioridadClass = 'badge badge-danger'; // Red
                      } elseif ($prioridad_id == '2') {
                          $prioridadClass = 'badge badge-warning'; // Yellow
                      } elseif ($prioridad_id == '3') {
                          $prioridadClass = 'badge badge-primary'; // Blue
                      }
                      ?>
                      <span class="<?php echo $prioridadClass; ?>"><?php echo $prioridad; ?></span>
                    </td>
                    <td><?php echo $surtimiento['tipo']; ?></td>
                    <td>
                      <?php
                      $estado_id = $surtimiento['estado_id'];
                      $estado = $surtimiento['estado'];
                      $estadoClass = '';
                      if ($estado_id == '1') {
                          $estadoClass = 'badge badge-warning'; 
                      } elseif ($estado_id == '2') {
                          $estadoClass = 'badge badge-primary'; 
                      } elseif ($estado_id == '3') {
                          $estadoClass = 'badge badge-success'; 
                      } elseif ($estado_id == '4') {
                          $estadoClass = 'badge badge-warning'; 
                      } elseif ($estado_id == '5') {
                          $estadoClass = 'badge badge-danger'; 
                      }
                      
                      ?>
                      <span class="<?php echo $estadoClass; ?>"><?php echo $estado; ?></span>
                    </td>
                    <td><?php echo $surtimiento['nombre_vendedor']; ?></td>
                    <td><?php echo $surtimiento['nombre_surtidores']; ?></td>
                    <?php if ($perfil == 2): ?>
                        <td>
                            <a href="asignar.php?id=<?php echo $surtimiento['id']; ?>" class="btn btn-primary">Asignar</a>
                        </td>
                    <?php endif; ?>
                    <td>
                        <!-- <a href="surtir.php?id=<?php echo $surtimiento['id']; ?>" class="btn btn-success">Surtir</a> -->
                        <a href="#" data-id="<?php echo $surtimiento['id']; ?>" class="btn btn-success surtir-row <?php echo ($surtimiento['estado_id'] == '5' || $surtimiento['estado_id'] == '3') ? 'disabled':''; ?>">Surtir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    </table>
</div>
<!-- Modal para Cierre de Solicitudes Pendientes -->
<div class="modal fade" id="cierreSolicitudesModal" tabindex="-1" role="dialog" aria-labelledby="cierreSolicitudesModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cierreSolicitudesModalLabel">Cierre de Solicitudes Pendientes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="filtroCierreForm">
          <div class="form-group">
            <label for="fechaInicio">Fecha de inicio</label>
            <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" required>
          </div>
          <div class="form-group">
            <label for="fechaFin">Fecha de fin</label>
            <input type="date" class="form-control" id="fechaFin" name="fechaFin" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="cerrarSolicitudes()">Cerrar solicitudes</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $('.surtir-row').click(function (event) {
      event.preventDefault();
      var id = $(this).data('id');
      $.ajax({
          url: '../classes/surtimiento.php',
          type: 'POST',
          data: {
              action: 'siguienteSurtimiento',
              id: id,
              usuario: '<?php echo $user_id; ?>',
              sucursal: '<?php echo $sucursal_id; ?>'
          },
          success: function(response) {
              $('.alert').alert();
              let reasigna = (id != response) ? 1 : 0;
              if(reasigna){
                alert("La solicitud de surtimiento que intentas tomar ya está en proceso, te asignamos otra que puedes tomar");
              }
              window.location.href = "surtir.php?id="+response;
          },
          error: function(xhr, status, error) {
              alert('Hubo un error al guardar la asignación: ' + error);
          }
      });
      
      
    });
    function cerrarSolicitudes() {
      // Obtener los valores del formulario de filtro
      const fechaInicio = document.getElementById('fechaInicio').value;
      const fechaFin = document.getElementById('fechaFin').value;

      // Obtener la fecha actual
      const hoy = new Date().toISOString().split('T')[0];  // Formato YYYY-MM-DD

      // Validar que las fechas estén completas
      if (!fechaInicio || !fechaFin) {
          alert('Por favor, seleccione un rango de fechas completo.');
          return;
      }

      // Validar que la fecha fin no sea mayor al día actual
      if (fechaFin >= hoy) {
          alert('La fecha de fin debe ser anterior a la fecha actual.');
          return;
      }
      
      // Validar que la fecha inicio sea menor a la fecha fin
      if (fechaInicio > fechaFin) {
          alert('La fecha de Inicio debe ser anterior a la fecha Fin.');
          return;
      }

      // Puedes agregar aquí una llamada AJAX para enviar las fechas al servidor y cerrar las solicitudes
      $.ajax({
          url: '../classes/surtimiento.php',
          type: 'POST',
          data: {
              action: 'cancelarSurtimientos',
              fechaInicio: fechaInicio,
              fechaFin: fechaFin,
              userId: '<?php echo $user_id; ?>',
              sucursal: '<?php echo $sucursal_id; ?>'
          },
          success: function(response) {
            if (response == 'OK') {
              alert("Se han cancelado las solicitudes de surtimiento de forma correcta");
              window.location.reload();
            }else{
              alert("Se ha detectado un problema "+ response);
            }
              
          },
          error: function(xhr, status, error) {
              alert('Hubo un problema al guardar los cambios: ' + error);
          }
      });
  }
  function openCancelModal() {
      $('#cierreSolicitudesModal').modal('show');
  }
</script>

</body>
</html>
