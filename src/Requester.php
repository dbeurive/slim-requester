<?php

/**
 * This file implements the "software requester" for Slim V3.
 */

namespace dbeurive\Slim;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\App;

/**
 * Class Requester
 *
 * This class implements the "software requester" for Slim V3.
 *
 * @note If you plan to modify the code of this class, be aware that many Slim methods return "clones" (of the object from which they were called).
 *       That is: the object upon which the method is called is not modified.
 *
 * @package dbeurive\Slim
 */
class Requester
{
    /**
     * @var array HTTP headers (ex: "Content-Length", "Content-Type"...).
     * @see https://developer.mozilla.org/fr/docs/HTTP/Headers
     */
    private $__httpHeaders = array();
    /**
     * @var array Server CGI environment variables (ex: "SERVER_NAME", "QUERY_STRING"...).
     * @see http://php.net/manual/fr/reserved.variables.server.php
     */
    private $__serverCgiEnvironmentVariables = array();
    /**
     * @var App Slim application.
     * @note This variable is used to initialise the container (for the dependency injection).
     */
    private $__application;
    /** @var Request HTTP request. */
    private $__request;
    /** @var Response HTTP response. */
    private $__response;
    /** @var string The text returned by the request. */
    private $__stdout;

    /**
     * SoftRequester constructor.
     *
     * @param App $inApplication The Slim application.
     * @throws \Exception
     */
    public function __construct(App $inApplication)
    {
        $this->__application = $inApplication;
    }

    /**
     * Return the Slim request.
     * @return Request The Slim request.
     */
    public function getRequest() {
        return $this->__request;
    }

    /**
     * Return the Slim response.
     * @return Response The Slim response.
     */
    public function getResponse() {
        return $this->__response;
    }

    /**
     * Set the HTTP headers for the request.
     *
     * @param array $inHttpHeaders The HTTP headers to set.
     *        See {@url https://developer.mozilla.org/fr/docs/HTTP/Headers} for the list of HTTP headers.
     * @param bool $inMerge This parameter determines whether the given list of headers should be merged to the existing one or not.
     *        If the value if false, then the given list of headers will replace the existing one.
     *        Otherwise, the given list of headers will be merged into the existing one.
     * @return $this
     */
    public function setHttpHeaders(array $inHttpHeaders, $inMerge=false) {
        if ($inMerge) {
            $this->__httpHeaders = array_merge($this->__httpHeaders, $inHttpHeaders);
        } else {
            $this->__httpHeaders = $inHttpHeaders;
        }
        return $this;
    }

    /**
     * Set the server CGI environment variables.
     *
     * @param array $inServerCgiEnvironmentVariables
     *        See {@url http://php.net/manual/fr/reserved.variables.server.php} for the list of variables.
     * @param bool $inMerge This parameter determines whether the given list of variables should be merged to the existing one or not.
     *        If the value if false, then the given list of variables will replace the existing one.
     *        Otherwise, the given list of variables will be merged into the existing one.
     * @return $this
     */
    public function setServerCgiEnvironmentVariables(array $inServerCgiEnvironmentVariables, $inMerge=false) {
        if ($inMerge) {
            $this->__serverCgiEnvironmentVariables = array_merge($this->__serverCgiEnvironmentVariables, $inServerCgiEnvironmentVariables);
        } else {
            $this->__serverCgiEnvironmentVariables = $inServerCgiEnvironmentVariables;
        }
        return $this;
    }

    /**
     * Execute a request and return what should have been returned through the standard output.
     * Let's consider a GET HTTP request to the following URL: http://www.google.com/search?req=a&option=2
     * - The request method is "GET" (parameter $inRequestMethod).
     * - The request URI is "/hello/toto" (parameter $inRequestUri).
     * - The query string is "a=1&b=2$c=3" (parameter $inQueryString).
     * - There is no request body.
     *
     * @param string $inRequestMethod HTTP Method to use ('GET', 'POST'...).
     * @param string $inRequestUri The request URI (example: "/hello/toto").
     * @param string $inQueryString The query string (example: "a=1&b=2$c=3").
     * @param string $inRequestBody Request's body to set (for the method POST, typically).
     * @return string The content of the standard output.
     * @throws \Exception
     * @see \Slim\DefaultServicesProvider::register Here you see how the environment is used.
     * @see \Slim\App::run Here, you can print the request's data (from a real request).
     * @see \Slim\Http\Environment
     * @note Please keep in mind that:
     *       - HTTP headers can be configured through the method SoftRequester::setHttpHeaders().
     *       - Server CGI environment variables can be configured through the method SoftRequester::setServerCgiEnvironmentVariables().
     * @see Requester::setHttpHeaders()
     * @see Requester::setServerCgiEnvironmentVariables()
     */
    private function request($inRequestMethod, $inRequestUri, $inQueryString='', $inRequestBody=null) {
        ob_start();

        // Configure the Slim application so that it will use the test environment.
        // See the method \Slim\DefaultServicesProvider::register.
        // $this->__applicationConfiguration['environment'] = $environment;
        // $app = new \Slim\App($this->__application);
        // $container = $app->getContainer();

        $container = $this->__application->getContainer();
        $container['environment'] = Environment::mock(array_merge(array(
            'REQUEST_METHOD' => $inRequestMethod,
            'REQUEST_URI'    => $inRequestUri,
            'QUERY_STRING'   => $inQueryString
        ), $this->__serverCgiEnvironmentVariables));

        // Add middleware, if necessary.

        if (! is_null($inRequestBody)) {
            $this->__application->add(function (Request $request, Response $response, $next) use($inRequestBody) {

                // Set the body.
                $fp = fopen("php://temp", 'r+');
                fwrite($fp, $inRequestBody);
                rewind($fp);
                $body = new \Slim\Http\Body($fp);

                // Please note:
                // \Slim\Http\Message::withBody returns a clone of the request.
                $request = $request->withBody($body);
                $response = $next($request, $response);
                return $response;
            });
        }

        if (count($this->__httpHeaders) > 0) {
            $headers = $this->__httpHeaders;
            $this->__application->add(function (Request $request, Response $response, $next) use($headers) {

                foreach ($headers as $_name => $_value) {
                    // Please note:
                    // \Slim\Http\Message::withHeader returns a clone of the request.
                    $request = $request->withHeader($_name, $_value);
                }

                $response = $next($request, $response);
                return $response;
            });
        }

        // Run the Slim application.
        $this->__application->run();

        // Store the request and the response for later use in the unit tests.
        $this->__request = $this->__application->getContainer()->get('request');
        $this->__response = $this->__application->getContainer()->get('response');

        $this->__stdout = ob_get_clean();
        return $this->__stdout;
    }

