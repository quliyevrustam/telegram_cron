<?php

namespace Controller\Channel;

use Controller\MainController;
use Model\Channel\Channel;
use Model\Channel\ChannelFound;
use Model\Channel\Message;
use Utilities\Helper;

class ChannelController extends MainController
{
    public function showChannel(int $id)
    {
        $channel = (new Channel())->getChannel($id);

        return $this->tmp()->render('Channel/channel.html', ['channel' => $channel]);
    }

    public function showFoundChannelList()
    {
        $channelCondition    = $this->http()->query->get('condition', ChannelFound::CONDITION_NOT_CHECKED);

        $filter = [
            'condition'   => $channelCondition,
        ];

        $foundChannels = (new ChannelFound())->getChannelFoundList($this->getPagination(), $filter);

        $foundChannelTable['records'] = $foundChannels['records'];
        $foundChannelTable['meta'] = $this->getPaginationMeta();
        $foundChannelTable['meta']['total'] = $foundChannels['total'];
        $foundChannelTable['meta']['condition'] = $channelCondition;

        return $this->tmp()->render('Channel/found_channel_table.html', ['found_channel_table' => $foundChannelTable]);
    }

    public function showFoundChannel(int $id)
    {
        $makeCondition = $this->http()->query->get('make_condition');
        if(!is_null($makeCondition))
        {
            (new ChannelFound())->edit($id, ['condition' => $makeCondition]);
        }

        $channel = (new ChannelFound())->getChannel($id);

        return $this->tmp()->render('Channel/found_channel.html', ['channel' => $channel]);
    }
}