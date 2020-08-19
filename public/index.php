<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';

use FastRoute\RouteCollector;

try {

    $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__ ).'/scr/View');
    $twig = new \Twig\Environment($loader, [
        'cache' => dirname(__DIR__ ).'/cache',
    ]);

    $dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
        $r->addRoute('GET', '/', [\Controller\Index\IndexController::class, 'index']);
    });

    $route = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

    switch ($route[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            echo '404 Not Found';
            break;

        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            echo '405 Method Not Allowed';
            break;

        case FastRoute\Dispatcher::FOUND:
            $controller = isset($route[1][0]) ? $route[1][0] : \Controller\IndexController::class;
            $method = isset($route[1][1]) ? $route[1][1] : 'index';
            $parameters = $route[2];

            $html = (new $controller($twig))->$method($parameters);
            echo $html;

            break;
    }
}
catch (Throwable $exception)
{
    echo $exception->getMessage();
}