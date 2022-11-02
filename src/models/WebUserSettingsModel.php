<?php

namespace mmaurice\cabinet\core\models;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\models\Model;

class WebUserSettingsModel extends Model
{
    public $tableName = 'web_user_settings';

    public function getList($filter = [], $getRelatedItems = false, $log = false)
    {
        $list = parent::getList($filter, $getRelatedItems, $log);

        $list = array_map(function ($value) {
            if (!in_array($value['setting_name'], ['pss'])) {
                return $value;
            }
        }, $list);

        return $list;
    }

    public static function settingPrepare(array $settings)
    {
        if (!empty($settings)) {
            return array_combine(array_map(function ($value) {
                return $value['setting_name'];
            }, $settings), array_map(function ($value) {
                return $value['setting_value'];
            }, $settings));
        }

        return [];
    }
}
