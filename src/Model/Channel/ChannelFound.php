<?php

namespace Model\Channel;

use Exception;
use Model\MainModel;
use Utilities\Helper;

class ChannelFound extends MainModel
{
    const TABLE_NAME = 'channel_found';

    const STATUS_NOT_CHANNEL = -4;

    /**
     * @return array
     * @throws Exception
     */
    public function getPeers(): array
    {
        $peers = [];
        $sql = "
        SELECT 
            c.`peer`
        FROM 
            `".self::TABLE_NAME."` c
        ORDER BY id DESC";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute();
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                $peers[$row->peer] = $row->peer;
            }
        }
        else
            throw new Exception('Error!');

        return $peers;
    }

    public function add(array $peers)
    {
        if(count($peers) == 0) return;

        foreach ($peers as $peer)
        {
            $sqlRequest = $this->db()->prepare("
            SELECT 
                id
            FROM 
                ".self::TABLE_NAME." 
            WHERE peer = :peer
            LIMIT 10;");
            $sqlRequest->execute(['peer' => $peer]);
            $row = $sqlRequest->fetch(\PDO::FETCH_OBJ);
            if(!$row)
            {
                $this->create($peer);
            }
        }
    }

    private function create(string $peer): int
    {
        $peer = Helper::removeEmoji($peer);
        $createDate = date('Y-m-d H:i:s');

        $sql = "
            INSERT INTO 
                ".self::TABLE_NAME." (peer, created_at) 
            VALUES 
                (:peer, :created_at)";
        $this->db()->prepare($sql)->execute(['peer' => $peer, 'created_at' => $createDate]);

        return $this->db()->lastInsertId();
    }

    public function getFoundChannels(): array
    {
        $foundChannels = [];
        $sql = "
        SELECT 
          id, 
          peer 
        FROM 
          ".self::TABLE_NAME." 
        WHERE 
          `status` = 0 
          AND checked_at IS NULL 
        ORDER BY id DESC 
        LIMIT 100;";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute();
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                $foundChannels[$row->id] = $row->peer;
            }
        }
        else
            throw new Exception('Error!');

        return $foundChannels;
    }

    public function edit(int $id, array $data): int
    {
        $updatedFields = ['external_id', 'name', 'follower_count', 'description', 'checked_at', 'status'];
        $updatedFields = array_fill_keys($updatedFields, 0);

        foreach ($data as $key=>$value)
        {
            if(!isset($updatedFields[$key])) unset($data[$key]);
        }

        if(isset($data['name'])) $data['name'] = Helper::removeEmoji($data['name']);
        if(isset($data['description'])) $data['description'] = Helper::removeEmoji($data['description']);

        $updateSQL = [];
        foreach ($data as $field=>$value)
        {
            $updateSQL[] = $field.'=:'.$field;
        }

        $data['id'] = $id;

        $sql = "
            UPDATE ".self::TABLE_NAME." 
            SET 
                ".(implode(',', $updateSQL))."
            WHERE id=:id;";
        $this->db()->prepare($sql)->execute($data);

        return $id;
    }
}