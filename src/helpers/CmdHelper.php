<?php

namespace mmaurice\cabinet\core\helpers;

use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;

class CmdHelper
{
    public static function textColor($color, $text)
    {
        return (new ConsoleColor)->apply($color, $text);
    }

    public static function logLine($string = '')
    {
        return self::drawLine(self::textColor('white', date('Y-m-d H:i:s') . ' > ') . $string);
    }

    public static function drawLine($string = '')
    {
        return self::drawString($string . PHP_EOL);
    }

    public static function drawString($string = '')
    {
        echo $string;

        return true;
    }
}
