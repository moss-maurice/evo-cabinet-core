<?php

namespace mmaurice\cabinet\core\models;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\models\Model;
use mmaurice\cabinet\core\models\SiteTmplvarContentvaluesModel;
use mmaurice\cabinet\core\models\SiteTmplvarTemplatesModel;
use mmaurice\cabinet\core\models\SiteTmplvarsModel;

class SiteContentModel extends Model
{
    const PUBLISHED = '1';
    const UNPUBLISHED = '0';
    const DELETED = '1';
    const UNDELETED = '0';

    public $tableName = 'site_content';

    public function __construct()
    {
        parent::__construct();

        // Добавляем реляцию для подключения TV-полей всех моделей, которые связаны с таблицей site_content
        // Реляция собрана в конструкторе, так как используются статичные методы классов, что не возможно в секции
        // переменных класса.

        // Логика очень простая:
        // 1) Отношение обязательно REL_MANY (так как эндпоинт - это ТВ-поле, а их к странице много)
        // 2) Для передачи собственного id в секцию JOIN, мы должны первым делом получить дубль самой себя. Но, чтобы не
        //    подключать остальные реляции, коннектимся к классу SiteContentModel
        // 3) Через JOIN подключаем таблицу всех ТВ-полей к шаблону, потом самих ТВ-полей, а потом значений ТВ-полей к
        //    странице. И именно тут нам нужен id нашей страницы, который мы пробросили в п.1, иначе мы бы получили ТВ-
        //    поля ко всем страницам. Иначе сделать не получится, потому что у нас может не быть значения для ТВ-поля,
        //    но может быть значение по-умолчанию для ТВ-поля. Но, так как мы не знаем через таблицу значений ТВ-полей,
        //    что конкретное ТВ-поле есть или его нет, то именно поэтому мы и провернули логику подключения ТВ-полей
        //    через справочник ТВ-полей к шаблонам с пробросом id записи через дубль самого себя.

        // Логика чуть запутанная для понимания, но это то, что нужно
        $this->relations['tv'] = ['id', [static::class, 'id'], self::REL_MANY, [
            'alias' => 'sc',
            'select' => [
                "tv.*",
                "tvcv.id AS value_id",
                "COALESCE(tvcv.value, tv.default_text, '') AS value",
            ],
            'join' => [
                "LEFT JOIN " . SiteTmplvarTemplatesModel::getFullModelTableName() . " tvt ON tvt.templateid = sc.template",
                "LEFT JOIN " . SiteTmplvarsModel::getFullModelTableName() . " tv ON tv.id = tvt.tmplvarid",
                "LEFT JOIN " . SiteTmplvarContentvaluesModel::getFullModelTableName() . " tvcv ON tvcv.tmplvarid = tv.id AND tvcv.contentid = sc.id",
            ],
        ]];
    }

    public function getRelatedData($item, $getRelatedItems = false, $log = false)
    {
        $item = parent::getRelatedData($item, $getRelatedItems, $log);

        // ТВ-поля приходят в виде массива. Но мы бы хотели иметь отношение ключ => значение, где ключем выступает имя
        // ТВ-поля, а значением - его значение, значение по-умолчанию или пусто.
        // Сделаем это тут в таком виде:
        if (array_key_exists('tv', $item)) {
            $tv = $item['tv'];

            $item['tv'] = [];

            array_filter($tv, function ($tvItem) use (&$item) {
                $item['tv'][$tvItem['name']] = (isset($tvItem['value']) ? $tvItem['value'] : '');

                if (stripos($item['tv'][$tvItem['name']], '||')) {
                    $item['tv'][$tvItem['name']] = explode('||', $item['tv'][$tvItem['name']]);
                }

                return true;
            });
        }

        return $item;
    }
}
