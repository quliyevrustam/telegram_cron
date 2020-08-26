<?php

namespace Controller\Test;

use Controller\MainController;

class TestController extends MainController
{
    public function testGet($name)
    {
        return $this->tmp()->render('Test/test_get.html', ['name' => $name]);
    }

    public function viewTestPost()
    {
        return $this->tmp()->render('Test/test_post.html', ['name' => 'Write your name to Form!']);
    }

    public function testPost()
    {
        $name = $this->http()->request->get('name');
        return $this->tmp()->render('Test/test_post.html', ['name' => $name]);
    }
}