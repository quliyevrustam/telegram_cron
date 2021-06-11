<?php

namespace Model\Channel;

use Exception;
use Model\MainModel;
use Utilities\Helper;
use Utilities\Pagination;

class Channel extends MainModel
{
    const TABLE_NAME = 'channel';

    const TYPE_ALL = 0;
    const TYPE_NOT_NEWS = 1;

    /**
     * @return array
     * @throws Exception
     */
    public function getChannelPeers(?string $type = null): array
    {
        $sqlPart = '';
        if($type == 'check_message')
            $sqlPart = ' AND c.external_id IS NOT NULL';

        $peers = [];
        $sql = "
        SELECT 
            c.`id`,
            c.`peer`
        FROM 
            `".self::TABLE_NAME."` c
        WHERE c.`status` > 0 $sqlPart
        ORDER BY c.follower_count DESC";
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

    /**
     * @param array $data
     */
    public function update(array $data)
    {
        $this->edit($data['id'], $data);
    }

    /**
     * @param int $id
     * @param array $data
     */
    private function edit(int $id, array $data)
    {
        $updatedFields = ['external_id', 'name', 'follower_count', 'description'];
        $updatedFields = array_fill_keys($updatedFields, 0);

        foreach ($data as $key=>$value)
        {
            if(!isset($updatedFields[$key])) unset($data[$key]);
        }
        if(count($data) == 0) return;

        $data['id'] = $id;
        $data['updated_at'] = date('Y-m-d H:i:s');

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
    }

    /**
     * @param int $channelId
     * @return array
     * @throws Exception
     */
    public function getChannel(int $channelId): array
    {
        $channel = [];
        $sql = "
        SELECT 
            c.`peer`,
            c.`name`,
            c.`follower_count`,
            c.`description`,
            c.`created_at`,
            (SELECT `id` FROM ".Message::TABLE_NAME." WHERE channel_id = c.id AND `status` > 0 ORDER BY created_at DESC LIMIT 1) AS last_message_id,
            (SELECT `id` FROM ".Message::TABLE_NAME." WHERE channel_id = c.id AND `status` > 0 ORDER BY err DESC LIMIT 1) AS most_popular_message_id
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

            $lastMessage = (new Message())->getMessageById($row->last_message_id);
            $lastMessage['created_at'] = Helper::timezoneConverter($lastMessage['created_at'], 'UTC', 'Asia/Baku');

            $mostPopularMessage = (new Message())->getMessageById($row->most_popular_message_id);
            $mostPopularMessage['created_at'] = Helper::timezoneConverter($mostPopularMessage['created_at'], 'UTC', 'Asia/Baku');

            $channel = [
                'peer'           => $row->peer,
                'name'           => $row->name,
                'follower_count' => $row->follower_count,
                'description'    => $row->description,
                'add_date'       => $addDate,
                'last_message' =>
                [
                    'external_id'    => $lastMessage['external_id'],
                    'date'  => $lastMessage['created_at'],
                    'text'  => $lastMessage['body']
                ],
                'most_popular_message' =>
                    [
                        'external_id'    => $mostPopularMessage['external_id'],
                        'date'  => $mostPopularMessage['created_at'],
                        'text'  => $mostPopularMessage['body']
                    ]
            ];
        }
        else
            throw new Exception('Error!');

        return $channel;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getChannelNames(): array
    {
        $channels = [];
        $sql = "
        SELECT 
            c.`id`,
            IF(c.`name` = '' OR c.`name` IS NULL, c.`peer`, c.`name`) AS name
        FROM 
            `".self::TABLE_NAME."` c
        WHERE `status` > 0 and c.`name` IS NOT NULL
        ORDER BY name ASC";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute();
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                $channels[] = [
                    'id'    => $row->id,
                    'name'  => $row->name,
                ];
            }
        }
        else
            throw new Exception('Error!');

        return $channels;
    }

    /**
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        $updatedFields = ['peer', 'external_id', 'name', 'follower_count', 'description'];
        $updatedFields = array_fill_keys($updatedFields, 0);

        foreach ($data as $key=>$value)
        {
            if(!isset($updatedFields[$key])) unset($data[$key]);
        }

        if(isset($data['name'])) $data['name'] = Helper::removeEmoji($data['name']);
        if(isset($data['description'])) $data['description'] = Helper::removeEmoji($data['description']);

        $data['created_at'] = date('Y-m-d H:i:s');

        $sql = "
            INSERT INTO 
                ".self::TABLE_NAME." (peer, external_id, name, follower_count, description, created_at) 
            VALUES 
                (:peer, :external_id, :name, :follower_count, :description, :created_at)";
        $this->db()->prepare($sql)->execute($data);

        return $this->db()->lastInsertId();
    }

    /**
     * @param Pagination $pagination
     * @param array $filter
     * @return array
     */
    public function getChannelList(Pagination $pagination, array $filter): array
    {
        $total = 0;
        $channels = [];

        $sql = $this->getChannelListSql($pagination, $filter);

        $sqlRequest = $this->db()->prepare($sql['records']);
        $sqlRequest->execute($sql['bind']);
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                $addDate = Helper::timezoneConverter($row->created_at, 'UTC', 'Asia/Baku');

                $channels[] = [
                    'id'             => $row->id,
                    'peer'           => $row->peer,
                    'name'           => !empty($row->name) ? $row->name : $row->peer,
                    'follower_count' => $row->follower_count,
                    'add_date'       => $addDate,
                ];
            }

            $totalRequest = $this->db()->prepare($sql['total']);
            $totalRequest->execute($sql['bind']);
            $total = $totalRequest->rowCount();
        }

        return ['records' => $channels, 'total' => $total];
    }

    /**
     * @param Pagination $pagination
     * @param array $filter
     * @return array
     */
    private function getChannelListSql(Pagination $pagination, array $filter): array
    {
        $sqlPart = '';

        $sql['total'] = "
        SELECT 
            `id`,
            `peer`,
            `name`,
            `follower_count`,
            `created_at`
        FROM 
          ".Channel::TABLE_NAME."
        WHERE 
          `status` > 0".$sqlPart;

        $sql['records'] = $sql['total']."
        ORDER BY ".$pagination->orderField." ".$pagination->orderDestination.", created_at DESC 
        LIMIT ".$pagination->offset.", ".$pagination->limit.";
        ";

        return $sql;
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $sql = "
            UPDATE ".self::TABLE_NAME." 
            SET status = -1
            WHERE id=:id;";
        $this->db()->prepare($sql)->execute(['id' => $id]);
    }
}