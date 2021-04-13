<?php
require_once dirname(__DIR__ ). '/vendor/autoload.php';

try {
    $container = null;

    // Create DI Container and write it to $container
    require_once (dirname(__DIR__ ).'/config/di.config.php');

    $channel = \Utilities\Helper::curlRequest('getChat', 'get', ['chat_id' => '@jurefuck'], false);

    \Utilities\Helper::prePrint($channel);
}
catch (Throwable $exception)
{
    print_r($exception->getMessage());
}