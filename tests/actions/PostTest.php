<?php

use dbeurive\Slim\Requester;
use dbeurive\Slim\PHPUnit\TestCase;

require_once implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'www', 'Index.php'));

class PostTest extends TestCase
{
    /** @var Requester */
    private $__Requester;

    public function setUp() {
        $this->__Requester = new Requester(new \Slim\App(array()), 'Index::run');
    }

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