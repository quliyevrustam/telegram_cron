<?php

use Model\Channel\Channel;

require_once dirname(__DIR__ ). '/vendor/autoload.php';

try {
    $container = null;

    // Create DI Container and write it to $container
    require_once (dirname(__DIR__ ).'/config/di.config.php');

    $channelHandler = new Channel($container);
    $channels = $channelHandler->getChannelPeers();

    foreach ($channels as $channelId=>$peer)
    {
        $channelBody = ['id' => $channelId];

        // Get Channel Info
        $result = \Utilities\Helper::curlRequest('getChat', 'get', ['chat_id' => '@'.$peer], false);

        if($result['ok'] == 1)
        {
            $channelBody['external_id'] = $result['result']['id'];
            $channelBody['name']        = $result['result']['title'];
        }

        // Get Channel Follower count
        $result = \Utilities\Helper::curlRequest('getChatMembersCount', 'get', ['chat_id' => '@'.$peer], false);

        if($result['ok'] == 1)
        {
            $channelBody['follower_count'] = $result['result'];
        }

        $channelHandler->update($channelBody);
    }
}
catch (Throwable $exception)
{
    print_r($exception->getMessage());
}