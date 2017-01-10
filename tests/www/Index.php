<?php

/**
 * This file implements a Slim's application runner.
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Index
 *
 * This class implements a Slim's application runner.
 * The method "run" performs the following actions:
 * - It defines the routes to test.
 * - It runs the application.
 * - It returns the instance of the Slim application.
 */
class Index
{
    /**
     * Run the application.
     * @param \Slim\App $inApplication An instance of \Slim\App.
     * @return \Slim\App
     */
    static public function run(\Slim\App $inApplication) {

        $inApplication->get('/get/{name}', function (Request $request, Response $response) {
            $name = $request->getAttribute('name');
            $response->getBody()->write("Hello, $name");
            return $response;
        });

        $inApplication->post('/post', function (Request $request, Response $response) {
            $data = $request->getParsedBody();
            $firstName = filter_var($data['firstname'], FILTER_SANITIZE_STRING);
            $lastName  = filter_var($data['lastname'], FILTER_SANITIZE_STRING);
            $response->getBody()->write("Hello, $firstName $lastName");
            return $response;
        });

        $inApplication->post('/jsonrpc', function (Request $request, Response $response) {
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

        $inApplication->run();
        return $inApplication;
    }
}