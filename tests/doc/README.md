# Apache 2.4 configuration

## Introduction

It is not necessary to configure a WEB server to test this package.
Therefore, you can ignore this entire document.

However, the test files include HTML documents that can be used to test the Slim application through a browser.
These possibilities can help you understand the use of Slim. 

## Configuration

This directory contains a script used to configuration the WEB environment for the tests.
  
> Please note that tests are done using Apache 2.4.

You may need to activate the rewrite module:

    sudo a2enmod rewrite
    sudo service apache2 restart

> Please note that the procedure to restart Apache depends on your system.

Go into _this directory_ (`Slim/tests/doc`) and run the script `virtual-hosts.php`.

    $ php virtual-hosts.php

This will generate the virtual host configuration files for Apache 2.4.

Copy the configuration file into the right location.
  
    sudo cp *.conf /etc/apache2/sites-available/

> Please note that the target directory depends on your system.
> It may not be `/etc/apache2/sites-available`;

Configure your local DNS (/etc/hosts):

    127.0.0.1   localhost slim-requester.localhost www.slim-requester.localhost
    
Activate the new configuration:

    sudo a2ensite slim-requester.localhost
    sudo service apache2 restart

> Please note that the procedure to restart Apache depends on your system.

Test your configuration: 

    $ curl http://www.slim-requester.localhost/get/toto
    
You should get the string "`Hello, toto`".

## Testing the application

| Route         | URL to use for test                            |
|---------------|------------------------------------------------|
| /get/{name}   | http://www.slim-requester.localhost/get/toto   |
| /post         | http://www.slim-requester.localhost/post.html  |
| /jsonrpc      | http://www.slim-requester.localhost/jsonrpc    |
