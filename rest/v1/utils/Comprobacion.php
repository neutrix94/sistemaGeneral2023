<?php
	class Comprobacion
	{
		private $link;
		function __construct( $connection )
		{
			$this->link = $connection;
		}
	//consulta si la respuesta existe en local
		public function comprobarSysSincronizacionPeticion( $unique_folio ){
			$row = array();
			$sql = "SELECT 
						id_peticion,
						hora_respuesta,
						contenido_respuesta
					FROM sys_sincronizacion_peticion
					WHERE folio_unico = '{$unique_folio}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si el registro de peticion ya existen en linea : {$this->link->error}" );
			if(  $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				$row['response'] = 'existe';
			}else{
				$row['response'] = 'no';
			}
			return json_encode( $row );
		}

		public function verificaRegistroExistente( $json ){
			$arreglo = json_decode( $json, true );
		//consulta si el registro existe

		}

		public function verificaInformacionRegistro( $json ){
			$arreglo = json_decode( $json, true );
		//consulta si el registro existe

		}

		public function verificaExistenciaRegistro( $json ){
			$arreglo = json_decode( $json, true );
		//consulta si el registro existe

		}
		
	}
?>