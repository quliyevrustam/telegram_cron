<?php

namespace Controller;

use Utilities\Auth;
use Psr\Container\ContainerInterface;

class MainController
{
    protected $db;
    private $auth;
    protected $http;
    protected $template_engine;
    protected $session;
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container        = $container;
        $this->db               = $container->get('db');
        $this->http             = $container->get('http');
        $this->session          = $container->get('session');
        $this->template_engine  = $container->get('tmp');
    }

    protected function model($className)
    {
        return new $className($this->container);
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