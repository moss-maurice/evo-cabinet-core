<?php

namespace mmaurice\cabinet\core\classes;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\configs\MainConfig;
use mmaurice\cabinet\core\prototypes\ClassPrototype;
use mmaurice\cabinet\core\providers\ModxProvider;

class InstallerClass extends ClassPrototype
{
    const STEP_WELCOME = 'welcome';
    const STEP_BACKUP = 'backup';
    const STEP_INSTALL = 'install';
    const STEP_DONE = 'done';

    protected $modx;
    protected $timestamp;

    protected $sourcesTemplates = [
        'main' => [
            'templatename' => 'Шаблон личного кабинета',
            'content' => '{{HEADER}}\r\n<main class="b-main">\r\n    <div class="wr">\r\n        <div>\r\n            [*content*]\r\n        </div>\r\n    </div>\r\n</main>\r\n{{FOOTER}}',
            'selectable' => 1,
        ],
    ];

    protected $sourcesResources = [
        'main' => [
            'type' => 'document',
            'contentType' => 'text/html',
            'alias' => 'lk',
            'pagetitle' => 'Личный кабинет',
            'published' => 1,
            'parent' => 0,
            'isfolder' => 0,
            'introtext' => '',
            'content' => '',
            'richtext' => 1,
            'menuindex' => 1000,
            'searchable' => 1,
            'cacheable' => 0,
            'createdby' => 1,
            'editedby' => 1,
            'publishedby' => 1,
            'deleted' => 0,
            'deletedon' => 0,
            'deletedby' => 0,
            'donthit' => 0,
            'privateweb' => 0,
            'privatemgr' => 0,
            'content_dispo' => 0,
            'hidemenu' => 0,
            'alias_visible' => 0,
        ],
    ];

    protected $sourcesPlugins = [
        'LK Handler' => [
            'description' => '<strong>0.1</strong> Users Private Cabinet for ModX Evo',
            'plugincode' => 'require(realpath($_SERVER[\\\'DOCUMENT_ROOT\\\'] . \\\'/assets/plugins/modx-evo-lk/web.php\\\'));\r\n',
            'locked' => 0,
            'properties' => '',
            'disabled' => 0,
            'hooks' => [
                [
                    'evtid' => 1000,
                    'priority' => 0,
                ],
                [
                    'evtid' => 20,
                    'priority' => 0,
                ],
                [
                    'evtid' => 91,
                    'priority' => 0,
                ],
            ],
        ],
    ];

