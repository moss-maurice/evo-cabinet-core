<?php

namespace mmaurice\cabinet\core\classes;

use mmaurice\cabinet\core\exceptions\AjaxException;
use mmaurice\cabinet\core\exceptions\BaseException;
use mmaurice\cabinet\core\exceptions\CommandException;
use mmaurice\cabinet\core\prototypes\SingletonPrototype;
use mmaurice\cabinet\core\providers\ModxProvider;

class AppClass extends SingletonPrototype
{
    const VERSION = 0.1;
    const ENV_VERSION = 'develop';
    const AUTHOR = 'Viktor Voronkov';
    const AUTHOR_EMAIL = 'kreexus@yandex.ru';

    protected $config;

    public static function __callStatic($class, $arguments)
    {
        $className = '\\' . __NAMESPACE__ . '\\' . ucfirst($class) . 'Class';

        if (!class_exists($className)) {
            throw new BaseException('Class "' . $className . '" is not exists!');
        }

        return call_user_func_array($className . '::init', $arguments);
    }

    public static function init()
    {
        $object = parent::init();

        if (!array_key_exists('DOCUMENT_ROOT', $_SERVER) or empty($_SERVER['DOCUMENT_ROOT'])) {
            $_SERVER['DOCUMENT_ROOT'] = realpath('../../../');
        }

        ModxProvider::modxInit();

        return $object;
    }

    public function run($className, $methodName, $methodParams = [], $objectParams = [])
    {
        if (!class_exists($className)) {
            return false;
        }

        if (!method_exists($className, $methodName)) {
            return false;
        }

        $object = new $className($objectParams);

        $result = call_user_func_array(array($object, $methodName), $methodParams);

        return $result;
    }

    public function runAjaxHandler()
    {
        if (!array_key_exists('action', $_REQUEST)) {
            throw new AjaxException('Query param "action" is not defined!');
        }

        $controls = explode('/', $_REQUEST['action']);

        $className = self::getControllerClassName($controls[0]);
        $actionName = self::getControllerActionName($controls[1]);

        unset($_REQUEST['action']);
        unset($_POST['action']);
        unset($_GET['action']);

        $result = $this->run($className, $actionName);

        return $result;
    }

    public function runConsoleHandler()
    {
        global $argv;

        $arguments = $argv;

        array_shift($arguments);

        $controls = array_shift($arguments);
        $controls = explode('/', str_replace('\\', '/', $controls));

        $className = self::getCommandClassName($controls[0]);

        if (!$className) {
            $className = self::getCommandClassName('help');
            //throw new CommandException('Command "' . $controls[0] . '" not found! Please, use command "help" to see available commandss');
        }

        $actionName = self::getCommandActionName((is_null($controls[1]) ? 'index' : $controls[1]));

        $result = $this->run($className, $actionName, $arguments);

        return $result;
    }

    public function runWebHandler()
    {
        $modx = ModxProvider::getModx();

        $eventClass = $this->getEventClassName($modx->event->name);

        if ($eventClass) {
            return new $eventClass($this);
        }
    }

    /**
     * Метод вызова контроллера
     *
     * @param string $controllerName
     * @param string $action
     * @param array $params
     * @return void
     */
    public function runController($controllerName, $action, $params = [], $template = null)
    {
        $className = self::getControllerClassName($controllerName);
        $actionName = self::getControllerActionName($action);
        $result = $this->run($className, $actionName, $params, ['template' => $template]);

        return $result;
    }

    /**
     * Метод подключения виджета
     *
     * @param string $widgetName
     * @param array $params
     * @return void
     */
    public function runWidget($widgetName, $params = [], $template = null)
    {
        $className = self::getWidgetClassName($widgetName);
        $actionName = self::getWidgetActionName();

        $result = $this->run($className, $actionName, $params, ['template' => $template]);

        return $result;
    }

    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Метод получения тела конфигурационного файла
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return void
     */
    public function getConfig($key, $defaultValue = null)
    {
        if (property_exists($this->config, $key)) {
            return $this->config->$key;
        }

        return $defaultValue;
    }

    public function getModxConfig($key, $defaultValue = null)
    {
        return ModxProvider::getConfig($key, $defaultValue);
    }

    /**
     * Метод получения рабочего каталога на сервере
     *
     * @return void
     */
    public static function getRoot($path = '/')
    {
        return realpath(dirname(__FILE__) . '/../..' . $path);
    }

    /**
     * Метод получения рабочего каталога на веб-сервере
     *
     * @return void
     */
    public static function getWebRoot($path = '/')
    {
        return str_replace([$_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR, '//'], ['', '/', '/'], self::getRoot($path));
    }

