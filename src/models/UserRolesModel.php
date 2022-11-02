<?php

namespace mmaurice\cabinet\core\models;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\models\Model;

class UserRolesModel extends Model
{
    const ROLE_ID_ADMIN = 1;
    const ROLE_ID_USER = 5;
    const ROLE_ID_AGENCY = 6;

    public $tableName = 'user_roles';
}
