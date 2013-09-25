<?php
class Settings {
    private static $instance;
    private static $settings;
   
    private function __construct($ini_file) {
        self::$settings = parse_ini_file($ini_file, true);
    }
   
    public static function getInstance($ini_file) {
        if(! isset(self::$instance[$ini_file])) {
            self::$instance[$ini_file] = new Settings($ini_file);           
        }
        return self::$instance[$ini_file];
    }
   
    public function __get($setting) {
        if(array_key_exists($setting, self::$settings)) {
            return self::$settings[$setting];
        } else {
            foreach(self::$settings as $section) {
                if(array_key_exists($setting, $section)) {
                    return $section[$setting];
                }
            }
        }
    }
}
?>