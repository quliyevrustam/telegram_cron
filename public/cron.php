<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';
require_once (dirname(__DIR__ ).'/config/di.config.php');

if($argc != 3) exit();
if($argv[0] != CRON_PATH) exit();

$cronClassName = "\\Cron\\".str_replace(' ', '', ucwords(
                                               str_replace('_', ' ',
                                                           preg_replace('/[^a-z_]/', '', $argv[1])
                                               )
                                           )
    );

$cronClassMethodName = 'action'.str_replace(' ', '', ucwords(
                                                   str_replace('_', ' ',
                                                               preg_replace('/[^a-z_]/', '', $argv[2])
                                                   )
                                               )
    );

use Model\Cron\Cron;

try {
    $beginTime = microtime(true);

    $cronData = [
        'cron_class_name'        => $cronClassName,
        'cron_class_method_name' => $cronClassMethodName,
    ];
    $cron = (new Cron());

    $cron->beginWork($cronData);

    (new $cronClassName())->$cronClassMethodName();

    $cron->endWork();

    echo "\n";
    echo 'Cron Duration: '.(microtime(true) - $beginTime).' sec';
    echo "\n";
}
catch (Throwable $exception)
{
    \Utilities\Helper::logError($exception->getMessage());
    print_r($exception->getTraceAsString());
    print_r($exception->getMessage());
    echo "\n";
}