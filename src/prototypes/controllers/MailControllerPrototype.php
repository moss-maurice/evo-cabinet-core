<?php

namespace mmaurice\cabinet\core\prototypes\controllers;

use mmaurice\cabinet\core\App;
use mmaurice\cabinet\core\helpers\MailerHelper;
use mmaurice\cabinet\core\prototypes\controllers\ControllerPrototype;

class MailControllerPrototype extends ControllerPrototype
{
    public function __construct()
    {
        return;
    }

    protected function renderMail($email, $subject, $templateName, $parametrs = [], $copyToAdmin = false)
    {
        $controllerName = App::init()->extractControllerName(get_called_class());

        $content = MailerHelper::renderTemplate($templateName, $parametrs);

        $layout = MailerHelper::renderTemplate('layouts/' . $this->layout, array_merge($parametrs, [
            'content' => $content,
        ]));

        return MailerHelper::send($email, $subject, $layout, $copyToAdmin);
    }
}
