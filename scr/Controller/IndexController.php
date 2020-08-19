<?php

namespace Controller;

use Utilities\Helper;

class IndexController
{
    public function index()
    {
        echo 'Hello, World!';
    }

    public function showName($name, $id)
    {
        echo 'Hello, #'.$id.' '.$name.'!';
    }

    public function postIndex()
    {

    }
}