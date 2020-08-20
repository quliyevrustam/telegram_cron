<?php

namespace Controller;

class MainController
{
    protected $db;
    protected $http;
    protected $template_engine;

    public function __construct($templateEngine, $http, $db)
    {
        $this->db = $db;
        $this->http = $http;
        $this->template_engine = $templateEngine;
    }

    protected function model($className)
    {
        return new $className($this->db);
    }
}