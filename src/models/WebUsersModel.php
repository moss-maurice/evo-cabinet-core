<?php

namespace mmaurice\cabinet\core\models;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\helpers\MailerHelper;
use mmaurice\cabinet\core\models\Model;
use mmaurice\cabinet\core\models\UserRolesModel;
use mmaurice\cabinet\core\models\WebGroupsModel;
use mmaurice\cabinet\core\models\WebUserAttributesModel;
use mmaurice\cabinet\core\models\WebUserSettingsModel;

class WebUsersModel extends Model
{
    public $tableName = 'web_users';
    public $relations = [
        'settings' => ['id', [WebUserSettingsModel::class, 'webuser'], self::REL_MANY],
        'attributes' => ['id', [WebUserAttributesModel::class, 'internalKey'], self::REL_ONE],
    ];
    public $roles = [
        'all' => 0,
        'admin' => UserRolesModel::ROLE_ID_ADMIN,
        'user' => UserRolesModel::ROLE_ID_USER,
        'agency' => UserRolesModel::ROLE_ID_AGENCY,
    ];

    public function getUserList()
    {
        $results = array();
        $users = $this->select(self::SELECT_FIELDS_ALL);
        $count = $this->getRecordCount($users);
        if ($count) {
            for ($i = 0; $i < $count; $i++) {
                $userData = $this->getRow($users);
                $results[] = $this->getUserData($userData['id']);
            }
        }
        return $results;
    }

    /**
     * Метод авторизации
     *
     * @param string $login
     * @param string $password
     * @param boolean $rememberMe
     * @return void
     */
    public function login($login, $password, $rememberMe = true)
    {
        $webUserPassword = $password;
        $webUser = $this->select('id, username', "username='{$login}' AND password='{$this->genUserHash($webUserPassword)}'");

        if ($this->getRecordCount($webUser)) {
            $this->autoLogin($login);
        }

        return $this->isLogged();
    }

    public function autoLogin($login)
    {
        $webUser = $this->select('id, username', "username='{$login}'");

        if ($this->getRecordCount($webUser)) {
            $row = $this->getRow($webUser);

            $this->loadUserData(intval($row['id']));
        }

        return $this->isLogged();
    }

    /**
     * Метод генерации хэша пароля пользователя
     *
     * @param string $password
     * @return void
     */
    public function genUserHash($password)
    {
        return md5($password);
    }

    /**
     * Метод генерации хэша пароля менеджера
     *
     * @param [type] $password
     * @param string $seed
     * @return void
     */
    public function genManagerHash($password, $seed = '1')
    {
        $algo = App::getModxConfig('pwd_hash_algo');
        if (!is_null($algo) and !empty($algo)) {
            $algorithm = $algo;
        } else {
            $algorithm = 'UNCRYPT';
        }
        $salt = md5($password . $seed);
        switch ($algorithm) {
            case 'BLOWFISH_Y':
                $salt = '$2y$07$' . substr($salt, 0, 22);
                break;
            case 'BLOWFISH_A':
                $salt = '$2a$07$' . substr($salt, 0, 22);
                break;
            case 'SHA512':
                $salt = '$6$' . substr($salt, 0, 16);
                break;
            case 'SHA256':
                $salt = '$5$' . substr($salt, 0, 16);
                break;
            case 'MD5':
                $salt = '$1$' . substr($salt, 0, 8);
                break;
        }
        if ($algorithm !== 'UNCRYPT') {
            $password = sha1($password) . crypt($password, $salt);
        } else {
            $password = sha1($salt . $password);
        }
        $result = strtolower($algorithm) . '>' . md5($salt . $password) . substr(md5($salt), 0, 8);
        return $result;
    }

    public function getUserId($login)
    {
        $user = $this->getItem([
            'select' => [
                "t.id AS id",
            ],
            'where' => [
                "t.username = '{$login}'",
            ],
        ]);

        if ($user) {
            return intval($user['id']);
        }

        return null;
    }

    public function getUserIdByEmail($email)
    {
        $user = $this->getItem([
            'select' => [
                "t.id AS id",
            ],
            'join' => [
                "JOIN " . WebUserAttributesModel::getFullModelTableName() . " AS wua ON wua.internalKey = t.id",
            ],
            'where' => [
                "wua.email = '{$email}'",
            ],
        ]);

        if ($user) {
            return intval($user['id']);
        }

        return null;
    }

