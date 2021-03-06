<?php

use dbeurive\Slim\PHPUnit\TestCase;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'AppInit.php';

class GetTest extends TestCase
{
    use AppInit;

    public function testHello() {
        $text = $this->__Requester->get('/get/toto');
        $this->assertEquals('Hello, toto', $text);
        $response = $this->__Requester->getResponse();
        $this->assertResponseBodyEquals('Hello, toto', $response);
        $this->assertResponseStatusCodeEquals(200, $response);
        $this->assertResponseIsOk($response);
        $this->assertResponseIsSuccessful($response);
        $this->assertResponseHasHeader('content-type', $response);
    }
}