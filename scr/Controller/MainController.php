<?php

namespace Controller;

use Utilities\Auth;

class MainController
{
    protected $db;
    protected $http;
    protected $template_engine;
    protected $session;


    public function __construct($templateEngine, $http, $db, $session)
    {
        $this->db               = $db;
        $this->http             = $http;
        $this->session          = $session;
        $this->template_engine  = $templateEngine;
    }

    protected function model($className)
    {
        return new $className($this->db, $this->session);
    }

    protected function auth()
    {
        return $this->model(Auth::class);
    }
}