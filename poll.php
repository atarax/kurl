<?php
/**
 * Created by PhpStorm.
 * User: cite
 * Date: 05/02/18
 * Time: 20:15
 */

$stdout = [];

exec('kubectl get svc -o json', $stdout);
$response = json_decode( implode(' ', $stdout) );

$backendsRegistered = [];
$backendsToCreate = [];
$backendsToDelete = [];

$backendsPresent = [];
$serviceNamesPresent = [];
$portsPresent = [];

foreach($response->items as $service) {

    if($service->spec->type !== 'LoadBalancer') {
        continue;
    }

    $serviceName = $service->metadata->name;
    $serviceNamesPresent[] = $serviceName;

    foreach($service->spec->ports as $port) {
        $backend = [];
        $backend['name'] = $serviceName;
        $backend['hostPort'] = $port->port;
        $backend['containerPort'] = $port->nodePort;
        $backendsPresent[] = $backend;
        $portsPresent[] = $port->port;
    }
}

$etcHost = getenv('ETC_HOST');
$etcPort = getenv('ETC_PORT');

$baseUrl = 'http://' . $etcHost . ':' . $etcPort . '/v2/keys';

$curl = curl_init($baseUrl . '/services');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$response = json_decode( curl_exec($curl) );

if( !isset($response->errorCode) ) {
    if (isset($response->node->nodes)) {
        foreach ($response->node->nodes as $backend) {
            $backendDefinition = json_decode($backend->value);

            if (!in_array($backendDefinition->name, $serviceNamesPresent)) {
                $backendsToDelete[] = $backend->key;
                continue;
            }

            $backendsRegistered[] = $backendDefinition->name;
        }
    }
}

foreach($backendsPresent as $presentBackend) {
    if( !in_array($presentBackend, $backendsRegistered) ) {
        $backendsToCreate[] = $presentBackend;
    }
}

foreach($backendsToDelete as $backend) {
    $curl = curl_init($baseUrl . $backend);
    curl_setopt($curl,CURLOPT_CUSTOMREQUEST, "DELETE");
    $response = curl_exec($curl);
}

foreach($backendsToCreate as $backend) {
    $curl = curl_init($baseUrl . '/services/' . $backend['name']);
    $content = json_encode($backend);

    curl_setopt($curl,CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($content))
    );

    $response = curl_exec($curl);
}

