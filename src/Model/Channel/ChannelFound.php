<?php

namespace Model\Channel;

use danog\MadelineProto\help;
use Exception;
use Model\MainModel;
use Utilities\Helper;
use Utilities\Pagination;

class ChannelFound extends MainModel
{
    const TABLE_NAME = 'channel_found';

    const CONDITION_NOT_CHECKED = 0;
    const CONDITION_NOT_CHANNEL = -4;

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
          `condition` = ".self::CONDITION_NOT_CHECKED." AND `status` > 0
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
        $updatedFields = ['external_id', 'name', 'follower_count', 'description', 'checked_at', 'condition', 'status'];
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
            $updateSQL[] = '`'.$field.'`=:'.$field;
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

    public function getChannelFoundList(Pagination $pagination, array $filter): array
    {
        $total = 0;
        $foundChannels = [];

        $sql = $this->getChannelFoundListSql($pagination, $filter);
        //Helper::prePrint($sql);

        $sqlRequest = $this->db()->prepare($sql['records']);
        $sqlRequest->execute($sql['bind']);
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                $addDate = Helper::timezoneConverter($row->created_at, 'UTC', 'Asia/Baku');
                $checkDate = Helper::timezoneConverter($row->checked_at, 'UTC', 'Asia/Baku');

                $foundChannels[] = [
                    'id'             => $row->id,
                    'peer'           => $row->peer,
                    'name'           => !empty($row->name) ? $row->name : 'None',
                    'follower_count' => $row->follower_count,
                    'add_date'       => $addDate,
                    'check_date'     => $checkDate,
                    'condition'      => $row->condition,
                ];
            }

            $totalRequest = $this->db()->prepare($sql['total']);
            $totalRequest->execute($sql['bind']);
            $total = $totalRequest->rowCount();
        }

        return ['records' => $foundChannels, 'total' => $total];
    }

    private function getChannelFoundListSql(Pagination $pagination, array $filter): array
    {
        $sqlPart = '';
        if(is_numeric($filter['condition']))
        {
            $sqlPart .= " AND `condition` = :condition";
            $sql['bind']['condition'] = $filter['condition'];
        }

        $sql['total'] = "
        SELECT 
            `id`,
            `peer`,
            `name`,
            `follower_count`,
            `created_at`,
            `checked_at`,
            `condition`
        FROM 
          ".ChannelFound::TABLE_NAME."
        WHERE 
          `status` > 0 AND checked_at IS NOT NULL".$sqlPart;

        $sql['records'] = $sql['total']."
        ORDER BY ".$pagination->orderField." ".$pagination->orderDestination.", created_at DESC 
        LIMIT ".$pagination->offset.", ".$pagination->limit.";
        ";

        return $sql;
    }

    public function getChannel(int $channelId): array
    {
        $sql = "
        SELECT 
            c.`peer`,
            c.`name`,
            c.`follower_count`,
            c.`description`,
            c.`created_at`,
            c.`checked_at`,
            c.`condition`
        FROM 
            `".self::TABLE_NAME."` c
        WHERE `status` > 0 and id = :id
        LIMIT 1";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute(['id' => $channelId]);
        $row = $sqlRequest->fetch(\PDO::FETCH_OBJ);
        if($row)
        {
            $addDate = Helper::timezoneConverter($row->created_at, 'UTC', 'Asia/Baku');
            $checkDate = Helper::timezoneConverter($row->checked_at, 'UTC', 'Asia/Baku');

            $channel = [
                'peer'           => $row->peer,
                'name'           => !empty($row->name) ? $row->name : 'None',
                'condition'      => $row->condition,
                'follower_count' => $row->follower_count,
                'description'    => $row->description,
                'add_date'       => $addDate,
                'check_date'     => $checkDate,
            ];
        }
        else
            throw new Exception('Error!');

        return $channel;
    }
}