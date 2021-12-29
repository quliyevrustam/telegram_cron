<?php

namespace Model;

use Core\Core;
use Exception;
use PDO;
use Utilities\Helper;

class MainModel extends Core
{
    private const STATUS_ACTIVE     = 1;
    private const STATUS_DELETED    = -1;

    public int $id;

    /**
     * @param int $id
     * @return static
     * @throws Exception
     */
    public static function getById(int $id): static
    {
        $object = new static();
        $sql = $object->db()->prepare("
        SELECT 
            id
        FROM 
            ".static::TABLE_NAME." 
        WHERE id = :id;");
        $sql->execute(['id' => $id]);
        $row = $sql->fetch(PDO::FETCH_OBJ);
        if($row)
        {
            $object->id = $id;
            return $object;
        }
        else
            throw new Exception('Model does not exist!');
    }

    public static function getAll()
    {

    }

    /**
     * @param string|null $comment
     * @throws Exception
     */
    public function delete(?string $comment = ''): void
    {
        if (empty($this->id)) throw new Exception('Model Object does not exist!');

        $deleteDate = date('Y-m-d H:i:s');
        $comment = Helper::removeEmoji($comment);

        $sql = "
            UPDATE " . static::TABLE_NAME . " 
            SET status = :status, deleted_at = :deleted_at, delete_comment = :delete_comment
            WHERE id=:id;";
        $this->db()->prepare($sql)->execute(
            [
                'id'             => $this->id,
                'status'         => self::STATUS_DELETED,
                'deleted_at'     => $deleteDate,
                'delete_comment' => $comment
            ]
        );
    }
}