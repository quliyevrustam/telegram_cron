<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';

use Controller\Channel\TopController;
use Controller\Cycle\AzeriVocabularyController;
use Controller\Index\IndexController;
use FastRoute\RouteCollector;
use Utilities\Auth;
use Utilities\Helper;

try {

    // Create DI Container and write it to $container
    require_once (dirname(__DIR__ ).'/config/di.config.php');

    $auth = new Auth($container);
    $auth->checkLogin();

    // Routing
    $dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r)
    {
        $r->addRoute('GET', '/login', [IndexController::class, 'viewLoginPage']);
        $r->addRoute('GET', '/logout', [IndexController::class, 'logoutUser']);
        $r->addRoute('GET', '/random/post', [AzeriVocabularyController::class, 'getRandomPost']);
        $r->addRoute('GET', '/', [IndexController::class, 'index']);
        $r->addRoute('GET', '/name/{name}/{id}', [IndexController::class, 'showName']);
        $r->addRoute('POST', '/', [IndexController::class, 'postIndex']);
        $r->addRoute('GET', '/channel/top', [TopController::class, 'showTop']);
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
            $controller = isset($route[1][0]) ? $route[1][0] : IndexController::class;
            $method = isset($route[1][1]) ? $route[1][1] : 'index';
            $arguments = $route[2];

            $page = (new $controller($container))->$method(...array_values($arguments));
            echo $page;

            break;
    }
}
catch (Throwable $exception)
{
    Helper::prePrint($exception->getMessage());
    Helper::prePrint($exception->getTrace());
}