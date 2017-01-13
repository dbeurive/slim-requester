<?php


/**
 * This file implements the WEB service's entry point.
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$configuration = require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$application = new \Slim\App($configuration);

$application->get('/get/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");
    return $response;
});

$application->post('/post', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $firstName = filter_var($data['firstname'], FILTER_SANITIZE_STRING);
    $lastName  = filter_var($data['lastname'], FILTER_SANITIZE_STRING);
    $response->getBody()->write("Hello, $firstName $lastName");
    return $response;
});

$application->post('/jsonrpc', function (Request $request, Response $response) {
    $data = $request->getBody();
    $data = json_decode($data, true);
    if (JSON_ERROR_NONE !== json_last_error()) {
        throw new \Exception("Invalid JSON");
    }

    $firstName = $data['firstname'];
    $lastName  = $data['lastname'];
    $response->getBody()->write("Hello, $firstName $lastName");
    return $response;
});

$application->run();

