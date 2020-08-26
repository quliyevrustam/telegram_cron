<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';

use FastRoute\RouteCollector;

try {

    // Create DI Container and write it to $container
    require_once (dirname(__DIR__ ).'/config/di.config.php');

    // Routing
    $dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r)
    {
        $r->addRoute('GET', '/', [\Controller\Index\IndexController::class, 'index']);
        $r->addRoute('GET', '/test/get/{name}', [\Controller\Test\TestController::class, 'testGet']);
        $r->addRoute('GET', '/test/post', [\Controller\Test\TestController::class, 'viewTestPost']);
        $r->addRoute('POST', '/test/post', [\Controller\Test\TestController::class, 'testPost']);
    });

    // Get current route by HTTP Request
    $http = $container->get('http');
    $route = $dispatcher->dispatch($http->getMethod(), $http->getPathInfo());
    switch ($route[0])
    {
        case FastRoute\Dispatcher::NOT_FOUND:
            echo '404 Not Found';
            break;

        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            echo '405 Method Not Allowed';
            break;

        case FastRoute\Dispatcher::FOUND:

            // Get Controller, Controller Method and Controller Method Arguments
            $controller = isset($route[1][0]) ? $route[1][0] : \Controller\Index\IndexController::class;
            $method = isset($route[1][1]) ? $route[1][1] : 'index';
            $arguments = $route[2];

            $page = (new $controller($container))->$method(...array_values($arguments));
            echo $page;

            break;
    }
}
catch (Throwable $exception)
{
    echo $exception->getMessage();
}