<?php

require_once dirname(__DIR__ ). '/config/config.php';
require_once dirname(__DIR__ ). '/vendor/autoload.php';

spl_autoload_register(function($className)
{
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    require_once dirname(__DIR__ ) . DIRECTORY_SEPARATOR . 'scr/'.$className . '.php';
});