    protected $sourceDump = [
        "DROP TABLE IF EXISTS `web_users_orders`;",
        "CREATE TABLE `web_users_orders` (`id` int(7) NOT NULL AUTO_INCREMENT, `webuser` int(7) NOT NULL, `from` varchar(2048) NOT NULL, `to` varchar(2048) NOT NULL, `adults` int(11) NOT NULL, `children` int(11) NOT NULL, `datefrom` date NOT NULL, `dateto` date NOT NULL, `nightsfrom` int(11) NOT NULL, `nightsto` int(11) NOT NULL, `day` enum('0','1') NOT NULL DEFAULT '0', `hotel` enum('Любой','2','3','4','5') NOT NULL DEFAULT 'Любой', `meal` enum('Любое','RO','BB','HB','FB','AI','UAI') NOT NULL DEFAULT 'Любое', `pricefrom` int(11) NOT NULL, `priceto` int(11) NOT NULL, `total_price` int(11) NOT NULL DEFAULT '0', `currency` enum('RUB','USD','EUR') NOT NULL DEFAULT 'RUB', `pricepart` enum('всех','человека') NOT NULL DEFAULT 'всех', `name` varchar(2048) NOT NULL, `tel` varchar(2048) NOT NULL, `email` varchar(2048) NOT NULL, `comment` text NOT NULL, `status` enum('new','approved','nonapproved') NOT NULL DEFAULT 'new', `add_date` datetime NOT NULL, `update_date` datetime NOT NULL, PRIMARY KEY (`id`)) ENGINE = MyISAM DEFAULT CHARSET = utf8;",
        "DROP TABLE IF EXISTS `web_users_orders_files`;",
        "CREATE TABLE `web_users_orders_files` (`id` int(10) NOT NULL AUTO_INCREMENT, `order_id` int(10) NOT NULL, `file_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL, `real_file_name` varchar(512) COLLATE utf8_unicode_ci NOT NULL, `mime` varchar(256) COLLATE utf8_unicode_ci NOT NULL, `size` varchar(10) COLLATE utf8_unicode_ci NOT NULL, `date` datetime NOT NULL, PRIMARY KEY (`id`)) ENGINE='InnoDB' DEFAULT CHARSET = utf8 COLLATE=utf8_unicode_ci;",
        "DROP TABLE IF EXISTS `web_user_threads`;",
        "CREATE TABLE `web_user_threads` (`id` int(10) NOT NULL AUTO_INCREMENT, `webuser` int(10) NOT NULL, `subject` varchar(2048) NOT NULL, `order_id` int(10) NOT NULL DEFAULT '0', `create_date` datetime NOT NULL, PRIMARY KEY (`id`)) ENGINE = MyISAM DEFAULT CHARSET = utf8;",
        "DROP TABLE IF EXISTS `web_user_thread_files`;",
        "CREATE TABLE `web_user_thread_files` (`id` int(10) NOT NULL AUTO_INCREMENT, `message_id` int(10) NOT NULL, `file_name` varchar(64) NOT NULL, `real_file_name` varchar(512) NOT NULL, `mime` varchar(256) NOT NULL, `size` int(10) NOT NULL, `date` datetime NOT NULL, PRIMARY KEY (`id`)) ENGINE = MyISAM DEFAULT CHARSET = utf8;",
        "DROP TABLE IF EXISTS `web_user_thread_messages`;",
        "CREATE TABLE `web_user_thread_messages` (`id` int(10) NOT NULL AUTO_INCREMENT, `sender` int(10) NOT NULL, `thread_id` int(10) NOT NULL, `message` text NOT NULL, `read_user` tinyint(1) NOT NULL DEFAULT '0', `date` datetime NOT NULL, PRIMARY KEY (`id`)) ENGINE = MyISAM DEFAULT CHARSET = utf8;",
        "DROP TABLE IF EXISTS `web_user_tourists`;",
        "CREATE TABLE `web_user_tourists` (`id` int(10) NOT NULL AUTO_INCREMENT, `webuser` int(10) NOT NULL, `last_name` varchar(512) NOT NULL, `first_name` varchar(512) NOT NULL, `patronomic` varchar(512) DEFAULT NULL, `pob` varchar(2048) NOT NULL, `gender` tinyint(1) NOT NULL, `dob` int(10) NOT NULL, `phone` varchar(100) NOT NULL, `mobile_phone` varchar(100) NOT NULL, `email` varchar(512) NOT NULL, `passport_serial` int(10) NOT NULL, `passport_num` int(10) NOT NULL, `passport_issue_date` date NOT NULL, `passport_issue_deportament` varchar(4096) NOT NULL, `date` datetime NOT NULL, `enabled` enum('0','1') NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE = MyISAM DEFAULT CHARSET = utf8;",
        "DROP TABLE IF EXISTS `web_user_tourists_files`;",
        "CREATE TABLE `web_user_tourists_files` (`id` int(10) NOT NULL AUTO_INCREMENT, `tourist_id` int(10) NOT NULL, `file_name` varchar(64) NOT NULL, `real_file_name` varchar(512) NOT NULL, `mime` varchar(256) NOT NULL, `size` int(10) NOT NULL, `date` datetime NOT NULL, PRIMARY KEY (`id`)) ENGINE = MyISAM DEFAULT CHARSET = utf8;",
    ];

