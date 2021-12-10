<?php

namespace Cron;

use Utilities\Cron;

class Message extends Cron
{
    public function actionUpdateErr(): void
    {
        (new \Model\Channel\Message())->updateErr();
    }
}