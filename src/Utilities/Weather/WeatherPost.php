<?php


namespace Utilities\Weather;


class WeatherPost
{
    const DAILY_WEATHER_TODAY = 0;
    const DAILY_WEATHER_TOMORROW = 1;

    public function __construct(int $type)
    {
        switch ($type)
        {
            case self::DAILY_WEATHER_TODAY:
                return new WeatherPostToday();
            case self::DAILY_WEATHER_TOMORROW:
                return new WeatherPostTomorrow();
        }
    }
}