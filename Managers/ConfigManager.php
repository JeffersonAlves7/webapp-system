<?php
if (file_exists("config.php")) {
    require_once("config.php");
} else {
    $DATABASE_HOST = "localhost";
    $DATABASE_USERNAME = "root";
    $DATABASE_PASSWORD = "";
    $DATABASE_NAME = "webapp";
    $ENVIRONMENT = "DEV";
}

class ConfigManager
{
    public static $DATABASE_HOST;
    public static $DATABASE_USERNAME;
    public static $DATABASE_PASSWORD;
    public static $DATABASE_NAME;
    public static $ENVIRONMENT;

    public static function init()
    {
        global $DATABASE_HOST, $DATABASE_USERNAME, $DATABASE_PASSWORD, $DATABASE_NAME, $ENVIRONMENT;

        self::$DATABASE_HOST = $DATABASE_HOST;
        self::$DATABASE_USERNAME = $DATABASE_USERNAME;
        self::$DATABASE_PASSWORD = $DATABASE_PASSWORD;
        self::$DATABASE_NAME = $DATABASE_NAME;
        self::$ENVIRONMENT = $ENVIRONMENT;
    }
}

ConfigManager::init();
