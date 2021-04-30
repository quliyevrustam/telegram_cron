<?php

namespace Controller\Message;

use Controller\MainController;
use Model\Channel\Message;
use Utilities\Helper;

class MessageController extends MainController
{
    public function showMessageList()
    {
        $messageRecords = (new Message())->getMessageList($this->getPagination());

        $messageTable['records'] = $messageRecords;
        $messageTable['meta'] = $this->getMeta();

        return $this->tmp()->render('Message/message_list.html', ['message_table' => $messageTable]);
    }
}