<?php

namespace Core;

use Psr\Container\ContainerInterface;

class Core
{
    protected $container;

    private $db;
    private $session;

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

    protected function db(string $dbName = 'db')
    {
        if(is_null($this->db)) $this->db = $this->container->get($dbName);

        return $this->db;
    }

    protected function session()
    {
        if(is_null($this->session)) $this->session = $this->container->get('session');

        return $this->session;
    }
}