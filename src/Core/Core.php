<?php

namespace Core;

use Psr\Container\ContainerInterface;

class Core
{
    private $container;

    private $db;
    private $session;

    public function __construct()
    {
        $this->container = \ZorgeDI::getContainer();
    }

    protected function model($className)
    {
        return new $className($this->container);
    }

    protected function db()
    {
        if(is_null($this->db)) $this->db = $this->container->get('db');

        return $this->db;
    }

    protected function session()
    {
        if(is_null($this->session)) $this->session = $this->container->get('session');

        return $this->session;
    }

    protected function getDI()
    {
        return $this->container;
    }
}