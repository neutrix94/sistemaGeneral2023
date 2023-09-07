<?php

class CodigoSAT
{
    // object properties
    public $id_categoria;
    public $id_subcategoria;
    public $nombre_categoria;
    public $nombre_subcategoria;
    public $id_sat;
    public $codigo_sat;
    public $descripcion_sat;
    public $descripcion_cls;


    // for pagination
    public function countAllSat()
    {
        $db = new db();
        $db = $db->conectDB();

        $where = '1=1';
        $where = (!empty($this->id_subcategoria)) ? $where." and subcategoria.id_subcategoria='{$this->id_subcategoria}'" : $where;
        $where = (!empty($this->id_categoria)) ? $where." and categoria.id_categoria='{$this->id_categoria}'" : $where;

        $sql = "select categoria.id_categoria
          from ec_categoria categoria
          inner join ec_subcategoria subcategoria on subcategoria.id_categoria = categoria.id_categoria
          left join ec_admin_codigos_sat sat on sat.id_categoria = categoria.id_categoria and sat.id_subcategoria = subcategoria.id_subcategoria
          where {$where}";

        $prep_state = $db->prepare($sql);
        $prep_state->execute();

        $num = $prep_state->rowCount(); //Returns the number of rows affected by the last SQL statement
        return $num;
    }


    function updateCodigoSat()
    {
        //Recupera Id tabla ec_admin_codigos_sat
        $db = new db();
        $db = $db->conectDB();

        $queryStatement= "select id from ec_admin_codigos_sat where id_categoria='".$this->id_categoria."' and id_subcategoria='".$this->id_subcategoria."';";
        $result = $db->query($queryStatement);
        $resultRow = $result->fetch();
        $value = $resultRow['id'];

        if (!empty($value)) {
          //Ejectua update
          $sql = "update ec_admin_codigos_sat set codigo_sat='{$this->codigo_sat}', descripcion_sat='{$this->descripcion_sat}', descripcion_cl='{$this->descripcion_cl}' where id='{$value}';";
        }else {
          //Ejecuta insert
          $sql = "insert into ec_admin_codigos_sat (id_categoria,id_subcategoria,codigo_sat,descripcion_sat,descripcion_cl) values ({$this->id_categoria},{$this->id_subcategoria},'{$this->codigo_sat}','{$this->descripcion_sat}','{$this->descripcion_cl}')";
        }
        // prepare query
        $resultadoCodigoSat = false;
        $prep_state = $db->prepare($sql);
        if ($prep_state->execute()) {
            //return true;
            $resultadoCodigoSat = true;
        }
        //Ejecuta actualización de productos con código sat a sistema de facturación
        try {
          //Recupera productos con base en categoria y subcategoria
          $productosFacturacion = "select id_productos from ec_productos where id_categoria='{$this->id_categoria}' and id_subcategoria='{$this->id_subcategoria}';";
          $stmtFacturacion = $db->query($productosFacturacion);
          error_log($productosFacturacion);
          $productosFacturacionList = [];
          $productoFact = [];
          while ($row = $stmtFacturacion->fetch()) {
            //error_log($row['id_productos']);
            $productoFact['idProducto']=$row['id_productos'];
            $productosFacturacionList[]=$productoFact;
          }
          //error_log('total productos: '. count($productosFacturacionList));
          //error_log(print_r($productosFacturacionList,true));
          if ($resultadoCodigoSat && count($productosFacturacionList)>0){
        			//error_log('CL - LOG Productos:Genera petición para actualizar productos en facturación');
        			//Recupera token
        			$sql = "select token from api_token where id_user=0 and expired_in > now() limit 1;";
              $respuesta = $db->query($sql);
              $resultRow = $respuesta->fetch();
        			$token = $resultRow['token'];
        			//Valida token
        			if ($token) {
        					//Recupera path de servicios
        					$sql = "select a.value from api_config a where a.key='api' and a.name='path' limit 1;";
        					$respuesta = $db->query($sql);
                  $resultRow = $respuesta->fetch();
            			$path = $resultRow['value'];
        					//Valida path
        					if ($path) {
        							//Prepar petición
        							$data = array(
        									'productos' => $productosFacturacionList
        							);
        							$post_data = json_encode($data);
        							error_log('CL - LOG Petición: '. $post_data);
        							// Inicializa curl request
        							$crl = curl_init($path.'/rest/v1/productos/nuevoFact');
        							curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        							curl_setopt($crl, CURLINFO_HEADER_OUT, true);
        							curl_setopt($crl, CURLOPT_POST, true);
        							curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
        							curl_setopt($crl, CURLOPT_HTTPHEADER, array(
        									'Content-Type: application/json',
        									'token: ' . $token)
        							);
        							// Ejecuta petición
        							$result = curl_exec($crl);
        							//error_log('CL - LOG Respuesta: '.$result);
        							// Cierra curl sesión
        							curl_close($crl);
        					}
        			}
        	}

        } catch (\Exception $e) {
          error_log('Error Consulta productos: ' . $e->getMessage());
        }

        //Agrega productos a cola de sincronziación magento
        try {
          //Recupera productos con base en categoria y subcategoria
          $productosMagento = "insert into ec_sync_magento(tipo,id_registro,estatus,detalle)
            select distinct
            	'Producto' tipo,
            	producto.id_productos id_registro,
                '1' estatus,
                'update' detalle
            from ec_productos producto
                inner join ec_producto_tienda_linea tienda on tienda.id_producto=producto.id_productos and tienda.habilitado=1
            where producto.id_categoria='{$this->id_categoria}' and producto.id_subcategoria='{$this->id_subcategoria}' and producto.habilitado=1 ";
          $stmtMagento = $db->prepare($productosMagento);
          error_log('Agrega productos ec_sync_magento: ' . $productosMagento);
          if ($stmtMagento->execute()) {
              return true;
          }else{
              return false;
          }
        } catch (\Exception $e) {
          error_log('Error Consulta productos: ' . $e->getMessage());
          return false;
        }


    }


