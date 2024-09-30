<?php
require_once 'connectionMi.php';
// Manejo de la solicitud POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $item = isset($_POST['item']) ? $_POST['item'] : [];
    $prioridad = isset($_POST['prioridad']) ? $_POST['prioridad'] : '';
    $pwd = isset($_POST['pwd']) ? $_POST['pwd'] : '';
    $sucursal = isset($_POST['sucursal']) ? $_POST['sucursal'] : '';
    $listaAsignacion = isset($_POST['listaAsignacion']) ? $_POST['listaAsignacion'] : '';

    if ($action == 'cancelarSurtimiento') {
        $surtimientoCRUD = new SurtimientoCRUD();
        $surtimientoCRUD->cancelaSurtimiento($id,1);
    }
    if ($action == 'pausarSurtimiento') {
        $surtimientoCRUD = new SurtimientoCRUD();
        $surtimientoCRUD->pausaSurtimiento($id,1);
    }
    if ($action == 'actualizarAsignacion') {
        $surtimientoCRUD = new SurtimientoCRUD();
        $surtimientoCRUD->actualizaAsignacion($listaAsignacion);
    }
    if ($action == 'sinInventario') {
        $surtimientoCRUD = new SurtimientoCRUD();
        $surtimientoCRUD->sinInventario($item);
    }
    if ($action == 'productoSurtido') {
        $surtimientoCRUD = new SurtimientoCRUD();
        $surtimientoCRUD->productoSurtido($item);
    }
    if ($action == 'priorizarSurtimiento') {
        $surtimientoCRUD = new SurtimientoCRUD();
        $surtimientoCRUD->priorizarSurtimiento($id, $prioridad);
    }
    if ($action == 'validaPwd') {
        $surtimientoCRUD = new SurtimientoCRUD();
        $surtimientoCRUD->validaPwd($pwd,$sucursal);
    }
    
}

class SurtimientoCRUD {
    private $conn;

