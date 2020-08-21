<?php

namespace Controller;

use Core\Core;
use Utilities\Auth;
use Psr\Container\ContainerInterface;

class MainController extends Core
{
    private $auth;

    protected $http;
    protected $template_engine;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->http             = $container->get('http');
        $this->template_engine  = $container->get('tmp');
    }

    protected function auth()
    {
        if(!$this->auth instanceof Auth)
        {
            $this->auth = new Auth($this->container);
        }

        return $this->auth;
    }
}