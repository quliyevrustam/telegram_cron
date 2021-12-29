<?php

namespace Cron;

use Exception;
use Model\Channel\ChannelFound;
use Utilities\Cron;
use Utilities\CronExceptionTreatment;
use Utilities\Helper;
use danog\MadelineProto\API;

class Channel extends Cron
{

    /**
     * @throws Exception
     */
    public function actionUpdateInfo(): void
    {
        $settings['app_info']['api_id'] = APP_API_ID;
        $settings['app_info']['api_hash'] = APP_API_HASH;
        $madelineProto = new API(MADELINE_SESSION_PATH, $settings);

        $channelHandler = new \Model\Channel\Channel();
        $channels = $channelHandler->getChannelPeers();

        foreach ($channels as $channelId=>$peer)
        {
            echo $channelId.' => '.$peer."\n";

            try {
                $channelInfo = $madelineProto->channels->getFullChannel(['channel' => $peer]);
                if($channelInfo && count($channelInfo) > 0)
                {
                    $channelBody = [];
                    if(isset($channelInfo['full_chat']['id'])) $channelBody['external_id'] = '-100'.$channelInfo['full_chat']['id'];
                    if(isset($channelInfo['chats'][0]['title']))
                    {
                        $channelBody['name'] = Helper::removeEmoji($channelInfo['chats'][0]['title']);
                        if(empty($channelBody['name'])) $channelBody['name'] = $peer;
                    }
                    if(isset($channelInfo['full_chat']['about'])) $channelBody['description'] = $channelInfo['full_chat']['about'];
                    if(isset($channelInfo['full_chat']['participants_count']))
                        $channelBody['follower_count'] = $channelInfo['full_chat']['participants_count'];

                    if(count($channelBody) > 0) $channelHandler->update($channelId, $channelBody);
                }
            } catch (\Throwable $e)
            {
                echo $channelId.' => '.$peer."\n";
                $channel = \Model\Channel\Channel::getById($channelId);
                (new CronExceptionTreatment($e))->execution($channel);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function actionAnalyzeFoundNewChannel(): void
    {
        $settings['app_info']['api_id'] = APP_API_ID;
        $settings['app_info']['api_hash'] = APP_API_HASH;
        $madelineProto = new API(MADELINE_SESSION_PATH, $settings);

        $foundChannel = new ChannelFound();
        $peers = $foundChannel->getFoundChannels();

        //$peers = [1550 => 'kolorit_21'];
        foreach ($peers as $channelId=>$peer)
        {
            echo $channelId.' => '.$peer."\n";
            $channelBody = [];

            try {
                $channelInfo = $madelineProto->channels->getFullChannel(['channel' => $peer]);
                Helper::prePrint($channelInfo);

                if($channelInfo && count($channelInfo) > 0)
                {
                    if (
                        (isset($channelInfo['chats'][0]['megagroup']) && $channelInfo['chats'][0]['megagroup'] == 1) ||
                        (isset($channelInfo['chats'][0]['gigagroup']) && $channelInfo['chats'][0]['gigagroup'] == 1) ||
                        (isset($channelInfo['chats'][0]['restricted']) && $channelInfo['chats'][0]['restricted'] == 1)
                    )
                    {
                        $channelBody['checked_at'] = date('Y-m-d H:i:s');
                        $channelBody['condition'] = ChannelFound::CONDITION_NOT_CHANNEL;

                        $foundChannel->edit($channelId, $channelBody);
                    }
                    else
                    {
                        if (isset($channelInfo['full_chat']['id']))
                        {
                            $channelBody['external_id'] = '-100' . $channelInfo['full_chat']['id'];
                        }
                        if (isset($channelInfo['chats'][0]['title']))
                        {
                            $channelBody['name'] = Helper::removeEmoji($channelInfo['chats'][0]['title']);
                            if (empty($channelBody['name']))
                            {
                                $channelBody['name'] = $peer;
                            }
                        }
                        if (isset($channelInfo['full_chat']['about']))
                        {
                            $channelBody['description'] = $channelInfo['full_chat']['about'];
                        }
                        if (isset($channelInfo['full_chat']['participants_count']))
                        {
                            $channelBody['follower_count'] = $channelInfo['full_chat']['participants_count'];
                        }

                        if (count($channelBody) > 0)
                        {
                            $channelBody['checked_at'] = date('Y-m-d H:i:s');
                            Helper::prePrint($channelBody);
                            $foundChannel->edit($channelId, $channelBody);
                        }
                    }
                }
            } catch (\Throwable $e)
            {
                echo $channelId.' => '.$peer."\n";
                $channel = ChannelFound::getById($channelId);
                (new CronExceptionTreatment($e))->execution($channel);
            }
        }
    }
}