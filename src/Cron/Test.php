<?php

namespace Cron;

use Utilities\Cron;

class Test extends Cron
{
    public function actionShowTest(): void
    {
        echo "It's alive!"."\n";
    }
}