<?php

namespace Model\User;

use Model\MainModel;

class User extends MainModel
{
    public function checkUser(string $username, string $password): array
    {
        $row = $this->database->query("
        SELECT 
            id,
            word,
            description
        FROM 
            azeri_vocabulary 
        WHERE showed_at IS NULL 
        ORDER BY RAND() 
        LIMIT 1;")->fetch(\PDO::FETCH_OBJ);
        if($row)
        {
            $this->id           = $row->id;
            $this->word         = $row->word;
            $this->description  = $row->description;
        }
    }
}