    protected $sourcesWebUsers = [
        'admin' => [
            'password' => '9b71e4f215c66e6c6b923855802357a5',
            'attributes' => [
                [
                    'internalKey' => '^^web_users0^^',
                    'fullname' => 'admin',
                    'role' => 1,
                    'email' => 'admin@host.local',
                ],
            ],
            'settings' => [
                [
                    'setting_name' => 'last-name',
                    'setting_value' => '',
                ],
                [
                    'setting_name' => 'first-name',
                    'setting_value' => 'Администратор',
                ],
                [
                    'setting_name' => 'pss',
                    'setting_value' => '5kTYm4pX7G',
                ],
            ],
            'groups' => [
                [
                    'webgroup' => 1,
                ],
            ],
        ],
    ];

    public function __construct()
    {
        $this->modx = ModxProvider::getModx();
        $this->timestamp = time();

        $this->log('Добро пожаловать в мастер установки личного кабинета v.' . App::VERSION . ' (' . APP::ENV_VERSION . ').');
        $this->log('Автор: ' . App::AUTHOR . ' (<a href="mailto:' . APP::AUTHOR_EMAIL . '">' . APP::AUTHOR_EMAIL . '</a>).');
    }

    public function autoInstall()
    {
        switch ($this->getCurrentStep()) {
            case self::STEP_WELCOME:
            default:
                $this->stepWelcome();
                break;
            case self::STEP_BACKUP:
                $this->stepBackup();
                break;
            case self::STEP_INSTALL:
                $this->stepInstall();
                break;
            case self::STEP_DONE:
                $this->stepDone();
                break;
        }
    }

    protected function stepWelcome()
    {
        $this->log();
        $this->log('Вы уже сделали backup базы данных?');
        $this->log('<a href="?step=install">Да</a> / <a href="?step=backup">Нет</a>');
    }

    protected function stepBackup()
    {
        $this->log();
        $this->log('Для создания backup вы можете воспользоваться пакетом <a href="adminer.php" target="_blank">Adminer</a>.');
        $this->log('Вы уже сделали backup базы данных?');
        $this->log('<a href="?step=install">Да</a> / <a href="?step=backup">Нет</a>');
    }

    protected function stepInstall()
    {
        $this->log();
        $this->log('Начало установки...');

        $this->installTemplates();
        $this->installResources();
        $this->installPlugins();
        $this->installDump();
        $this->cleanCache();
        $this->cleanInstaller();

        $this->stepDone();
    }

    protected function stepDone()
    {
        $this->log();
        $this->log('Установка завершена...');
        $this->log();
        $this->log('========================================');
        $this->log();
        $this->log('1) Убедитесь, что каталог "' . realpath(dirname(__FILE__)) . '" удалён после процесса установки. Если это не произошло автоматически - удалите вручную!');
        $this->log('2) Убедитесь, что кеш так же был очищен автоматически. Если этого не произошло - необходимо авторизироваться в панели администратора и удалить кеш вручную!');
        $this->log('3) Проверьте, нормально ли работает установленный кабинет:');
        $this->log(' - Форма авторизации: <strong><a href="' . App::init()->makeUrl('/{lk}/') . '" taget="_blank">ссылка</a></strong>');
        $this->log(' - Логин администратора: <strong>admin</strong>');
        $this->log(' - Пароль администратора: <strong>5kTYm4pX7G</strong>');
        $this->log();
        $this->log('В базе пользователей ЛК есть только администратор!');
        $this->log();
        $this->log('========================================');
        $this->log();
        $this->log('<a href="/">Перейти на Главную страницу</a>');
        $this->log('<a href="/admin/">Перейти на Панель администратора</a>');
        $this->log();
        $this->log('========================================');
        $this->log();
    }

