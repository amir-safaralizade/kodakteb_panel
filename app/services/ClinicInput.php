<?php

namespace App\services;

class ClinicInput
{
    public static function digits($value)
    {
        if (!is_string($value)) return $value;

        return strtr(trim($value), array_combine(
            preg_split('//u', '۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY),
            str_split('01234567890123456789')
        ));
    }

    public static function money($value)
    {
        $value = self::digits($value);

        return is_string($value) ? str_replace([',', '٬', '٫'], ['', '', '.'], $value) : $value;
    }

    public static function date($value)
    {
        $value = self::digits($value);
        if (!is_string($value)) return $value;
        $value = str_replace(['-', '.'], '/', $value);
        if (preg_match('/^\d{8}$/', $value)) {
            return substr($value, 0, 4).'/'.substr($value, 4, 2).'/'.substr($value, 6, 2);
        }

        return $value;
    }
}
