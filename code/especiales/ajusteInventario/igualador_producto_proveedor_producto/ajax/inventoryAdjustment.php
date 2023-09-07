<?php

	class inventoryAdjustment
	{
		private $link;
		function __construct( $connection )
		{
			$this->link = $connection;
		}

		public function getStores( $store_id, $onchange_function = null, $read_only = null ) {
			$sql = "SELECT 
						id_sucursal AS store_id,
						nombre AS store_name
					FROM sys_sucursales
					WHERE IF( $store_id = -1, id_sucursal > 0, id_sucursal = {$store_id} )";
					//die( $sql );
			$stm = $this->link->query( $sql ) or die ( "Error al consultar los datos de las sucursales : {$this->link->error}" );
			$resp = "<select id=\"config_store_id\" class=\"form-control\"" . ( $onchange_function != null ? " onchange=\"{$onchange_function};\"" : "" ) .
			( $read_only != null ? " disabled" : "" ) . ">";
				$resp .= ( $store_id == -1 ? "<option value=\"0\">-- SELECCIONAR --</option>" : "" );
			while( $row = $stm->fetch_assoc() ){
				$resp .= "<option value=\"{$row['store_id']}\">{$row['store_name']}";
			}
			$resp .= "</select>";
			return $resp;
		}

		public function getStoreWharehouses( $store_id = -100, $warehouse_id = null, $read_only = null ){
			$sql = "SELECT
						id_almacen AS warehouse_id,
						nombre AS warehouse_name
					FROM ec_almacen WHERE id_sucursal = {$store_id}";
					//die( $sql );
			$stm = $this->link->query( $sql ) or die ( "Error al consultar los datos de las sucursales : {$this->link->error}" );
			$resp = "<select id=\"config_warehouse_id\" class=\"form-control\"" . ( $onchange_function != null ? " onchange=\"{$onchange_function};\"" : "" ) .
			( $read_only != null ? " disabled" : "" ) . ">";
				$resp .= "<option value=\"0\">-- SELECCIONAR --</option>";
			while( $row = $stm->fetch_assoc() ){
				$resp .= "<option value=\"{$row['warehouse_id']}\"" . ( $warehouse_id != null && $warehouse_id == $row['warehouse_id'] ? " selected" : "" ) . ">{$row['warehouse_name']}";
			}
			$resp .= "</select>";
			return $resp;


		}
	}
?>