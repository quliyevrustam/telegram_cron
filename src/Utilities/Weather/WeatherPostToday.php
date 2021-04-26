<?php

namespace Utilities\Weather;

use Utilities\HtmlFormat;

class WeatherPostToday extends AbstractWeatherPostByDay
{
    public $weatherDataKey = 0;

    /**
     * @return string
     */
    protected function getPostHeader(): string
    {
        return HtmlFormat::makeBold('Погода на сегодня ('.$this->weatherData['day'].'):');
    }

    /**
     * @return string
     */
    protected function getChanel(): string
    {
        return CHANNEL_BAKU_WEATHER_TODAY;
    }
}