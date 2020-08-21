<?php

namespace Model;

use Utilities\Database;
use Symfony\Component\HttpFoundation\Session\Session;
use Psr\Container\ContainerInterface;

class MainModel
{
    protected $db;
    protected $session;
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container        = $container;
        $this->db               = $container->get('db');
        $this->session          = $container->get('session');
    }

    protected function model($className)
    {
        return new $className($this->container);
    }
}