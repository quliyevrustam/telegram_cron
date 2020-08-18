<?php

namespace Model;

use Utilities\Database;

class MainModel
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }
}