<?php

namespace Utilities;

use Model\MainModel;
use Model\User\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Auth extends MainModel
{
    public function checkLogin()
    {
        if(!$this->session->get('login'))
        {
            $this->session->set('login', true);
            $response = new RedirectResponse('/login');
            $response->send();
        }
    }

    public function loginUser(string $username, string $password):void
    {
        $result = $this->model(User::class)->checkUser($username, $password);
    }
}