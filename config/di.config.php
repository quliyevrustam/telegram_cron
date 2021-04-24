<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Utilities\Database;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

final class ZorgeDI
{
    private static $instances;

    private static $container;

    private function __construct()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            [
                'db'      => function () {
                    return new Database();
                },
                'session' => function () {
                    $session = new Session();
                    $session->start();
                    return $session;
                },
                'http'    => function () {
                    $http = Request::createFromGlobals();
                    return $http;
                },
                'tmp'     => function () {
                    $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/src/View');
                    $twig = new \Twig\Environment(
                        $loader, [
                            'cache'            => dirname(__DIR__) . '/cache',
                            'debug'            => true,
                            'auto_reload'      => true,
                            'strict_variables' => true
                        ]
                    );
                    return $twig;
                },
            ]
        );

        self::$container = $containerBuilder->build();
    }

    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize!");
    }

    public static function getContainer(): ContainerInterface
    {
        if (is_null(self::$instances)) {
            self::$instances = new static();
        }

        return self::$container;
    }
}