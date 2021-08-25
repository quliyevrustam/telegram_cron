<?php

namespace Utilities;

use danog\MadelineProto\RPCErrorException;
use Model\Channel\Channel;

class CronExceptionTreatment
{
    private $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;

        Helper::logError($exception->getMessage());
    }

    public function execution(array $data): void
    {
        if($this->exception instanceof RPCErrorException)
            if(
                $this->exception->getCode() == 400 &&
                $this->exception->getMessage() == 'CHANNEL_PRIVATE' &&
                isset($data['channel_id']) && is_numeric($data['channel_id']))
            {
                (new Channel())->delete($data['channel_id'], 'CHANNEL_PRIVATE');
            }
    }
}