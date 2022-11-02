<?php

namespace mmaurice\cabinet\core\prototypes\models;

use mmaurice\cabinet\core\prototypes\models\ModelPrototype;

/**
 * Класс-прототип модели для работы с БД
 */
class DbModelPrototype extends ModelPrototype
{
    const MODE_ASSOC = 'assoc';
    const MODE_NUM = 'num';
    const MODE_BOTH = 'both';
    const DATE_TYPE_DATE = 'DATE';
    const DATE_TYPE_TIME = 'TIME';
    const DATE_TYPE_YEAR = 'YEAR';
    const DATE_TYPE_DATETIME = 'DATETIME';
    const SELECT_FIELDS_ALL = '*';

    protected $db;
    protected $modx;
    public $tableName = '';

    /**
     * Метод конструктора класса
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        global $modx;

        $this->modx = $modx;
        $this->db = $this->modx->db;

        $this->connect($this->db->config['host'], $this->db->config['dbase'], $this->db->config['user'], $this->db->config['pass'], true);

        return;
    }

    public function connect($host, $dataBase, $user, $password, $persist = true)
    {
        return $this->db->connect($host, $dataBase, $user, $password, $persist);
    }

    public function disconnect()
    {
        return $this->db->disconnect();
    }

    public function escape($string)
    {
        return $this->db->escape($string);
    }

    public function delete($where = '', $fields = '')
    {
        $from = $this->getFullTableName($this->tableName);

        if (!empty($where)) {
            //$where = $this->escape($where);
        }

        return $this->db->delete($from, $where, $fields);
    }

    public function getAffectedRows()
    {
        return $this->db->getAffectedRows();
    }

    public function getColumn($name, $resource)
    {
        return $this->db->getColumn($name, $resource);
    }

    public function getColumnNames($resource)
    {
        return $this->db->getColumnNames($resource);
    }

    public function getHtmlGrid($resource, $data)
    {
        return $this->db->getHTMLGrid($resource, $data);
    }

    public function getInsertId($connection = null)
    {
        return $this->db->getInsertId($connection);
    }

    public function getRecordCount($resource)
    {
        return $this->db->getRecordCount($resource);
    }

    public function getRow($resource, $mode = self::MODE_ASSOC)
    {
        return $this->db->getRow($resource, $mode);
    }

    public function getTableMetaData($table)
    {
        return $this->db->getTableMetaData($table);
    }

    public function getValue($resource)
    {
        return $this->db->getValue($resource);
    }

    public function getXML($resource)
    {
        return $this->db->getXML($resource);
    }

    public function insert($fields, $fromFields = '*', $fromTable = '', $where = '', $limit = '')
    {
        $intoTable = $this->getFullTableName($this->tableName);

        if (empty($fromFields)) {
            $fromFields = self::SELECT_FIELDS_ALL;
        }

        if (!empty($fromTable)) {
            $fromTable = $this->getFullTableName($fromTable);
        }

        if (!empty($where)) {
            $where = $this->escape($where);
        }

        if (!empty($limit)) {
            $limit = $this->escape($limit);
        }

        return $this->db->insert($fields, $intoTable, $fromFields, $fromTable, $where, $limit);
    }

    public function makeArray($resource = '')
    {
        return $this->db->makeArray($resource);
    }

    public function prepareDate($timestamp, $fieldType = self::DATE_TYPE_DATETIME)
    {
        $timestamp = intval($timestamp);

        return $this->db->prepareDate($timestamp, $fieldType);
    }

    public function query($sql)
    {
        return $this->db->query($sql);
    }

    public function beginTransaction()
    {
        $this->query('SET autocommit=0;');

        return $this->query('START TRANSACTION;');
    }

    public function commit()
    {
        return $this->query('COMMIT;');
    }

    public function rollback()
    {
        return $this->query('ROLLBACK;');
    }

    public function select($fields = '*', $where = '', $orderBy = '', $limit = '')
    {
        if (empty($fields)) {
            $fields = self::SELECT_FIELDS_ALL;
        }

        if ($fields !== self::SELECT_FIELDS_ALL) {
            $fields = $this->escape($fields);
        }

        if (empty($orderBy)) {
            $orderBy = $this->escape($orderBy);
        }

        if (empty($limit)) {
            $limit = $this->escape($limit);
        }

        $from = $this->getFullTableName($this->tableName);

        return $this->db->select($fields, $from, $where, $orderBy, $limit);
    }

    public function update($fields, $where = '')
    {
        $table = $this->getFullTableName($this->tableName);

        if (empty($where)) {
            $where = $this->escape($where);
        }

        return $this->db->update($fields, $table, $where);
    }

    public function getLastError()
    {
        return $this->db->getLastError();
    }

    public function initDataTypes()
    {
        return $this->db->initDataTypes();
    }

    public function getFullTableName($tableName)
    {
        return $this->modx->getFullTableName($tableName);
    }

    public function unescape($string)
    {
        return stripcslashes($string);
    }
}
