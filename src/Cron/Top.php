<?php

namespace Cron;

use Model\Channel\TopMessage;
use Utilities\Cron;
use danog\MadelineProto\API;

class Top extends Cron
{
    public function actionPostDailyMessage(): void
    {
        try {
            $channelPost = (new TopMessage())->getMessagePost(TopMessage::TYPE_ALL_DAILY);

            $settings['app_info']['api_id'] = APP_API_ID;
            $settings['app_info']['api_hash'] = APP_API_HASH;
            $madelineProto = new API(MADELINE_SESSION_PATH, $settings);
            $madelineProto->start();

            $result = $madelineProto->messages->sendMessage(
                [
                    'peer'       => CHANNEL_TOP_AZERI_POST,
                    'message'    => $channelPost,
                    'parse_mode' => 'HTML',
                ]
            );

            if(isset($result['updates'][0]['id']))
                echo "New Post ID: ".$result['updates'][0]['id']."\n";
        }
        catch (\Throwable $exception)
        {
            echo $exception->getCode()."\n";
            echo $exception->getMessage()."\n";
        }
    }

    public function actionPostNotNewsWeeklyMessage(): void
    {
        try {
            $channelPost = (new TopMessage())->getMessagePost(TopMessage::TYPE_NOT_NEWS_WEEKLY);

            $settings['app_info']['api_id'] = APP_API_ID;
            $settings['app_info']['api_hash'] = APP_API_HASH;
            $madelineProto = new API(MADELINE_SESSION_PATH, $settings);
            $madelineProto->start();

            $result = $madelineProto->messages->sendMessage(
                [
                    'peer'       => CHANNEL_TOP_AZERI_POST,
                    'message'    => $channelPost,
                    'parse_mode' => 'HTML',
                ]
            );

            if(isset($result['updates'][0]['id']))
                echo "New Post ID: ".$result['updates'][0]['id']."\n";
        }
        catch (\Throwable $exception)
        {
            echo $exception->getCode()."\n";
            echo $exception->getMessage()."\n";
        }
    }
}