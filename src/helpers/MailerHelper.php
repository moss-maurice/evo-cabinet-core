<?php

namespace mmaurice\cabinet\core\helpers;

use mmaurice\cabinet\core\App;

/**
 * Хэлпер для форматирования сообщений
 */
class MailerHelper
{
    /**
     * Метод отправки почты
     * 
     * Метод автоматически определяет настроенный метод отправки и использует его для отправки письма
     * 
     * Рекомендуется отправлять почту только этим методом
     *
     * @param  string $address          - Адрес получателя письма
     * @param  string $subject          - Заголовок письма
     * @param  string $body             - HTML-тело письма
     * @param  bool   $copyToAdmin      - Флаг отправки копии письма администратору (по-умолчанию, false)
     *
     * @return bool
     */
    static public function send($address, $subject, $body, $copyToAdmin = false)
    {
        global $modx;

        if ($modx->getConfig('client_siteEmail') === 'smtp') {
            // Отправка методом SMTP (необходимо настроить SMTP-подключение)

            return static::sendFromSmtp($address, $subject, $body, $copyToAdmin);
        } else {
            // Отправка методом встроенной функции mail()
            // Если явным образом не выбран SMTP-метод, всегда будет использован этот метод

            return static::sendFromMail($address, $subject, $body, $copyToAdmin);
        }

        return false;
    }

    /**
     * Отправка почты методом SMTP
     * 
     * ОЧЕНЬ ВАЖНОЕ ДОПОЛНЕНИЕ !!!
     * 
     * Если ты это читаешь, то, возможно, у тебя проблемы с почтой и проводишь отладку!
     * Но, перед тем как что-то тут править, прочитай эту нотификацию!
     * 
     * Не каждый SMTP-сервер будет работать с паролем от почтового аккаунта.
     * Зачастую, для подключения к SMTP-серверу, нужно выпускать отдельный пароль для приложений.
     * На примере Яндекса - нужен пароль приложений ПОЧТА -> ВСЕ ПРОГРАММЫ -> ПОЧТОВЫЕ ПРОГРАММЫ
     * Так же важно, что при сохранении пароля SMTP в настройках - в БД он шифруется.
     * Чтобы пароль корректно выходил из БД, не нужно его задавать в явном виде в переменной $modx->mail->Password
     * В явном виде пароль нужно задавать только если он отличается от того, что указан в настройках сайта
     * 
     * Подробно тут https://snipp.ru/php/smtp-phpmailer#link-yandeks-pochta
     * 
     * Совершенно рабочий инструмент для тестирования SMTP есть в виде модуля в панели администратора
     * Чтобы его открыть, нужно перейти в админку -> МОДУЛИ -> ТЕСТИРОВАНИЕ -> SMTP
     * Этот инструмент точно работает. И если что-то не работает - проблему надо искать в доступах, а точнее в пароле.
     *
     * @param  string $address          - Адрес получателя письма
     * @param  string $subject          - Заголовок письма
     * @param  string $body             - HTML-тело письма
     * @param  bool   $copyToAdmin      - Флаг отправки копии письма администратору (по-умолчанию, false)
     *
     * @return bool
     */
    static protected function sendFromSmtp($address, $subject, $body, $copyToAdmin = false)
    {
        global $modx;

        $modx->loadExtension('MODxMailer');

        $modx->mail->IsHTML(true);
        $modx->mail->From = $modx->config['smtp_username'];
        $modx->mail->FromName = $modx->config['client_siteName'];
        //$modx->mail->SMTPSecure = 'ssl';
        $modx->mail->Subject = $subject;
        $modx->mail->msgHTML($body);
        $modx->mail->ClearAllRecipients();
        $modx->mail->AddAddress($address);
        $modx->mail->SMTPDebug = 3;

        if ($copyToAdmin and isset($modx->config['client_siteEmail']) and !empty($modx->config['client_siteEmail'])) {
            $modx->mail->AddCC($modx->config['client_siteEmail'], isset($modx->config['client_siteName']) ? $modx->config['client_siteName'] : '');
        }

        ob_start();

        $result = $modx->mail->send();
        $log = ob_get_clean();

        if (!$result) {
            //var_dump($modx->mail->ErrorInfo);
            //var_dump($log);
            return false;
        }

        return true;
    }

    /**
     * Отправка почты методом Mail
     *
     * @param  string $address          - Адрес получателя письма
     * @param  string $subject          - Заголовок письма
     * @param  string $body             - HTML-тело письма
     * @param  bool   $copyToAdmin      - Флаг отправки копии письма администратору (по-умолчанию, false)
     *
     * @return bool
     */
    static protected function sendFromMail($address, $subject, $body, $copyToAdmin = false)
    {
        global $modx;

        $modx->loadExtension('MODxMailer');

        $modx->mail->IsHTML(true);
        $modx->mail->setFrom($modx->config['site_name'], $modx->config['emailsender']);
        $modx->mail->Subject = $subject;
        $modx->mail->msgHTML($body);
        $modx->mail->ClearAllRecipients();
        $modx->mail->AddAddress($address);

        if ($copyToAdmin and isset($modx->config['client_siteEmail']) and !empty($modx->config['client_siteEmail'])) {
            $modx->mail->AddCC($modx->config['client_siteEmail'], isset($modx->config['client_siteName']) ? $modx->config['client_siteName'] : '');
        }

        $result = $modx->mail->send();

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Метод рендеринга файла шаблона
     *
     * @param  string $view             - Имя файла шаблона
     * @param  array  $parametrs        - Массив переменных шаблона (по-умолчанию, пустой массив)
     *
     * @return mixed
     */
    static public function renderTemplate($view, $parametrs = [])
    {
        if (file_exists(App::getPublicRoot('/mails/' . $view . '.php'))) {
            return App::response()->renderTemplate(App::getPublicRoot('/mails/' . $view . '.php'), $parametrs);
        } else if (file_exists(App::getCoreRoot('/mails/' . $view . '.php'))) {
            return App::response()->renderTemplate(App::getCoreRoot('/mails/' . $view . '.php'), $parametrs);
        }

        return false;
    }

    /**
     * Метод валидации Email-адреса
     *
     * @param  string $email            - Адрес электронной почты
     *
     * @return void
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
