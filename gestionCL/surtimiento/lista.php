<?php
require_once '../classes/surtimiento.php';
include('../../conect.php');

// Obtiene datos de usuario logueado & lista de pedidos
$surtimientoCRUD = new SurtimientoCRUD();
$idUsuario = isset($user_id) ? $user_id : null;
$usuario =  $surtimientoCRUD->getUserProfile($idUsuario);
$perfil = (isset($usuario[0]) && ($usuario[0]['tipo_perfil'] == '4' || $usuario[0]['tipo_perfil'] == '8') &&  $usuario[0]['id_encargado'] == $idUsuario ) ? '2': '1';
$surtimientos = $surtimientoCRUD->listaSurtir($perfil,$idUsuario,$sucursal_id);

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
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>No Pedido</th>
                <th>Prioridad</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Vendedor</th>
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
                    <?php if ($perfil == 2): ?>
                        <td>
                            <a href="asignar.php?id=<?php echo $surtimiento['id']; ?>" class="btn btn-primary">Asignar</a>
                        </td>
                    <?php endif; ?>
                    <td>
                        <a href="surtir.php?id=<?php echo $surtimiento['id']; ?>" class="btn btn-success">Surtir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
