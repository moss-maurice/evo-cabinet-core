<?php

namespace mmaurice\cabinet\core\prototypes\controllers;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\classes\RequestClass;
use mmaurice\cabinet\core\providers\ModxProvider;

class ControllerPrototype
{
    public $layout = 'main';
    public $template;
    public $breadcrumbs = array();
    public $title = '';

    /**
     * Метод конструктора класса
     * 
     * @return void
     */
    public function __construct()
    {
        $this->breadcrumbs = App::init()->getConfig('breadcrumbsBase', array());
        $this->title = 'Page "' . get_called_class() . '".';

        $this->beforeAction(RequestClass::$method);

        return;
    }

    public static function controller()
    {
        $class = get_called_class();

        return new $class;
    }

    public function beforeAction($action)
    {
        return;
    }

    public function actionRun()
    {
        return $this->render($this->template, array(), false);
    }

    /**
     * Метод рендеринга вида
     *
     * @param string $templateName
     * @param array $parametrs
     * @return void
     */
    protected function render($templateName, $parametrs = [])
    {
        $controllerName = App::init()->extractControllerName(get_called_class());

        $content = App::response()->renderTemplate(App::getPublicRoot('/views/' . $controllerName . '/' . $templateName . '.php'), $parametrs);

        $layout = App::response()->renderTemplate(App::getPublicRoot('/views/layouts/' . $this->layout . '.php'), array_merge($parametrs, ['content' => $content]));

        ModxProvider::sendForward(App::init()->getConfig('handlePage'), $layout);
    }

    protected function renderAjax($parametrs = [])
    {
        header('Content-type: application/json; charset=utf-8');

        echo json_encode($parametrs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        App::init()->end();
    }
}
