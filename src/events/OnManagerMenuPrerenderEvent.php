<?php

namespace mmaurice\cabinet\events;

use mmaurice\cabinet\core\prototypes\EventPrototype;
//use mmaurice\cabinet\core\classes\AppClass;
//use mmaurice\cabinet\core\providers\ModxProvider;

/**
 * Событие OnManagerMenuPrerenderEvent
 * 
 * Срабатывает при попытке отрисовке главного меню панели администратора.
 * Тут можно реализовать вывод пользовательского пункта меню в панели администратора, ведущего на кастомный модуль.
 */
class OnManagerMenuPrerenderEvent extends EventPrototype
{
    public function __construct(AppClass $app)
    {
        parent::__construct($app);

        $this->initModuleTab();
    }

    protected function initModuleTab()
    {
        /*
        Добавить модуль в общую навигацию
        ModxProvider::modxInit();
        $modx = ModxProvider::getModx();

        //$_customlang = include MODX_BASE_PATH . 'assets/modules/clientsettings/lang.php';
        //$userlang = $modx->getConfig('manager_language');
        //$langitem = isset($_customlang[$userlang]) ? $_customlang[$userlang] : reset($_customlang);

        //$moduleid = $modx->db->getValue(
        //$modx->db->select('id', $modx->getFullTablename('site_modules'), "name = 'Туроператор'"));

        $langitem = 'Туроператор';
        $moduleid = 9;

        $params['menu']['turoperator'] = [
            'turoperator',
            'main',
            '<i class="fa fa-cog"></i>' . $langitem,
            'index.php?a=112&id=' . $moduleid,
            $langitem,
            '',
            '',
            'main',
            0,
            100,
            '',
        ];

        $modx->event->output(serialize($params['menu']));
        */
    }
}
