<?php

namespace Controller\Channel;

use Controller\MainController;
use Model\Channel\Channel;
use Model\Channel\Message;
use Utilities\Helper;

class ChannelController extends MainController
{
    public function showChannel(int $id)
    {
        $channel = (new Channel())->getChannel($id);

        return $this->tmp()->render('Channel/channel.html', ['channel' => $channel]);
    }
}