<?php

namespace Cron;

use Utilities\Cron;
use Telegram\Bot\Api;

class Quran extends Cron
{
    public function actionPostAyah(): void
    {
        $ayah = $this->model(\Model\Cycle\Quran::class);
        $channelQuote = $ayah->getRandomQuote();

        $telegram = new Api(BOT_KEY);
        $response = $telegram->sendMessage(
            [
                'chat_id'    => CHANNEL_KEY_DAILY_AYAH,
                'text'       => $channelQuote,
                'parse_mode' => 'Markdown'
            ]
        );

        if($messageId = $response->getMessageId())
        {
            echo "New Post ID: ".$messageId."\n";
            $ayah->setShowed();
        }
    }
}