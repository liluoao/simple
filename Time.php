<?php

use Carbon\Carbon;

class Time {
   
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
