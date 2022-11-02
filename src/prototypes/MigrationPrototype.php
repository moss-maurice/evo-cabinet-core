<?php

namespace mmaurice\cabinet\core\prototypes;

use mmaurice\cabinet\core\classes\AppClass;
use mmaurice\cabinet\core\helpers\CmdHelper;
use mmaurice\cabinet\core\providers\ModxProvider;

class MigrationPrototype
{
    public function __construct()
    {
        ModxProvider::modxInit();

        $modx = ModxProvider::getModx();

        $modx->db->query("SET NAMES utf8;");
        $modx->db->query("SET time_zone = '+00:00';");
        $modx->db->query("SET foreign_key_checks = 0;");
        $modx->db->query("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';");
    }

    public function run()
    {
        return true;
    }
}
