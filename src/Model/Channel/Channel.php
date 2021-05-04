<?php

namespace Model\Channel;

use Exception;
use Model\MainModel;
use Utilities\Helper;

class Channel extends MainModel
{
    const TABLE_NAME = 'channel';

    const TYPE_ALL = 0;
    const TYPE_NOT_NEWS = 1;

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

    public function getChannelNames(): array
    {
        $channels = [];
        $sql = "
        SELECT 
            c.`id`,
            c.`name`
        FROM 
            `".self::TABLE_NAME."` c
        WHERE `status` > 0 and c.`name` IS NOT NULL
        ORDER BY c.`name` ASC";
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
}