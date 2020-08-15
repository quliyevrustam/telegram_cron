<?php

require_once dirname(__DIR__ ). '/config/autoload.php';

use Telegram\Bot\Api;
use Model\Cycle\AzeriVocabulary;

try {
    $vocabulary = new AzeriVocabulary();
    $channelPost = $vocabulary->getRandomPost();

    $telegram = new Api(BOT_KEY);
    $response = $telegram->sendMessage([
        'chat_id'   => CHANNEL_KEY_TEST,
        'text'      => $channelPost,
        'parse_mode'=> 'Markdown'
    ]);

    if($messageId = $response->getMessageId())
    {
        echo "New Post ID: ".$messageId."\n";
        $vocabulary->setShowed($vocabulary->id);
    }
}
catch (Throwable $exception)
{
    print_r($exception->getMessage());
}