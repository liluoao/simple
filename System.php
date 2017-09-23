<?php

namespace Rmlx\Util;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class System {

    /**
     * 设置log，并返回logger对象
     *
     * @param $logName string log名称
     * @param $pathName string log文件名
     * @return Logger
     */
    public function getLogger($logName, $pathName) {
        //获取配置log级别
        $levelName = strtoupper(config('app.log_level'));
        $levelArr = [
            'DEBUG' => 100,
            'INFO' => 200,
            'NOTICE' => 250,
            'WARNING' => 300,
            'ERROR' => 400,
            'CRITICAL' => 500,
            'ALERT' => 550,
            'EMERGENCY' => 600,
        ];
        $level = isset($levelArr[$levelName]) ? $levelArr[$levelName] : 400;

        $log = new Logger($logName);
        // Now add some handlers
        $stream = new StreamHandler(storage_path("logs/{$pathName}.log"), $level);
        $log->pushHandler($stream);
        $log->pushHandler(new FirePHPHandler());

        return $log;
    }

    /**
     * 手动debug方法
     *
     * @param $string
     * @param array $data
     */
    public static function debugToFile($string, $data = []) {
        $msg = date('Y-m-d H:i:s') . ' ' . $string;
        if (!empty($data)) {
            $formatStr = var_export($data, true);
            $msg = $msg . "\n" . $formatStr;
        }
        error_log($msg . "\r\n", 3, storage_path("logs/debug.log"));
    }
}
