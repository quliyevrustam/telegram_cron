<?php

namespace Controller\Index;

use Controller\MainController;

class IndexController extends MainController
{
    public function index()
    {
        return $this->tmp()->render('Index/start.html', ['name' => 'World']);
    }
}