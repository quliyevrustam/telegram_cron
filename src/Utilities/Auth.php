<?php

namespace Utilities;

use Core\Core;
use Model\User\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Psr\Container\ContainerInterface;

class Auth extends Core
{
    private $http;

    private $loginPageUrl = '/login';
    private $isLoginPage;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->http         = $container->get('http');

        $this->isLoginPage = ($this->http->getPathInfo() == $this->loginPageUrl);
    }

    public function checkLogin()
    {
        if(empty($this->session()->get('user_id')))
        {
            if(!empty($this->http->request->get('username')) &&
                !empty($this->http->request->get('password'))
            )
            {
                $this->loginUser($this->http->request->get('username'), $this->http->request->get('password'));
            }

            if(!$this->isLoginPage) (new RedirectResponse($this->loginPageUrl))->send();
        }
    }
    public function getUserId(): int
    {
        if(!$this->session()->get('user_id'))
        {
            $this->logoutUser();
        }

        return $this->session()->get('user_id');
    }

    public function loginUser(string $username, string $password):void
    {
        $user = $this->model(User::class)->checkUser($username, $password);
        if($user)
        {
            $this->session()->set('user_id', $user->id);
            $this->session()->set('user_name', $user->name);
            (new RedirectResponse('/'))->send();
        }

        (new RedirectResponse($this->loginPageUrl))->send();
    }

    public function logoutUser():void
    {
        $this->session()->invalidate();

        (new RedirectResponse($this->loginPageUrl))->send();
    }
}