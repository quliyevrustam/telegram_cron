<?php

namespace Model\Channel;

use Model\MainModel;
use Utilities\Helper;
use Utilities\TextFormat;

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

    /**
     * @param array $data
     */
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

    /**
     * @param array $data
     */
    private function create(array $data)
    {
        $sql = "
            INSERT INTO 
                ".self::TABLE_NAME." (external_id, channel_id, view_count, forward_count, reply_count, body, created_at) 
            VALUES 
                (:external_id, :channel_id, :view_count, :forward_count, :reply_count, :body, :created_at)";
        $this->db()->prepare($sql)->execute($data);
    }

    /**
     * @param int $id
     * @param array $data
     */
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

        if(isset($data['body'])) $data['body'] = trim(addslashes(Helper::removeEmoji($data['body'])));

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

    /**
     * @return string
     */
    public function TopMessagePost(): string
    {
        $todayBegin = Helper::timezoneConverter(date('Y-m-d H:i:s'), 'Asia/Baku', 'UTC');

        $topMessages = [];
        $sql = "
        SELECT 
          c.`peer`, 
          c.`name`, 
          ch.`external_id`, 
          ch.`body` 
        FROM 
          `channel_message` ch 
          LEFT JOIN `channel` c ON ch.`channel_id` = c.`id` 
        WHERE 
          ch.`created_at` > '".$todayBegin."'
        ORDER BY 
          ch.view_count DESC 
        LIMIT 
          0, 5;";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute();
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                $topMessages[] = [
                    'external_id' => $row->external_id,
                    'peer'        => $row->peer,
                    'name'        => $row->name,
                    'body'        => $row->body,
                ];
            }
        }

        $i = 1;
        $post = TextFormat::makeBold(date('d/m/Y'))."\n"."\n";
        foreach ($topMessages as $message)
        {
            // Prepare Message Link
            $messageLink = 'https://t.me/'.$message['peer'].'/'.$message['external_id'];

            // Prepare Message Body
            $message['body'] = str_replace("\n", ' ', $message['body']);
            $messageBody = TextFormat::makeItalic($message['body']);

            // Prepare Message Post
            $post .= TextFormat::makeBold($i.'. '.$message['name'])."\n";
            if($message['body'] != '...') $post .= TextFormat::makeBold('Текст').': '.$messageBody."\n";
            $post .= TextFormat::makeBold('Ссылка').': '.$messageLink."\n"."\n";

            $i++;
        }

        return $post;
    }
}