<?php

namespace Cron;

use Utilities\Cron;
use Utilities\Helper;

if (!file_exists( 'public/madeline/session/madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', 'public/madeline/session/madeline.php');
}
include 'public/madeline/session/madeline.php';

class TelegramListener extends Cron
{
    public function actionImportMessage(): void
    {
        try {
            $settings['app_info']['api_id'] = APP_API_ID;
            $settings['app_info']['api_hash'] = APP_API_HASH;
            $madelineProto = new \danog\MadelineProto\API(APP_PATH.'/public/madeline/session/cron.telegram.session.madeline',$settings);
            //$madelineProto->start();

            $channels = $this->model(\Model\Channel\Channel::class)->getChannelPeers();
            $channelMessage = $this->model(\Model\Channel\Message::class);

            $offset_id = 0;
            $limit = 1;

            foreach ($channels as $channelId=>$peer)
            {
                $request = $madelineProto->messages->getHistory(['peer' => $peer, 'offset_id' => $offset_id, 'offset_date' => 0, 'add_offset' => 0, 'limit' => $limit, 'max_id' => 0, 'min_id' => 0, 'hash' => 0 ]);
                if(count($request['messages']) > 0)
                {
                    foreach ($request['messages'] as $message)
                    {
                        $replyCount = (isset($message['replies']['replies'])) ? $message['replies']['replies'] : 0;
                        $body = (isset($message['message'])) ? mb_substr($message['message'], 0, 100)."..." : '';
                        $date = date('Y-m-d H:i:s', $message['date']);
                        $viewCount = (isset($message['views'])) ? $message['views'] : 0;
                        $forwardCount = (isset($message['forwards'])) ? $message['forwards'] : 0;
                        $groupedId = (isset($message['grouped_id'])) ? $message['grouped_id'] : 0;
                        $isGrouped = $groupedId == 0 ? \Model\Channel\Message::MESSAGE_SINGLE : \Model\Channel\Message::MESSAGE_GROUPED;

                        $channelMessageBody = [
                            'external_id'   => $message['id'],
                            'channel_id'    => $channelId,
                            'view_count'    => $viewCount,
                            'forward_count' => $forwardCount,
                            'reply_count'   => $replyCount,
                            'body'          => $body,
                            'created_at'    => $date,
                            'grouped_id'    => $groupedId,
                            'is_grouped'    => $isGrouped,
                        ];

                        $channelMessage->update($channelMessageBody);
                    }
                }
            }
        }
        catch (\danog\MadelineProto\Exception $e) {
            echo $e->getMessage();
        }
    }
}