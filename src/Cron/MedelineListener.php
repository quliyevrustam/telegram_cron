<?php

namespace Cron;

use Utilities\Cron;

class MedelineListener extends Cron
{
    public function actionUpdateErr(): void
    {
        $message = $this->model(\Model\Channel\Message::class);
        $message->updateErr();
    }
}