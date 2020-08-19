<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';

use FastRoute\RouteCollector;

try {
    $dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
        $r->addRoute('GET', '/', [\Controller\IndexController::class, 'index']);
        $r->addRoute('GET', '/name/{name}/{id}', [\Controller\IndexController::class, 'showName']);
        $r->addRoute('POST', '/', [\Controller\IndexController::class, 'postIndex']);
    });

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
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

            (new $controller())->$method(...array_values($parameters));

            break;
    }
}
catch (Throwable $exception)
{
    echo $exception->getMessage();
}