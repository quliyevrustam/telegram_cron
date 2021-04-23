<?php

namespace Utilities;

class HtmlFormat
{
    public static function makeBold(string $text): string
    {
        return '<b>'.$text.'</b>';
    }

    public static function makeItalic(string $text): string
    {
        return '<i>'.$text.'</i>';
    }

    public static function makeCode(string $text): string
    {
        return '<code>'.$text.'</code>';
    }
}