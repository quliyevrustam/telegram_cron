<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';

use FastRoute\RouteCollector;
use Utilities\Auth;

try {

    // Create DI Container and write it to $container
    require_once (dirname(__DIR__ ).'/config/di.config.php');

    $auth = new Auth($container);
    $auth->checkLogin();

    $dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r)
    {
        $r->addRoute('GET', '/login', [\Controller\Index\IndexController::class, 'viewLoginPage']);
        $r->addRoute('GET', '/logout', [\Controller\Index\IndexController::class, 'logoutUser']);
        $r->addRoute('GET', '/random/post', [\Controller\Cycle\AzeriVocabularyController::class, 'getRandomPost']);
        $r->addRoute('GET', '/', [\Controller\Index\IndexController::class, 'index']);
        $r->addRoute('GET', '/name/{name}/{id}', [\Controller\Index\IndexController::class, 'showName']);
        $r->addRoute('POST', '/', [\Controller\Index\IndexController::class, 'postIndex']);
    });

    $route = $dispatcher->dispatch($container->get('http')->getMethod(), $container->get('http')->getPathInfo());
    switch ($route[0])
    {
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

            $html = (new $controller($container))->$method(...array_values($parameters));
            echo $html;


            break;
    }
}
catch (Throwable $exception)
{
    echo $exception->getMessage();
}