<?php

namespace Cron;

use Model\Cycle\AzeriVocabulary;
use Utilities\Cron;
use Telegram\Bot\Api;

class Vocabulary extends Cron
{
    public function actionPostWord(): void
    {
        $vocabulary = $this->model(AzeriVocabulary::class);
        $channelPost = $vocabulary->getRandomPost();

        $telegram = new Api(BOT_KEY);
        $response = $telegram->sendMessage([
            'chat_id'   => CHANNEL_KEY_AZERI_WORD,
            'text'      => $channelPost,
            'parse_mode'=> 'Markdown'
        ]);

        if($messageId = $response->getMessageId())
        {
            echo "New Post ID: ".$messageId."\n";
            $vocabulary->setShowed($vocabulary->id);
        }
    }
}