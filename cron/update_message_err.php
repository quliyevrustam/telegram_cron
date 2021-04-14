<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';

use Telegram\Bot\Api;

try {
    $container = null;

    // Create DI Container and write it to $container
    require_once (dirname(__DIR__ ).'/config/di.config.php');

    (new \Model\Channel\Message($container))->updateErr();
}
catch (Throwable $exception)
{
    print_r($exception->getMessage());
}