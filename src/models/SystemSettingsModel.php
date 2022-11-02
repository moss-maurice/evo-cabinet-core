<?php

namespace mmaurice\cabinet\models;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\models\Model;

class SystemSettingsModel extends Model
{
    public $tableName = 'system_settings';

    public function getList($filter = [], $getRelatedItems = false, $log = false)
    {
        $list = parent::getList($filter, $getRelatedItems, $log);

        $preparedList = [];

        if (is_array($list) and !empty($list)) {
            foreach ($list as $index => $item) {
                $preparedList[$item['setting_name']] = $item['setting_value'];
            }

            ksort($preparedList);

            return $preparedList;
        }

        return $list;
    }
}
