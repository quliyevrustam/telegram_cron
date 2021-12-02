<?php

namespace Cron;

use Model\Channel\TopMessage;
use Utilities\Cron;
use Telegram\Bot\Api;

class Top extends Cron
{
    public function actionPostDailyMessage(): void
    {
        $topPost = new TopMessage();
        $channelPost = $topPost->getMessagePost(TopMessage::TYPE_ALL_DAILY);

        $telegram = new Api(BOT_KEY);
        $response = $telegram->sendMessage([
            'chat_id'   => CHANNEL_TOP_AZERI_POST,
            'text'      => $channelPost,
            'parse_mode'=> 'HTML'
        ]);

        if($messageId = $response->getMessageId())
        {
            echo "New Post ID: ".$messageId."\n";
        }
    }

    public function actionPostNotNewsWeeklyMessage(): void
    {
        $topPost = new TopMessage();
        $channelPost = $topPost->getMessagePost(TopMessage::TYPE_NOT_NEWS_WEEKLY);

        $telegram = new Api(BOT_KEY);
        $response = $telegram->sendMessage([
            'chat_id'   => CHANNEL_TOP_AZERI_POST,
            'text'      => $channelPost,
            'parse_mode'=> 'HTML'
        ]);

        if($messageId = $response->getMessageId())
        {
            echo "New Post ID: ".$messageId."\n";
        }
    }
}