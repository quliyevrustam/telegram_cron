<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';

use Model\Channel\TopMessage;
use Telegram\Bot\Api;

try {
    $container = null;

    // Create DI Container and write it to $container
    require_once (dirname(__DIR__ ).'/config/di.config.php');

    $topPost = new TopMessage($container);
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
catch (Throwable $exception)
{
    print_r($exception->getTraceAsString());
    print_r($exception->getMessage());
}