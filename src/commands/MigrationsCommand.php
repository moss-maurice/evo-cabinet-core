<?php

namespace mmaurice\cabinet\core\commands;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\exceptions\CommandException;
use mmaurice\cabinet\core\helpers\CmdHelper;
use mmaurice\cabinet\core\prototypes\commands\CommandProtorype;
use mmaurice\cabinet\core\providers\ModxProvider;

class MigrationsCommand extends CommandProtorype
{
    // php cli.php migrations
    public function actionIndex()
    {
        return $this->actionMigrate();
    }

    // php cli.php migrations/migrate
    public function actionMigrate()
    {
        global $database_server;
        global $database_user;
        global $database_password;
        global $dbase;
        global $table_prefix;
        global $modx;

        CmdHelper::drawLine();

        ModxProvider::modxInit();
        $modx = ModxProvider::getModx();

        $migrations = $this->getMigrationFiles();

        if (is_array($migrations) and !empty($migrations)) {
            foreach ($migrations as $migration) {
                $this->pushMigration($migration);
            }

            $modx->clearCache();
            $modx->clearCache('full');

            CmdHelper::logLine(CmdHelper::textColor('yellow', 'CMS cache is erased!'));
        }

        CmdHelper::logLine(CmdHelper::textColor('light_green', 'Done!'));

        return true;
    }

    // php cli.php migrations/remove
    public function actionRemove()
    {
        global $table_prefix;

        ModxProvider::modxInit();
        $modx = ModxProvider::getModx();
    }

    protected function pushMigration($migration)
    {
        global $database_server;
        global $database_user;
        global $database_password;
        global $dbase;
        global $table_prefix;
        global $modx;

        $done = false;

        ModxProvider::modxInit();
        $modx = ModxProvider::getModx();

        if (pathinfo($migration)['extension'] === 'sql') {
            $sqls = trim(file_get_contents($migration));

            if (!empty($sqls)) {
                $sqls = preg_split("/\;\s*[\r\n]+/", $sqls);

                if (is_array($sqls) and !empty($sqls)) {
                    foreach ($sqls as $index => $sql) {
                        $sql = trim($sql);

                        $sql = str_replace([
                            '{table_prefix}',
                        ], [
                            $table_prefix,
                        ], $sql);

                        if (!empty($sql)) {
                            $resource = $modx->db->query($sql);

                            if (!$resource) {
                                throw new CommandException('SQL import error: ' . $modx->db->getLastError() . '. In migration file "' . $migration . '". SQL code: ' . $sql);
                            } else {
                                $done = true;
                            }
                        }
                    }
                }
            }
        } else if (pathinfo($migration)['extension'] === 'php') {
            require_once($migration);

            $migrationClassName = 'migration_' . preg_replace('/(\.migration)$/i', '', pathinfo($migration)['filename']);

            if (class_exists($migrationClassName)) {
                CmdHelper::logLine(CmdHelper::textColor('white', 'Migration "' . pathinfo($migration)['basename'] . '" start'));

                if ((new $migrationClassName)->run()) {
                    $done = true;
                } else {
                    CmdHelper::logLine(CmdHelper::textColor('light_red', 'Migration "' . pathinfo($migration)['basename'] . '" fail'));
                }
            } else {
                throw new CommandException('Migration class "' . $migrationClassName . '" is not found');
            }
        }

        if ($done) {
            $migrationBaseName = basename($migration);

            CmdHelper::logLine(CmdHelper::textColor('magenta', 'Migration "' . $migrationBaseName . '" done'));

            $sql = "INSERT INTO `{$table_prefix}migrations` (`name`) VALUES ('{$migrationBaseName}');";

            $resource = $modx->db->query($sql);
        }
    }

    protected function getMigrationFiles()
    {
        global $dbase;
        global $table_prefix;
        global $modx;

        ModxProvider::modxInit();
        $modx = ModxProvider::getModx();

        $folders = $this->migrationsFolder();

        $list = [];

        if ($folders) {
            $migrationsList = [];

            foreach ($folders as $folder) {
                $migrationsList = array_merge($migrationsList, glob($folder . DIRECTORY_SEPARATOR . '*.migration.sql'));
                $migrationsList = array_merge($migrationsList, glob($folder . DIRECTORY_SEPARATOR . '*.migration.php'));
            }

            $sql = "SHOW TABLES
                FROM {$dbase}
                LIKE '{$table_prefix}migrations'";

            $resource = $modx->db->query($sql);

            if ($modx->db->getRecordCount($resource)) {
                $versionsFiles = [];

                $sql = "SELECT
                        name
                    FROM {$table_prefix}migrations";

                $resource = $modx->db->query($sql);

                if ($modx->db->getRecordCount($resource)) {
                    while ($row = $modx->db->getRow($resource)) {
                        foreach ($folders as $folder) {
                            $path = realpath($folder . DIRECTORY_SEPARATOR . $row['name']);

                            if ($path) {
                                array_push($versionsFiles, $path);
                            }
                        }
                    }
                }

                $list = array_diff($migrationsList, $versionsFiles);
            } else {
                $sql = "CREATE TABLE {$table_prefix}migrations (
                        name varchar(2048) NOT NULL COMMENT 'Имя миграции',
                        create_date datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
                        update_date datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления'
                    ) ENGINE='InnoDB' DEFAULT CHARSET = utf8 COMMENT = 'Таблица миграций';";

                $resource = $modx->db->query($sql);

                $list = $migrationsList;
            }
        }

        return $list;
    }

    protected function migrationsFolder()
    {
        return array_values(array_filter([
            realpath(App::getCoreRoot('/migrations')),
            realpath(App::getPublicRoot('/migrations')),
        ]));
    }
}
