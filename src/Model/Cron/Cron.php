<?php

namespace Model\Cron;

use Model\Channel\MessageGrouped;
use Model\MainModel;

class Cron extends MainModel
{
    public const TABLE_NAME = 'cron';

    public function beginWork(array $data)
    {
        $sqlRequest = $this->db()->prepare("
        SELECT 
            id
        FROM 
            ".self::TABLE_NAME." 
        WHERE cron_class_name = :cron_class_name AND cron_class_method_name = :cron_class_method_name
        LIMIT 1;");
        $sqlRequest->execute(['cron_class_name' => $data['cron_class_name'], 'cron_class_method_name' => $data['cron_class_method_name']]);
        $row = $sqlRequest->fetch(\PDO::FETCH_OBJ);
        if($row)
        {
            $this->id = $row->id;
        }
        else
        {
            $this->id = $this->create($data);
        }

        $sql = "
            UPDATE ".self::TABLE_NAME." 
            SET 
                work_begin_date =:work_begin_date
            WHERE id=:id;";
        $this->db()->prepare($sql)->execute([
            'id'                => $this->id,
            'work_begin_date'   => date('Y-m-d H:i:s')
        ]);
    }

    private function create(array $data): int
    {
        $updatedFields = ['cron_class_name', 'cron_class_method_name'];
        $updatedFields = array_fill_keys($updatedFields, 0);

        foreach ($data as $key=>$value)
        {
            if(!isset($updatedFields[$key])) unset($data[$key]);
        }

        $data['created_at'] = date('Y-m-d H:i:s');

        $sql = "
            INSERT INTO 
                ".self::TABLE_NAME." (cron_class_name, cron_class_method_name, created_at) 
            VALUES 
                (:cron_class_name, :cron_class_method_name, :created_at)";
        $this->db()->prepare($sql)->execute($data);

        return $this->db()->lastInsertId();
    }

    public function endWork()
    {
        $sql = "
            UPDATE ".self::TABLE_NAME." 
            SET 
                work_end_date =:work_end_date
            WHERE id=:id;";
        $this->db()->prepare($sql)->execute([
            'id'            => $this->id,
            'work_end_date' => date('Y-m-d H:i:s')
        ]);
    }
}