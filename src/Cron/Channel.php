<?php

namespace Cron;

use Utilities\Cron;
use Utilities\Helper;

class Channel extends Cron
{
    public function actionUpdateInfo(): void
    {
        $channelHandler = $this->model(\Model\Channel\Channel::class);
        $channels = $channelHandler->getChannelPeers();

        foreach ($channels as $channelId=>$peer)
        {
            echo $peer."\n";

            $channelBody = ['id' => $channelId];

            // Get Channel Info
            $result = Helper::curlTelegramBotRequest('getChat', 'get', ['chat_id' => '@'.$peer]);

            if($result['ok'] == 1)
            {
                $channelBody['external_id'] = $result['result']['id'];
                $channelBody['name']        = $result['result']['title'];
            }

            // Get Channel Follower count
            $result = Helper::curlTelegramBotRequest('getChatMembersCount', 'get', ['chat_id' => '@'.$peer]);

            if($result['ok'] == 1)
            {
                $channelBody['follower_count'] = $result['result'];
            }

            $channelHandler->update($channelBody);
        }
    }
}