    function getAllSatCode($from_record_num, $records_per_page)
    {
        $db = new db();
        $db = $db->conectDB();

        $where = '1=1';
        $where = (!empty($this->id_subcategoria)) ? $where." and subcategoria.id_subcategoria='{$this->id_subcategoria}'" : $where;
        $where = (!empty($this->id_categoria)) ? $where." and categoria.id_categoria='{$this->id_categoria}'" : $where;

        $sql = "select categoria.id_categoria,
        	categoria.nombre as nombre_categoria,
        	subcategoria.id_subcategoria,
          subcategoria.nombre as nombre_subcategoria,
          sat.id as id_sat,
          sat.codigo_sat,
          sat.descripcion_sat,
          sat.descripcion_cl
          from ec_categoria categoria
          inner join ec_subcategoria subcategoria on subcategoria.id_categoria = categoria.id_categoria
          left join ec_admin_codigos_sat sat on sat.id_categoria = categoria.id_categoria and sat.id_subcategoria = subcategoria.id_subcategoria
          where {$where}
          order by categoria.nombre asc
          limit {$from_record_num}, {$records_per_page}
          ;";

      $stmt = $db->prepare($sql);
      $stmt->execute();

      return $stmt;
      $db_conn = NULL;
    }

    // for edit user form when filling up
    function getCodigoSat()
    {
        $db = new db();
        $db = $db->conectDB();

        $sql = "select categoria.id_categoria,
          categoria.nombre as nombre_categoria,
          subcategoria.id_subcategoria,
          subcategoria.nombre as nombre_subcategoria,
          sat.id as id_sat,
          sat.codigo_sat,
          sat.descripcion_sat,
          sat.descripcion_cl
          from ec_categoria categoria
          inner join ec_subcategoria subcategoria on subcategoria.id_categoria = categoria.id_categoria
          left join ec_admin_codigos_sat sat on sat.id_categoria = categoria.id_categoria and sat.id_subcategoria = subcategoria.id_subcategoria
          where subcategoria.id_subcategoria='".$this->id."'
          limit 1
          ;";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->id_categoria = $row['id_categoria'];
        $this->id_subcategoria = $row['id_subcategoria'];
        $this->nombre_categoria = $row['nombre_categoria'];
        $this->nombre_subcategoria = $row['nombre_subcategoria'];
        $this->descripcion_sat = $row['descripcion_sat'];
        $this->descripcion_cl = $row['descripcion_cl'];
        $this->codigo_sat = $row['codigo_sat'];
    }

    function getFamilia()
    {
        $db = new db();
        $db = $db->conectDB();

        $sql = "select distinct categoria.id_categoria,
        	categoria.nombre as nombre_categoria
        	from ec_categoria categoria;";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt;
        $db_conn = NULL;
    }

    function getTipo()
    {
        $db = new db();
        $db = $db->conectDB();

        $sql = "select distinct subcategoria.id_subcategoria,
        	subcategoria.nombre as nombre_subcategoria
        	from ec_subcategoria subcategoria;";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt;
        $db_conn = NULL;
    }

}
