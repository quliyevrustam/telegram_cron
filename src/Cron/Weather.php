<?php

namespace Cron;

use Exception;
use Utilities\Cron;
use Utilities\Weather\WeatherPostToday;
use Utilities\Weather\WeatherPostTomorrow;

class Weather extends Cron
{
    /**
     * @throws Exception
     */
    public function actionPostWeatherToday(): void
    {
        $weatherPost = new WeatherPostToday();
        $weatherPost->createPost();
        $weatherPost->sendPost();
    }

    /**
     * @throws Exception
     */
    public function actionPostWeatherTomorrow(): void
    {
        $weatherPost = new WeatherPostTomorrow();
        $weatherPost->createPost();
        $weatherPost->sendPost();
    }
}