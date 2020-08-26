<?php

namespace Model\User;

use Model\MainModel;

class User extends MainModel
{
    const STATUS_BLOCKED = 0;
    const STATUS_ACTIVE = 1;

    public function checkUser(string $username, string $password)
    {
        $userRequest = $this->db()->prepare("
        SELECT * 
        FROM users
         WHERE email=:email and password=:password and status = :status");
        $userRequest->execute([
            'email' => $username,
            'password' => $password,
            'status' => self::STATUS_ACTIVE,
        ]);
        return $userRequest->fetch(\PDO::FETCH_OBJ);
    }
}