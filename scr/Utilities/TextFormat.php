<?php

namespace Utilities;

class TextFormat
{
    public static function makeBold(string $text): string
    {
        return '*'.$text.'*';
    }

    public static function makeItalic(string $text): string
    {
        return '_'.$text.'_';
    }

    public static function makeUrl(string $text, string $url): string
    {
        return '['.$text.']'.'('.$url.')';
    }

    public static function makeInlineCode(string $text): string
    {
        return '`'.$text.'`';
    }

    public static function makeCodeBlock(string $text): string
    {
        return '```'.$text.'```';
    }
}