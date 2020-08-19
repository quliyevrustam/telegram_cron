<?php


namespace Utilities;


class Helper
{
    public static function prePrint($expression): void
    {
        echo '<pre>';
        print_r($expression);
        echo '</pre>';
    }
}