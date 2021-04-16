<?php

namespace Model\Channel;

use Model\MainModel;
use Utilities\Helper;
use Utilities\TextFormat;

class MessageGrouped extends MainModel
{
    const TABLE_NAME = 'channel_message_grouped';

    public function update(array $data)
    {
        $sqlRequest = $this->db()->prepare("
        SELECT 
            id
        FROM 
            ".self::TABLE_NAME." 
        WHERE status > 0 AND message_id = :message_id
        LIMIT 1;");
        $sqlRequest->execute([
            'message_id'    => $data['message_id'],
        ]);
        $row = $sqlRequest->fetch(\PDO::FETCH_OBJ);
        if(!$row)
        {
            $this->create($data);
            $this->updateMainMessageId($data);
        }
    }

    private function create(array $data): int
    {
        $updatedFields = ['message_id', 'message_external_id', 'channel_id', 'grouped_id'];
        $updatedFields = array_fill_keys($updatedFields, 0);

        foreach ($data as $key=>$value)
        {
            if(!isset($updatedFields[$key])) unset($data[$key]);
        }

        $data['created_at'] = date('Y-m-d H:i:s');

        $sql = "
            INSERT INTO 
                ".self::TABLE_NAME." (message_id, message_external_id, channel_id, grouped_id, created_at) 
            VALUES 
                (:message_id, :message_external_id, :channel_id, :grouped_id, :created_at)";
        $this->db()->prepare($sql)->execute($data);

        return $this->db()->lastInsertId();
    }

    private function updateMainMessageId(array $data): void
    {
        $mainMessageId = $this->getMainMessageId($data);

        $sql = "
            UPDATE ".self::TABLE_NAME." 
            SET 
                main_message_id = :main_message_id
            WHERE grouped_id=:grouped_id AND channel_id=:channel_id;";
        $this->db()->prepare($sql)->execute([
                'main_message_id' => $mainMessageId,
                'channel_id'      => $data['channel_id'],
                'grouped_id'      => $data['grouped_id']
        ]);
    }

    private function getMainMessageId(array $data): int
    {
        $mainMessageId = 0;

        $sqlRequest = $this->db()->prepare("
        SELECT 
          message_id 
        FROM 
          channel_message_grouped 
        WHERE 
          `status` > 0 
          AND grouped_id = :grouped_id 
          AND channel_id = :channel_id
        ORDER BY 
          message_external_id ASC 
        LIMIT 1;");
        $sqlRequest->execute([
            'grouped_id'    => $data['grouped_id'],
            'channel_id'    => $data['channel_id'],
        ]);
        $row = $sqlRequest->fetch(\PDO::FETCH_OBJ);
        if($row)
        {
            $mainMessageId = $row->message_id;
        }

        return $mainMessageId;
    }
}