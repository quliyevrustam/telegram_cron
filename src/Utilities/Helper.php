<?php

namespace Utilities;

use Exception;
use JetBrains\PhpStorm\Pure;

class Helper
{
    const API_RESULT_CHAT_NOT_FOUND = 2;

    const TABLE_FIELD_SYMBOL_LIMIT = 50;
    const TABLE_FIELD_DATE_FORMAT = 'H:i d/m/Y';

    /**
     * @param $expression
     */
    public static function prePrint($expression): void
    {
        echo "\n";
        echo '--------------------';
        echo '<pre>';
        print_r($expression);
        echo '</pre>';
        echo '--------------------';
        echo "\n";
    }

    /**
     * @param $method
     * @param $type
     * @param array $data
     * @return false|mixed
     * @throws Exception
     */
    public static function curlTelegramBotRequest ($method, $type, $data = [])
    {
        // api.telegram speed is 1 request in 5 seconds
        sleep(5);

        $curl = curl_init();

        if($curl)
        {
            //echo 'https://api.telegram.org/bot'.BOT_KEY.'/'.$method.($type == 'get' ? '?'.http_build_query($data) : '')."\n";

            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot'.BOT_KEY.'/'.$method.($type == 'get' ? '?'.http_build_query($data) : ''));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);

            if ($type == 'post')
            {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $out = curl_exec($curl);

            if (curl_errno($curl)) $errorMsg = curl_error($curl);

            curl_close($curl);

            $out = json_decode($out, true);

            if(empty ($out) || !$out) return false;

            if(isset($out['error_code']) && $out['error_code'] == 400) $out['ok'] = self::API_RESULT_CHAT_NOT_FOUND;

            if(isset($out['ok']) && $out['ok'] == false && isset($out['description'])) $errorMsg = $out['description'];
            if (isset($errorMsg)) throw new Exception($errorMsg);

            return $out;
        }
        else
            return false;
    }

    /**
     * @param string|null $string
     * @return string
     */
    public static function removeEmoji(?string $string = null) : string
    {
        if (empty ($string)) return '';

        // Match Emoticons
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $string);

        // Match Miscellaneous Symbols and Pictographs
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);

        // Match Transport And Map Symbols
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);

        // Match Miscellaneous Symbols
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);

        // Match Dingbats
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);

        $clear_string = preg_replace('%(?:
              \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
        )%xs', '', $clear_string);

        return trim($clear_string);
    }

    /**
     * @param $date
     * @param $timezoneFrom
     * @param $timezoneTo
     * @param string $format
     * @return string
     * @throws Exception
     */
    public static function timezoneConverter($date, $timezoneFrom, $timezoneTo, $format = 'Y-m-d H:i:s')
    {
        $handler = new \DateTime($date, new \DateTimeZone($timezoneFrom));
        $handler->setTimezone(new \DateTimeZone($timezoneTo));
        return $handler->format($format);
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getCurrentDayBegin(): string
    {
        return Helper::timezoneConverter(date('Y-m-d 00:00:00'), 'Asia/Baku', 'UTC');
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getWeekBegin(): string
    {
        return Helper::timezoneConverter(date('Y-m-d 00:00:00', strtotime('sunday -1 week')), 'Asia/Baku', 'UTC');
    }

    /**
     * @param string $errorMessage
     */
    public static function logError(string $errorMessage): void
    {
        error_log('['.date('Y-m-d H:i:s').'] '.$errorMessage."\n\n", 3, ERROR_LOG_PATH);
    }

    /**
     * @param $url
     * @param string $type
     * @param array $data
     * @return false|mixed
     * @throws Exception
     */
    public static function curlRequest ($url, $type = 'get', $data = [])
    {
        $curl = curl_init();

        if($curl)
        {
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);

            if ($type == 'post')
            {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $out = curl_exec($curl);

            if (curl_errno($curl)) $errorMsg = curl_error($curl);

            curl_close($curl);

            $out = json_decode($out, true);

            if(empty ($out) || !$out) return false;

            if (isset($errorMsg)) throw new Exception($errorMsg);

            return $out;
        }
        else
            return false;
    }

    /**
     * @param string|null $text
     * @param int $limit
     * @return string|null
     */
    #[Pure]
    public static function textPublicFormat(?string $text, $limit = self::TABLE_FIELD_SYMBOL_LIMIT): ?string
    {
        if(mb_strlen($text) > $limit)
        {
            $text = mb_substr($text, 0, $limit);
            if(!empty($text) && $text <> '...') $text .= '...';
        }

        return $text;
    }

    /**
     * @param string $datetime
     * @return string
     * @throws Exception
     */
    public static function DateTimePublicFormat(string $datetime): string
    {
        return (new \DateTime($datetime))->format(self::TABLE_FIELD_DATE_FORMAT);
    }
}