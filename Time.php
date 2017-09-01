<?php

use Carbon\Carbon;

class Time {
    /**
     *
     * 计算两个以YYYY-MM为格式的日期，相差几个月
     *
     */
    public static function getBetweenTwoMonth($month1, $month2) {

        $Month_List_a1 = explode("-", $month1);
        $Month_List_a2 = explode("-", $month2);

        $d1 = mktime(0, 0, 0, $Month_List_a1[1], 1, $Month_List_a1[0]);

        $d2 = mktime(0, 0, 0, $Month_List_a2[1], 1, $Month_List_a2[0]);

        $Month = round(($d1 - $d2) / 3600 / 24 / 30);

        return $Month;
    }

    /**
     * 获取当前月有多少天
     *
     * @return int
     */
    public static function getCurrentMonthDays($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        list($y, $m) = explode('-', $date);

        return cal_days_in_month(CAL_GREGORIAN, $m, $y);
    }

    /**
     * 获取指定日期的年或月或日
     *
     * @param string $date "YYYY-MM-DD"格式的日期
     * @param string $type year/month/day
     * @return int|string 返回数字
     */
    public static function getDateNumber($date = null, $type = 'year') {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        list($y, $m, $d) = explode('-', $date);
        $dateNumberArr = [
            'year' => $y,
            'month' => $m,
            'day' => $d,
        ];

        return $dateNumberArr[$type];
    }

    /**
     * 将UTC时间转换为北京时间Y-m-d H:i:s格式
     *
     * @param $utcTime
     * @return string
     */
    public static function changeUTCToDatetime($utcTime) {
        return Carbon::parse("{$utcTime} +8 hours")->toDateTimeString();
    }
}
