<?php

use dbeurive\Slim\PHPUnit\TestCase;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'AppInit.php';

class PostTest extends TestCase
{
    use AppInit;

    public function testHello() {
        $parameters = ['firstname' => 'Mickey', 'lastname' => 'Mouse'];

        $text = $this->__Requester->post('/post', $parameters);
        $this->assertEquals('Hello, Mickey Mouse', $text);
        $response = $this->__Requester->getResponse();

        $this->assertResponseBodyEquals('Hello, Mickey Mouse', $response);
        $this->assertResponseStatusCodeEquals(200, $response);
        $this->assertResponseIsOk($response);
        $this->assertResponseIsSuccessful($response);
        $this->assertResponseHasHeader('content-type', $response);
    }
}