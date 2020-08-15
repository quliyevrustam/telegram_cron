<?php

use Symfony\Component\Routing;

$routes = new Routing\RouteCollection();
$routes->add('hello', new Routing\Route('/hello/{name}', ['name' => 'World']));

return $routes;