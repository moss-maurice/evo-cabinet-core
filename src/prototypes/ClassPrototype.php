<?php

namespace mmaurice\cabinet\core\prototypes;

class ClassPrototype
{
    public static function init()
    {
        $className = self::getCalledClass();
        return new $className;
    }

    protected static function getCalledClass()
    {
        return get_called_class();
    }
}
