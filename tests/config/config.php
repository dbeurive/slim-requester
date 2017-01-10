<?php

return call_user_func(function () {

    // For the list of pre-defined settings, please consult the file below:
    // See vendor/slim/slim/Slim/Container.php

    $config = [
        'displayErrorDetails'    => true,
        'addContentLengthHeader' => true
    ];
    return $config;
});