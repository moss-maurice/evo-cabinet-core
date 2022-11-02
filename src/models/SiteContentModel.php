<?php

namespace mmaurice\cabinet\core\models;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\models\Model;

class SiteContentModel extends Model
{
    const PUBLISHED = '1';
    const UNPUBLISHED = '0';
    const DELETED = '1';
    const UNDELETED = '0';

    public $tableName = 'site_content';
}
