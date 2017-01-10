<?php

use dbeurive\Slim\Requester;
use dbeurive\Slim\PHPUnit\TestCase;

require_once implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'www', 'Index.php'));

class GetTest extends TestCase
{
    /** @var Requester */
    private $__Requester;

    public function setUp() {
        $this->__Requester = new Requester(new \Slim\App(array()), 'Index::run');
    }

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