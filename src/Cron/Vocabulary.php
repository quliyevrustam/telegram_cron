<?php

namespace Cron;

use Model\Cycle\AzeriVocabulary;
use Utilities\Cron;
use danog\MadelineProto\API;

class Vocabulary extends Cron
{
    public function actionPostWord(): void
    {
        try {
            $vocabulary = new AzeriVocabulary();
            $channelPost = $vocabulary->getRandomPost();

            $settings['app_info']['api_id'] = APP_API_ID;
            $settings['app_info']['api_hash'] = APP_API_HASH;
            $madelineProto = new API(MADELINE_SESSION_PATH, $settings);
            $madelineProto->start();

            $result = $madelineProto->messages->sendMessage(
                [
                    'peer'       => CHANNEL_KEY_AZERI_WORD,
                    'message'    => $channelPost,
                    'parse_mode' => 'Markdown'
                ]
            );

            if(isset($result['updates'][0]['id']))
            {
                echo "New Post ID: ".$result['updates'][0]['id']."\n";
                $vocabulary->setShowed($vocabulary->ids);
            }
        }
        catch (\Throwable $exception)
        {
            echo $exception->getCode()."\n";
            echo $exception->getMessage()."\n";
        }
    }
}