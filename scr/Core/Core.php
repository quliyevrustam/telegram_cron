<?php

namespace Core;

use Psr\Container\ContainerInterface;

class Core
{
    private $container;

    private $db;
    private $session;

    public function __construct(ContainerInterface $container)
    {
        $this->container        = $container;
    }

    protected function model($className)
    {
        return new $className($this->container);
    }

    protected function db()
    {
        if(is_null($this->db)) $this->db = $this->getDI()->get();

        return $this->db;
    }

    protected function session()
    {
        if(is_null($this->session)) $this->session = $this->getDI()->get('session');

        return $this->session;
    }

    protected function getDI()
    {
        return $this->container;
    }
}