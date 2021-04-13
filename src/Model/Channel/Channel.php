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
        $sqlRequest = $this->db()->prepare("
        SELECT 
            c.`id`,
            c.`peer`
        FROM 
            `".self::TABLE_NAME."` c
        WHERE `status` > 0
        ORDER BY created_at DESC");
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
}