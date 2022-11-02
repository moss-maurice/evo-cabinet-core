<?php

namespace mmaurice\cabinet\core\models;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\models\Model;
use mmaurice\cabinet\core\models\UserRolesModel;

class WebUserAttributesModel extends Model
{
    public $tableName = 'web_user_attributes';
    public $relations = [
        'roles' => ['role', [UserRolesModel::class, 'id'], self::REL_ONE],
    ];
}
