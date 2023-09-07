<?php
//echo 'here';
	class Triggers extends FrontEnd
	{
		private $link;
		private $schema_name;
		
		function __construct( $connection, $schema_name ){
			//echo 'here constructor';
			$this->link = $connection;
			$this->schema_name = $schema_name;
		}

		public function getConnection(){
			return $this->link;
		}

		public function getTablesSchema(){
			$sql = "SELECT 
						TABLES.TABLE_NAME,
						COUNT( TRIGGERS.TRIGGER_NAME ) AS TRIGGERS_COUNTER
					FROM TABLES 
					LEFT JOIN TRIGGERS
					ON TABLES.TABLE_NAME = TRIGGERS.EVENT_OBJECT_TABLE
					AND TRIGGERS.TRIGGER_SCHEMA = TABLES.TABLE_SCHEMA
					WHERE TABLES.TABLE_SCHEMA = '{$this->schema_name}'
					GROUP BY TABLES.TABLE_NAME";
			//echo $sql;
			$stm = $this->link->query( $sql ) or die( "Error al consultar las tablas : {$this->link->error}" );
			return $this->buildTablesCatalogue( $stm );
		}

		public function getTableStructure( $table, $column_name = null ){
			$sql = "SELECT 
						COLUMN_NAME, 
						ORDINAL_POSITION, 
						COLUMN_DEFAULT, 
						IS_NULLABLE,
						DATA_TYPE,
						CHARACTER_MAXIMUM_LENGTH,
						NUMERIC_PRECISION,
						COLUMN_TYPE,
						COLUMN_KEY,
						EXTRA
					FROM COLUMNS
					WHERE TABLE_SCHEMA = '{$this->schema_name}'
					AND TABLE_NAME = '{$table}'";
			$sql .= $column_name == null ? "" : " AND COLUMN_NAME = '{$column_name}'";

			//echo $sql;
			$stm = $this->link->query( $sql ) or die( "Error al consultar la estructura de la tabla : {$this->link->error}" );
			if( $column_name != null ){
				$row = $stm->fetch_assoc();
				return $row;
			}
			return $this->buildTableStructure( $table, $stm );
		}

		public function getTableTriggers( $table ){
			$resp = array();
			$sql = "SELECT 
						TRIGGER_NAME,
						EVENT_MANIPULATION,
						ACTION_STATEMENT,
						EVENT_OBJECT_TABLE,
						ACTION_TIMING
					FROM TRIGGERS
					WHERE TRIGGER_SCHEMA = '{$this->schema_name}'
					AND EVENT_OBJECT_TABLE = '{$table}'
					ORDER BY  CASE `EVENT_MANIPULATION`
						WHEN 'INSERT' THEN 1
						WHEN 'UPDATE' THEN 2
						WHEN 'DELETE' THEN 3
					END";
			//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar la estructura de la tabla : {$sql} {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ){
				$row['ACTION_STATEMENT'] = "DROP TRIGGER IF EXISTS {$row['TRIGGER_NAME']}|
DELIMITER $$
CREATE TRIGGER {$row['TRIGGER_NAME']}
{$row['ACTION_TIMING']} {$row['EVENT_MANIPULATION']} ON {$table}
FOR EACH ROW
{$row['ACTION_STATEMENT']} $$";

				array_push( $resp, $row );
			}
			return json_encode( $resp );
			//return $this->buildTableStructure( $table, $stm );
		}

		public function buildTriggers( $table, $fields, $key_field, $timing = "AFTER", $events ){
			$resp = array();
			foreach ( $events as $key => $value) {
				array_push( $resp, $this->makepreviousTrigger( $table, $fields, $key_field, $timing ) );
			}
				# code...
			return  $this->makepreviousTrigger( $table, $fields, $key_field, $timing );
			return json_encode( $resp );
		}
//formaTrigger
		public function makepreviousTrigger( $table, $fields, $key_field, $timing = "AFTER" ){//, $events, $timing
			$fields = explode( ',', $fields );
			$sinchronization_insert = "\t\tINSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,\n\t\tid_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
\t\tSELECT 
\t\t\tNULL,
\t\t\tstore_id,
\t\t\tid_sucursal,";
			$json = "\t\t\tCONCAT('{',
\t\t\t\t'\"table_name\" : \"{$table}\",',
\t\t\t\t'\"action_type\" : \"insert\",',
\t\t\t\t'\"primary_key\" : \"{$key_field}\",',
\t\t\t\t'\"primary_key_value\" : \"', new.{$key_field}, '\"";
			//$trigger_name = str_replace(search, replace, subject);
			$trigger = "DROP TRIGGER IF EXISTS {$trigger_name}|
DELIMITER $$
CREATE TRIGGER {$trigger_name}
{$timing} INSERT ON {$table}
FOR EACH ROW
BEGIN
\tDECLARE store_id INTEGER;
\tSELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	IF( new.sincronizar = 1 )
	THEN";
			$trigger .= "\n{$sinchronization_insert}";
			$json_fields = "";
			foreach ( $fields as $key => $field ) {
				$field_info = $this->getTableStructure( $table, $field );
				$json_fields .= ",',\n\t\t\t\t";
				$json_fields .= "'\"{$field_info['COLUMN_NAME']}\" : \"',";
				if( $field_info['IS_NULLABLE'] == "YES" ){
					$json_fields .= " IF( new.{$field_info['COLUMN_NAME']} IS NULL, '', new.{$field_info['COLUMN_NAME']} ), '\"";
				}else{
					$json_fields .= " new.{$field_info['COLUMN_NAME']}, '\"";
				}
			}
			$json .= $json_fields . "',\n\t\t\t\t'}'\n\t\t\t),";
			$trigger .= "\n{$json}\n";
			$trigger .= "\t\t\tNOW(),
\t\t\t0,
\t\t\t1
\t\tFROM sys_sucursales 
\t\tWHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
\tEND IF;
END $$";
			return $trigger;
		}

		public function buildTrigger(){

		}
	}

?>