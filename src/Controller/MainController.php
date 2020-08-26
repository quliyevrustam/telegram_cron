<?php

namespace Controller;

use Core\Core;
use Utilities\Auth;
use Psr\Container\ContainerInterface;

class MainController extends Core
{
    private $auth;
    private $tmp;
    private $http;

    protected function auth()
    {
        if(!$this->auth instanceof Auth)
        {
            $this->auth = new Auth($this->getDI());
        }

        return $this->auth;
    }

    protected function http()
    {
        if(is_null($this->http)) $this->http = $this->getDI()->get('http');

        return $this->http;
    }

    protected function tmp()
    {
        if(is_null($this->tmp)) $this->tmp = $this->getDI()->get('tmp');

        return $this->tmp;
    }
}