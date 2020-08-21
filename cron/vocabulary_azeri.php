<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';

use Telegram\Bot\Api;
use Model\Cycle\AzeriVocabulary;

try {

    // Create DI Container and write it to $container
    require_once (dirname(__DIR__ ).'/config/di.config.php');

    $vocabulary = new AzeriVocabulary($container);
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
catch (Throwable $exception)
{
    print_r($exception->getMessage());
}