    public function getUserIdByPhone($phone)
    {
        $user = $this->getItem([
            'select' => [
                "t.id AS id",
            ],
            'join' => [
                "JOIN " . WebUserAttributesModel::getFullModelTableName() . " AS wua ON wua.internalKey = t.id",
            ],
            'where' => [
                "wua.phone = '{$phone}'",
            ],
        ]);

        if ($user) {
            return intval($user['id']);
        }

        return null;
    }

    public function getLoginByEmail($login)
    {
        $userAttributesModel = new WebUserAttributesModel;
        $userModel = new WebUsersModel;

        if (!empty($login)) {
            $hasWebUser = $userAttributesModel->select('id, internalKey', "email='{$login}'");

            if ($userAttributesModel->getRecordCount($hasWebUser)) {
                $attributes = $userAttributesModel->getRow($hasWebUser);

                $webUser = $userModel->select('id, username', "id='{$attributes['internalKey']}'");

                if ($userModel->getRecordCount($webUser)) {
                    $user = $userModel->getRow($webUser);

                    return $user['username'];
                }
            }
        }

        return '';
    }

    /**
     * Метод регистрации пользователя
     *
     * @param string $login
     * @param string $password
     * @param string $passwordRetype
     * @param string $email
     * @param array $inputFields
     * @return void
     */
    public function register($login, $password, $passwordRetype, $email, $inputFields = array(), $role = false, $autologin = true)
    {
        $userAttributesModel = new WebUserAttributesModel;
        $userSettingsModel = new WebUserSettingsModel;
        $groupsModel = new WebGroupsModel;

        if (!is_null($login) and !empty($login) and !is_null($password) and !empty($password) and !is_null($passwordRetype) and !empty($passwordRetype) and !is_null($email) and !empty($email) and ($password === $passwordRetype)) {
            if (is_null($this->getUserId($login))) {
                $fields = array(
                    'username' => $login,
                    'password' => $this->genUserHash($password),
                );

                if ($this->insert($fields)) {
                    if ($userId = $this->getInsertId()) {

                        $this->updatePass($userId, $password, $passwordRetype);

                        if (array_key_exists('fullname', $inputFields) and !empty($inputFields['fullname'])) {
                            $fullname = trim($inputFields['fullname']);
                        } else if (array_key_exists('first_name', $inputFields) or array_key_exists('last_name', $inputFields) or array_key_exists('middle_name', $inputFields)) {
                            $fullname = [];

                            if (!empty($inputFields['first_name'])) {
                                $fullname[] = trim($inputFields['first_name']);
                            }

                            if (!empty($inputFields['last_name'])) {
                                $fullname[] = trim($inputFields['last_name']);
                            }

                            if (!empty($inputFields['middle_name'])) {
                                $fullname[] = trim($inputFields['middle_name']);
                            }

                            $fullname = trim(implode(' ', $fullname));
                        }

                        $fields = array(
                            'internalKey' => $userId,
                            'fullname' => isset($fullname) ? $fullname : $login,
                            'email' => $email,
                            'role' => UserRolesModel::ROLE_ID_USER,
                            'createdon' => time(),
                            'editedon' => time(),
                            'dob' => 0,
                        );

                        $fields['role'] = is_bool($role) ? UserRolesModel::ROLE_ID_USER : $role;

                        if (array_key_exists('phone', $inputFields) and !empty($inputFields['phone'])) {
                            $fields['phone'] = $inputFields['phone'];
                            unset($inputFields['phone']);
                        }
                        if (array_key_exists('mobilephone', $inputFields) and !empty($inputFields['mobilephone'])) {
                            $fields['mobilephone'] = $inputFields['mobilephone'];
                            unset($inputFields['mobilephone']);
                        }
                        if (array_key_exists('gender', $inputFields) and !empty($inputFields['gender'])) {
                            $fields['gender'] = $inputFields['gender'];
                            unset($inputFields['gender']);
                        }
                        if (array_key_exists('country', $inputFields) and !empty($inputFields['country'])) {
                            $fields['country'] = $inputFields['country'];
                            unset($inputFields['country']);
                        }
                        if (array_key_exists('street', $inputFields) and !empty($inputFields['street'])) {
                            $fields['street'] = $inputFields['street'];
                            unset($inputFields['street']);
                        }
                        if (array_key_exists('city', $inputFields) and !empty($inputFields['city'])) {
                            $fields['city'] = $inputFields['city'];
                            unset($inputFields['city']);
                        }
                        if (array_key_exists('state', $inputFields) and !empty($inputFields['state'])) {
                            $fields['state'] = $inputFields['state'];
                            unset($inputFields['state']);
                        }
                        if (array_key_exists('zip', $inputFields) and !empty($inputFields['zip'])) {
                            $fields['zip'] = $inputFields['zip'];
                            unset($inputFields['zip']);
                        }
                        if (array_key_exists('fax', $inputFields) and !empty($inputFields['fax'])) {
                            $fields['fax'] = $inputFields['fax'];
                            unset($inputFields['fax']);
                        }

                        if (is_array($inputFields) and !empty($fields)) {
                            if ($userAttributesModel->insert($fields)) {
                                $inputFields['login_home'] = 465;

                                foreach ($inputFields as $inputFieldsKey => $inputFieldsValue) {
                                    $field = array(
                                        'webuser' => $userId,
                                        'setting_name' => $inputFieldsKey,
                                        'setting_value' => $inputFieldsValue,
                                    );
                                    $userSettingsModel->insert($field);
                                }

                                $this->updatePass($userId, $password, $password);
                            }
                        }

                        $fields = array(
                            'webgroup' => 1,
                            'webuser' => $userId,
                        );
                        $groupsModel->insert($fields);
                    }
                }
                if ($autologin) {
                    $this->login($login, $password);
                }

                return true;
            }
        }
        return false;
    }

