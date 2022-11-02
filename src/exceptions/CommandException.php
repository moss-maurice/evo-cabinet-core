<?php

namespace mmaurice\cabinet\core\exceptions;

use mmaurice\cabinet\core\exceptions\BaseException;
use mmaurice\cabinet\core\helpers\CmdHelper;

class CommandException extends BaseException
{
    public function makeResponce()
    {
        $error = parent::makeResponce();

        CmdHelper::drawLine(CmdHelper::textColor('red', '##'));
        CmdHelper::drawLine(CmdHelper::textColor('red', '## Error: ' . $error['message']));
        CmdHelper::drawLine(CmdHelper::textColor('red', '## Code: ' . $error['code']));
        CmdHelper::drawLine(CmdHelper::textColor('red', '## File: ' . $error['file']));
        CmdHelper::drawLine(CmdHelper::textColor('red', '## Line: ' . $error['line']));

        if (is_array($error['trace']) and !empty($error['trace'])) {
            foreach ($error['trace'] as $key => $trace) {
                CmdHelper::drawLine(CmdHelper::textColor('red', '## > ' . ($key + 1) . ': ' . $trace['file'] . ' [' . $trace['line'] . '] ' . $trace['class'] . $trace['type'] . $trace['function'] . '()'));
            }
        }

        CmdHelper::drawLine(CmdHelper::textColor('red', '##'));
    }
}
