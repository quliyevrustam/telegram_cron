<?php

namespace Utilities;

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

    public static function curlRequest ($method, $type, $data = [], bool $returnJson = true)
    {
        // api.telegram speed is 1 request in 2 seconds
        sleep(2);

        $curl = curl_init();

        if($curl)
        {
            //echo 'https://api.telegram.org/bot'.BOT_KEY.'/'.$method.($type == 'get' ? '?'.http_build_query($data) : '');

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
            curl_close($curl);

            if($returnJson !== true)
                $out = json_decode($out, true);

            if(empty ($out) || !$out) return false;

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

        return $clear_string;
    }

    public static function timezoneConverter($date, $timezoneFrom, $timezoneTo, $format = 'Y-m-d H:i:s')
    {
        $handler = new \DateTime($date, new \DateTimeZone($timezoneFrom));
        $handler->setTimezone(new \DateTimeZone($timezoneTo));
        return $handler->format($format);
    }
}