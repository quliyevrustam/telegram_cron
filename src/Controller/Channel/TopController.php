<?php

namespace Controller\Channel;

use Controller\MainController;
use Model\Channel\Message;

class TopController extends MainController
{
    public function showTop()
    {
        $top = $this->model(Message::class)->getTop();

        return $this->tmp()->render('Channel/Top/view.html', ['top' => $top]);
    }
}
