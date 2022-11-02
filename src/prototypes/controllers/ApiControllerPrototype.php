<?php

namespace mmaurice\cabinet\core\prototypes\controllers;

use mmaurice\cabinet\core\App;

class ApiControllerPrototype extends mmaurice\cabinet\core\prototypes\controllers\ControllerPrototype
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    const MESSAGE_DEFAULT_RENDER = 'Успешный запрос!';
    const MESSAGE_DEFAULT_CHECKISAJAX = 'Доступ запрещен!';

    const CODE_SUCCESS = 200;
    const CODE_ERROR = 400;
    const CODE_ERROR_INPUT_PROPERTIES = 401;
    const CODE_ERROR_INPUT_FIELDS = 402;

    public function beforeAction($action)
    {
        $this->checkIsAjax();

        return parent::beforeAction($action);
    }

    protected function fieldsFilter($fields)
    {
        if (is_array($fields) and !empty($fields)) {
            foreach ($fields as $field) {
                if (!array_key_exists($field, $_REQUEST) or empty($_REQUEST[$field])) {
                    return $this->render([], self::CODE_ERROR_INPUT_PROPERTIES, self::STATUS_ERROR, 'Обязательный параметр "' . $field . '" не передан!');
                }
            }
        } else {
            return $this->render([], self::CODE_ERROR_INPUT_FIELDS, self::STATUS_ERROR, 'Массив полей не передан!');
        }

        return true;
    }

    protected function checkIsAjax($code = self::CODE_ERROR, $status = self::STATUS_ERROR, $message = self::MESSAGE_DEFAULT_CHECKISAJAX)
    {
        if (!App::request()->isAjaxRequest()) {
            return $this->render([], $code, $status, $message);
        }

        return true;
    }

    protected function render($parametrs = [], $code = self::CODE_SUCCESS, $status = self::STATUS_SUCCESS, $message = self::MESSAGE_DEFAULT_RENDER)
    {
        $result = [
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'data' => $parametrs,
        ];

        if (defined('LK_DEBUG') and (LK_DEBUG === true)) {
            $result['db_query_log'] = $_SESSION['db_query_log'];
        }

        return $this->renderAjax($result);
    }
}