    /**
     * Execute a GET request.
     * Let's consider a GET HTTP request to the following URL: http://www.google.com/search?req=a&option=2
     * - The request URI is "/hello/toto" (parameter $inRequestUri).
     * - The query string is "a=1&b=2$c=3" (parameter $inQueryString).
     * - There is no request body.
     *
     * @param string $inRequestUri The request URI (example: "/hello/toto").
     * @param string $inQueryString The query string (example: "a=1&b=2$c=3").
     * @return string The content of the standard output.
     * @note Please keep in mind that:
     *       - HTTP headers can be configured through the method SoftRequester::setHttpHeaders().
     *       - Server CGI environment variables can be configured through the method SoftRequester::setServerCgiEnvironmentVariables().
     * @see Requester::setHttpHeaders()
     * @see Requester::setServerCgiEnvironmentVariables()
     */
    public function get($inRequestUri, $inQueryString='') {
        return $this->request('GET', $inRequestUri, $inQueryString, null);
    }

    /**
     * Execute a POST request.
     *
     * @param string $inRequestUri The request URI (example: "/hello/toto").
     * @param array $inPostParameters List of parameters to pass.
     * @return string The content of the standard output.
     * @see \Slim\Http\Request::getParsedBody for a detailed description of how the request body is parsed.
     *      The parsing relies on the value of the 'Content-Type' header.
     *      It should be "application/x-www-form-urlencoded".
     * @note Please keep in mind that:
     *       - HTTP headers can be configured through the method SoftRequester::setHttpHeaders().
     *       - Server CGI environment variables can be configured through the method SoftRequester::setServerCgiEnvironmentVariables().
     * @see Requester::setHttpHeaders()
     * @see Requester::setServerCgiEnvironmentVariables()

     */
    public function post($inRequestUri, $inPostParameters=[]) {

        // Prepare the content of the request's body.
        $bodyContent = [];
        foreach ($inPostParameters as $_name => $_value) {
            $bodyContent[] = $_name . '=' . urlencode($_value);
        }
        $bodyContent = implode('&', $bodyContent);

        // Prepare the request's headers.
        if (! array_key_exists('Content-Type', $this->__httpHeaders)) {
            $this->__httpHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
        }
        if (! array_key_exists('Content-Length', $this->__httpHeaders)) {
            $this->__httpHeaders['Content-Length'] = strlen($bodyContent);
        }

        return $this->request('POST', $inRequestUri, '', $bodyContent);
    }

    /**
     * Send a JSON RPC request.
     *
     * @param string $inRequestUri The request URI (example: "/hello/toto").
     * @param array $inParameters The structure that will be converted into JSON, and that contains the data to send.
     * @return string string The content of the standard output.
     * @note Please keep in mind that:
     *       - HTTP headers can be configured through the method SoftRequester::setHttpHeaders().
     *       - Server CGI environment variables can be configured through the method SoftRequester::setServerCgiEnvironmentVariables().
     * @see Requester::setHttpHeaders()
     * @see Requester::setServerCgiEnvironmentVariables()
     */
    public function jsonRpc($inRequestUri, $inParameters=[]) {
        $bodyContent = json_encode($inParameters);

        // Prepare the request's headers.
        if (! array_key_exists('Content-Type', $this->__httpHeaders)) {
            $this->__httpHeaders['Content-Type'] = 'application/jsonrequest';
        }
        if (! array_key_exists('Accept', $this->__httpHeaders)) {
            $this->__httpHeaders['Accept'] = 'application/jsonrequest';
        }
        if (! array_key_exists('Content-Length', $this->__httpHeaders)) {
            $this->__httpHeaders['Content-Length'] = strlen($bodyContent);
        }
        return $this->request('POST', $inRequestUri, '', $bodyContent);
    }
}