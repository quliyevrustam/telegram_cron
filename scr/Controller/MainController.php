<?php

namespace Controller;

class MainController
{
    protected $http;
    protected $template_engine;

    public function __construct($templateEngine, $http)
    {
        $this->http = $http;
        $this->template_engine = $templateEngine;
    }
}