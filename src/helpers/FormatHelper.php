<?php

namespace mmaurice\cabinet\core\helpers;

use DateTime;

class FormatHelper
{
    // Отрезать все символы слева и оставить $length символов справа
    public static function trimToLengthRight($string, $length, $format = '%s')
    {
        $string = mb_substr((string) $string, strlen($string) - $length, strlen($string));

        return sprintf($format, $string);
    }

    // Отрезать все символы справа и оставить $length символов слева
    public static function trimToLengthLeft($string, $length, $format = '%s')
    {
        $string = mb_substr((string) $string, 0, $length);

        return sprintf($format, $string);
    }

    public static function trimToLengthInside($string, $lengthBefore, $lengthAfter = null, $format = '%s')
    {
        if (is_null($lengthAfter)) {
            $lengthAfter = $lengthBefore;
        }

        $string = self::trimToLengthRight($string, strlen($string) - $lengthBefore);
        $string = self::trimToLengthLeft($string, strlen($string) - $lengthAfter);

        return sprintf($format, $string);
    }

    public static function trimToLengthOutside($string, $lengthBefore, $lengthAfter = null, $format = '%s %s')
    {
        if (is_null($lengthAfter)) {
            $lengthAfter = $lengthBefore;
        }

        $stringBefore = self::trimToLengthLeft($string, $lengthBefore);
        $stringAfter = self::trimToLengthRight($string, $lengthAfter);

        return sprintf($format, $stringBefore, $stringAfter);
    }

    public static function dateConvert($date, $formatFrom = 'Y-m-d H:i:s', $formatTo = 'H:i d.m.Y')
    {
        if (is_string($date) and !empty($date)) {
            $dateTime = DateTime::createFromFormat($formatFrom, $date);

            if ($dateTime instanceof DateTime) {
                return $dateTime->format($formatTo);
            }
        }

        return null;
    }

    public static function dateCorrect($date, $modifier, $format = 'Y-m-d H:i:s')
    {
        if (is_string($modifier)) {
            preg_match('/([\+\-]*)(\d+)\s*(s|m|h|d)/i', $modifier, $matches);

            if (is_array($matches) and !empty($matches)) {
                $time = intval($matches[2]);

                if (empty($matches[1])) {
                    $matches[1] = '+';
                }

                switch ($matches[3]) {
                    case 's':
                        $modifier = $time;
                        break;
                    case 'm':
                        $modifier = $time * 60;
                        break;
                    case 'h':
                        $modifier = $time * 60 * 60;
                        break;
                    case 'd':
                        $modifier = $time * 60 * 60 * 24;
                        break;
                    default:
                        break;
                }

                if ($matches[1] === '-') {
                    $modifier = 0 - $modifier;
                }
            }
        }

        $timestamp = self::dateToTimestampConvert($date, $format);

        return self::timestampToDateConvert($timestamp + $modifier, $format);
    }

    public static function toRus($date)
    {
        return str_replace([
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
            'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec',
        ], [
            'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек',
            'янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек',
        ], $date);
    }

    public static function declOfNum($number, $titles)
    {
        $cases = array(2, 0, 1, 1, 1, 2);

        $format = $titles[($number % 100 > 4 and $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];

        return sprintf($format, $number);
    }

    public static function numFormat($num, $format = '%s', $decimals = 2, $decPoint = '.', $thousandsSep = '')
    {
        $num = number_format((float) $num, (int) $decimals, (string) $decPoint, (string) $thousandsSep);

        return sprintf($format, $num);
    }

    public static function dateToTimestampConvert($date, $formatFrom = 'Y-m-d H:i:s')
    {
        $dateTime = DateTime::createFromFormat($formatFrom, $date);

        if ($dateTime instanceof DateTime) {
            return intval($dateTime->getTimestamp());
        }

        return null;
    }

    public static function timestampToDateConvert($timestamp, $formatTo = 'H:i d.m.Y')
    {
        return date($formatTo, $timestamp);
    }

    public static function dateToAges($date, $formatFrom = 'Y-m-d H:i:s')
    {
        $dateTimeTo = DateTime::createFromFormat($formatFrom, date($formatFrom));
        $dateTimeFrom = DateTime::createFromFormat($formatFrom, $date);

        if (($dateTimeTo instanceof DateTime) and ($dateTimeFrom instanceof DateTime)) {
            $diffDateTime = $dateTimeFrom->diff($dateTimeTo);

            return $diffDateTime->y;
        }

        return null;
    }

    public static function phoneFormat($phone)
    {
        $rawPhone = preg_replace('/([^\d]+)/i', '', $phone);
        $rawPhone = preg_replace('/^8/i', '7', $rawPhone);

        preg_match('/^(\d*)(\d{3})(\d{3})(\d{2})(\d{2})$/i', $rawPhone, $matches);

        if (is_array($matches) and !empty($matches)) {
            array_shift($matches);

            return '+' . $matches[0] . ' (' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3] . '-' . $matches[4];
        }

        return $photo;
    }

    public static function maskId($id, $mask = '0', $length = 8)
    {
        $id = strval($id);
        $length = abs($length);

        if (strlen($id) < $length) {
            for ($i = strlen($id); $i < $length; $i++) {
                $id = $mask . $id;
            }
        } else if (strlen($id) > $length) {
            $id = substr($id, ($length * -1));
        }

        return $id;
    }

    public static function dateAutoFormat($date, $formatFrom = 'Y-m-d H:i:s')
    {
        $formatedDate = DateTime::createFromFormat($formatFrom, $date);

        $diff = (new DateTime)->diff($formatedDate);
        $diffDays = intval($diff->format("%R%a"));

        if ($diffDays === 0) {
            return self::dateConvert($date, $formatFrom, 'H:i');
        }

        return self::dateConvert($date, $formatFrom, 'd') . ' ' . self::toRus(self::dateConvert($date, $formatFrom, 'M')) . ((intval(self::dateConvert($date, $formatFrom, 'Y')) !== intval(date('Y'))) ? ' ' . self::dateConvert($date, $formatFrom, 'Y') : '');
    }
}
