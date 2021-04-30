<?php

namespace Model\Channel;

use Model\MainModel;
use Utilities\Helper;
use Utilities\Pagination;

class Message extends MainModel
{
    public const TABLE_NAME = 'channel_message';

    public const ERR_COOFICIENT_VIEW = 0.5;
    public const ERR_COOFICIENT_REPLY = 0.2;
    public const ERR_COOFICIENT_FORWARD = 0.3;

    public const MESSAGE_GROUPED = 1;
    public const MESSAGE_SINGLE = 0;

    private $max_view_count     = 0;
    private $max_reply_count    = 0;
    private $max_forward_count  = 0;

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
            $id = $row->id;
        }
        else
        {
            $id = $this->create($data);
        }

        if($data['is_grouped'] == self::MESSAGE_GROUPED)
        {
            $groupedMessageData['message_id']           = $id;
            $groupedMessageData['channel_id']           = $data['channel_id'];
            $groupedMessageData['grouped_id']           = $data['grouped_id'];
            $groupedMessageData['message_external_id']  = $data['external_id'];

            $groupedMessage = $this->model(MessageGrouped::class);
            $groupedMessage->update($groupedMessageData);
        }
    }

    /**
     * @param array $data
     * @return int
     */
    private function create(array $data): int
    {
        $updatedFields = ['external_id', 'channel_id', 'view_count', 'forward_count', 'reply_count', 'body', 'created_at', 'is_grouped'];
        $updatedFields = array_fill_keys($updatedFields, 0);

        foreach ($data as $key=>$value)
        {
            if(!isset($updatedFields[$key])) unset($data[$key]);
        }

        if(isset($data['body'])) $data['body'] = Helper::removeEmoji($data['body']);

        $sql = "
            INSERT INTO 
                ".self::TABLE_NAME." (external_id, channel_id, view_count, forward_count, reply_count, body, created_at, is_grouped) 
            VALUES 
                (:external_id, :channel_id, :view_count, :forward_count, :reply_count, :body, :created_at, :is_grouped)";
        $this->db()->prepare($sql)->execute($data);

        return $this->db()->lastInsertId();
    }

    /**
     * @param int $id
     * @param array $data
     * @return int
     */
    private function edit(int $id, array $data): int
    {
        $updatedFields = ['external_id', 'channel_id', 'view_count', 'forward_count', 'reply_count', 'body', 'err', 'is_grouped'];
        $updatedFields = array_fill_keys($updatedFields, 0);

        foreach ($data as $key=>$value)
        {
            if(!isset($updatedFields[$key])) unset($data[$key]);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        if(isset($data['body'])) $data['body'] = Helper::removeEmoji($data['body']);

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

    public function updateErr(): void
    {
        $this->fillMaxProperty();

        $sql = "
        SELECT 
          c.`id`, 
          c.`view_count`,
          c.`reply_count`, 
          c.`forward_count`
        FROM 
          ".Message::TABLE_NAME." c
        WHERE 
          c.`status` > 0 AND c.`created_at` > '".Helper::getCurrentDayBegin()."'
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

    private function fillMaxProperty(): void
    {
        $sql = "
        SELECT 
          MAX(c.`view_count`) AS max_view_count,
          MAX(c.`reply_count`) AS max_reply_count,
          MAX(c.`forward_count`) AS max_forward_count
        FROM 
          ".Message::TABLE_NAME." c
        WHERE 
          c.`status` > 0 AND c.`created_at` > '".Helper::getCurrentDayBegin()."'
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
    }

    /**
     * @param int $viewCount
     * @param int $replyCount
     * @param int $forwardCount
     * @return float
     */
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

    public function getMessageList(Pagination $pagination): array
    {
        $messages = [];
        $sql = "
        SELECT 
          c.`peer`, 
          c.`name` as channel_name, 
          ch.`view_count`, 
          ch.`forward_count`, 
          ch.`reply_count`, 
          ch.`err`, 
          ch.`created_at`,
          ch.external_id,
          IF(
          ch.`body` LIKE '...' AND ch.`is_grouped` = 1,
          (SELECT `body`  FROM ".Message::TABLE_NAME." ch  WHERE id IN (SELECT cmg.`message_id` FROM ".MessageGrouped::TABLE_NAME." cmg WHERE cmg.`main_message_id` = ch.`id`) AND `body` NOT LIKE '...' LIMIT 1), 
          ch.`body`) AS body
        FROM 
          ".Message::TABLE_NAME." ch 
          LEFT JOIN ".Channel::TABLE_NAME." c ON ch.`channel_id` = c.`id` 
        WHERE 
          ch.`status` > 0 
          AND c.`status` > 0 
          AND (
            ch.`is_grouped` = 0 
            OR 
            (ch.`is_grouped` = 1 AND ch.id IN (SELECT cmg.`main_message_id` FROM ".MessageGrouped::TABLE_NAME." cmg)
            )
          ) 
          AND ch.`created_at` > '".Helper::getCurrentDayBegin()."' 
        ORDER BY ch.".$pagination->orderField." ".$pagination->orderDestination.", ch.created_at DESC 
        LIMIT ".$pagination->offset.", ".$pagination->limit.";
        ";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute();
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                $createDate = Helper::timezoneConverter($row->created_at, 'UTC', 'Asia/Baku');

                $messages[] = [
                    'peer'          => $row->peer,
                    'channel_name'  => $row->channel_name,
                    'view_count'    => $row->view_count,
                    'forward_count' => $row->forward_count,
                    'reply_count'   => $row->reply_count,
                    'err'           => $row->err,
                    'created_at'    => $createDate,
                    'body'          => $row->body,
                    'external_id'   => $row->external_id,
                ];
            }
        }

        return $messages;
    }
}