    public function updatePass($userId, $password, $passwordRetype)
    {
        $userSettingsModel = new WebUserSettingsModel;
        if (!is_null($password) and !empty($password) and !is_null($passwordRetype) and !empty($passwordRetype) and ($password === $passwordRetype)) {
            $hasWebUser = $this->select('id, username', "id='" . $userId . "'");
            if ($this->getRecordCount($hasWebUser)) {
                $this->update(array('password' => $this->genUserHash($password)), "id = '" . $userId . "'");
                return true;
            }
        }
        return false;
    }

    public function updateProfile($userId, $inputFields = array())
    {
        $result = false;
        $userAttributesModel = new WebUserAttributesModel();
        $userSettingsModel = new WebUserSettingsModel();

        if (in_array('password', array_keys($inputFields)) and in_array('password_retype', array_keys($inputFields))) {
            $this->updatePass($userId, $inputFields['password'], $inputFields['password_retype']);
            unset($inputFields['password']);
            unset($inputFields['password_retype']);
        }

        $fields = [];
        if (array_key_exists('first_name', $inputFields) and array_key_exists('last_name', $inputFields) and array_key_exists('middle_name', $inputFields)) {
            $fullname = '';
            if (array_key_exists('last_name', $inputFields)) {
                $fullname .= $inputFields['last_name'];
            }
            if (array_key_exists('first_name', $inputFields)) {
                $fullname .= ' ' . $inputFields['first_name'];
            }
            if (array_key_exists('middle_name', $inputFields)) {
                $fullname .= ' ' . $inputFields['middle_name'];
            }
            $fullname = trim(str_replace('  ', ' ', $fullname), ' ');
            $inputFields['fullname'] = $fullname;
        }
        if (array_key_exists('role', $inputFields) and !empty($inputFields['role'])) {
            $fields['role'] = $this->escape($inputFields['role']);
            unset($inputFields['role']);
        }
        if (array_key_exists('fullname', $inputFields) and !empty($inputFields['fullname'])) {
            $fields['fullname'] = $this->escape($inputFields['fullname']);
            unset($inputFields['fullname']);
        }
        if (array_key_exists('email', $inputFields) and !empty($inputFields['email'])) {
            $fields['email'] = $this->escape($inputFields['email']);
            unset($inputFields['email']);
        }
        if (array_key_exists('phone', $inputFields)) {
            $fields['phone'] = $this->escape($inputFields['phone']);
            unset($inputFields['phone']);
        }
        if (array_key_exists('dob', $inputFields)) {
            $fields['dob'] = $this->escape($inputFields['dob']);
            unset($inputFields['dob']);
        }
        if (array_key_exists('mobilephone', $inputFields)) {
            $fields['mobilephone'] = $this->escape($inputFields['mobilephone']);
            unset($inputFields['mobilephone']);
        }
        if (array_key_exists('gender', $inputFields)) {
            $fields['gender'] = $this->escape($inputFields['gender']);
            unset($inputFields['gender']);
        }
        if (array_key_exists('country', $inputFields)) {
            $fields['country'] = $this->escape($inputFields['country']);
            unset($inputFields['country']);
        }
        if (array_key_exists('street', $inputFields)) {
            $fields['street'] = $this->escape($inputFields['street']);
            unset($inputFields['street']);
        }
        if (array_key_exists('city', $inputFields)) {
            $fields['city'] = $this->escape($inputFields['city']);
            unset($inputFields['city']);
        }
        if (array_key_exists('state', $inputFields)) {
            $fields['state'] = $this->escape($inputFields['state']);
            unset($inputFields['state']);
        }
        if (array_key_exists('zip', $inputFields)) {
            $fields['zip'] = $this->escape($inputFields['zip']);
            unset($inputFields['zip']);
        }
        if (array_key_exists('fax', $inputFields)) {
            $fields['fax'] = $this->escape($inputFields['fax']);
            unset($inputFields['fax']);
        }
        if (!empty($fields)) {
            $fields['editedon'] = time();

            if ($userAttributesModel->update($fields, "internalKey = '" . $userId . "'")) {
                $result = true;
            }
        }

        if (is_array($inputFields) and !empty($inputFields)) {
            foreach ($inputFields as $inputFieldsKey => $inputFieldsValue) {
                $userSetting = $userSettingsModel->select('*', "webuser = '" . $userId . "' AND setting_name = '" . $inputFieldsKey . "'");

                if ($userSettingsModel->getRecordCount($userSetting) > 0) {
                    if ($userSettingsModel->update(array(
                        'setting_value' => $userSettingsModel->escape($inputFieldsValue),
                    ), "webuser = '" . $userId . "' AND setting_name = '" . $inputFieldsKey . "'")) {
                        $result = true;
                    }
                } else {
                    $userSettingsModel->insert(array(
                        'webuser' => $userId,
                        'setting_name' => $inputFieldsKey,
                        'setting_value' => $userSettingsModel->escape($inputFieldsValue),
                    ));
                }
            }
        }

        return $result;
    }

