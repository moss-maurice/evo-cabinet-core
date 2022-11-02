<?php

namespace mmaurice\cabinet\core\prototypes\widgets;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\prototypes\SingletonPrototype;

/**
 * Класс-прототип виджета
 */
class WidgetPrototype extends SingletonPrototype
{
    public $template;

    /**
     * Метод конструктора класса
     *
     * @param array $parametrs
     * @return void
     */
    public function __construct($parametrs = [])
    {
        if (is_array($parametrs) and !empty($parametrs)) {
            foreach ($parametrs as $parametrName => $parametrValue) {
                if (property_exists($this, $parametrName)) {
                    $this->$parametrName = $parametrValue;
                }
            }
        }
    }

    static public function init($parametrs = [])
    {
        $className = get_called_class();

        return new $className($parametrs);
    }

    /**
     * Метод рендеринга вида
     *
     * @param string $templateName
     * @param array $parametrs
     * @param boolean $show
     * @return void
     */
    protected function render($templateName, $parametrs = [])
    {
        $widgetName = App::init()->extractWidgetName(get_called_class());
        $content = App::response()->renderTemplate(App::getPublicRoot() . '/widgets/views/' . $widgetName . '/' . $templateName . '.php', $parametrs);

        echo $content;
    }

    /**
     * Метод основной логики виджета
     *
     * @return void
     */
    public function run()
    {
        return $this->render($this->template, []);
    }
}
