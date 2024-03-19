<?php
	class scriptVersionerInstaller
	{
		private $link;
		function __construct( $connection )
		{
			$this->link->connection;
		}

		public function create_tables(){
		//
			$sql = "CREATE TABLE `versionador_configuracion` ( 
				`id_versionador_configuracion` INT NOT NULL AUTO_INCREMENT , 
				`id_rama_versionador` INT NOT NULL ,  
				`url_api` VARCHAR( 200 ) NOT NULL,   
				`url_servidor_mysql` VARCHAR( 100 ) NOT NULL,   
				`usuario_mysql` VARCHAR( 50 ) NOT NULL,   
				`nombre_base_datos_mysql` VARCHAR( 100 ) NOT NULL,   
				`contrasena_mysql` VARCHAR( 50 ) NOT NULL,
				`es_servidor` INT(1) NOT NULL DEFAULT '0',  
				`sincronizar` INT(1) NOT NULL DEFAULT '1',  
				PRIMARY KEY  (`id_versionador_configuracion`)
			) ENGINE = InnoDB;";

			$sql = "CREATE TABLE `versionador_ramas` ( 
				`id_rama` INT NOT NULL AUTO_INCREMENT ,  
				`nombre` VARCHAR(100) NOT NULL ,  
				`orden` INT NOT NULL ,  
				`observaciones` 
				VARCHAR(200) NOT NULL ,  
				`url_repositorio_central` VARCHAR(200) NOT NULL ,  
				`habilitado` INT(1) NOT NULL DEFAULT '1' ,  
				`fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,  
				`fecha_actualizacion` TIMESTAMP NULL DEFAULT NULL ,    
				PRIMARY KEY  (`id_rama`)
			) ENGINE = InnoDB;";

			$sql = "INSERT INTO `versionador_ramas` (`id_rama`, `nombre`, `orden`, `observaciones`, `url_repositorio_central`, `habilitado`, `fecha_creacion`, `fecha_actualizacion`) 
			VALUES 
			('1', 'Desarrollo', '1', 'Rama de desarrollo', '', '1', CURRENT_TIMESTAMP, NULL), 
			('2', 'Pruebas', '2', 'Rama de Pruebas', '', '1', CURRENT_TIMESTAMP, NULL),
			('3', 'Produccion', '3', 'Rama de Produccion', '', '1', CURRENT_TIMESTAMP, NULL);";

			$sql = "CREATE TABLE `versionador_status_scripts` ( 
				`id_status_script` INT NOT NULL AUTO_INCREMENT ,  
				`nombre` VARCHAR(50) NOT NULL ,  
				`sincronizar` INT(1) NOT NULL DEFAULT '1',   
				PRIMARY KEY  (`id_status_script`)
			) ENGINE = InnoDB;";
			/*se crea tabla de scripts*/
			$sql = "CREATE TABLE `versionador_scripts` ( 
				`id_script` INT NOT NULL AUTO_INCREMENT ,  
				`codigo` LONGTEXT NOT NULL ,  
				`descripcion` TEXT NOT NULL ,  
				`ejecutado` INT(1) NOT NULL DEFAULT '0' ,  
				`folio_unico` VARCHAR(30) NOT NULL ,  
				`fecha_alta` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,  
				`fecha_ejecucion` TIMESTAMP NULL DEFAULT NULL ,    
				PRIMARY KEY  (`id_script`)
			) ENGINE = InnoDB;";

			$sql = "CREATE TABLE `versionador_ramas_scripts` ( 
				`id_rama_script` INT NOT NULL AUTO_INCREMENT , 
				`id_rama` INT NOT NULL ,  
				`id_script` INT NOT NULL ,  
				`id_status_script` INT NOT NULL DEFAULT '1',  
				`resultado` TEXT NOT NULL ,  
				`fecha_alta` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,  
				`fecha_ejecucion` TIMESTAMP NULL DEFAULT NULL ,  
				`sincronizar` INT(1) NOT NULL DEFAULT '1' ,    
				PRIMARY KEY  (`id_rama_script`)
			) ENGINE = InnoDB;"

			$sql = "CREATE TABLE `versionador_ramas_remotas` ( 
				`id_rama_remota` INT NOT NULL AUTO_INCREMENT , 
				`id_rama` INT NOT NULL ,  
				`nombre` INT NOT NULL ,  
				`url` VARCHAR( 200 ) NOT NULL,
				`resultado` TEXT NOT NULL ,  
				`activa` INT(1) NOT NULL DEFAULT '1' ,    
				`fecha_alta` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
				`sincronizar` INT(1) NOT NULL DEFAULT '1' ,    
				PRIMARY KEY  (`id_rama_remota`)
			) ENGINE = InnoDB;"

			$sql = "CREATE TABLE `versionador_bitacora_errores_scripts` ( 
				`id_rama_script_error` INT NOT NULL AUTO_INCREMENT , 
				`id_rama` INT NOT NULL ,  
				`id_rama_script` INT NOT NULL , 
				`error` TEXT NOT NULL ,  
				`fecha_error` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
				`sincronizar` INT(1) NOT NULL DEFAULT '1' ,    
				PRIMARY KEY  (`id_rama_script_error`)
			) ENGINE = InnoDB;";
		}
	}
?>