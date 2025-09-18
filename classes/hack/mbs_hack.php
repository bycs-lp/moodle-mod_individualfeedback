<?php
namespace mod_individualfeedback\hack;

class mbs_hack {
    private static $mbstestrunning = false;

    public static function start_mbs_test() {
        self::$mbstestrunning = true;
    }

    public static function stop_mbs_test() {
        self::$mbstestrunning = false;
    }

    public static function is_running_core_test() {
        return(PHPUNIT_TEST && empty(self::$mbstestrunning));
    }
}