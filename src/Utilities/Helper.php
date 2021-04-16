<?php

namespace Utilities;

use Exception;

class Helper
{
    public static function prePrint($expression): void
    {
        echo '--------------------';
        echo '<pre>';
        print_r($expression);
        echo '</pre>';
        echo '--------------------';
    }

    /**
     * @param $method
     * @param $type
     * @param array $data
     * @param bool $returnJson
     * @return mixed|bool|string
     * @throws Exception
     */
    public static function curlRequest ($method, $type, $data = [])
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

            if(isset($out['ok']) && $out['ok'] == false && isset($out['description'])) $errorMsg = $out['description'];

            if (isset($errorMsg)) throw new Exception($errorMsg);

            return $out;
        }
        else
            return false;
    }

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

    public static function timezoneConverter($date, $timezoneFrom, $timezoneTo, $format = 'Y-m-d H:i:s')
    {
        $handler = new \DateTime($date, new \DateTimeZone($timezoneFrom));
        $handler->setTimezone(new \DateTimeZone($timezoneTo));
        return $handler->format($format);
    }

    public static function getCurrentDayBegin(): string
    {
        return Helper::timezoneConverter(date('Y-m-d 00:00:00'), 'Asia/Baku', 'UTC');
    }
}