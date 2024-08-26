<?php
$id = isset($_GET['id']) ? $_GET['id'] : null;
require_once '../classes/surtimiento.php';
include('../../conect.php');
$surtimientoCRUD = new SurtimientoCRUD();
$listaAsignacion = $surtimientoCRUD->listaAsignacion($id,$sucursal_id);
//echo json_encode($listaAsignacion);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Ítem</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
    <a href="javascript: history.go(-1)">⬅️ Lista de pedidos</a><br>
    <h1 class="text-center">Asignación</h1>
    <table class="table table-bordered">
        <tbody>
            <tr>
                <td colspan="2">Productos pendiente de surtir</td>
                <td id="pendientesSurtir"></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2">Productos pendiente de asignar</td>
                <td id="pendientesAsignar"></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2">Surtidor
                    <select class="form-control" id="surtidorSelect"></select>
                </td>
                <td># de partidas
                    <input type="number" class="form-control" id="partidasInput">
                </td>
                <td>
                    <button class="btn btn-primary" onclick="asignarPartidas()">
                        <i class="fa fa-plus"></i> Agregar
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <h3>Lista de Asignaciones</h3>
    <table class="table table-bordered" id="asignacionesTable">
        <thead>
            <tr>
                <th>Surtidor</th>
                <th># Partidas</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <!-- Las filas se agregarán dinámicamente aquí -->
        </tbody>
    </table>
    <br>
    <button id="btnPrioriza" class="btn btn-primary" onclick="priorizarSurtimiento()">⭐️ Priorizar surtimiento</button>
    <button id="btnAsigna" class="btn btn-success" onclick="guardarAsignacion()">✔️ Guardar asignación</button>
    <button id="btnPausa" class="btn btn-warning" onclick="pausarSurtimiento()">⏸️ Pausar surtimiento</button>
    <button id="btnCancela" class="btn btn-danger" onclick="cancelarSurtimiento()">✖️ Cancelar surtimiento</button>
</div>

