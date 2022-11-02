<?php

namespace mmaurice\cabinet\core\exceptions;

use mmaurice\cabinet\core\exceptions\BaseException;

class WebExceptions extends BaseException
{
    public function makeResponce()
    {
        $error = parent::makeResponce();
        echo '<div>';
        echo '<strong>Error:</strong> ' . $error['message'] . '<br />';
        echo '<strong>Code:</strong> ' . $error['code'] . '<br />';
        echo '<strong>File:</strong> ' . $error['file'] . '<br />';
        echo '<strong>Line:</strong> ' . $error['line'] . '<br />';
        foreach ($error['trace'] as $key => $trace) {
            echo '<strong>#' . ($key + 1) . ':</strong> ' . $trace['file'] . ' [' . $trace['line'] . '] ' . $trace['class'] . $trace['type'] . $trace['function'] . '()' . '<br />';
        }
        echo '</div>';
    }
}
