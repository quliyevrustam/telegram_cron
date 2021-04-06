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
        $curl = curl_init();

        if($curl)
        {
            echo 'https://api.telegram.org/bot'.BOT_KEY.'/'.$method.($type == 'get' ? '?'.http_build_query($data) : '');

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
}