<?php

namespace Model;

use Utilities\Database;
use Symfony\Component\HttpFoundation\Session\Session;

class MainModel
{
    protected $database;
    protected $session;

    public function __construct(Database $database, Session $session)
    {
        $this->session  = $session;
        $this->database = $database;
    }

    protected function model($className)
    {
        return new $className($this->db, $this->session);
    }
}