    /**
     * Метод разлогинивания пользователя
     *
     * @return void
     */
    public function logout()
    {
        if ($this->isLogged()) {
            $_SESSION['web_user'] = null;
            unset($_SESSION['web_user']);
        }
        return !$this->isLogged();
    }

    /**
     * Метод проверки состояния авторизации пользователя
     *
     * @return boolean
     */
    public function isLogged()
    {
        if (array_key_exists('web_user', $_SESSION)) {
            return true;
        }
        return false;
    }

    /**
     * Метод авторегистрации залогиненного менеджера
     *
     * @return void
     */
    public function autoManagerRegister()
    {
        $userAttributesModel = new WebUserAttributesModel;
        $userSettingsModel = new WebUserSettingsModel;
        $groupsModel = new WebGroupsModel;
        if (array_key_exists('mgrShortname', $_SESSION) and array_key_exists('mgrInternalKey', $_SESSION)) {
            $login = $_SESSION['mgrShortname'];
            $webUser = $this->select('id, username', "username='" . $login . "' AND password='manager_users'");
            if (!$this->getRecordCount($webUser)) {
                $fields = array(
                    'username' => $login,
                    'password' => 'manager_users',
                );
                if ($this->insert($fields)) {
                    if ($userId = $this->getInsertId()) {
                        $fields = array(
                            'internalKey' => $userId,
                            'fullname' => $login,
                            'createdon' => time(),
                        );
                        if (array_key_exists('mgrEmail', $_SESSION)) {
                            $fields['email'] = $_SESSION['mgrEmail'];
                        }
                        if ($userAttributesModel->insert($fields)) {
                            $fields = array(
                                'webuser' => $userId,
                                'setting_name' => 'login_home',
                                'setting_value' => '465',
                            );
                            if ($userSettingsModel->insert($fields)) {
                                $fields = array(
                                    'webgroup' => 1,
                                    'webuser' => $userId,
                                );
                                $groupsModel->insert($fields);
                            }
                        }
                    }
                }
            }
            return $this->autoManagerLogin();
        }
        return false;
    }

    /**
     * Метод автоматического входа менеджера
     *
     * @return void
     */
    public function autoManagerLogin()
    {
        if (array_key_exists('mgrShortname', $_SESSION) and array_key_exists('mgrInternalKey', $_SESSION)) {
            $login = $_SESSION['mgrShortname'];
            $webUser = $this->select('id, username', "username='" . $login . "' AND password='manager_users'");
            if ($this->getRecordCount($webUser)) {
                $row = $this->getRow($webUser);
                $this->loadUserData((int) $row['id']);
            }
        }
        return $this->isLogged();
    }

    /**
     * Метод загрузки пользовательских данных
     *
     * @param integer $id
     * @return void
     */
    public function loadUserData($id)
    {
        $userData = $this->getUserData($id);
        if (!empty($userData)) {
            $_SESSION['web_user'] = $userData;
            return true;
        }
        return false;
    }

