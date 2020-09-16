<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';

use Telegram\Bot\Api;
use Model\Cycle\Quran;

try {
    $container = null;

    // Create DI Container and write it to $container
    require_once (dirname(__DIR__ ).'/config/di.config.php');

    $ayah = new Quran($container);
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
catch (Throwable $exception)
{
    print_r($exception->getMessage());
}