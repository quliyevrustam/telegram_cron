<?php

namespace Controller\Index;

use Controller\MainController;

class IndexController extends MainController
{
    public function index()
    {
        return $this->template_engine->render('index/index.html', ['name' => 'World']);
    }
}