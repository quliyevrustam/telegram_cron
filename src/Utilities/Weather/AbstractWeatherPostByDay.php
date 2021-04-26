<?php

namespace Utilities\Weather;

use Exception;
use Utilities\Helper;
use Utilities\HtmlFormat;
use Telegram\Bot\Api;

abstract class AbstractWeatherPostByDay
{
    protected $weatherData;
    protected $weatherDataKey;
    protected $post;

    /**
     * @return string
     */
    abstract protected function getPostHeader(): string;

    abstract protected function getChanel(): string;

    /**
     * @return string
     * @throws Exception
     */
    public function createPost()
    {
        $this->getWeatherData();

        $this->post = $this->getPostHeader();
        $this->post .= "\n"."\n";
        $this->post .= $this->getPostBody();

        return $this->post;
    }

    /**
     * @throws Exception
     */
    private function getWeatherData(): void
    {
        $url = "https://api.openweathermap.org/data/2.5/onecall?lat=40.3777&lon=49.892&exclude=current,minutely,hourly,alerts&appid=".WEATHER_API_ID."&lang=ru&units=metric";
        $result = Helper::curlRequest($url);

        $weatherData = [];
        if($result && isset($result["daily"]) && isset($result["daily"][$this->weatherDataKey]))
        {
            $resultTomorrow = $result["daily"][$this->weatherDataKey];

            $weatherData['day'] = date('d/m/Y', $resultTomorrow["dt"]);
            $weatherData['sunrise'] = date('Y-m-d H:i:s', $resultTomorrow["sunrise"]);
            $weatherData['sunset']  = date('Y-m-d H:i:s', $resultTomorrow["sunset"]);

            $weatherData['temp']        = $resultTomorrow["temp"];
            $weatherData['feels_like']  = $resultTomorrow["feels_like"];
            $weatherData['description'] = $resultTomorrow["weather"][0]["description"];
        }

        $this->weatherData = $weatherData;
    }

    /**
     * @return string
     */
    private function getPostBody(): string
    {
        $post = '';
        $post .= 'Температура: '.HtmlFormat::makeBold(round(($this->weatherData['feels_like']['day']))).HtmlFormat::makeCode('°');
        $post .= "\n";
        $post .= 'Состояние погоды: '.HtmlFormat::makeBold($this->weatherData['description']);
        $post .= "\n"."\n";
        $post .= HtmlFormat::makeBold('В течении дня:');
        $post .= "\n";
        $post .= 'Утром: '.HtmlFormat::makeBold(round(($this->weatherData['feels_like']['morn']))).HtmlFormat::makeCode('°');
        $post .= "\n";
        $post .= 'Днем: '.HtmlFormat::makeBold(round(($this->weatherData['feels_like']['day']))).HtmlFormat::makeCode('°');
        $post .= "\n";
        $post .= 'Вечером: '.HtmlFormat::makeBold(round(($this->weatherData['feels_like']['eve']))).HtmlFormat::makeCode('°');
        $post .= "\n";
        $post .= 'Ночью: '.HtmlFormat::makeBold(round(($this->weatherData['feels_like']['night']))).HtmlFormat::makeCode('°');
        $post .= "\n"."\n";
        $post .= 'Рассвет: '.HtmlFormat::makeBold(Helper::timezoneConverter($this->weatherData['sunrise'], 'UTC', 'Asia/Baku', 'H:i'));
        $post .= "\n";
        $post .= 'Закат: '.HtmlFormat::makeBold(Helper::timezoneConverter($this->weatherData['sunset'], 'UTC', 'Asia/Baku', 'H:i'));

        return $post;
    }

    public function sendPost()
    {
        $telegram = new Api(BOT_KEY);
        $telegram->sendMessage([
            'chat_id'   => $this->getChanel(),
            'text'      => $this->post,
            'parse_mode'=> 'HTML'
        ]);
    }
}