<?php

namespace Cron;

use danog\MadelineProto\RPCErrorException;
use Exception;
use Model\Channel\ChannelFound;
use Utilities\Cron;

if (!file_exists( MADELINE_PATH)) {
    copy('https://phar.madelineproto.xyz/madeline.php', MADELINE_PATH);
}
include MADELINE_PATH;

use danog\MadelineProto\API;
use Utilities\CronExceptionTreatment;

class TelegramListener extends Cron
{
    private $foundChannels = [];

    public function actionImportMessage(): void
    {
        $settings['app_info']['api_id'] = APP_API_ID;
        $settings['app_info']['api_hash'] = APP_API_HASH;
        $madelineProto = new API(MADELINE_SESSION_PATH, $settings);
        //$madelineProto->start();

        $channels = (new \Model\Channel\Channel())->getChannelPeers('check_message');
        $channelMessage = $this->model(\Model\Channel\Message::class);

        $limit = 25;

        //$channels = [4 => 'nataosmanli'];
        foreach ($channels as $channelId => $peer)
        {
            echo $channelId.' => '.$peer."\n";
            try {
                $request = $madelineProto->messages->getHistory([
                        'peer'        => $peer,
                        'offset_id'   => 0,
                        'offset_date' => 0,
                        'add_offset'  => 0,
                        'limit'       => $limit,
                        'max_id'      => 0,
                        'min_id'      => 0,
                        'hash'        => 0
                ]);
                if (count($request['messages']) > 0)
                {
                    $this->ImportMessage($channelMessage, $channelId, $request['messages']);
                }
            } catch (\Throwable $e)
            {
                (new CronExceptionTreatment($e))->execution(['channel_id' => $channelId]);
            }
        }
    }

    private function ImportMessage(\Model\Channel\Message $channelMessage, int $channelId, array $messages): void
    {
        foreach ($messages as $message)
        {
            $replyCount = (isset($message['replies']['replies'])) ? $message['replies']['replies'] : 0;
            $body = (isset($message['message'])) ? mb_substr($message['message'], 0, 100) . "..." : '';
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

    /**
     * @throws Exception
     */
    public function actionFindNewChannel(): void
    {
        $settings['app_info']['api_id'] = APP_API_ID;
        $settings['app_info']['api_hash'] = APP_API_HASH;
        $madelineProto = new API(MADELINE_SESSION_PATH, $settings);
        //$madelineProto->start();

        $channels = (new \Model\Channel\Channel())->getChannelPeers('check_message');

        $limit = 10;

        //$channels = [4 => 'nataosmanli'];
        foreach ($channels as $channelId=>$peer)
        {
            $request = $madelineProto->messages->getHistory(['peer' => $peer, 'offset_id' => 0, 'offset_date' => 0, 'add_offset' => 0, 'limit' => $limit, 'max_id' => 0, 'min_id' => 0, 'hash' => 0 ]);
            if(count($request['messages']) > 0)
            {
                foreach ($request['messages'] as $message)
                {
                    if (isset($message['message'])) $this->findTelegramChannelFromMessage($message['message']);
                }
            }
        }

        $channelFound = new ChannelFound();
        $channelFoundPeers = $channelFound->getPeers();

        //Helper::prePrint($channelFoundPeers);

        //Helper::prePrint($this->foundChannels);
        //Helper::prePrint($channels);

        $newChannels = array_udiff($this->foundChannels, $channels, $channelFoundPeers, "strcasecmp");

        if(count($newChannels) > 0)
        {
            $channelFound->add($newChannels);
        }

        //Helper::prePrint($newChannels);
    }

    /**
     * @param string $message
     */
    private function findTelegramChannelFromMessage(string $message)
    {
        preg_match_all('/(http:\/\/|https:\/\/)?(www)?t.me\/([A-Za-z0-9_]*)*\/?/i', $message, $channels);
        if(isset($channels[0]) && count($channels[0]) > 0)
        {
            foreach ($channels[0] as $channel)
            {
                $channel = str_replace(['https://t.me/', 't.me/', '/'], '', $channel);
                if(mb_strlen($channel) >= 3 and mb_strtolower(substr($channel, -3)) != 'bot') $this->foundChannels[$channel] = $channel;
            }
        }

        preg_match_all('/@([A-Za-z0-9_]*)*\/?/i', $message, $channels);
        if(isset($channels[0]) && count($channels[0]) > 0)
        {
            foreach ($channels[0] as $channel)
            {
                $channel = str_replace(['@'], '', $channel);
                if(mb_strlen($channel) >= 3 and mb_strtolower(substr($channel, -3)) != 'bot') $this->foundChannels[$channel] = $channel;
            }
        }
    }
}