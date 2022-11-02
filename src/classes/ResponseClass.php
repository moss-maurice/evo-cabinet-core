<?php

namespace mmaurice\cabinet\core\classes;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\exceptions\WebExceptions;
use mmaurice\cabinet\core\prototypes\ClassPrototype;

class ResponseClass extends ClassPrototype
{
    /**
     * Метод рендеринга файла шаблона
     *
     * @param string $__tplName__
     * @param array $__variables__
     * @param boolean $__return__
     * @return void
     */
    public function renderTemplate($__tplName__, $__variables__ = [])
    {
        try {
            $__variables__['webRoot'] = App::getPublicWebRoot();
            
            $__tplPath__ = realpath($__tplName__);
            if (!file_exists($__tplPath__) or !is_file($__tplPath__)) {
                throw new WebExceptions('Template file "' . $__tplName__ . '" is not found!');
            }
            if (is_array($__variables__) and !empty($__variables__)) {
                extract($__variables__, EXTR_PREFIX_SAME, 'data');
            } else {
                $data = $__variables__;
            }
            ob_start();
            ob_implicit_flush(false);
            include($__tplPath__);
            $content = ob_get_clean();
            return $content;
        } catch (WebExceptions $exceptiob) {
            echo $exceptiob->getMessage();
        }
    }

    public function renderChunk($chunkName, $variables = [])
    {
        return ModxProvider::getConfig($chunkName, $variables);
    }

    /**
     * Метод редиректа
     *
     * @param string $url
     * @return void
     */
    public function redirect($url)
    {
        return ModxProvider::redirect($url);
    }

    /**
     * Метод возврата результата выполнения AJAX в формате JSON
     *
     * @param mixed $data
     * @return void
     */
    public function ajaxReturnJson($data)
    {
        echo json_encode($data);
        App::init()->end();
    }
}
