<?php

namespace Model\Channel;

use Model\MainModel;
use Utilities\Helper;

class Message extends MainModel
{
    const TABLE_NAME = 'channel_message';

    /**
     * @return string
     */
    public function getTop()
    {
        return '';
    }

    public function update(array $data)
    {
        $sqlRequest = $this->db()->prepare("
        SELECT 
            id
        FROM 
            ".self::TABLE_NAME." 
        WHERE status > 0 AND external_id = :external_id AND channel_id = :channel_id
        LIMIT 1;");
        $sqlRequest->execute(['external_id' => $data['external_id'], 'channel_id' => $data['channel_id']]);
        $row = $sqlRequest->fetch(\PDO::FETCH_OBJ);
        if($row)
        {
            $this->edit($row->id, $data);
        }
        else
        {
            $this->create($data);
        }
    }

    private function create(array $data)
    {
        $sql = "
            INSERT INTO 
                ".self::TABLE_NAME." (external_id, channel_id, view_count, forward_count, reply_count, body, created_at) 
            VALUES 
                (:external_id, :channel_id, :view_count, :forward_count, :reply_count, :body, :created_at)";
        $this->db()->prepare($sql)->execute($data);
    }

    private function edit(int $id, array $data)
    {
        $updatedFields = ['external_id', 'channel_id', 'view_count', 'forward_count', 'reply_count', 'body'];
        $updatedFields = array_fill_keys($updatedFields, 0);

        foreach ($data as $key=>$value)
        {
            if(!isset($updatedFields[$key])) unset($data[$key]);
        }

        $data['id'] = $id;
        $data['updated_at'] = date('Y-m-d H:i:s');

        $sql = "
            UPDATE ".self::TABLE_NAME." 
            SET 
                external_id=:external_id,
                channel_id=:channel_id,
                view_count=:view_count,
                forward_count=:forward_count,
                reply_count=:reply_count,
                body=:body,
                updated_at=:updated_at
            WHERE id=:id;";
        $this->db()->prepare($sql)->execute($data);
    }
}