<?php

namespace Utilities;

use danog\MadelineProto\Exception as danogException;
use danog\MadelineProto\RPCErrorException;
use Exception;
use Model\Channel\Channel;
use Model\Channel\ChannelFound;
use Model\MainModel;
use Throwable;

class CronExceptionTreatment
{
    private $exception;

    /**
     * CronExceptionTreatment constructor.
     * @param Throwable $exception
     */
    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;

        Helper::logError($exception->getMessage());
    }

    /**
     * @param MainModel|null $model
     * @throws Exception
     */
    public function execution(MainModel $model = null): void
    {
        echo get_class($this->exception)."\n";
        echo $this->exception->getCode()."\n";
        echo $this->exception->getMessage()."\n";

        if($this->exception instanceof RPCErrorException)
        {
            if(
                $this->exception->getCode() == 400 &&
                in_array($this->exception->getMessage(), ['CHANNEL_INVALID', 'CHANNEL_PRIVATE', 'USERNAME_INVALID']))
            {
                $model->delete($this->exception->getMessage());
            }
            elseif (
                $this->exception->getCode() == 420 &&
                str_starts_with($this->exception->getMessage(), 'FLOOD_WAIT'))
            {
                $seconds = (int) str_replace('FLOOD_WAIT_', '', $this->exception->getMessage());
                if($seconds > 0)
                {
                    $model->sleep($seconds);
                }
            }
        }
        elseif($this->exception instanceof danogException)
        {
            if(
                $this->exception->getCode() == 0 &&
                $this->exception->getMessage() == 'This peer is not present in the internal peer database')
            {
                $model->delete($this->exception->getMessage());
            }
        }
    }
}