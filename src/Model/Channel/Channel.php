<?php

namespace Model\Channel;

use Exception;
use Model\MainModel;
use Utilities\Helper;

class Channel extends MainModel
{
    const TABLE_NAME = 'channel';

    /**
     * @return array
     * @throws Exception
     */
    public function getChannelPeers(): array
    {
        $peers = [];
        $sql = "
        SELECT 
            c.`id`,
            c.`peer`
        FROM 
            `".self::TABLE_NAME."` c
        WHERE `status` > 0
        ORDER BY id DESC";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute();
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                $peers[$row->id] = $row->peer;
            }
        }
        else
            throw new Exception('Error!');

        return $peers;
    }

    public function update(array $data)
    {
        $this->edit($data['id'], $data);
    }

    private function edit(int $id, array $data)
    {
        $updatedFields = ['external_id', 'name', 'follower_count'];
        $updatedFields = array_fill_keys($updatedFields, 0);

        foreach ($data as $key=>$value)
        {
            if(!isset($updatedFields[$key])) unset($data[$key]);
        }
        if(count($data) == 0) return;

        $data['id'] = $id;
        $data['updated_at'] = date('Y-m-d H:i:s');

        if(isset($data['name'])) $data['name'] = trim(addslashes(Helper::removeEmoji($data['name'])));

        $sql = "
            UPDATE ".self::TABLE_NAME." 
            SET 
                external_id=:external_id,
                name=:name,
                follower_count=:follower_count,
                updated_at=:updated_at
            WHERE id=:id;";
        $this->db()->prepare($sql)->execute($data);
    }
}