<?php

namespace mmaurice\cabinet\core\events;

use Bramus\Router\Router;
use mmaurice\cabinet\core\classes\AppClass;
use mmaurice\cabinet\core\classes\RequestClass;
use mmaurice\cabinet\core\configs\RoutesConfig;

class OnPageNotFoundEvent extends mmaurice\cabinet\core\prototypes\EventPrototype
{
    public function __construct(AppClass $app)
    {
        parent::__construct($app);

        $this->initMatchRoutes($app);
    }

    protected function initMatchRoutes(AppClass $app)
    {
        $routesConfig = new RoutesConfig;

        $sources = array_merge($routesConfig->source, $routesConfig->sourceIndex);

        if (is_array($sources) and !empty($sources)) {
            $router = new Router;

            $router->before('GET|POST', '.*', function () {
                header('X-Powered-By: kreexus/modx-evo-lk');
            });

            foreach ($sources as $routeMap => $routeHandle) {
                $routeHandleArray = explode('/', $routeHandle);

                $className = $app::getControllerClassName($routeHandleArray[0]);
                $actionName = $app::getControllerActionName($routeHandleArray[1]);

                $map = $app->makeUrl($routeMap);

                $router->before('GET|POST', $map, function() use ($className, $actionName) {
                    RequestClass::$controller = $className;
                    RequestClass::$method = $actionName;
                });

                $router->match('GET|POST', $map, $className . '@' . $actionName);
            }

            $router->set404(function() {
                // do nothing
                return;
            });

            $router->run();
        }
    }
}
