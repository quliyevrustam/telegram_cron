<?php

namespace Controller\Index;

use Controller\MainController;

class IndexController extends MainController
{
    public function index()
    {
        return $this->tmp()->render('Index/start.html', ['name' => 'World']);
    }

    public function showName($name, $id)
    {
        return $this->tmp()->render('Index/start.html', ['name' => '#'.$id.' '.$name]);
    }

    public function postIndex()
    {
        $name = $this->http()->request->get('name');
        return $this->tmp()->render('Index/start.html', ['name' => $name]);
    }

    public function viewLoginPage()
    {
        return $this->tmp()->render('Index/login.html');
    }

    public function logoutUser()
    {
        $this->auth()->logoutUser();
    }
}