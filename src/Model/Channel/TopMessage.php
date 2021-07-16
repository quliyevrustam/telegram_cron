<?php

namespace Model\Channel;

use Model\MainModel;
use Utilities\Helper;
use Utilities\HtmlFormat;

class TopMessage extends MainModel
{
    public const TABLE_NAME = 'channel_message';

    public const TYPE_ALL_DAILY = 1;
    public const TYPE_NOT_NEWS_WEEKLY = 2;

    /**
     * @param int $type
     * @param int $limit
     * @return string
     */
    public function MessagePost(int $type, int $limit = 5): string
    {
        $sqlPart = '';
        if($type == self::TYPE_ALL_DAILY)
        {
            $sqlPart = " AND ch.`created_at` > '".Helper::getCurrentDayBegin()."'";
        }
        elseif ($type == self::TYPE_NOT_NEWS_WEEKLY)
        {
            $sqlPart = " AND ch.`created_at` > '".Helper::getWeekBegin()."' AND 
            ch.`channel_id` NOT IN (SELECT channel_id FROM ".ChannelLabel::TABLE_NAME." WHERE `status` > 0 AND label_id = ".ChannelLabelType::NEWS.")";
        }

        $topMessages = [];
        $sql = "
        SELECT 
          c.`peer`, 
          c.`name`, 
          ch.`external_id`, 
          IF(
          ch.`body` LIKE '...' AND ch.`is_grouped` = 1, 
          (SELECT ch2.`body`  FROM ".Message::TABLE_NAME." ch2 WHERE ch2.id IN (SELECT cmg.`message_id` FROM `channel_message_grouped` cmg WHERE cmg.`main_message_id` = ch.`id`) AND ch2.`body` NOT LIKE '...' LIMIT 1), 
          ch.`body`) AS body
        FROM 
          ".Message::TABLE_NAME." ch 
          LEFT JOIN ".Channel::TABLE_NAME." c ON ch.`channel_id` = c.`id` 
        WHERE   
          ch.`status` > 0 AND c.`status` > 0 AND 
          (ch.`is_grouped` = ".Message::MESSAGE_SINGLE." OR (ch.`is_grouped` = ".Message::MESSAGE_GROUPED." AND ch.id IN (SELECT cmg.`main_message_id` FROM `channel_message_grouped` cmg)))
          ".$sqlPart."
        ORDER BY 
          ch.err DESC, ch.created_at DESC 
        LIMIT 
          0, ".$limit.";";
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

        $post = self::generateTopMessageHeader($type);
        $post .= "\n"."\n";
        $post .= self::generateTopMessageBody($topMessages);

        return $post;
    }

    /**
     * @param array $topMessages
     * @return string
     */
    private static function generateTopMessageBody(array $topMessages): string
    {
        $postBody = '';
        $i = 1;
        foreach ($topMessages as $message)
        {
            // Prepare Message Name
            $message['name'] = str_replace(".", HtmlFormat::makeCode('.'), $message['name']);

            // Prepare Message Body
            $message['body'] = str_replace("\n", '.', $message['body']);
            $messageBody = HtmlFormat::makeItalic($message['body']);

            // Prepare Message Link
            $messageLink = 'https://t.me/'.$message['peer'].'/'.$message['external_id'];

            // Prepare Message Post
            $postBody .= HtmlFormat::makeBold($i.'. '.$message['name'])."\n";
            if($message['body'] != '...') $postBody .= HtmlFormat::makeBold('Текст').': '.$messageBody."\n";
            $postBody .= HtmlFormat::makeBold('Ссылка').': '.$messageLink."\n"."\n";

            $i++;
        }

        return $postBody;
    }
    
    private static function generateTopMessageHeader(int $type): string
    {
        $postHeader = '';
        if($type == self::TYPE_ALL_DAILY)
        {
            $postHeader = HtmlFormat::makeBold(date('d/m/Y'));
        }
        elseif ($type == self::TYPE_NOT_NEWS_WEEKLY)
        {
            $postHeader = HtmlFormat::makeBold('Топ 5 постов за неделю, с Не Новостных telegram-каналов');
        }
        
        return $postHeader;
    }
}