<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';

use Telegram\Bot\Api;

try {
    $container = null;

    // Create DI Container and write it to $container
    require_once (dirname(__DIR__ ).'/config/di.config.php');

    $topPost = new \Model\Channel\Message($container);
    $channelPost = $topPost->TopMessagePost();

    $telegram = new Api(BOT_KEY);
    $response = $telegram->sendMessage([
        'chat_id'   => CHANNEL_TEST,
        'text'      => $channelPost,
        'parse_mode'=> 'Markdown'
    ]);

    if($messageId = $response->getMessageId())
    {
        echo "New Post ID: ".$messageId."\n";
    }
}
catch (Throwable $exception)
{
    print_r($exception->getTraceAsString());
    print_r($exception->getMessage());
}