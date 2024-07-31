<?php
require_once '../classes/surtimiento.php';
//include( '../../conect.php' );

// Obtener parámetros de la URL
$idUsuario = isset($_GET['idUsuario']) ? $_GET['idUsuario'] : null;
$perfil = isset($_GET['perfil']) ? $_GET['perfil'] : null;

$surtimientoCRUD = new SurtimientoCRUD();
$surtimientos = $surtimientoCRUD->listaSurtir($perfil,$idUsuario);
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
<div class="container mt-5">
    <h1 class="text-center">Lista de Pedidos</h1>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>No Pedido</th>
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
            <?php foreach ($surtimientos as $surtimiento): ?>
                <tr class="<?php echo $surtimiento['es_complemento'] ? 'complemento-true' : ''; ?>">
                    <td><?php echo $surtimiento['no_pedido']; ?></td>
                    <td><?php echo $surtimiento['tipo']; ?></td>
                    <td><?php echo $surtimiento['estado']; ?></td>
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
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
