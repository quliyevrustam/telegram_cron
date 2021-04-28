<?php

namespace Model\Channel;

use Exception;
use Model\MainModel;
use Utilities\Helper;

class ChannelFound extends MainModel
{
    const TABLE_NAME = 'channel_found';

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
            LIMIT 1;");
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
}