    protected function installTemplates()
    {
        $this->log();
        $this->log('Установка шаблонов...');

        if (is_array($this->sourcesTemplates) and !empty($this->sourcesTemplates)) {
            foreach ($this->sourcesTemplates as $template => $sourcesTemplate) {
                $templateId = 0;

                $templatesResource = $this->modx->db->select('*', $this->modx->getFullTableName('site_templates'), "`templatename` = '" . $sourcesTemplate['templatename'] . "'");

                if ($this->modx->db->getRecordCount($templatesResource)) {
                    $result = $this->modx->db->getRow($templatesResource);
                    $templateId = (integer) $result['id'];

                    if ($templateId) {
                        if ($this->modx->db->update(array_merge($sourcesTemplate, [
                            'editedon' => $this->timestamp,
                        ]), $this->modx->getFullTableName('site_templates'), "id = '" . $templateId . "'")) {
                            $this->log('Шаблон [' . $templateId . '] обновлён.');
                        } else {
                            $this->log('Обновление шаблона [' . $templateId . '] не требуется.');
                        }
                    }
                } else {
                    if ($this->modx->db->insert(array_merge($sourcesTemplate, [
                        'createdon' => $this->timestamp,
                        'editedon' => $this->timestamp,
                    ]), $this->modx->getFullTableName('site_templates'))) {
                        $templateId = (integer) $this->modx->db->getInsertId();
                        $this->log('Шаблон [' . $templateId . '] установлен.');
                    }
                }

                if ($templateId > 0) {
                    $this->sourcesTemplates[$template]['id'] = $templateId;
                }
            }
        }

        $this->log('Установка шаблонов завершена!');
    }

    protected function installResources()
    {
        $this->log();
        $this->log('Установка ресурсов...');

        if (is_array($this->sourcesResources) and !empty($this->sourcesResources)) {
            foreach ($this->sourcesResources as $name => $sourceResource) {
                $resourceId = 0;

                $resource = $this->modx->db->select('*', $this->modx->getFullTableName('site_content'), "`alias` = '" . $sourceResource['alias'] . "' AND `parent` = '" . $sourceResource['parent'] . "' AND `isfolder` = '" . $sourceResource['isfolder'] . "'");

                if ($this->modx->db->getRecordCount($resource)) {
                    $result = $this->modx->db->getRow($resource);
                    $resourceId = (integer) $result['id'];

                    if ($resourceId) {
                        if ($this->modx->db->update(array_merge($sourceResource, [
                            'template' => $this->sourcesTemplates['main']['id'],
                            'editedon' => $this->timestamp,
                        ]), $this->modx->getFullTableName('site_content'), "id = '" . $resourceId . "'")) {
                            $this->log('Ресурс [' . $resourceId . '] обновлён.');
                        } else {
                            $this->log('Обновление ресурса [' . $resourceId . '] не требуется.');
                        }
                    }
                } else {
                    if ($this->modx->db->insert(array_merge($sourceResource, [
                        'template' => $this->sourcesTemplates['main']['id'],
                        'createdon' => $this->timestamp,
                        'editedon' => $this->timestamp,
                        'publishedon' => $this->timestamp,
                    ]), $this->modx->getFullTableName('site_content'))) {
                        $resourceId = (integer) $this->modx->db->getInsertId();
                        $this->log('Ресурс [' . $resourceId . '] установлен.');
                    }
                }

                if ($resourceId > 0) {
                    $this->sourcesResources[$name]['id'] = $resourceId;
                }

                if ($name === 'main') {
                    $config = file_get_contents(App::getPublicRoot('/configs/MainConfig.php'));
                    $config = preg_replace('/(public\s*\$handlePage)[^\;]*\;/i', '${1} = ' . $resourceId . '${3};', $config);
                    if (file_put_contents(App::getPublicRoot('/configs/MainConfig.php'), $config)) {
                        App::init()->setConfig(new MainConfig);
                        $this->log('Файл конфигурации "' . App::getPublicRoot('/configs/MainConfig.php') . '" обновлён.');
                    }
                }
            }
        }

        $this->log('Установка ресурсов завершена!');
    }

