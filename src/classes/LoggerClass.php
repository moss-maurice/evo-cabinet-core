<?php

namespace mmaurice\cabinet\core\classes;

use mmaurice\cabinet\core\helpers\CmdHelper;
use mmaurice\cabinet\core\prototypes\ClassPrototype;

class LoggerClass extends ClassPrototype
{
    const NO_MESSAGES = 0;
    const ALL_MESSAGES = 1;
    const IMPORTANT_MESSAGES = 2;
    const ERROR_MESSAGES = 3;

    const LEVEL_NORMAL = 0;
    const LEVEL_IMPORTANT = 1;
    const LEVEL_ERROR = 2;

    const SOURCE_CONSOLE = 'STDOUT';

    protected $level;

    public function __construct($level = self::ALL_MESSAGES, $source = self::SOURCE_CONSOLE)
    {
        $this->level = intval($level);
    }

    protected function checkMessageLevel($level)
    {
        switch ($this->level) {
            case self::ALL_MESSAGES:
                return in_array($level, [self::LEVEL_ERROR, self::LEVEL_IMPORTANT, self::LEVEL_NORMAL]);

                break;
            case self::IMPORTANT_MESSAGES:
                return in_array($level, [self::LEVEL_ERROR, self::LEVEL_IMPORTANT]);

                break;
            case self::ERROR_MESSAGES:
                return in_array($level, [self::LEVEL_ERROR]);

                break;
            case self::NO_MESSAGES:
            default:

                break;
        }

        return false;
    }

    public function set($line, $options = [], $level = self::LEVEL_NORMAL, $stamp = true)
    {
        if ($this->checkMessageLevel($level)) {
            $rawLine = ($stamp ? CmdHelper::textColor('yellow', date('Y-m-d H:i:s')) . CmdHelper::textColor('white', ' > ') : '') . CmdHelper::textColor('light_cyan', $line);

            if (is_array($options) and !empty($options)) {
                foreach ($options as $key => $option) {
                    $options[$key] = CmdHelper::textColor('white', $key) . ': ' . CmdHelper::textColor('magenta', $option);
                }

                $rawLine .= CmdHelper::textColor('white', ' (' . implode('; ', array_values($options)) . ')');
            }

            fwrite(STDOUT, $rawLine . PHP_EOL);
        }
    }
}