    public function deleteUser($id = null)
    {
        if (is_null($id)) {
            $id = $this->getId();
        }

        $result = $this->update([
            'deleted' => '1',
        ], "id = '{$id}'");

        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * Метод получения пользовательских данных
     *
     * @param integer $id
     * @return void
     */
    public function getUserData($id)
    {
        $result = array();
        $userAttributesModel = new WebUserAttributesModel;
        $userSettingsModel = new WebUserSettingsModel;
        $webUser = $this->select('id, username', "id='" . $id . "'");
        if ($this->getRecordCount($webUser)) {
            $row = $this->getRow($webUser);
            $result = array(
                'user_id' => (int) $row['id'],
                'user_name' => $this->unescape($row['username']),
            );
            $webUserAttributes = $userAttributesModel->select(WebUserAttributesModel::SELECT_FIELDS_ALL, "internalKey='" . $result['user_id'] . "'");
            if ($userAttributesModel->getRecordCount($webUserAttributes)) {
                $row = $userAttributesModel->getRow($webUserAttributes);
                $result['attributes'] = array(
                    'fullname' => $this->unescape($row['fullname']),
                    'role' => (int) $row['role'],
                    'email' => $this->unescape($row['email']),
                    'phone' => $this->unescape($row['phone']),
                    'mobilephone' => $this->unescape($row['mobilephone']),
                    'blocked' => (int) $row['blocked'],
                    'blockeduntil' => (int) $row['blockeduntil'],
                    'blockedafter' => (int) $row['blockedafter'],
                    'logincount' => (int) $row['logincount'],
                    'lastlogin' => (int) $row['lastlogin'],
                    'thislogin' => (int) $row['thislogin'],
                    'failedlogincount' => (int) $row['failedlogincount'],
                    'sessionid' => $this->unescape($row['sessionid']),
                    'dob' => (int) $row['dob'],
                    'gender' => (int) $row['gender'],
                    'country' => $this->unescape($row['country']),
                    'street' => $this->unescape($row['street']),
                    'city' => $this->unescape($row['city']),
                    'state' => $this->unescape($row['state']),
                    'zip' => $this->unescape($row['zip']),
                    'fax' => $this->unescape($row['fax']),
                    'photo' => $this->unescape($row['photo']),
                    'comment' => $this->unescape($row['comment']),
                );
            }
            $webUserSettings = $userSettingsModel->select(WebUserSettingsModel::SELECT_FIELDS_ALL, "webuser='" . $result['user_id'] . "'");
            if ($userSettingsModel->getRecordCount($webUserSettings)) {
                $result['settings'] = array();
                while ($row = $userSettingsModel->getRow($webUserSettings)) {
                    $result['settings'][$row['setting_name']] = $this->unescape($row['setting_value']);
                }
            }
        }
        return $result;
    }

    public function hasUser($login)
    {
        $login = $this->escape($login);
        $webUser = $this->select(self::SELECT_FIELDS_ALL, "username='" . $login . "'");
        if ($this->getRecordCount($webUser)) {
            return true;
        }
        return false;
    }

    public function forgotPass($email, $code = null)
    {
        $userAttributesModel = new WebUserAttributesModel;
        $userModel = new WebUsersModel;

        if (is_int($email) and !is_null($code)) {
            $hasWebUser = $userAttributesModel->select('id, internalKey, email', "internalKey='" . $email . "'");
            if ($userAttributesModel->getRecordCount($hasWebUser)) {
                $attributes = $userAttributesModel->getRow($hasWebUser);

                $webUser = $userModel->select('id, username, password', "id='" . $attributes['internalKey'] . "'");
                if ($userModel->getRecordCount($webUser)) {
                    $user = $userModel->getRow($webUser);

                    if (md5($user['id'] . $user['password']) == $code) {
                        $password = $this->passGenerate();

                        if ($this->updatePass($attributes['internalKey'], $password, $password)) {
                            $subject = 'Восстановление пароля';
                            $parametrs = [
                                'login' => $user['username'],
                                'password' => $password,
                            ];
                            $content = MailerHelper::renderTemplate('mailer/restore', $parametrs);
                            $layout = MailerHelper::renderTemplate('layouts/mail', array_merge($parametrs, ['content' => $content]));

                            return MailerHelper::send($attributes['email'], $subject, $layout);
                        }
                    }
                }
            }
        } elseif (is_string($email)) {
            $hasWebUser = $userAttributesModel->select('id, internalKey, email', "email='" . $email . "'");
            if ($userAttributesModel->getRecordCount($hasWebUser)) {
                $attributes = $userAttributesModel->getRow($hasWebUser);

                $webUser = $userModel->select('id, username, password', "id='" . $attributes['internalKey'] . "'");
                if ($userModel->getRecordCount($webUser)) {
                    $user = $userModel->getRow($webUser);

                    $subject = 'Восстановление пароля';
                    $parametrs = [
                        'key' => $user['id'] . 'ff' . md5($user['id'] . $user['password']),
                    ];
                    $content = MailerHelper::renderTemplate('mailer/restoreQuery', $parametrs);
                    $layout = MailerHelper::renderTemplate('layouts/mail', array_merge($parametrs, ['content' => $content]));

                    return MailerHelper::send($attributes['email'], $subject, $layout);
                }
            }
        }

        return false;
    }

    /**
     * Метод получения идентификатора пользователя
     *
     * @return void
     */
    public function getId()
    {
        if (isset($_SESSION) and array_key_exists('web_user', $_SESSION) and array_key_exists('user_id', $_SESSION['web_user'])) {
            return intval($_SESSION['web_user']['user_id']);
        }

        return null;
    }

    public function getIdByLogin($login)
    {
        $result = 0;
        $webUser = $this->select('id, username', "username='" . $login . "'");
        if ($this->getRecordCount($webUser)) {
            $result = $this->getRow($webUser);
        }
        return $result;
    }

    public function getIdById($id)
    {
        $result = 0;
        $webUser = $this->select('id, username', "id='" . $id . "'");
        if ($this->getRecordCount($webUser)) {
            $result = $this->getRow($webUser);
        }
        return $result;
    }

    /**
     * Метод получения логина пользователя
     *
     * @return void
     */
    public function getLogin()
    {
        if (array_key_exists('web_user', $_SESSION) and array_key_exists('user_name', $_SESSION['web_user'])) {
            return (string) $_SESSION['web_user']['user_name'][$name];
        }
        return '';
    }

    public function getRole()
    {
        $role = $this->roles[UserRolesModel::ROLE_ID_USER];

        if (array_key_exists('web_user', $_SESSION) and array_key_exists('attributes', $_SESSION['web_user']) and array_key_exists('role', $_SESSION['web_user']['attributes'])) {
            foreach ($this->roles as $roleName => $roleId) {
                if (intval($_SESSION['web_user']['attributes']['role']) === intval($roleId)) {
                    $role = $roleName;

                    break;
                }
            }
        }

        return $role;
    }

    /**
     * Метод получения аттрибутов аккаунта
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return void
     */
    public function getAttribute($name, $defaultValue = null)
    {
        if (array_key_exists('web_user', $_SESSION)) {
            $userData = $this->getUserData(intval($_SESSION['web_user']['user_id']));

            if (array_key_exists('attributes', $userData) and array_key_exists($name, $userData['attributes'])) {
                return $userData['attributes'][$name];
            }
        }

        return $defaultValue;
    }

    /**
     * Метод получения настроек аккаунта
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return void
     */
    public function getSetting($name, $defaultValue = null)
    {
        if (array_key_exists('web_user', $_SESSION)) {
            $userData = $this->getUserData(intval($_SESSION['web_user']['user_id']));

            if (array_key_exists('settings', $userData) and array_key_exists($name, $userData['settings'])) {
                return $userData['settings'][$name];
            }
        }

        return $defaultValue;
    }

    /**
     * Метод записи аттрибутов аккаунта
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setAttribute($name, $value, $userId = null)
    {
        if (array_key_exists('web_user', $_SESSION) and array_key_exists('attributes', $_SESSION['web_user']) and array_key_exists($name, $_SESSION['web_user']['attributes'])) {
            $userAttributesModel = new WebUserAttributesModel;
            $_SESSION['web_user']['attributes'][$name] = $value;
            if (is_null($userId)) {
                $result = $userAttributesModel->update([
                    $name => $value,
                    'editedon' => time(),
                ], "id = '{$this->getId()}'");
            } else {
                $result = $userAttributesModel->update([
                    $name => $value,
                    'editedon' => time(),
                ], "id = '{$userId}'");
            }
            if ($result) {
                return true;
            }
        }
        return false;
    }

    /**
     * Метод записи настроек аккаунта
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setSetting($name, $value, $userId = null)
    {
        if (array_key_exists('web_user', $_SESSION) and array_key_exists('settings', $_SESSION['web_user'])) {
            $userSettingsModel = new WebUserSettingsModel;
            $hasSetting = array_key_exists($name, $_SESSION['web_user']['settings']);
            $_SESSION['web_user']['settings'][$name] = $value;
            if (is_null($userId)) {
                $userId = $this->getId();
            }
            if ($hasSetting) {
                $setting = $userSettingsModel->select(WebUserSettingsModel::SELECT_FIELDS_ALL, "webuser = '" . $userId . "' AND setting_name = '" . $name . "'");
                if ($userSettingsModel->getRecordCount($setting)) {
                    if ($userSettingsModel->update(array('setting_value' => $value), "webuser = '" . $userId . "' AND setting_name = '" . $name . "'")) {
                        return true;
                    }
                } else {
                    if ($userSettingsModel->insert(array('setting_name' => $name, 'setting_value' => $value, 'webuser' => $userId))) {
                        return true;
                    }
                }
            } else {
                if ($userSettingsModel->insert(array('setting_name' => $name, 'setting_value' => $value, 'webuser' => $userId))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Метод обновления пароля аккаунта
     *
     * @param string $password
     * @param string $passwordRetype
     * @return void
     */
    public function updatePassword($password, $passwordRetype)
    {
        if (!empty($password) and !empty($passwordRetype) and ($password === $passwordRetype)) {
            $passHash = $this->genUserHash($password);
            $result = $this->update(array('password' => $passHash), "id = '" . $this->getId() . "'");
            if ($result) {
                $this->updatePass($this->getId(), $password, $passwordRetype);
                return true;
            }
        }
        return false;
    }

    public function kyr2Lat($string)
    {
        $kyr = array('A', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'a', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ', '-');
        $lat = array('A', 'B', 'V', 'G', 'D', 'E', 'YO', 'ZH', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'TC', 'CH', 'SH', 'SH', '_', 'Y', '_', 'E', 'YU', 'YA', 'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'tc', 'ch', 'sh', 'sh', '_', 'y', '_', 'e', 'yu', 'ya', '_', '_');
        $string = str_replace('__', '_', str_replace($kyr, $lat, $string));
        return $string;
    }

    public function passGenerate($number = 10)
    {
        $symbols = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'v', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
        $result = '';
        for ($i = 0; $i < $number; $i++) {
            $index = rand(0, count($symbols) - 1);
            $result .= $symbols[$index];
        }
        return $result;
    }

    /**
     * Сервисный метод генерации пароля.
     *
     * @return string
     */
    public function generatePassword()
    {
        return mb_substr(md5(static::generateCode()), 2, 10);
    }

    /**
     * Сервисный метод создания SMS-кода.
     *
     * @return string
     */
    static public function generateCode()
    {
        $code = mb_substr(str_replace('.', '', mb_stristr(microtime(true), '.')) . rand(0, 9999), 0, 4);

        if (iconv_strlen($code) < 4) {
            $code = mb_substr($code .= rand(0, 9999), 0, 4);
        }

        return $code;
    }

    public function checkUserIsset($userLogin)
    {
        $userModel = new WebUsersModel;

        if ($userLogin) {
            if (!empty($userData = $userModel->getIdByLogin($userLogin))) {
                if ($id = intval($userData['id'])) {
                    return $id;
                }
            }
        }

        return false;
    }

    public function getUsersByRole($roleId, $page, $limit)
    {
        $roleId = intval($roleId);
        $page = intval($page);
        $limit = intval($limit);

        $usersCounts = $this->getItem([
            'select' => [
                "COUNT(*) as count"
            ],
            'from' => "`evo_web_user_attributes` ",
            'where' => [
                "t.role = '" . $roleId . "'"
            ]
        ]);

        $total = intval($usersCounts['count']);
        $pages = ceil($total / $limit);

        $usersList = $this->getList([
            'join' => [
                "JOIN `evo_web_user_attributes` as e_wua ON t.id = e_wua.internalKey"
            ],
            'where' => [
                "e_wua.role = '" . $roleId . "'"
            ],
            'limit' => $limit,
            'offset' => ($limit * ($page - 1)) < 0 ? 0 : ($limit * ($page - 1)),
        ], true);

        return [
            'usersList' => $usersList,
            'pages' => $pages
        ];
    }

    public function getUsersList($page, $limit, $filter = [])
    {
        $filter = array_filter($filter);

        $page = intval($page);

        if (!is_null($limit)) {
            $limit = intval($limit);
        }

        $join = [
            "LEFT JOIN " . WebUserAttributesModel::getFullModelTableName() . " wua ON wua.internalKey = t.id",
        ];
        $where = [
            "t.deleted = '0'",
        ];

        $order = [];

        if (array_key_exists('login', $filter) and !empty($filter['login'])) {
            $where = array_merge($where, [
                "AND t.username LIKE '%" . $filter['login'] . "%'",
                "AND wua.role = '{$filter['roleId']}'",
            ]);
        }

        if (array_key_exists('roleId', $filter) and !empty($filter['roleId'])) {
            $join = array_merge($join, [
                //"LEFT JOIN " . WebUserSettingsModel::getFullModelTableName() . " wus_agency_status ON wus_agency_status.webuser = t.id AND wus_agency_status.setting_name = 'agency_status'",
                "LEFT JOIN " . WebUserSettingsModel::getFullModelTableName() . " wus_type ON wus_type.webuser = t.id AND wus_type.setting_name = 'type'",
                //"LEFT JOIN " . WebUserSettingsModel::getFullModelTableName() . " wus_role ON wus_role.webuser = t.id AND wus_role.setting_name = 'role'",
            ]);

            if (intval($filter['roleId']) === UserRolesModel::ROLE_ID_AGENCY) {
                $where = array_merge($where, [
                    //"AND wus_agency_status.setting_value = 'true'",
                    "AND wus_type.setting_value IN ('agency')",
                    //"AND wua.role = '{$filter['roleId']}'",
                ]);
            } else {
                $where = array_merge($where, [
                    //"AND (wus_agency_status.setting_value = 'false' OR wus_agency_status.setting_value IS NULL)",
                    "AND (wus_type.setting_value NOT IN ('agency') OR wus_type.setting_value IS NULL)",
                    //"AND wua.role = '{$filter['roleId']}'",
                ]);
            }
        }

        if (array_key_exists('agency', $filter) and !empty($filter['agency'])) {
            $join = array_merge($join, [
                "LEFT JOIN " . WebUserSettingsModel::getFullModelTableName() . " wus ON wus.webuser = t.id AND wus.setting_name = 'agency'",
            ]);

            $where = array_merge($where, [
                "AND wus.setting_value LIKE '%" . $filter['agency'] . "%'",
            ]);
        }

        if ((array_key_exists('email', $filter) and !empty($filter['email'])) or (array_key_exists('phone', $filter) and !empty($filter['phone']))) {
            if (array_key_exists('email', $filter) and !empty($filter['email'])) {
                $where = array_merge($where, [
                    "AND wua.email LIKE '%" . $filter['email'] . "%'",
                ]);
            }

            if (array_key_exists('phone', $filter) and !empty($filter['phone'])) {
                $where = array_merge($where, [
                    "AND (RIGHT('0000000000' + REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(wua.phone, ' ', ''), '+', ''), '-', ''), '(', ''), ')', ''), 10) LIKE '%" . $filter['phone'] . "%' OR RIGHT('0000000000' + REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(wua.mobilephone, ' ', ''), '+', ''), '-', ''), '(', ''), ')', ''), 10) LIKE '%" . $filter['phone'] . "%')",
                ]);
            }
        }

        switch ($filter['field']) {
            case 'id':
                $order = array_merge($order, [
                    "t.id " . $filter['direction'],
                ]);

                break;
            case 'login':
                $order = array_merge($order, [
                    "t.username " . $filter['direction'],
                ]);

                break;
            case 'name':
                $order = array_merge($order, [
                    "wua.fullname " . $filter['direction'],
                ]);

                break;
            case 'email':
                $order = array_merge($order, [
                    "wua.email " . $filter['direction'],
                ]);

                break;
            case 'phone':
                $order = array_merge($order, [
                    "wua.phone " . $filter['direction'],
                ]);

                break;
            default:
                $order = array_merge($order, [
                    "wua.fullname ASC",
                ]);

                break;
        }

        $usersCounts = $this->getList([
            'join' => $join,
            'where' => $where,
        ]);

        $total = (is_array($usersCounts) ? count($usersCounts) : 0);

        if (is_null($limit)) {
            $limit = $total;
        }

        $pages = ceil($total / $limit);

        $usersList = $this->getList([
            'join' => $join,
            'where' => $where,
            'order' => $order,
            'limit' => $limit,
            'offset' => ($limit * ($page - 1)) < 0 ? 0 : ($limit * ($page - 1)),
        ], true);

        return [
            'usersList' => $usersList,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit,
        ];
    }
}
