<?php
    
    $msjLog = isset($_GET["mssgLog"]) ? $_GET["mssgLog"] : "";
    error_log("EL MENSAJE: ".$msjLog);

    $logDirectory = __DIR__ . '/logs/';

    error_log("EL DIRECTORIO: ".$logDirectory);
    
    // Crear el directorio si no existe
    if (!is_dir($logDirectory)) {
        mkdir($logDirectory, 0755, true);
    }

    $logFileName = 'log_' . date('Y-m-d') . '.log';
    $logFilePath = $logDirectory . $logFileName;

    $mssgParts = explode("\n", $msjLog);

    for ($i=0 ; $i < count($mssgParts) ; $i++ ) { 
        $logMessage = date('[Y-m-d H:i:s]') . ' ' . $mssgParts[$i] . PHP_EOL;
        file_put_contents($logFilePath, $logMessage, FILE_APPEND);
    }

    // Prepara el mensaje con la fecha y hora actual
    //$logMessage = date('[Y-m-d H:i:s]') . ' ' . $msjLog . PHP_EOL;

    // Escribe el mensaje en el archivo

    echo "Se ha escrito en el log, ruta: ".$logDirectory;

?>