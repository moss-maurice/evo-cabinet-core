<?php

namespace mmaurice\cabinet\core\commands;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\helpers\CmdHelper;

class HelpCommand extends mmaurice\cabinet\core\prototypes\commands\CommandProtorype
{
    // php cli.php help
    public function actionIndex()
    {
        CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##'));
        CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##') . CmdHelper::textColor('white', ' Use command string "') . CmdHelper::textColor('magenta', 'php cli.php [command[/method]]') . CmdHelper::textColor('white', '" to run concrete command.'));
        CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##') . CmdHelper::textColor('white', ' Use command method "') . CmdHelper::textColor('magenta', 'php cli.php [command]/help') . CmdHelper::textColor('white', '" to get list of availavles method.'));
        CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##'));

        $folders = array_values(array_filter([
            realpath(App::getCoreRoot() . 'commands'),
            realpath(App::getPublicRoot() . 'commands'),
        ]));

        if ($folders) {
            CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##') . CmdHelper::textColor('white', ' List of availables commands:'));
            CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##'));

            foreach ($folders as $folder) {
                $list = glob($folder . DIRECTORY_SEPARATOR . '*Command.php');

                if ($list) {
                    foreach ($list as $item) {
                        $command = strtolower(preg_replace('/(Command\.php)/i', '', pathinfo($item)['basename']));

                        CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##') . CmdHelper::textColor('white', ' > ') . CmdHelper::textColor((in_array($command, ['help']) ? 'yellow' : 'light_cyan'), $command));
                    }
                }
            }

            CmdHelper::drawLine(' ' . CmdHelper::textColor('light_green', '##'));
        }

        return true;
    }
}
