<?php

namespace Model\Channel;

use Model\MainModel;
use Utilities\Helper;
use Utilities\TextFormat;

class Message extends MainModel
{
    const TABLE_NAME = 'channel_message';

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
        $updatedFields = ['external_id', 'channel_id', 'view_count', 'forward_count', 'reply_count', 'body', 'err'];
        $updatedFields = array_fill_keys($updatedFields, 0);

        foreach ($data as $key=>$value)
        {
            if(!isset($updatedFields[$key])) unset($data[$key]);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        if(isset($data['body'])) $data['body'] = trim(addslashes(Helper::removeEmoji($data['body'])));

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
     * @return string
     */
    public function TopMessagePost(): string
    {
        $todayBegin = Helper::timezoneConverter(date('Y-m-d 00:00:00'), 'Asia/Baku', 'UTC');

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
          ch.err DESC, ch.created_at DESC 
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
            $linkPeer = str_replace('_', "\_",$message['peer']);
            $messageLink = 'https://t.me/'.$linkPeer.'/'.$message['external_id'];

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

    private $max_view_count     = 0;
    private $max_reply_count    = 0;
    private $max_forward_count  = 0;

    public function updateErr(): void
    {
        $todayBegin = Helper::timezoneConverter(date('Y-m-d 00:00:00'), 'Asia/Baku', 'UTC');

        $sql = "
        SELECT 
          MAX(c.`view_count`) AS max_view_count,
          MAX(c.`reply_count`) AS max_reply_count,
          MAX(c.`forward_count`) AS max_forward_count
        FROM 
          `channel_message` c
        WHERE 
          c.`status` > 0 AND c.`created_at` > '".$todayBegin."'
        ORDER BY c.created_at DESC;";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute();
        $row = $sqlRequest->fetch(\PDO::FETCH_OBJ);
        if($row)
        {
            $this->max_view_count = $row->max_view_count;
            $this->max_reply_count = $row->max_reply_count;
            $this->max_forward_count = $row->max_forward_count;
        }

        $sql = "
        SELECT 
          c.`id`, 
          c.`view_count`,
          c.`reply_count`, 
          c.`forward_count`
        FROM 
          `channel_message` c
        WHERE 
          c.`status` > 0 AND c.`created_at` > '".$todayBegin."'
        ORDER BY c.created_at DESC;";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute();
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                $err = $this->countErr($row->view_count, $row->reply_count, $row->forward_count);

                $this->edit($row->id, ['err' => $err]);
            }
        }
    }

    const ERR_COOFICIENT_VIEW       = 0.5;
    const ERR_COOFICIENT_REPLY      = 0.2;
    const ERR_COOFICIENT_FORWARD    = 0.3;

    private function countErr(int $viewCount, int $replyCount, int $forwardCount): float
    {
        $viewVariable = $replyVariable = $forwardVariable = 0;

        if($this->max_view_count > 0)
        {
            $viewVariable = ($viewCount*self::ERR_COOFICIENT_VIEW)/$this->max_view_count;
            if($viewVariable > self::ERR_COOFICIENT_VIEW) $viewVariable = self::ERR_COOFICIENT_VIEW;
        }

        if($this->max_reply_count > 0)
        {
            $replyVariable = ($replyCount*self::ERR_COOFICIENT_REPLY)/$this->max_reply_count;
            if($replyVariable > self::ERR_COOFICIENT_REPLY) $replyVariable = self::ERR_COOFICIENT_REPLY;
        }

        if($this->max_forward_count > 0)
        {
            $forwardVariable = ($forwardCount*self::ERR_COOFICIENT_FORWARD)/$this->max_forward_count;
            if($forwardVariable > self::ERR_COOFICIENT_FORWARD) $forwardVariable = self::ERR_COOFICIENT_FORWARD;
        }

        return round(($viewVariable + $replyVariable + $forwardVariable), 4);
    }
}