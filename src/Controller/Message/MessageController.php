<?php

namespace Controller\Message;

use Controller\MainController;
use Exception;
use Model\Channel\Channel;
use Model\Channel\Message;
use Utilities\Helper;

class MessageController extends MainController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function showMessageList()
    {
        $beginDate      = $this->http()->query->get('begin_date', date('Y-m-d'));
        $endDate        = $this->http()->query->get('end_date', date('Y-m-d'));
        $channelType    = $this->http()->query->get('channel_type', Channel::TYPE_ALL);
        $channelId      = $this->http()->query->get('channel_id', Channel::TYPE_ALL);

        $filter = [
            'begin_date'   => $beginDate,
            'end_date'     => $endDate,
            'channel_type' => $channelType,
            'channel_id'   => $channelId,
        ];

        $messageRecords = (new Message())->getMessageList($this->getPagination(), $filter);

        $messageTable['records'] = $messageRecords['records'];
        $messageTable['meta'] = $this->getPaginationMeta();
        $messageTable['meta']['begin_date'] = $beginDate;
        $messageTable['meta']['end_date'] = $endDate;
        $messageTable['meta']['channel_type'] = $channelType;
        $messageTable['meta']['total'] = $messageRecords['total'];
        $messageTable['meta']['channel_names'] = (new Channel())->getChannelNames();
        $messageTable['meta']['channel_id'] = $channelId;

        return $this->tmp()->render('Message/message_table.html', ['message_table' => $messageTable]);
    }
}