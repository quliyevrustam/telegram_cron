<?php

namespace Model;

use Utilities\Database;

class MainModel
{
    protected $database;

    public function __construct()
    {
        $this->database = new Database();
    }
}