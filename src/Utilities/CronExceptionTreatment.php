<?php

namespace Utilities;

use danog\MadelineProto\RPCErrorException;
use Model\Channel\Channel;
use Model\Channel\ChannelFound;

class CronExceptionTreatment
{
    private $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;

        Helper::logError($exception->getMessage());
    }

    public function execution(int $channelId): void
    {
        if($this->exception instanceof RPCErrorException)
        {
            echo $this->exception->getCode()."\n";
            echo $this->exception->getMessage()."\n";
            if(
                $this->exception->getCode() == 400 &&
                in_array($this->exception->getMessage(), ['CHANNEL_INVALID', 'CHANNEL_PRIVATE']))
            {
                (new Channel())->delete($channelId, $this->exception->getMessage());
            } elseif (
                $this->exception->getCode() == 420 &&
                str_starts_with($this->exception->getMessage(), 'FLOOD_WAIT'))
            {
                $seconds = (int) str_replace('FLOOD_WAIT_', '', $this->exception->getMessage());
                if($seconds > 0)
                {
                    (new Channel())->sleep($channelId, $seconds);
                }
            }
        }
    }

    public function executionChannelFound(int $channelId): void
    {
        if($this->exception instanceof RPCErrorException)
        {
            echo $this->exception->getCode()."\n";
            echo $this->exception->getMessage()."\n";
            if(
                $this->exception->getCode() == 400 &&
                in_array($this->exception->getMessage(), ['CHANNEL_INVALID', 'CHANNEL_PRIVATE', 'USERNAME_INVALID']))
            {
                (new ChannelFound())->delete($channelId, $this->exception->getMessage());
            } elseif (
                $this->exception->getCode() == 420 &&
                str_starts_with($this->exception->getMessage(), 'FLOOD_WAIT'))
            {
                $seconds = (int) str_replace('FLOOD_WAIT_', '', $this->exception->getMessage());
                if($seconds > 0)
                {
                    (new ChannelFound())->sleep($channelId, $seconds);
                }
            }
        }
    }
}