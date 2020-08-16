<?php

// https://symfony.com/doc/current/create_framework/templating.html

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel;
use Symfony\Component\Routing;

try {
    $request = Request::createFromGlobals();
    $routes = include __DIR__.'/src/app.php';

    $context = new Routing\RequestContext();
    $matcher = new Routing\Matcher\UrlMatcher($routes, $context);

    $controllerResolver = new Symfony\Component\HttpKernel\Controller\ControllerResolver();
    $argumentResolver = new Symfony\Component\HttpKernel\Controller\ArgumentResolver();

    $framework = new Simplex\Framework($matcher, $controllerResolver, $argumentResolver);
    $response = $framework->handle($request);

    $response->send();
}
catch (Throwable $exception)
{
    print_r($exception->getMessage());
}