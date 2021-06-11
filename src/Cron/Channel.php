<?php

namespace Cron;

use Model\Channel\ChannelFound;
use Utilities\Cron;
use Utilities\Helper;

class Channel extends Cron
{
    public function actionUpdateInfo(): void
    {
        $channelHandler = new \Model\Channel\Channel();
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

                if(isset($result['result']['description']))
                    $channelBody['description'] = $result['result']['description'];

                // Get Channel Follower count
                $result = Helper::curlTelegramBotRequest('getChatMembersCount', 'get', ['chat_id' => '@'.$peer]);

                if($result['ok'] == 1)
                {
                    $channelBody['follower_count'] = $result['result'];
                }

                $channelHandler->update($channelBody);
            }
            elseif($result['ok'] == Helper::API_RESULT_CHAT_NOT_FOUND)
            {
                $channelHandler->delete($channelId);
            }
        }
    }

    public function actionAnalyzeFoundNewChannel(): void
    {
        $foundChannels = new ChannelFound();
        $peers = $foundChannels->getFoundChannels();

        foreach ($peers as $channelId=>$peer)
        {
            $channelInfo = [];
            echo $peer."\n";

            try
            {
                $result = Helper::curlTelegramBotRequest('getChat', 'get', ['chat_id' => '@'.$peer]);

                if($result['ok'] == 1)
                {
                    if(isset($result['result']['type']) && $result['result']['type'] == 'channel')
                    {
                        $channelInfo['checked_at'] = date('Y-m-d H:i:s');
                        if(isset($result['result']['id'])) $channelInfo['external_id'] = $result['result']['id'];
                        if(isset($result['result']['title'])) $channelInfo['name'] = $result['result']['title'];
                        if(isset($result['result']['description'])) $channelInfo['description'] = $result['result']['description'];

                        $foundChannels->edit($channelId, $channelInfo);

                        $result = Helper::curlTelegramBotRequest('getChatMembersCount', 'get', ['chat_id' => '@'.$peer]);

                        if($result['ok'] == 1)
                        {
                            $channelInfo = ['follower_count' => $result['result']];

                            $foundChannels->edit($channelId, $channelInfo);
                        }
                    }

                }
            }
            catch (\Throwable $exception)
            {
                Helper::logError($exception->getMessage());

                if($exception->getMessage() == 'Bad Request: chat not found')
                {
                    $channelInfo['checked_at'] = date('Y-m-d H:i:s');
                    $channelInfo['condition'] = ChannelFound::CONDITION_NOT_CHANNEL;

                    $foundChannels->edit($channelId, $channelInfo);
                }
                continue;
            }
        }
    }
}