<?php


$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
'db' => function () {
return new Database();
},
'session' => function () {
$session = new Session();
$session->start();
return $session;
},
'http' => function () {
$http = Request::createFromGlobals();
return $http;
},
'tmp' => function () {
$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__ ).'/scr/View');
$twig = new \Twig\Environment($loader, [
'cache' => dirname(__DIR__ ).'/cache',
'debug' => true,
'auto_reload' => true,
'strict_variables' => true
]);
return $twig;
},
]);
$container = $containerBuilder->build();