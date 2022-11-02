<?php

namespace mmaurice\cabinet\core\models;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\prototypes\models\ExtendDbModelPrototype;

class Model extends ExtendDbModelPrototype
{
    public function insert($fields, $fromFields = '*', $fromTable = '', $where = '', $limit = '')
    {
        if (!empty($fields) and is_array($fields) and !empty($this->tableName)) {
            $sql = "INSERT
                INTO " . $this->getFullTableName($this->tableName) . "
                    (`" . implode("`, `", array_keys($fields)) . "`)
                VALUES
                    ('" . implode("', '", array_values($fields)) . "');";

            if ($this->query($sql)) {
                return $this->getInsertId();
            }
        }

        return null;
    }

    public function update($fields, $where = '')
    {
        if (!empty($fields) and is_array($fields) and !empty($this->tableName)) {
            $set = array_map(function ($key, $value) {
                return "`{$key}` = '{$value}'";
            }, array_keys($fields), array_values($fields));

            $sql = "UPDATE " . $this->getFullTableName($this->tableName) . "
                SET " . implode(", ", $set) . "
                WHERE " . $where;

            return $this->query($sql);
        }

        return false;
    }

    public function delete($where = '', $fields = '')
    {
        if (!empty($where) and !empty($this->tableName)) {
            $sql = "DELETE FROM" . $this->getFullTableName($this->tableName) . "
                WHERE " . $where;

            return $this->query($sql);
        }

        return false;
    }
}