    protected function installPlugins()
    {
        $this->log();
        $this->log('Установка плагинов...');

        if (is_array($this->sourcesPlugins) and !empty($this->sourcesPlugins)) {
            foreach ($this->sourcesPlugins as $name => $sourcePlugin) {
                $pliginId = 0;

                $hooks = $sourcePlugin['hooks'];
                unset($sourcePlugin['hooks']);

                $pluginsResource = $this->modx->db->select('*', $this->modx->getFullTableName('site_plugins'), "`name` = '" . $name . "'");

                if ($this->modx->db->getRecordCount($pluginsResource)) {
                    $result = $this->modx->db->getRow($pluginsResource);
                    $pliginId = (integer) $result['id'];

                    if ($pliginId) {
                        if ($this->modx->db->update(array_merge($sourcePlugin, [
                            'editedon' => $this->timestamp,
                        ]), $this->modx->getFullTableName('site_plugins'), "id = '" . $pliginId . "'")) {
                            $this->log('Плагин [' . $pliginId . '] обновлён.');
                        } else {
                            $this->log('Обновление плагина [' . $pliginId . '] не требуется.');
                        }
                    }
                } else {
                    if ($this->modx->db->insert(array_merge($sourcePlugin, [
                        'name' => $name,
                        'createdon' => $this->timestamp,
                        'editedon' => $this->timestamp,
                    ]), $this->modx->getFullTableName('site_plugins'))) {
                        $pliginId = (integer) $this->modx->db->getInsertId();
                        $this->log('Плагин [' . $pliginId . '] установлен.');
                    }
                }

                if ($pliginId > 0) {
                    $this->sourcesPlugins[$name]['id'] = $pliginId;

                    if (is_array($hooks) and !empty($hooks)) {
                        foreach ($hooks as $index => $hook) {
                            $pluginHooksResource = $this->modx->db->select('*', $this->modx->getFullTableName('site_plugin_events'), "`pluginid` = '" . $pliginId . "' AND `evtid` = '" . $hook['evtid'] . "'");

                            if ($this->modx->db->getRecordCount($pluginHooksResource)) {
                                if ($this->modx->db->update($hook, $this->modx->getFullTableName('site_plugin_events'), "pluginid = '" . $pliginId . "' AND `evtid` = '" . $hook['evtid'] . "'")) {
                                    $this->log('Хук [' . $hook['evtid'] . '] плагина [' . $pliginId . '] обновлён.');
                                } else {
                                    $this->log('Обновление хука [' . $hook['evtid'] . '] плагина [' . $pliginId . '] не требуется.');
                                }
                            } else {
                                $this->modx->db->insert(array_merge($hook, [
                                    'pluginid' => $pliginId,
                                ]), $this->modx->getFullTableName('site_plugin_events'));
                                $this->log('Хук [' . $hook['evtid'] . '] плагина [' . $pliginId . '] установлен.');
                            }

                            $this->sourcesPlugins[$name]['hooks'][$index]['pluginid'] = $pliginId;
                        }
                    }
                }
            }
        }

        $this->log('Установка плагинов завершена!');
    }

