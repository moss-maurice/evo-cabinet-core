<?php

namespace mmaurice\cabinet\core\exceptions;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\exceptions\BaseException;

class AjaxException extends BaseException
{
    public function makeResponce()
    {
        $error = parent::makeResponce();

        App::response()->ajaxReturnJson($error);
    }
}
