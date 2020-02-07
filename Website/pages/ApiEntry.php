<?php
    require_once '../../private/server/household/endpoints/Transaction.php';
    require_once '../../private/server/common/api/GetRequest.php';

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Allow-Methods: DELETE,GET,OPTION,POST,PUT');
    header('Content-type: application/json');

    // Register Endpoints
    $registeredEndpoints = [
        "transaction" => new TransactionEndpoint(),
    ];
    
    $apiRequest = new GetRequest();

    // Check if the endpoint parameter is set
    if ($apiRequest->GetEndpoint() == null) {
        $apiRequest->EndRequest(HttpStatus::NOT_FOUND, "'endpoint' was not set.");
    }

    // Check if the endpoint exists
    $endpoint = $apiRequest->GetEndpoint();
    if (!array_key_exists($endpoint, $registeredEndpoints)) {
        $apiRequest->EndRequest(HttpStatus::NOT_FOUND, "\'{$endpoint}' is not a registered endpoint.");
    }
    
    $httpMethod = $apiRequest->GetHttpMethod();
    $selectedEndpoint = $registeredEndpoints[$endpoint];
    switch($httpMethod) {
        case 'GET':
            $selectedEndpoint->get();
            break;
        case 'POST':
            $selectedEndpoint->post();
            break;
        case 'PUT':
            $selectedEndpoint->put();
            break;
        case 'DELETE':
            $selectedEndpoint->delete();
            break;
        case 'OPTIONS': {
            $selectedEndpoint->options();
            break;
        }
        default:
            $apiRequest->EndRequest(HttpStatus::NOT_IMPLEMENTED, "HTTP Method '{$httpMethod}' is not supported.");
    }
?>