    protected function installDump()
    {
        $this->log();
        $this->log('Установка дампа в базу данных...');

        $queryCounts = 0;

        if (is_array($this->sourceDump) and !empty($this->sourceDump)) {
            foreach ($this->sourceDump as $query) {
                preg_match('/^DROP\s*TABLE\s*IF\s*EXISTS\s*\`([^\`]+)\`/i', $query, $matches);
                if (is_array($matches) and !empty($matches)) {
                    array_shift($matches);
                    $query = str_replace($matches[0], trim($this->modx->getFullTableName($matches[0]), '`'), $query);
                }
                preg_match('/^CREATE\s*TABLE\s*\`([^\`]+)\`/i', $query, $matches);
                if (is_array($matches) and !empty($matches)) {
                    array_shift($matches);
                    $query = str_replace($matches[0], trim($this->modx->getFullTableName($matches[0]), '`'), $query);
                }
                preg_match('/^INSERT\s*INTO\s*\`([^\`]+)\`/i', $query, $matches);
                if (is_array($matches) and !empty($matches)) {
                    array_shift($matches);
                    $query = str_replace($matches[0], trim($this->modx->getFullTableName($matches[0]), '`'), $query);
                }

                if ($this->modx->db->query($query)) {
                    $queryCounts++;
                }
            }
        }

        if (is_array($this->sourcesWebUsers) and !empty($this->sourcesWebUsers)) {
            foreach ($this->sourcesWebUsers as $username => $sourceWebUser) {
                $webUserId = 0;

                $attributes = $sourceWebUser['attributes'];
                $settings = $sourceWebUser['settings'];
                $groups = $sourceWebUser['groups'];
                unset($sourceWebUser['attributes']);
                unset($sourceWebUser['settings']);
                unset($sourceWebUser['groups']);

                $webUsersResource = $this->modx->db->select('*', $this->modx->getFullTableName('web_users'), "`username` = '" . $username . "'");

                if ($this->modx->db->getRecordCount($webUsersResource)) {
                    $result = $this->modx->db->getRow($webUsersResource);
                    $webUserId = (integer) $result['id'];

                    if ($webUserId) {
                        if ($this->modx->db->update($sourceWebUser, $this->modx->getFullTableName('web_users'), "id = '" . $webUserId . "'")) {
                            $this->log('Пользователь [' . $webUserId . '] обновлён.');
                        } else {
                            $this->log('Обновление пользователя [' . $webUserId . '] не требуется.');
                        }
                    }
                } else {
                    if ($this->modx->db->insert(array_merge($sourceWebUser, [
                        'username' => $username,
                    ]), $this->modx->getFullTableName('web_users'))) {
                        $webUserId = (integer) $this->modx->db->getInsertId();
                        $this->log('Пользователь [' . $webUserId . '] добавлен.');
                    }
                }

                if ($webUserId > 0) {
                    $this->sourcesWebUsers[$username]['id'] = $webUserId;

                    if (is_array($attributes) and !empty($attributes)) {
                        foreach ($attributes as $index => $attribute) {
                            $attributeId = 0;

                            $attributesResource = $this->modx->db->select('*', $this->modx->getFullTableName('web_user_attributes'), "`internalKey` = '" . $webUserId . "'");

                            if ($this->modx->db->getRecordCount($attributesResource)) {
                                $result = $this->modx->db->getRow($attributesResource);
                                $attributeId = (integer) $result['id'];

                                if ($attributeId) {
                                    if ($this->modx->db->update($attribute, $this->modx->getFullTableName('web_user_attributes'), "id = '" . $attributeId . "'")) {
                                        $this->log('Аттрибут [' . $attributeId . '] обновлён.');
                                    } else {
                                        $this->log('Обновление аттрибута [' . $attributeId . '] не требуется.');
                                    }
                                }
                            } else {
                                if ($this->modx->db->insert(array_merge($attribute, [
                                    'internalKey' => $webUserId,
                                ]), $this->modx->getFullTableName('web_user_attributes'))) {
                                    $attributeId = (integer) $this->modx->db->getInsertId();
                                    $this->log('Аттрибут [' . $attributeId . '] добавлен.');
                                }
                            }

                            if ($attributeId > 0) {
                                $this->sourcesWebUsers[$username]['attibutes'][$index]['id'] = $attributeId;
                            }
                        }
                    }

                    if (is_array($settings) and !empty($settings)) {
                        foreach ($settings as $index => $setting) {
                            $settingsResource = $this->modx->db->select('*', $this->modx->getFullTableName('web_user_settings'), "`webuser` = '" . $webUserId . "' AND `setting_name` = '" . $setting['setting_name'] . "'");

                            if ($this->modx->db->getRecordCount($settingsResource)) {
                                $result = $this->modx->db->getRow($settingsResource);

                                if ($this->modx->db->update($setting, $this->modx->getFullTableName('web_user_settings'), "id = '" . $settingId . "'")) {
                                    $this->log('Сеттинг [' . $setting['setting_name'] . '] пользователя [' . $webUserId . '] обновлён.');
                                } else {
                                    $this->log('Обновление сеттинга [' . $setting['setting_name'] . '] пользователя [' . $webUserId . '] не требуется.');
                                }
                            } else {
                                $this->modx->db->insert(array_merge($setting, [
                                    'webuser' => $webUserId,
                                ]), $this->modx->getFullTableName('web_user_settings'));
                                $this->log('Сеттинг [' . $setting['setting_name'] . '] пользователя [' . $webUserId . '] добавлен.');
                            }
                        }
                    }

                    if (is_array($groups) and !empty($groups)) {
                        foreach ($groups as $index => $group) {
                            $groupId = 0;

                            $groupsResource = $this->modx->db->select('*', $this->modx->getFullTableName('web_groups'), "`webuser` = '" . $webUserId . "' AND `webgroup` = '" . $group['webgroup'] . "'");

                            if ($this->modx->db->getRecordCount($groupsResource)) {
                                $result = $this->modx->db->getRow($groupsResource);
                                $groupId = (integer) $result['id'];

                                if ($groupId) {
                                    if ($this->modx->db->update($group, $this->modx->getFullTableName('web_groups'), "id = '" . $groupId . "'")) {
                                        $this->log('Группа [' . $groupId . '] обновлёна.');
                                    } else {
                                        $this->log('Обновление группы [' . $groupId . '] не требуется.');
                                    }
                                }
                            } else {
                                if ($this->modx->db->insert(array_merge($group, [
                                    'webuser' => $webUserId,
                                ]), $this->modx->getFullTableName('web_groups'))) {
                                    $groupId = (integer) $this->modx->db->getInsertId();
                                    $this->log('Группа [' . $groupId . '] добавлена.');
                                }
                            }

                            if ($groupId > 0) {
                                $this->sourcesWebUsers[$username]['groups'][$index]['id'] = $groupId;
                            }
                        }
                    }
                }
            }
        }

        $this->log('Установка дампа завершена.');
    }

