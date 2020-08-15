<?php

namespace Utilities;

use PDO;

class Database extends PDO
{
    public function __construct()
    {
        parent::__construct(
            DB_DRIVER.':host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USERNAME,
            DB_PASSWORD);
    }
}