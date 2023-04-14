<?php

namespace mmaurice\cabinet\core\prototypes\models;

use mmaurice\cabinet\core\prototypes\models\DbModelPrototype;

class ExtendDbModelPrototype extends DbModelPrototype
{
    const REL_ONE = 'one';
    const REL_MANY = 'many';

    public $tableName = '';
    public $relations = [];

    public $namespaces = [
        '\\mmaurice\\cabinet\\models\\',
        '\\mmaurice\\cabinet\\core\\models\\',
    ];

    public static function getTableName()
    {
        $object = self::getObject();

        return $object->tableName;
    }

    public static function getFullModelTableName()
    {
        $object = self::getObject();

        return $object->getFullTableName(self::getTableName());
    }

    public static function getClassName()
    {
        return get_called_class();
    }

    public static function getObject()
    {
        $class = self::getClassName();

        $object = new $class;

        return $object;
    }

    public function getList($filter = [], $getRelatedItems = false, $log = false)
    {
        $resource = $this->search($filter, $log);

        if ($this->getRecordCount($resource)) {
            $results = [];

            while ($row = $this->getRow($resource)) {
                $item = $row;

                if ($getRelatedItems) {
                    $item = $this->getRelatedData($item, $getRelatedItems, $log);
                }

                $results[] = $item;
            }

            return $results;
        }

        return null;
    }

    public function getItem($filter = [], $getRelatedItems = false, $log = false)
    {
        $resource = $this->search($filter, $log);

        if ($this->getRecordCount($resource)) {
            $item = $this->getRow($resource);

            if ($getRelatedItems) {
                $item = $this->getRelatedData($item, $getRelatedItems, $log);
            }

            return $item;
        }

        return null;
    }

    public function search($filter = [], $log = false)
    {
        $query = $this->getRawSql($filter);

        /*if ($log or (defined('LK_DEBUG') and (LK_DEBUG === true))) {
            if (!array_key_exists('db_query_log', $_SESSION)) {
                $_SESSION['db_query_log'] = [];
            }

            $query = str_replace(PHP_EOL, chr(13), trim($query, PHP_EOL) . ";");

            $_SESSION['db_query_log'][] = $query;
        }*/

        $resource = $this->query($query);

        return $resource;
    }

    public function query($sql)
    {
        if (defined('LK_DEBUG') and (LK_DEBUG === true)) {
            if (!array_key_exists('db_query_log', $_SESSION)) {
                $_SESSION['db_query_log'] = [];
            }

            $sql = str_replace(PHP_EOL, chr(13), trim($sql, PHP_EOL) . ";");

            $_SESSION['db_query_log'][] = $sql;
        }

        return parent::query($sql);
    }

    public function getRawSql($filter = [])
    {
        if (!array_key_exists('alias', $filter)) {
            $filter['alias'] = 't';
        }

        if (!array_key_exists('select', $filter)) {
            $filter['select'] = $filter['alias'] . '.*';
        }

        if (!is_array($filter['select'])) {
            $filter['select'] = [$filter['select']];
        }

        if (!array_key_exists('from', $filter) or empty($filter['from'])) {
            $filter['from'] = $this->getFullTableName($this->tableName);
        }

        return "SELECT" . PHP_EOL
            . "\t" . implode("," . PHP_EOL . "\t", $filter['select']) . PHP_EOL
            . "FROM " . $filter['from'] . " " . $filter['alias'] . PHP_EOL
            . (!empty($filter['join']) ? implode(PHP_EOL, $filter['join']) . PHP_EOL : "")
            . (!empty($filter['where']) ? "WHERE" . PHP_EOL . "\t" . implode(PHP_EOL . "\t", $filter['where']) . PHP_EOL : "")
            . (!empty($filter['group']) ? "GROUP BY" . PHP_EOL . "\t" . implode("," . PHP_EOL . "\t", $filter['group']) . PHP_EOL : "")
            . (!empty($filter['having']) ? "HAVING" . PHP_EOL . "\t" . implode(PHP_EOL . "\t", $filter['having']) . PHP_EOL : "")
            . (!empty($filter['order']) ? "ORDER BY" . PHP_EOL . "\t" . implode("," . PHP_EOL . "\t", $filter['order']) . PHP_EOL : "")
            . (!empty($filter['limit']) ? "LIMIT " . intval($filter['limit']) . PHP_EOL : "")
            . (!empty($filter['offset']) ? "OFFSET " . intval($filter['offset']) . PHP_EOL : "");
    }

    public function getRelatedData($item, $getRelatedItems = false, $log = false)
    {
        $getRelatedItems = $getRelatedItems === true ? ['*'] : $getRelatedItems;

        if (is_array($getRelatedItems) and !empty($getRelatedItems)) {
            if (is_array($this->relations) and !empty($this->relations)) {
                foreach ($getRelatedItems as $relatedItem) {
                    if (preg_match('/^([^\.]+)\.*([^$]*)$/imu', $relatedItem, $matches)) {
                        foreach ($this->relations as $name => $relation) {
                            if (($matches[1] !== '*') and ($matches[1] !== $name)) {
                                continue;
                            }

                            $preparedClasses = [];
                            $preparedClasses[] = $relation[1][0];

                            foreach ($this->namespaces as $namespace) {
                                $preparedClasses[] = $namespace . $relation[1][0];
                            }

                            if (is_array($preparedClasses) and !empty($preparedClasses)) {
                                foreach ($preparedClasses as $preparedClass) {
                                    if (!class_exists($preparedClass)) {
                                        continue;
                                    }

                                    $class = new $preparedClass;

                                    if (!is_null($item[$relation[0]])) {
                                        $filter = [
                                            'from' => $this->getFullTableName($class->tableName),
                                            'where' => [
                                                (isset($relation[3]['alias']) ? $relation[3]['alias'] : 't') . ".{$relation[1][1]} = '{$item[$relation[0]]}'",
                                            ],
                                        ];

                                        if (array_key_exists(3, $relation) and is_array($relation[3]) and !empty($relation[3])) {
                                            foreach ($relation[3] as $key => $value) {
                                                if (array_key_exists($key, $filter)) {
                                                    $filter[$key] = array_merge($filter[$key], $value);
                                                } else {
                                                    $filter[$key] = $value;
                                                }
                                            }
                                        }

                                        $nextRelations = ($matches[1] !== '*') ? (!empty($matches[2]) ? [$matches[2]] : false) : true;

                                        if ($relation[2] === self::REL_ONE) {
                                            $item[$name] = $class->getItem($filter, $nextRelations, $log);
                                        } else {
                                            $item[$name] = $class->getList($filter, $nextRelations, $log);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $item;
    }
}