    protected function cleanCache()
    {
        $this->log();
        $this->log('Очистка кеша...');

        $cachePath = realpath($_SERVER['DOCUMENT_ROOT'] . '/assets/cache/');
        $cacheFiles = scandir($cachePath);

        if (is_array($cacheFiles) and !empty($cacheFiles)) {
            foreach ($cacheFiles as $index => $cacheFile) {
                if (!is_file(realpath($cachePath . '/' . $cacheFile))) {
                    unset($cacheFiles[$index]);
                } else {
                    preg_match('/^(.*)\.php$/i', $cacheFile, $matches);
                    if (!is_array($matches) or empty($matches)) {
                        unset($cacheFiles[$index]);
                    }
                    if (in_array($cacheFile, ['siteManager.php'])) {
                        unset($cacheFiles[$index]);
                    }
                }
            }
        }

        if (is_array($cacheFiles) and !empty($cacheFiles)) {
            foreach ($cacheFiles as $cacheFile) {
                $this->log('Удаление файл кеша "' . realpath($cachePath . '/' . $cacheFile) . '".');
                unlink(realpath($cachePath . '/' . $cacheFile));
            }
        }

        $this->log('Очистка кеша завершена!');
    }

    protected function cleanInstaller()
    {
        $this->log();
        $this->log('Удаление инсталлятора...');

        $this->removeDirRecursive(App::getPublicRoot('/install'));

        $this->log('Удаление инстяллятора завершено!');
        
    }

    protected function getCurrentStep()
    {
        if (array_key_exists('step', $_GET)) {
            return $_GET['step'];
        }
        return self::STEP_WELCOME;
    }

    protected function log($message = '')
    {
        echo date('Y-m-d H:i:s') . '> ' . $message . PHP_EOL;
    }

    protected function removeDirRecursive($path)
    {
        $directory = opendir($path);
        while (($entry = readdir($directory)) !== false) {
            if (!in_array($entry, ['.', '..'])) {
                if (is_dir(realpath($path . '/' . $entry))) {
                    $this->removeDirRecursive(realpath($path . '/' . $entry));
                } else {
                    unlink(realpath($path . '/' . $entry));
                }
            }
        }

        closedir($directory);
        rmdir($path);
    }
}
