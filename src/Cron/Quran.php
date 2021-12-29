<?php

namespace Cron;

use Utilities\Cron;
use danog\MadelineProto\API;

class Quran extends Cron
{
    public function actionPostAyah(): void
    {
        try
        {
            $ayah = new \Model\Cycle\Quran();
            $channelQuote = $ayah->getRandomQuote();

            $settings['app_info']['api_id'] = APP_API_ID;
            $settings['app_info']['api_hash'] = APP_API_HASH;
            $madelineProto = new API(MADELINE_SESSION_PATH, $settings);
            $madelineProto->start();

            $result = $madelineProto->messages->sendMessage(
                [
                    'peer'       => CHANNEL_KEY_DAILY_AYAH,
                    'message'    => $channelQuote,
                    'parse_mode' => 'HTML',
                ]
            );

            if (isset($result['updates'][0]['id']))
            {
                echo "New Post ID: " . $result['updates'][0]['id'] . "\n";
                $ayah->setShowed();
            }
        }
        catch (\Throwable $exception)
        {
            echo $exception->getCode() . "\n";
            echo $exception->getMessage() . "\n";
        }
    }
}