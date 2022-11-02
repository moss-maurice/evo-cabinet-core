<?php

namespace mmaurice\cabinet\core\prototypes\models;

/**
 * Класс-прототип модели
 */
class ModelPrototype
{
    public function __construct()
    {
        return;
    }

    public static function model()
    {
        $class = get_called_class();

        return new $class;
    }
}
