<?php

namespace Controller;

class MainController
{
    protected $template_engine;

    public function __construct( $template_engine)
    {
        $this->template_engine = $template_engine;
    }
}