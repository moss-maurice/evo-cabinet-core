<?php

namespace mmaurice\cabinet\core\prototypes\commands;

use mmaurice\cabinet\core\helpers\CmdHelper;

class CommandProtorype
{
    public function __call($name, $arguments)
    {
        return $this->actionHelp();
    }

    public function actionIndex()
    {
        return $this->actionHelp();
    }

    public function actionHelp()
    {
        if (preg_match('/([\b\w]+)Command$/i', get_called_class(), $matches)) {
            array_shift($matches);

            CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##'));
            CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##') . CmdHelper::textColor('white', ' Use command string "') . CmdHelper::textColor('magenta', 'php cli.php ' . strtolower($matches[0]) . '[/method]') . CmdHelper::textColor('white', '" to run concrete command.'));
            CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##'));
        }

        $methods = array_filter(get_class_methods($this), function ($method) {
            if (preg_match('/action([^$]+)/i', $method, $matches)) {
                return true;
            }

            return false;
        });

        $methods = array_map(function ($method) {
            return strtolower(preg_replace('/(action)/i', '', $method));
        }, $methods);

        if ($methods) {
            CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##') . CmdHelper::textColor('white', ' List of availables command methods:'));
            CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##'));

            foreach ($methods as $method) {
                CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##') . CmdHelper::textColor('white', ' > ') . CmdHelper::textColor(((in_array($method, ['index', 'help'])) ? 'yellow' : 'light_cyan'), $method));
            }

            CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##'));
        }

        return true;
    }
}
