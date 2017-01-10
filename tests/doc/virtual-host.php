<?php

// This script generates the configuration for the virtual hosts.
// Please note that the doc is designed for Apache 2.4.

function create_configuration($inServerName, $inWwwDirectory) {

    $wwwDirectory = realpath($inWwwDirectory);
    $logDirectory = realpath(implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'logs')));
    $configuration = implode(DIRECTORY_SEPARATOR, array(__DIR__, $inServerName . '.conf'));

    if (false === chmod($wwwDirectory, 0755)) {
        print "Can not set the right permissions (0755) for the directory $wwwDirectory.\n";
        exit(1);
    }

    if (false === chmod($logDirectory, 0777)) {
        print "Can not set the right permissions (0755) for the directory $logDirectory.\n";
        exit(1);
    }

    $conf = <<<EOS
<VirtualHost *:80>
     ServerName $inServerName
     ServerAlias www.$inServerName

     DocumentRoot "$wwwDirectory"
     LogLevel debug
     ErrorLog  "$logDirectory/apache-error.log"
     CustomLog "$logDirectory/apache-access.log" common

     Options Indexes FollowSymLinks

     <Directory />
            Require all granted
            # You may need to activate the rewrite module (sudo a2enmod rewrite)
            RewriteEngine On
            LogLevel alert rewrite:trace3
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^ index.php [QSA,L]
     </Directory>
</VirtualHost>
EOS;

    file_put_contents($configuration, $conf . PHP_EOL);
}

$baseWww = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'www'));

$hosts = array(
    'slim-requester.localhost' => '',
);

foreach ($hosts as $_host => $_dir) {
    create_configuration($_host, $baseWww . DIRECTORY_SEPARATOR .  $_dir);
}

print "Add the following entries to your /etc/hosts:\n";
print '127.0.0.1 localhost ' . implode(' ', array_keys($hosts)) . PHP_EOL;
print "Copy the configuration files into the right location (should be '/etc/apache2/sites-available' under Ubuntu).\n";
print "Then enable the virtual hosts and reload the Apache configuration:\n";
print 'sudo a2dissite ' . implode(' ', array_keys($hosts)) . ' (eventually)' . PHP_EOL;
print 'sudo a2ensite ' . implode(' ', array_keys($hosts)) . PHP_EOL;
print "sudo service apache2 reload" . PHP_EOL;
