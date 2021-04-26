<?php

namespace Utilities\Weather;

use Utilities\HtmlFormat;

class WeatherPostTomorrow extends AbstractWeatherPostByDay
{
    public $weatherDataKey = 1;

    /**
     * @return string
     */
    protected function getPostHeader(): string
    {
        return HtmlFormat::makeBold('Погода на завтра ('.$this->weatherData['day'].'):');
    }

    /**
     * @return string
     */
    protected function getChanel(): string
    {
        return CHANNEL_BAKU_WEATHER_TOMORROW;
    }
}