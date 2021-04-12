<?php

require_once dirname(__DIR__ ). '/vendor/autoload.php';
require_once (dirname(__DIR__ ).'/config/di.config.php');

if (!file_exists(dirname(__DIR__) . '/cron/madeline/session/madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', dirname(__DIR__) . '/cron/madeline/session/madeline.php');
}
include dirname(__DIR__) . '/cron/madeline/session/madeline.php';

try {
    $settings['app_info']['api_id'] = APP_API_ID;
    $settings['app_info']['api_hash'] = APP_API_HASH;
    $madelineProto = new \danog\MadelineProto\API('madeline/session/telegram.session.madeline',$settings);
    $madelineProto->start();

    $channels = [
        'azeri_word_by_day',
        'jurefuck',
        'zangezuravtonomia',
        'voice_of_turkey',
        'nataosmanli',
        'dayaz',
        'operativmm',
    ];
    $offset_id = 0;
    $limit = 1;

    foreach ($channels as $channel)
    {
        $request = $madelineProto->messages->getHistory(['peer' => $channel, 'offset_id' => $offset_id, 'offset_date' => 0, 'add_offset' => 0, 'limit' => $limit, 'max_id' => 0, 'min_id' => 0, 'hash' => 0 ]);
        if(count($request['messages']) > 0)
        {
            foreach ($request['messages'] as $message)
            {
                $replyCount = (isset($message['replies']['replies'])) ? $message['replies']['replies'] : 0;

                echo 'channel '.$channel."\n";
                echo 'id '.$message['id']."\n";
                echo 'date '.$message['date']."\n";
                echo 'views '.$message['views']."\n";
                echo 'forwards '.$message['forwards']."\n";
                echo 'replies '.$replyCount."\n";
                echo 'message '.mb_substr($message['message'], 0, 100)."..."."\n";

                echo "\n"."\n";

                //print_r($message);
            }
        }
    }
}
catch (\danog\MadelineProto\Exception $e) {
    echo $e->getMessage();
}