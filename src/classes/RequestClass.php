<?php

namespace mmaurice\cabinet\core\classes;

use mmaurice\cabinet\core\prototypes\ClassPrototype;

class RequestClass extends ClassPrototype
{
    const REQUEST_METHDOS_POST = 'POST';
    const REQUEST_METHDOS_GET = 'GET';

    public static $controller;
    public static $method;

    /**
     * Метод извлечения данных из массива $_REQUEST
     *
     * @param string $fieldName
     * @param mixed $defaultValue
     * @return void
     */
    public function extractRequest($fieldName, $defaultValue = null)
    {
        return $this->extractArray($_REQUEST, $fieldName, $defaultValue);
    }

    /**
     * Метод извлечения данных из массива $_POST
     *
     * @param string $fieldName
     * @param mixed $defaultValue
     * @return void
     */
    public function extractPost($fieldName, $defaultValue = null)
    {
        return $this->extractArray($_POST, $fieldName, $defaultValue);
    }

    public function extractAll()
    {
        return $_REQUEST;
    }

    /**
     * Метод извлечения данных из массива $_GET
     *
     * @param string $fieldName
     * @param mixed $defaultValue
     * @return void
     */
    public function extractGet($fieldName, $defaultValue = null)
    {
        return $this->extractArray($_GET, $fieldName, $defaultValue);
    }

    /**
     * Получение метода запроса страницы
     *
     * @return void
     */
    public function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Проверка на GET-запрос
     *
     * @return boolean
     */
    public function isGetRequest()
    {
        if (self::getRequestMethod() === self::REQUEST_METHDOS_GET) {
            return true;
        }
        return false;
    }

    /**
     * Проверка на POST-запрос
     *
     * @return boolean
     */
    public function isPostRequest()
    {
        if (self::getRequestMethod() === self::REQUEST_METHDOS_POST) {
            return true;
        }
        return false;
    }

    /**
     * Проверка на AJAX-запрос
     *
     * @return boolean
     */
    public function isAjaxRequest()
    {
        if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) and !empty($_SERVER['HTTP_X_REQUESTED_WITH']) and (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            return true;
        }
        return false;
    }

    public function getCsrf()
    {
        /*if (!self::isCliMode()) {
            if (empty(session_id())) {
                session_start();
            }
            if (!array_key_exists('rest_csrf_token', $_SESSION)) {
                $_SESSION['rest_csrf_token'] = base64_encode(time());
            }
            return $_SESSION['rest_csrf_token'];
        }*/
        return false;
    }

    public function checkCsrf()
    {
        /*if (!self::isCliMode()) {
            /*if (empty(session_id())) {
                session_start();
            }
            if (self::isPostRequest()) {
                $token = self::extractPost('csrf');
                if (!is_null($token) and ($token === $_SESSION['rest_csrf_token'])) {
                    return true;
                }
            }
        }*/
        return false;
    }

    public function extractArray(array $array, $fieldName, $defaultValue = null)
    {
        if ($this->hasArrayKey($array, $fieldName)) {
            return $this->valideType($array[$fieldName]);
        }

        return $defaultValue;
    }

    public function hasArrayKey(array $array, $fieldName)
    {
        if (array_key_exists($fieldName, $array)) {
            return true;
        }

        return false;
    }

    public function valideType($value, $type = null)
    {
        if (is_null($type)) {
            if (is_numeric($value)) {
                $type = 'integer';
            } else if (is_array($value)) {
                $type = 'array';
            } else {
                if (is_string($value) and preg_match('/^\s*(\d+\.\d+)\s*$/', $value, $matches)) {
                    $type = 'float';
                }

                $type = 'string';
            }
        }

        switch ($type) {
            case 'integer':
            case 'int':
                return intval($value);
            case 'double':
            case 'float':
                return floatval(trim($value));
            case 'array':
                foreach ($value as $key => $item) {
                    $value[$key] = $this->valideType($item);
                }

                return $value;
            case 'string':
            default:
                return trim(strval($value));
        }
    }
}