<script>
    var listaAsignacion = {
        pendienteSurtir: 0,
        pendienteAsignar: 0,
        Surtidores: [],
        items: [],
        cancelado: 0,
        pausado: 0
    };

    document.addEventListener('DOMContentLoaded', function() {

        listaAsignacion.pendienteSurtir = <?php echo $listaAsignacion['pendienteSurtir'] ?>;
        listaAsignacion.pendienteAsignar = <?php echo $listaAsignacion['pendienteAsignar'] ?>;
        listaAsignacion.Surtidores =<?php echo json_encode($listaAsignacion['Surtidores']) ?>; 
        listaAsignacion.items = <?php echo json_encode($listaAsignacion['items']) ?>; 
        listaAsignacion.id = '<?php echo $id ?>'; 
        listaAsignacion.cancelado = <?php echo $listaAsignacion['cancelado'] ?>;
        listaAsignacion.pausado = <?php echo $listaAsignacion['pausado'] ?>;
        
        document.getElementById('pendientesSurtir').textContent = listaAsignacion.pendienteSurtir;
        document.getElementById('pendientesAsignar').textContent = listaAsignacion.pendienteAsignar;
        document.getElementById('partidasInput').value = listaAsignacion.pendienteAsignar;
        if(listaAsignacion.cancelado){
          document.getElementById('btnCancela').style.display = 'none';
        }
        if(listaAsignacion.pausado){
          document.getElementById('btnPausa').style.display = 'none';
        }
        poblarSurtidorSelect();
        actualizarTablaAsignaciones();
    
    });

    function poblarSurtidorSelect() {
        var surtidorSelect = document.getElementById('surtidorSelect');
        listaAsignacion.Surtidores.forEach(surtidor => {
            var option = document.createElement('option');
            option.value = surtidor.id;
            option.textContent = surtidor.nombre;
            surtidorSelect.appendChild(option);
        });
      
      
    }

    function asignarPartidas() {
        var id_surtidor = document.getElementById('surtidorSelect').value;
        var partidas = parseInt(document.getElementById('partidasInput').value);
        
        if(partidas == 0){
            alert('Debe indicar el número de partidad por asignar.');
            return;
        }
        if(partidas < 0){
            alert('No puede indicar partidas negativas');
            return;
        }
        
        if (partidas > listaAsignacion.pendienteAsignar) {
            alert('El número de partidas no puede ser mayor al pendiente de asignar.');
            return;
        }

        if (id_surtidor && partidas) {
            listaAsignacion.items.push({ id_surtidor: id_surtidor, nombre_surtidor: '', partidas: partidas });
            listaAsignacion.pendienteAsignar -= partidas;
            document.getElementById('pendientesAsignar').textContent = listaAsignacion.pendienteAsignar;
            actualizarTablaAsignaciones();
        }
    }

    function actualizarTablaAsignaciones() {
        var tableBody = document.getElementById('asignacionesTable').getElementsByTagName('tbody')[0];
        tableBody.innerHTML = ''; // Limpiar la tabla

        listaAsignacion.items.forEach((item, index) => {
            var row = tableBody.insertRow();

            var cellSurtidor = row.insertCell(0);
            var cellPartidas = row.insertCell(1);
            var cellEliminar = row.insertCell(2);

            if (!item.nombre_surtidor) {
              surtidorNombre = listaAsignacion.Surtidores.find(s => s.id == item.id_surtidor).nombre;
            }else{
              surtidorNombre = item.nombre_surtidor;
            }
            cellSurtidor.innerHTML = surtidorNombre;
            cellPartidas.innerHTML = item.partidas;
            cellEliminar.innerHTML = '<button class="btn btn-danger" onclick="eliminarAsignacion(' + index + ')">Eliminar</button>';
        });
    }

    function eliminarAsignacion(index) {
        var partidas = Number(listaAsignacion.items[index].partidas);
        listaAsignacion.pendienteAsignar += partidas;
        document.getElementById('pendientesAsignar').textContent = listaAsignacion.pendienteAsignar;
        
        listaAsignacion.items.splice(index, 1);
        actualizarTablaAsignaciones();
    }

    function guardarAsignacion() {
        if (listaAsignacion.pendienteAsignar > 0) {
            alert('Debes asignar todos los productos antes de guardar. Tienes '+ listaAsignacion.pendienteAsignar + ' pendiente(s)');
            return;
        }

        if (confirm('¿Estás seguro de guardar la asignación?')) {
            $.ajax({
                url: '../classes/surtimiento.php',
                type: 'POST',
                data: {
                    action: 'actualizarAsignacion',
                    listaAsignacion: listaAsignacion
                },
                success: function(response) {
                    window.location.href = 'lista.php';
                },
                error: function(xhr, status, error) {
                    alert('Hubo un error al guardar la asignación: ' + error);
                }
            });
        }
    }

    function pausarSurtimiento() {
        // Lógica para pausar el surtimiento en la base de datos
        if (confirm('¿Estás seguro de pausar el surtimiento?')) {
              $.ajax({
                  url: '../classes/surtimiento.php',
                  type: 'POST',
                  data: {
                      action: 'pausarSurtimiento',
                      id: '<?php echo $id; ?>'
                  },
                  success: function(response) {
                      window.location.href = 'lista.php';
                  },
                  error: function(xhr, status, error) {
                      alert('Hubo un error al cancelar la asignación: ' + error);
                  }
              });
        }
    }

    function cancelarSurtimiento() {
        // Lógica para cancelar el surtimiento en la base de datos
        if (confirm('¿Estás seguro de cancelar el surtimiento?')) {
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
        }
    }
    
    function priorizarSurtimiento() {
        // Lógica para pausar el surtimiento en la base de datos
        if (confirm('¿Estás seguro de subir el nivel de prioridad?')) {
              $.ajax({
                  url: '../classes/surtimiento.php',
                  type: 'POST',
                  data: {
                      action: 'priorizarSurtimiento',
                      id: '<?php echo $id; ?>',
                      prioridad: '1'
                  },
                  success: function(response) {
                      window.location.href = 'lista.php';
                  },
                  error: function(xhr, status, error) {
                      alert('Hubo un error al priorizar el surtimiento: ' + error);
                  }
              });
        }
    }
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
