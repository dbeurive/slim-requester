- [Introduction](#a0)
- [License](#a1)
- [Installation](#a2)
- [Synopsis](#a3)
- [API](#a4)
- [Examples](#a5)


# <a name="a0"></a>Introduction

This package contains a "requester" the [Slim framework](https://www.slimframework.com/).

The requester allows you to perform requests over your Slim application without the need for a WEB server.
This is particularly useful when you want to automate unit tests.
Indeed, while you are unit-testing your application's logic, you don't want to test the WEB server's configuration.

> Please note that this package is a work in progress, since new features will be added.

# <a name="a1"></a>License

This code is published under the following license:

[Creative Commons Attribution 4.0 International Public License](https://creativecommons.org/licenses/by/4.0/legalcode)

See the file [LICENSE.TXT](LICENSE.TXT)

# <a name="a2"></a>Installation

From the command line:

    composer require dbeurive/slim-requester

Or, from within the file `composer.json`:

    "require": {
        "dbeurive/slim-requester": "*"
    }

# <a name="a3"></a>Synopsis

Create a the Slim application:

```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use dbeurive\Slim\Requester;

// Create your Slim application

$configuration = array(/* your configuration */);
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

// Create the requester

$requester = new Requester($application);

// And then you can perform requests:

$text = $requester->get('/get/toto');
$parameters = ['firstname' => 'Mickey', 'lastname' => 'Mouse'];
$text = $requester->post('/post', $parameters);
$response = $requester->getResponse();
```

# <a name="a4"></a>API

    \dbeurive\Slim\Requester::__construct(App $inApplication)
    \dbeurive\Slim\Requester::get($inRequestUri, $inQueryString='')
    \dbeurive\Slim\Requester::post($inRequestUri, $inPostParameters=[])
    \dbeurive\Slim\Requester::jsonRpc($inRequestUri, $inParameters=[])
    \dbeurive\Slim\Requester::setServerCgiEnvironmentVariables(array $inServerCgiEnvironmentVariables, $inMerge=false)
    \dbeurive\Slim\Requester::setHttpHeaders(array $inHttpHeaders, $inMerge=false)
    \dbeurive\Slim\Requester::getResponse()
    \dbeurive\Slim\Requester::getRequest()

Please see the file [Requester.php](src/Requester.php) for a detailed description of the API.

# <a name="a5"></a>Examples

Please see the unit tests for examples.

The file below creates the application:

* [index.php](tests/www/index.php): this file is the application's entry point.

The three files below implement unit tests:

* [AppInit.php](tests/actions/AppInit.php): this file creates the application and initializes the requester.
* [GetTest.php](tests/actions/GetTest.php)
* [JsonRpcTest.php](tests/actions/JsonRpcTest.php)
* [PostTest.php](tests/actions/PostTest.php)