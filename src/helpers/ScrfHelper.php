<?php

namespace mmaurice\cabinet\core\helpers;

use mmaurice\cabinet\core\configs\MainConfig;
use mmaurice\cabinet\core\App;

class ScrfHelper
{
    const PREFIX = '_csrfToken';
    const WIPE_TIMER = 5;

    static protected $permittedChars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    static public function generate()
    {
        if (!array_key_exists(self::PREFIX . 'Wipe', $_SESSION) or (array_key_exists(self::PREFIX . 'Wipe', $_SESSION) and (time() > $_SESSION[self::PREFIX . 'Wipe']))) {
            self::wipe();
        }

        if (!array_key_exists(self::PREFIX, $_SESSION)) {
            $hash = self::generateHash(mt_rand(11, 15));

            $_SESSION[self::PREFIX] = self::generateToken($hash);
            $_SESSION[self::PREFIX . 'Hash'] = $hash;
            $_SESSION[self::PREFIX . 'Wipe'] = time() + self::WIPE_TIMER;
        }

        return $_SESSION[self::PREFIX . 'Hash'];
    }

    static public function check($hash)
    {
        $token = self::generateToken($hash);

        if (array_key_exists(self::PREFIX, $_SESSION) and !empty($_SESSION[self::PREFIX]) and ($_SESSION[self::PREFIX] === $token)) {
            self::wipe();

            return true;
        }

        return false;
    }

    static public function wipe()
    {
        unset($_SESSION[self::PREFIX]);
        unset($_SESSION[self::PREFIX . 'Hash']);
        unset($_SESSION[self::PREFIX . 'Wipe']);
    }

    static public function checkPost()
    {
        $hash = App::request()->extractPost(self::PREFIX);

        return self::check($hash);
    }

    static protected function generateToken($hash, $csrf = null)
    {
        if (is_null($csrf)) {
            $csrf = (new MainConfig)->csrfToken;
        }

        return md5($hash . $csrf);
    }

    static protected function generateHash($length = 10, $permittedChars = null)
    {
        if (is_null($permittedChars)) {
            $permittedChars = self::$permittedChars;
        }

        $permittedCharsLength = strlen($permittedChars);

        $salt = '';

        for ($i = 0; $i < $length; $i++) {
            $salt .= $permittedChars[mt_rand(0, $permittedCharsLength - 1)];
        }

        return $salt;
    }
}