    public function __construct() {
        $db = new ConnectionMi();
        $this->conn = $db->openConnection();
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO ec_surtimiento (id, no_pedido, tipo, estado, id_vendedor, prioridad, es_complemento, vendedor_notificado, surtidor_notificado, fecha_creacion, creado_por, fecha_modificacion, modificado_por, rango_ubicaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssisississ", $data['id'], $data['no_pedido'], $data['tipo'], $data['estado'], $data['id_vendedor'], $data['prioridad'], $data['es_complemento'], $data['vendedor_notificado'], $data['surtidor_notificado'], $data['fecha_creacion'], $data['creado_por'], $data['fecha_modificacion'], $data['modificado_por'], $data['rango_ubicaciones']);
        return $stmt->execute();
    }

    public function listaSurtir($perfil=null,$idUsuario=null,$sucursal=null) {
        $estados = $perfil=='2' ? "'1','2','3','4','5'" : "'1','2'" ;
        $result = $this->conn->query("SELECT 
            s.id,
            s.no_pedido,
            CASE 
                WHEN s.tipo = 1 THEN 'Muestra'
                WHEN s.tipo = 2 THEN 'Pedido'
                ELSE s.tipo
            END AS tipo,
            CASE 
                WHEN s.estado = 1 THEN 'Pendiente'
                WHEN s.estado = 2 THEN 'Proceso'
                WHEN s.estado = 3 THEN 'Completado'
                WHEN s.estado = 4 THEN 'Pausa'
                WHEN s.estado = 5 THEN 'Cancelada'
                ELSE s.estado
            END AS estado,
            s.estado estado_id,
            concat(u.nombre, ' ', u.apellido_paterno) AS nombre_vendedor,
            CASE 
                WHEN s.prioridad = 1 THEN 'Alto'
                WHEN s.prioridad = 2 THEN 'Medio'
                WHEN s.prioridad = 3 THEN 'Normal'
                ELSE s.prioridad
            END AS prioridad,
            s.prioridad prioridad_id,
            s.es_complemento,
            s.vendedor_notificado,
            s.surtidor_notificado,
            s.fecha_creacion,
            s.creado_por,
            s.fecha_modificacion,
            s.modificado_por,
            s.rango_ubicaciones
        FROM ec_surtimiento s
        LEFT JOIN sys_users u ON u.id_usuario = s.id_vendedor
        WHERE u.id_sucursal = '{$sucursal}'
        AND s.estado in ({$estados})
        ORDER BY s.estado, s.prioridad, s.fecha_creacion asc;");
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getUserProfile($idUsuario=null) {
        $result = $this->conn->query("SELECT  u.id_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.puesto, u.id_sucursal, u.tipo_perfil, s.id_encargado
            FROM sys_users u
            LEFT JOIN sys_sucursales s on s.id_sucursal = u.id_sucursal
            WHERE u.id_usuario='{$idUsuario}' limit 1;");
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function update($id, $data) {
        $stmt = $this->conn->prepare("UPDATE ec_surtimiento SET no_pedido = ?, tipo = ?, estado = ?, id_vendedor = ?, prioridad = ?, es_complemento = ?, vendedor_notificado = ?, surtidor_notificado = ?, fecha_creacion = ?, creado_por = ?, fecha_modificacion = ?, modificado_por = ?, rango_ubicaciones = ? WHERE id = ?");
        $stmt->bind_param("issssisississs", $data['no_pedido'], $data['tipo'], $data['estado'], $data['id_vendedor'], $data['prioridad'], $data['es_complemento'], $data['vendedor_notificado'], $data['surtidor_notificado'], $data['fecha_creacion'], $data['creado_por'], $data['fecha_modificacion'], $data['modificado_por'], $data['rango_ubicaciones'], $id);
        return $stmt->execute();
    }

    public function listaAsignacion($id=null,$id_sucursal) {
        if(!$id){
          return '';
        }
        
        // Obtener lista de surtidores
        $surtidoresResult = $this->conn->query("SELECT u.id_usuario, 
                   CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre
            FROM sys_users u
            WHERE id_sucursal = '{$id_sucursal}'
              AND tipo_perfil IN ('3');");

        $surtidores = array();
        if ($surtidoresResult->num_rows > 0) {
            while($row = $surtidoresResult->fetch_assoc()) {
                $surtidores[] = array('id' => $row['id_usuario'], 'nombre' => $row['nombre']);
            }
        }

        // Obtener el valor de pendienteAsignar
        $pendienteAsignarResult = $this->conn->query("SELECT COUNT(*) AS total
            FROM ec_surtimiento_detalle
            WHERE id_surtimiento = '{$id}'
              AND id_asignado = '' or id_asignado is null");
        $pendienteAsignar = 0;
        if ($pendienteAsignarResult->num_rows > 0) {
            $row = $pendienteAsignarResult->fetch_assoc();
            $pendienteAsignar = $row['total'];
        }
        
        // Obtener el valor de pendienteSurtir
        $pendienteSurtirResult = $this->conn->query("SELECT COUNT(*) AS total
            FROM ec_surtimiento_detalle
            WHERE id_surtimiento = '{$id}'
              AND estado = 1");
        $pendienteSurtir = 0;
        if ($pendienteSurtirResult->num_rows > 0) {
            $row = $pendienteSurtirResult->fetch_assoc();
            $pendienteSurtir = $row['total'];
        }
        
        // Obtener lista de items x surtidor
        $itemsResult = $this->conn->query("SELECT count(*) partidas, sd.id_asignado id_surtidor, concat(u.nombre, ' ', u.apellido_paterno) AS nombre_surtidor, s.no_pedido, s.estado
            from ec_surtimiento_detalle sd
            left join sys_users u on u.id_usuario = sd.id_asignado
            left join ec_surtimiento s on s.id = sd.id_surtimiento
            where sd.id_surtimiento = '{$id}'
             and sd.id_asignado != '' and sd.id_asignado is not null
            group by sd.id_asignado
            ;");

        $items = array();
        $cancelado =0;
        $pausado = 0;
        if ($itemsResult->num_rows > 0) {
            while($row = $itemsResult->fetch_assoc()) {
                $items[] = array(
                  'partidas' => $row['partidas'], 
                  'id_surtidor' => $row['id_surtidor'],
                  'nombre_surtidor' => $row['nombre_surtidor'],
                  'id_pedido' => $row['id_pedido'],
                  'asignado' => 1
                );
                $cancelado = ($row['estado'] == '5') ? 1 : $cancelado;
                $pausado = ($row['estado'] == '4') ? 1 : $pausado;
            }
        }

        // Asignar valores a la estructura de datos
        $data = array(
            'pendienteSurtir' => $pendienteSurtir,
            'pendienteAsignar' => $pendienteAsignar,
            'Surtidores' => $surtidores,
            'items' => $items,
            'cancelado' => $cancelado,
            'pausado' => $pausado,
        );

        $this->conn->close();

        return $data;
    }
    
    public function listaSurtidores($sucursal_id) {
        $result = $this->conn->query("SELECT u.id_usuario, 
                   CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre
            FROM sys_users u
            WHERE id_sucursal = '{$sucursal_id}'
              AND tipo_perfil IN ('14', '15', '16');");
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function cancelaSurtimiento($id = null, $idUsuario=null) {
        $idUsuario = empty($idUsuario) ? 1 : $idUsuario;
        $query = "UPDATE ec_surtimiento SET fecha_modificacion = now(), estado = '5' WHERE id = '{$id}';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stmt->close();
        $this->conn->close();
        //return true;
    }
    
    public function pausaSurtimiento($id = null, $idUsuario=null) {
        $idUsuario = empty($idUsuario) ? 1 : $idUsuario;
        $query = "UPDATE ec_surtimiento SET fecha_modificacion = now(), estado = '4' WHERE id = '{$id}';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stmt->close();
        $this->conn->close();
        //return true;
    }
    
    public function priorizarSurtimiento($id = null, $prioridad=null) {
        $query = "UPDATE ec_surtimiento SET fecha_modificacion = now(), prioridad = '{$prioridad}' WHERE id = '{$id}';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stmt->close();
        $this->conn->close();
        //return true;
    }
    
    public function actualizaAsignacion($data=null) {
        //error_log(print_r($data,true));
        //Limpia asignaciones
        $idUsuario = empty($idUsuario) ? 1 : $idUsuario;
        $query = "UPDATE ec_surtimiento_detalle SET id_asignado = null WHERE id_surtimiento = '".$data['id']."';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $query = "UPDATE ec_surtimiento_detalle SET fecha_modificacion = now(), id_asignado = '".$item['id_surtidor']."' WHERE (id_asignado='' or id_asignado is null) and id_surtimiento = '".$data['id']."' limit {$item['partidas']};";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
            }
        } else {
            echo "No hay items para iterar.\n";
        }
        $stmt->close();
        $this->conn->close();
        //return true;
    }
    
    public function listaDetalleSurtimiento($id=null,$sucursal=null) {
        $ubicacionSel = ($sucursal == 1) ? " ifnull(ub.numero_ubicacion_desde, 'ND') numero_ubicacion_desde, ifnull(ub.altura_desde,'ND') altura_desde," : " ifnull(ub.numero_ubicacion_desde, 'ND') numero_ubicacion_desde, ifnull(ub.altura_desde,'ND') altura_desde,";
        $ubicacionJoin = ($sucursal == 1) ? " LEFT JOIN ec_proveedor_producto_ubicacion_almacen ub ON ub.id_producto = sd.id_producto and ub.habilitado = 1  and ub.es_principal = 1 ":" LEFT JOIN ec_sucursal_producto_ubicacion_almacen ub ON ub.id_producto = sd.id_producto AND ub.id_sucursal = '{$sucursal}' and ub.habilitado = 1  and ub.es_principal = 1 ";
        $result = $this->conn->query("SELECT 
                sd.id,
                sd.id_producto,
                sd.id_asignado,
                sd.id_surtimiento,
                p.nombre,
                p.clave,
                p.codigo_barras_4,
                p.orden_lista,
                {$ubicacionSel}
                sd.cantidad_solicitada,
                sd.cantidad_surtida,
                sd.estado,
                sd.sin_inventario,
                s.id_vendedor,
                concat(u.nombre, ' ', u.apellido_paterno) AS nombre_vendedor,
                pp_data.claves_proveedor,
                pp_data.codigos_barras,
                pp_data.max_prioridad_surtimiento,
                pp_data.clave_prioridad_maxima
            FROM ec_surtimiento_detalle sd
            LEFT JOIN ec_productos p ON p.id_productos = sd.id_producto
            {$ubicacionJoin}
            INNER JOIN ec_surtimiento s ON s.id = sd.id_surtimiento
            LEFT JOIN sys_users u ON u.id_usuario = s.id_vendedor
            LEFT JOIN 
                (
                    SELECT distinct
                        pp.id_producto,
                        group_concat(pp.clave_proveedor ORDER BY pp.prioridad_surtimiento DESC) AS claves_proveedor,
                        replace(group_concat(concat_ws(',',pp.codigo_barras_pieza_1, pp.codigo_barras_pieza_2, pp.codigo_barras_pieza_3) SEPARATOR ','),' ','') AS codigos_barras,
                        max(pp.prioridad_surtimiento) AS max_prioridad_surtimiento,
                        (SELECT pp2.clave_proveedor 
                         FROM ec_proveedor_producto pp2 
                         WHERE pp2.id_producto = pp.id_producto 
                         AND pp2.habilitado = 1 
                         ORDER BY pp2.prioridad_surtimiento DESC 
                         LIMIT 1) AS clave_prioridad_maxima
                    FROM 
                        ec_proveedor_producto pp
                    WHERE 
                        pp.habilitado = 1
                    GROUP BY 
                        pp.id_producto
                ) AS pp_data ON pp_data.id_producto = sd.id_producto
            WHERE  
                sd.id_surtimiento = '{$id}'
                -- and sd.id_asignado='104'
                AND sd.estado IN (1,2);");
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function sinInventario($item = null) {
        $query = "UPDATE ec_surtimiento_detalle sd SET sd.fecha_modificacion = now(), sd.estado = '5', sd.sin_inventario = 1 WHERE sd.id = '".$item['id']."';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        // Obtener surtimienti para valira estado
        $pendiente = false;
        $itemsResult = $this->conn->query("SELECT s.id, s.estado estadoS, sd.estado estadoD
            from ec_surtimiento s
            inner join ec_surtimiento_detalle sd on sd.id_surtimiento = s.id
            where s.id= '" . $item['id_surtimiento'] . "'and sd.estado = '1';");

        while($row = $itemsResult->fetch_assoc()) {
            $pendiente = true;
        }
      
        $estado = ($pendiente) ? '2' : '3';
        //Actualiza cabecera 2
        $queryS = "UPDATE ec_surtimiento s SET s.fecha_modificacion = now(), s.estado = '{$estado}' WHERE s.estado != '{$estado}' and s.id = '{$item['id_surtimiento']}' ;";
        $stmt = $this->conn->prepare($queryS);
        $stmt->execute();
        
        $stmt->close();
        $this->conn->close();
    }
    
    public function productoSurtido($item = null){
        //Actualiza detalle
        $query = "UPDATE ec_surtimiento_detalle sd SET sd.fecha_modificacion = now(), sd.estado = '3', sd.cantidad_surtida ='".$item['cantidad_surtida']."' WHERE sd.id = '".$item['id']."';";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        // Obtener surtimienti para valira estado
        $pendiente = false;
        $itemsResult = $this->conn->query("SELECT s.id, s.estado estadoS, sd.estado estadoD
            from ec_surtimiento s
            inner join ec_surtimiento_detalle sd on sd.id_surtimiento = s.id
            where s.id= '" . $item['id_surtimiento'] . "'and sd.estado = '1';");

        while($row = $itemsResult->fetch_assoc()) {
            $pendiente = true;
        }
      
        $estado = ($pendiente) ? '2' : '3';
        //Actualiza cabecera 2
        $queryS = "UPDATE ec_surtimiento s SET s.fecha_modificacion = now(), s.estado = '{$estado}' WHERE s.estado != '{$estado}' and s.id = '{$item['id_surtimiento']}' ;";
        $stmt = $this->conn->prepare($queryS);
        $stmt->execute();
        
        $stmt->close();
        $this->conn->close();
    }
    
    public function validaPwd($pwd = null, $sucursal= null){
        $valid = false;
        $query = "SELECT id_usuario from sys_users
          where tipo_perfil in ('4','8')
          and id_sucursal = '{$sucursal}'
          and contrasena = md5('".$pwd."');";
          
        $itemsResult = $this->conn->query($query);

        while($row = $itemsResult->fetch_assoc()) {
            $valid = true;
        }
        $this->conn->close();
        echo $valid;
    }
    
    
}
?>
