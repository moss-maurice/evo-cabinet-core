<?php

namespace mmaurice\cabinet\core\prototypes;

class SingletonPrototype
{
    protected static $instance = [];

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    protected function __wakeup()
    {

    }

    public static function init()
    {
        $className = self::getCalledClass();

        if (!array_key_exists($className, self::$instance)) {
            self::$instance[$className] = new $className;
        }

        return self::$instance[$className];
    }

    protected static function getCalledClass()
    {
        return get_called_class();
    }
}

