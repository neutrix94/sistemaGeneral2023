<?php
    error_reporting(E_ALL);
    if( !include( '../../vendor/autoload.php' ) ){
        die("Eror al incluir ../../vendor/autoload.php");
    }/*else{
        echo "ok";
    }*/

    putenv('GOOGLE_APPLICATION_CREDENTIALS=/storage/casa-de-las-luces-f106cdae4177.json');
    //putenv('GOOGLE_APPLICATION_CREDENTIALS=../../api_credenciales.json');

    use Google\Service\CloudRun;
    use Google\Client as Google_Client;
    use Google\Service\CloudRun\UpdateServiceRequest;

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->setScopes([Google_Service_CloudRun::CLOUD_PLATFORM]);

    $service = new Google_Service_CloudRun($client);

    $serviceName = 'sistemageneral2023';
    $projectID = 'casa-de-las-luces';
    $regionName = 'us-central1';

    $serviceDetails = $service->projects_locations_services->get("projects/$projectID/locations/$regionName/services/$serviceName");
        
    $currentEnvVars = $serviceDetails->getTemplate()->getContainers()[0]->getEnv();
    
    foreach ($currentEnvVars as $envVar) {
        if ($envVar->getName() === 'DB_HOST') {
            $envVar->setValue( $_POST['host_local'] );
        }
        if ($envVar->getName() === 'DB_USER') {
            $envVar->setValue( $_POST['usuario_local'] );
        }
        if ($envVar->getName() === 'DB_NAME') {
            $envVar->setValue( $_POST['pass_local'] );
        }
        if ($envVar->getName() === 'DB_PASS') {
            $envVar->setValue( $_POST['nombre_local'] );
        }
    }

    $updateMask = 'spec.template.spec.containers.env';

    $updatedService = $service->projects_locations_services->patch("projects/$projectID/locations/$regionName/services/$serviceName", $serviceDetails);

    echo 'ok';
?>