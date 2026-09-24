<?php
/**
 * Created by PhpStorm.
 * User: nasim
 * Date: 6/20/2018 AD
 * Time: 1:44 PM
 */

namespace App\MyHelpers;


use Morilog\Jalali\Jalalian;
use Illuminate\Http\Request;

class MyJalaliDate
{
    public function georgianToJalali($date, $get_time = true)
    {
        $date = explode(" ", $date);
        $time = '';
        if (sizeof($date) > 0) {
            if (sizeof($date) > 1) {
                $time = $date[1];
                $date = explode("-", $date[0]);
            } else {
                $date = explode("-", $date[0]);
            }
        }
        if (sizeof($date) < 3) {
            return '';
        }

        $day = $date[2];
        $month = $date[1];
        $year = $date[0];

        $date = \Morilog\Jalali\CalendarUtils::toJalali($year, $month, $day);
        if ($get_time) {
            return implode('-', $date) . ' ' . $time;
        } else {
            return implode('-', $date);
        }
    }

    public function getMonthNames($month)
    {
        $ret = '';
        switch ($month) {
            case '1':
                $ret = 'فروردین';
                break;
            case '2':
                $ret = 'اردیبهشت';
                break;
            case '3':
                $ret = 'خرداد';
                break;
            case '4':
                $ret = 'تیر';
                break;
            case '5':
                $ret = 'مرداد';
                break;
            case '6':
                $ret = 'شهریور';
                break;
            case '7':
                $ret = 'مهر';
                break;
            case '8':
                $ret = 'آبان';
                break;
            case '9':
                $ret = 'آذر';
                break;
            case '10':
                $ret = 'دی';
                break;
            case '11':
                $ret = 'بهمن';
                break;
            case '12':
                $ret = 'اسفند';
                break;
        }
        return $ret;
    }

    public function jalaliToGeorgian($date)
    {
        $date = str_replace('/', '-', $date);
        $date = $this->convert($date);
        $tDate = explode("-", $date);
        $day = $tDate[2];
        $month = $tDate[1];
        $year = $tDate[0];

        if ($year != '0000') {
            $gDate = \Morilog\Jalali\CalendarUtils::toGregorian($year, $month, $day);
            return $gDate['0'] . '-' . $gDate['1'] . '-' . $gDate['2'];
        } else
            return "0000-00-00";
    }


    public function today()
    {
        return Jalalian::forge('today')->format(' %d %B، %Y');
    }

    public function convert($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

        return $englishNumbersOnly;
    }

    public function firstOfJalaliMonthDateInJalali($georgian_date)
    {
        $georgian_date = explode(' ', $georgian_date)[0];
        $jdate = $this->georgianToJalali($georgian_date);
        $jdate = explode('-', $jdate);
        return $jdate[0] . '-' . $jdate[1] . '-01';
    }

    public function endOfJalaliMonthDateInJalali($georgian_date)
    {
        $georgian_date = explode(' ', $georgian_date)[0];
        $jdate = $this->georgianToJalali($georgian_date, false);
        $jdate = explode('-', $jdate);
        $date = (new Jalalian($jdate[0], $jdate[1], $jdate[2], 23, 59, 59))->getMonthDays();
        return $jdate[0] . '-' . $jdate[1] . '-' . $date;
    }

    public function firstOfJalaliMonthDateInGeorgian($georgian_date)
    {
        return $this->jalaliToGeorgian($this->firstOfJalaliMonthDateInJalali($georgian_date));
    }

    public function endOfJalaliMonthDateInGeorgian($georgian_date)
    {
        return $this->jalaliToGeorgian($this->endOfJalaliMonthDateInJalali($georgian_date));
    }

}
