<?php

namespace Core;

use Psr\Container\ContainerInterface;

class Core
{
    protected $db;
    protected $session;
    protected $container;

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