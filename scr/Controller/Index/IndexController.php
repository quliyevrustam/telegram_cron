<?php

namespace Controller\Index;

use Controller\MainController;

class IndexController extends MainController
{
    public function index()
    {
        return $this->template_engine->render('Index/start.html', ['name' => 'World']);
    }

    public function showName($name, $id)
    {
        return $this->template_engine->render('Index/start.html', ['name' => '#'.$id.' '.$name]);
    }

    public function postIndex()
    {
        $name = $this->http->request->get('name');
        return $this->template_engine->render('Index/start.html', ['name' => $name]);
    }
}