<?php

namespace Model;

use Utilities\Database;
use Symfony\Component\HttpFoundation\Session\Session;

class MainModel
{
    protected $db;
    protected $session;

    public function __construct(Database $db, Session $session)
    {
        $this->session  = $session;
        $this->db = $db;
    }

    protected function model($className)
    {
        return new $className($this->db, $this->session);
    }
}