    public static function getPublicRoot($path = '/')
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, realpath(dirname(__FILE__) . "/../../../../../app/{$path}"));
    }

    public static function getCoreRoot($path = '/')
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, realpath(dirname(__FILE__) . '/..') . $path);
    }

    public static function getPublicWebRoot($path = '/')
    {
        return str_replace([realpath($_SERVER['DOCUMENT_ROOT']), DIRECTORY_SEPARATOR, '//'], ['', '/', '/'], self::getPublicRoot($path));
    }

    public function isCliMode()
    {
        return ModxProvider::isCliMode();
    }

    public function redirect($url, $properties = [])
    {
        return ModxProvider::redirect($this->makeUrl($url, $properties));
    }

    /**
     * Метод принудительного завершения выполнения кода
     *
     * @return void
     */
    public function end($message = '')
    {
        if ($this->isCliMode()) {
            exit($message);
        }

        die($message);
    }

    public function getAppWebAlias()
    {
        $handlePageSource = ModxProvider::getDocument($this->getConfig('handlePage'));

        return trim($handlePageSource['alias'], '/');
        //return trim($this->getConfig('alias'), '/');
    }

    public function makeUrl($path = '/', $properties = [])
    {
        if (is_array($properties) and !empty($properties)) {
            $path .= '?' . http_build_query($properties);
        }

        $path = str_replace(['{lk}'], [$this->getAppWebAlias()], $path);
        $path = rtrim(rtrim(parse_url($path, PHP_URL_PATH), '/') . '?' . parse_url($path, PHP_URL_QUERY), '?');

        return $path;
    }

    public function extractControllerName($className)
    {
        $regexp = str_replace('\\', '\\\\', self::getControllerClassName('(.*)'));

        if (preg_match('/' . $regexp . '/i', '\\' . $className, $matches)) {
            array_shift($matches);

            return lcfirst($matches[0]);
        }

        return '';
    }

    public function extractWidgetName($className)
    {
        $regexp = str_replace('\\', '\\\\', self::getWidgetClassName('(.*)'));

        if (preg_match('/' . $regexp . '/i', '\\' . $className, $matches)) {
            array_shift($matches);

            return lcfirst($matches[0]);
        }

        return '';
    }

    public static function getEventClassName($eventClassName)
    {
        $class = str_replace('\\core\\classes\\', '\\events\\', '\\' . __NAMESPACE__ . '\\' . ucfirst($eventClassName) . 'Event');

        if (!class_exists($class)) {
            $class = str_replace('\\core\\classes\\', '\\core\\events\\', '\\' . __NAMESPACE__ . '\\' . ucfirst($eventClassName) . 'Event');
        }

        if (class_exists($class)) {
            return $class;
        }

        return false;
    }

    public static function getControllerClassName($controllerClassName)
    {
        $controllerClassName = trim($controllerClassName, "\/\\");

        $preparedControllerClassName = ucfirst($controllerClassName) . 'Controller';

        if (preg_match('/^(.*)\/([^\/\^]+)$/imU', $controllerClassName, $matches)) {
            $matches[2] = ucfirst($matches[2]);

            $preparedControllerClassName = "{$matches[1]}\\{$matches[2]}Controller";
        }

        return str_replace('\\core\\classes\\', '\\controllers\\', '\\' . __NAMESPACE__ . '\\' . $preparedControllerClassName);
    }

    public static function getControllerActionName($controllerActionName)
    {
        $controllerActionName = trim($controllerActionName, "\/\\");

        return 'action' . ucfirst($controllerActionName);
    }

    public static function getCommandClassName($commandClassName)
    {
        $class = str_replace('\\core\\classes\\', '\\commands\\', '\\' . __NAMESPACE__ . '\\' . ucfirst($commandClassName) . 'Command');

        if (!class_exists($class)) {
            $class = str_replace('\\core\\classes\\', '\\core\\commands\\', '\\' . __NAMESPACE__ . '\\' . ucfirst($commandClassName) . 'Command');
        }

        if (class_exists($class)) {
            return $class;
        }

        return false;
    }

    public static function getCommandActionName($commandActionName)
    {
        return 'action' . ucfirst($commandActionName);
    }

    public static function getWidgetClassName($widgetClassName)
    {
        return str_replace('\\core\\classes\\', '\\widgets\\', '\\' . __NAMESPACE__ . '\\' . ucfirst($widgetClassName) . 'Widget');
    }

    public static function getWidgetActionName()
    {
        return 'run';
    }
}
