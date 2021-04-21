<?php

namespace Cron;

use Model\Channel\TopMessage;
use Utilities\Cron;
use Telegram\Bot\Api;

class Top extends Cron
{
    public function actionPostDailyMessage(): void
    {
        $topPost = $this->model(TopMessage::class);
        $channelPost = $topPost->MessagePost(TopMessage::TYPE_ALL_DAILY);

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

    public function actionTestPostDailyMessage(): void
    {
        $topPost = $this->model(TopMessage::class);
        $channelPost = $topPost->MessagePost(TopMessage::TYPE_ALL_DAILY);

        $telegram = new Api(BOT_KEY);
        $response = $telegram->sendMessage([
            'chat_id'   => CHANNEL_TEST,
            'text'      => $channelPost,
            'parse_mode'=> 'HTML'
        ]);

        if($messageId = $response->getMessageId())
        {
            echo "New Post ID: ".$messageId."\n";
        }
    }

    public function actionTestPostNotNewsWeeklyMessage(): void
    {
        $topPost = $this->model(TopMessage::class);
        $channelPost = $topPost->MessagePost(TopMessage::TYPE_NOT_NEWS_WEEKLY);

        $telegram = new Api(BOT_KEY);
        $response = $telegram->sendMessage([
            'chat_id'   => CHANNEL_TEST,
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
        $topPost = $this->model(TopMessage::class);
        $channelPost = $topPost->MessagePost(TopMessage::TYPE_NOT_NEWS_WEEKLY);

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